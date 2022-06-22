<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Action for adding/editing a attempt.
 *
 * @package mod_solo
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/



require_once("../../../config.php");
require_once($CFG->dirroot.'/mod/solo/lib.php');

use \mod_solo\constants;
use \mod_solo\utils;

global $USER,$DB;

// first get the nfo passed in to set up the page
$attemptid = optional_param('attemptid',0 ,PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Course Module ID
$stepno  = optional_param('stepno', constants::STEP_NONE, PARAM_INT);
$action = optional_param('action','edit',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record(constants::M_MODNAME, array('id' => $cm->instance), '*', MUST_EXIST);
if($stepno<2){
    $type = constants::M_STEP_PREPARE;
}else {
    $type = $moduleinstance->{'step' . $stepno};
}

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/solo:view', $context);

//set up the page object
$PAGE->set_url('/mod/solo/attempt/manageattempts.php', array('attemptid'=>$attemptid, 'id'=>$id, 'stepno'=>$stepno));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
//Get admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}
$PAGE->force_settings_menu(true);

//Set up the attempt type specific parts of the form data
$renderer = $PAGE->get_renderer('mod_solo');
$attempt_renderer = $PAGE->get_renderer('mod_solo','attempt');

//are we in new or edit mode?
$attempt=false;
if ($attemptid) {
    $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE, array('id'=>$attemptid,constants::M_MODNAME => $cm->instance), '*', MUST_EXIST);
    if(!$attempt){
        print_error('could not find attempt of id:' . $attemptid);
    }
    //This wopuld force a step, if we needed to
    $lateststep = $attempt->completedsteps;
    $edit = true;
} else {
    $lateststep = constants::STEP_NONE;
    $edit = false;
}

//we always head back to the solo attempts page
$redirecturl = new moodle_url('/mod/solo/view.php', array('id'=>$cm->id));
//just init this when we need it.
$topichelper=false;
$attempthelper = new \mod_solo\attempthelper($cm);

//handle delete actions
if($action == 'confirmdelete'){
    $usecount = $DB->count_records(constants::M_ATTEMPTSTABLE,array(constants::M_MODNAME =>$cm->instance));
    echo $renderer->header($moduleinstance, $cm, 'attempts', null, get_string('confirmattemptdeletetitle', constants::M_COMPONENT));
    echo $attempt_renderer->confirm(get_string("confirmattemptdelete",constants::M_COMPONENT),
            new moodle_url('/mod/solo/attempt/manageattempts.php', array('action'=>'delete','id'=>$cm->id,'attemptid'=>$attemptid)),
            $redirecturl);
    echo $renderer->footer();
    return;

    /////// Delete attempt NOW////////
}elseif ($action == 'delete'){
    require_sesskey();
    $attempthelper->delete_attempt($attemptid);
    redirect($redirecturl);
}

$siteconfig = get_config(constants::M_COMPONENT);
$token= utils::fetch_token($siteconfig->apiuser,$siteconfig->apisecret);

$PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/solo/view.php', array('id'=>$id)));
$PAGE->navbar->add(get_string('editingattempt', constants::M_COMPONENT, utils::get_steplabel($type)));
$mode='attempts';

echo $renderer->header($moduleinstance, $cm,$mode, null, get_string('edit', constants::M_COMPONENT));

//show open close dates
$hasopenclosedates = $moduleinstance->viewend > 0 || $moduleinstance->viewstart>0;
if($hasopenclosedates){
    echo $renderer->box($renderer->show_open_close_dates($moduleinstance), 'generalbox');
    $current_time=time();
    $closed = false;
    if ( $current_time>$moduleinstance->viewend && $moduleinstance->viewend>0){
        echo get_string('activityisclosed',constants::M_COMPONENT);
        $closed = true;
    }elseif($current_time<$moduleinstance->viewstart){
        echo get_string('activityisnotopenyet',constants::M_COMPONENT);
        $closed = true;
    }
    //if we are not a teacher and the activity is closed/not-open leave at this point
    if(!has_capability('mod/solo:preview', $context) && $closed){
        echo $renderer->footer();
        exit;
    }
}

echo $attempt_renderer->add_edit_page_links($moduleinstance, $attempt,$stepno,$cm,$context);

echo html_writer::start_div(constants::M_COMPONENT .'_step' . $type);

//generic step info
$stepcontent = $moduleinstance;
$stepcontent->attemptid = $attemptid;
$stepcontent->type = $type;
$stepcontent->stepno = $stepno;
$stepcontent->cmid = $cm->id;
$stepcontent->nexturl = $redirecturl;
if(!empty($moduleinstance->targetwords)) {
    $stepcontent->targetwords = explode(PHP_EOL, $moduleinstance->targetwords);
}
//steps "prepare" and "record" use the same media prompt, prepare that here
$topicmedia = [];
$context = \context_module::instance($cm->id);

//Prepare speaking topic text
$topicmedia['itemtext']=$moduleinstance->speakingtopic;

//Prepare IFrame
if(!empty(trim($moduleinstance->topiciframe))){
    $topicmedia['itemiframe']=$moduleinstance->topiciframe;
}

//Prepare TTS prompt
if(!empty(trim($moduleinstance->topictts))){
    $topicmedia['itemtts']=utils::fetch_polly_url($token,$moduleinstance->region,$moduleinstance->topictts,'text',$moduleinstance->topicttsvoice);
}

//Prepare YT Clip
if(!empty(trim($moduleinstance->topicytid))){
    $ytvideoid = trim($moduleinstance->topicytid);
    //if its a YT URL we want to parse the id from it
    if(\core_text::strlen($ytvideoid)>11){
        $urlbits=[];
        preg_match('/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/', $ytvideoid, $urlbits);
        if($urlbits && count($urlbits)>7){
            $ytvideoid=$urlbits[7];
        }
    }
    $topicmedia['itemytvideoid']=$ytvideoid;
    $topicmedia['itemytvideostart']=$moduleinstance->topicytstart;
    $topicmedia['itemytvideoend']=$moduleinstance->topicytend;
}

//media items
$itemid=0;
$filearea='topicmedia';
$mediaurls = utils::fetch_media_urls($context->id,$filearea,$itemid);
if($mediaurls && count($mediaurls)>0){
    foreach($mediaurls as $mediaurl){
        $file_parts = pathinfo(strtolower($mediaurl));
        switch($file_parts['extension'])
        {
            case "jpg":
            case "jepg":
            case "png":
            case "gif":
            case "bmp":
            case "svg":
            case "webp":
                $topicmedia['itemimage'] = $mediaurl;
                break;

            case "mp4":
            case "m4v":
            case "mov":
            case "webm":
            case "ogv":
                $topicmedia['itemvideo'] = $mediaurl;
                break;

            case "mp3":
            case "ogg":
            case "wav":
            case "m4a":
                $topicmedia['itemaudio'] = $mediaurl;
                break;

            default:
                //do nothing
        }//end of extension switch
    }//end of for each
}//end of if mediaurls



//specific step data and then render
switch($type) {

    case constants::STEP_PREPARE:

        //there is only one contentitem in the array , it just seems the neatest way to pass a big chunk of data to a partial
        $stepcontent->contentitems = [$topicmedia];
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepprepare', $stepcontent);

        break;
    case constants::STEP_MEDIARECORDING:

        $media = 'audio';
        if ($media=='video') {
        $stepcontent->recordvideo = 1;
        }
        else{
            $stepcontent->recordaudio = 1;
        }

        //there is only one contentitem in the array , it just seems the neatest way to pass a big chunk of data to a partial
        $stepcontent->contentitems = [$topicmedia];
        $transcribestep = utils::fetch_step_no($moduleinstance,constants::M_STEP_TRANSCRIBE);
        if($stepno > $transcribestep && $transcribestep!==false){
            //flag this is post transcription
            $stepcontent->posttranscribing = true;

            //if we already have a transcript then we need to show that (or a blank)
            if(isset($attempt->selftranscript)&&!empty($attempt->selftranscript)){
                $stepcontent->selftranscript=$attempt->selftranscript;
            }else{
                $stepcontent->selftranscript='';
            }
        }
        $stepcontent->rec = utils::fetch_recorder_data($cm, $moduleinstance, $media, $token);
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepmediarecord', $stepcontent);
        break;

    case constants::STEP_SELFTRANSCRIBE:
        $recordstepno = utils::fetch_step_no($moduleinstance,constants::M_STEP_RECORD);
        //if we have a selftranscript set it
        if(isset($attempt->selftranscript)&&!empty($attempt->selftranscript)){
            $stepcontent->selftranscript=$attempt->selftranscript;
        }else{
            //otherwise make a blank one
            $stepcontent->selftranscript='';
            //in the case that this is a re-try and they already entered a pre-transcript, we load that up again
            if($stepno < $recordstepno && $recordstepno!==false) {
                $oldattempt = $attempthelper->fetch_latest_complete_attempt();
                if($oldattempt && $oldattempt->id !== $attempt->id){
                    $stepcontent->selftranscript=$oldattempt->selftranscript;
                    //we really ought to do something to make sure we dont get suggestions for
                }
            }
        }


        //if we are transcribing first and then talking, we want to do things a bit differently
        if($stepno<$recordstepno){
            $stepcontent->contentitems = [$topicmedia];
            $stepcontent->activityid =$moduleinstance->id;
            $stepcontent->prerecording=true;
            echo $renderer->render_from_template(constants::M_COMPONENT . '/stepselftranscribe', $stepcontent);
        }else{
            //we will need an audio file to transcribe from
            $stepcontent->audiofilename=$attempt->filename;
            echo $renderer->render_from_template(constants::M_COMPONENT . '/stepselftranscribe', $stepcontent);
        }


        break;
        
    case constants::STEP_MODEL:
        $modelmedia = [];
        //Prepare IFrame
        if(!empty(trim($moduleinstance->modeliframe))){
            $modelmedia['itemiframe']=$moduleinstance->modeliframe;
        }

        //Prepare TTS prompt
        if(!empty(trim($moduleinstance->modeltts))){
            $modelmedia['itemtts']=utils::fetch_polly_url($token,$moduleinstance->region,$moduleinstance->modeltts,'text',$moduleinstance->modelttsvoice);
        }

        //Prepare YT Clip
        if(!empty(trim($moduleinstance->modelytid))){
            $modelytvideoid = trim($moduleinstance->modelytid);
            //if its a YT URL we want to parse the id from it
            if(\core_text::strlen($modelytvideoid)>11){
                $urlbits=[];
                preg_match('/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/', $modelytvideoid, $urlbits);
                if($urlbits && count($urlbits)>7){
                    $modelytvideoid=$urlbits[7];
                }
            }
            $modelmedia['itemytvideoid']= $modelytvideoid;
            $modelmedia['itemytvideostart']=$moduleinstance->modelytstart;
            $modelmedia['itemytvideoend']=$moduleinstance->modelytend;
        }

        //media items
        $itemid=0;
        $filearea='modelmedia';
        $mediaurls = utils::fetch_media_urls($context->id,$filearea,$itemid);
        if($mediaurls && count($mediaurls)>0){
            foreach($mediaurls as $mediaurl){
                $file_parts = pathinfo(strtolower($mediaurl));
                switch($file_parts['extension'])
                {
                    case "jpg":
                    case "png":
                    case "gif":
                    case "bmp":
                    case "svg":
                        $modelmedia['itemimage'] = $mediaurl;
                        break;

                    case "mp4":
                    case "mov":
                    case "webm":
                    case "ogv":
                        $modelmedia['itemvideo'] = $mediaurl;
                        break;

                    case "mp3":
                    case "ogg":
                    case "wav":
                        $modelmedia['itemaudio'] = $mediaurl;
                        break;

                    default:
                        //do nothing
                }//end of extension switch
            }//end of for each
        }//end of if mediaurls
        //there is only one contentitem in the array , it just seems the neatest way to pass a big chunk of data to a partial
        $stepcontent->contentitems = [$modelmedia];
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepmodel', $stepcontent);
        break;

}

echo html_writer::end_div();
echo $renderer->footer();