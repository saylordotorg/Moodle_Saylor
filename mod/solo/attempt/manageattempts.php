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
 * replace i) MOD_solo eg MOD_CST, then ii) solo eg cst, then iii) attempt eg attempt
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
$type  = optional_param('type', constants::STEP_NONE, PARAM_INT);
$action = optional_param('action','edit',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record(constants::M_MODNAME, array('id' => $cm->instance), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/solo:view', $context);

//set up the page object
$PAGE->set_url('/mod/solo/attempt/manageattempts.php', array('attemptid'=>$attemptid, 'id'=>$id, 'type'=>$type));
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
    $success = \mod_solo\attempt\helper::delete_attempt($moduleinstance,$attemptid,$context);
    redirect($redirecturl);
}

$siteconfig = get_config(constants::M_COMPONENT);
$token= utils::fetch_token($siteconfig->apiuser,$siteconfig->apisecret);


//get the mform for our attempt
switch($type) {

    case constants::STEP_AUDIORECORDING:
        $targetwords = $attempt ? $attempt->topictargetwords : '';
        if ($attempt && !empty(trim($attempt->mywords))) {
            $targetwords .= $attempt ? PHP_EOL . trim($attempt->mywords) : '';
        }
        $mform = new \mod_solo\attempt\audiorecordingform(null,
                array('moduleinstance' => $moduleinstance,
                        'token' => $token,
                        'targetwords' => $targetwords,
                        'attempt' => $attempt));
        break;

    case constants::STEP_USERSELECTIONS:
        $targetwords = $attempt ? $attempt->topictargetwords : '';
        $mform = new \mod_solo\attempt\userselectionsform(null,
                array('moduleinstance' => $moduleinstance,
                        'token' => $token,
                        'cm'=>$cm));
        break;

    case constants::STEP_SELFTRANSCRIBE:
        $audiofilename = '';
        if ($attempt) {
            $audiofilename = $attempt->filename;
        }
        $mform = new \mod_solo\attempt\selftranscribeform(null,
                array('moduleinstance' => $moduleinstance, 'filename' => $audiofilename));
        break;


    case constants::STEP_NONE:
    default:
        print_error('No attempt type specifified');
}

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
    require_sesskey();

    $newattempt = $data;
    $newattempt->solo = $moduleinstance->id;
    $newattempt->userid = $USER->id;
    $newattempt->modifiedby=$USER->id;
    $newattempt->timemodified=time();

    //first insert a new attempt if we need to
    //that will give us a attemptid, we need that for saving files
    if($edit) {
        $newattempt->id = $data->attemptid;
    }else{
        $newattempt->timecreated=time();
        $newattempt->createdby=$USER->id;

        //try to insert it
        if (!$newattempt->id = $DB->insert_record(constants::M_ATTEMPTSTABLE,$newattempt)){
            print_error("Could not insert solo attempt!");
            redirect($redirecturl);
        }
    }


    //type specific settings
    switch($type) {
        case constants::STEP_USERSELECTIONS:
            $newattempt->topictargetwords = $moduleinstance->targetwords;
            break;

        case constants::STEP_AUDIORECORDING:
            $rerecording = $attempt && $newattempt->filename
                    && $attempt->filename != $newattempt->filename;

            //if rerecording we want to clear old AI data out
            //as well as self transcript and force us back to self transcript
            if($rerecording) {
                utils::clear_ai_data($moduleinstance->id, $newattempt->id);
                utils::remove_stats($newattempt);
                $newattempt->selftranscript="";
                $newattempt->completedsteps = $type;
            }
            //if rerecording, or we are in "new" mode (first recording) we register our AWS task
            if($rerecording || !$edit){
                utils::register_aws_task($moduleinstance->id, $newattempt->id, $context->id);
            }

            //if we have streaming transcriptdata
            if($data->streamingtranscript){
                $jsontranscript = utils::parse_streaming_results($data->streamingtranscript);
                $objecttranscript = json_decode($jsontranscript);
                $newattempt->jsontranscript = $jsontranscript;
                $newattempt->transcript = $objecttranscript->results->transcripts[0]->transcript;
                //we do not need this, so just blank it.
                $newattempt->vtttranscript = '';

            }

            break;
        case constants::STEP_SELFTRANSCRIBE:
            //if the user has altered their self transcript, we ought to recalc all the stats and ai data
            $st_altered = $attempt && $newattempt->selftranscript
                    && $attempt->selftranscript != $newattempt->selftranscript;
            if($st_altered) {
                $stats = utils::calculate_stats($newattempt->selftranscript, $attempt, $moduleinstance->ttslanguage);
                if ($stats) {
                    $stats = utils::fetch_sentence_stats($newattempt->selftranscript,$stats,$moduleinstance->ttslanguage);
                    $stats = utils::fetch_word_stats($newattempt->selftranscript,$moduleinstance->ttslanguage,$stats);
                    $stats = utils::calc_grammarspell_stats($newattempt->selftranscript,$moduleinstance->region,
                            $moduleinstance->ttslanguage,$stats);

                    utils::save_stats($stats, $attempt);
                }
                //recalculate AI data, if the selftranscription is altered AND we have a jsontranscript
                if($attempt->jsontranscript){
                    $passage = $newattempt->selftranscript;
                    $aitranscript = new \mod_solo\aitranscript($attempt->id, $context->id,$passage,$attempt->transcript, $attempt->jsontranscript);
                    $aitranscript->recalculate($passage,$attempt->transcript, $attempt->jsontranscript);
                }
            }
            break;

        default:
    }

    //Set the last completed stage
    if($lateststep < $type){
        $newattempt->completedsteps = $type;
    }

    //now update the db
    if (!$DB->update_record(constants::M_ATTEMPTSTABLE,$newattempt)){
        print_error("Could not update solo attempt!");
        redirect($redirecturl);
    }

    //if we just finished the last step then lets indicate this activity complete in the Moodle sense.
    if($type==constants::STEP_SELFTRANSCRIBE){
        //notify completion handler that we are finished
        $completion=new completion_info($course);
        if($completion->is_enabled($cm) && $moduleinstance->completionallsteps) {
            $completion->update_state($cm,COMPLETION_COMPLETE);
        }
    }

    //go back to top page
    redirect($redirecturl);
}

//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the attempt into a data object
if ($edit) {
    $data = $attempt;
    $data->attemptid = $attempt->id;
}else{
    $data=new stdClass;
    $data->attemptid = null;
    $data->visible = 1;
}
$data->type=$type;

//init our attempt, we move the id fields around a little
$data->id = $cm->id;
switch($type){
    case constants::STEP_AUDIORECORDING:
    case constants::STEP_USERSELECTIONS:
    case constants::STEP_SELFTRANSCRIBE:
    default:
}
$mform->set_data($data);
$PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/solo/view.php', array('id'=>$id)));
$PAGE->navbar->add(get_string('editingattempt', constants::M_COMPONENT, get_string($mform->typestring, constants::M_COMPONENT)));
$mode='attempts';

echo $renderer->header($moduleinstance, $cm,$mode, null, get_string('edit', constants::M_COMPONENT));



//show open close dates
$hasopenclosedates = $moduleinstance->viewend > 0 || $moduleinstance->viewstart>0;
if($hasopenclosedates){
    echo $renderer->box($renderer->show_open_close_dates($moduleinstance), 'generalbox');
    $current_time=time();
    $closed = false;
    if ( $current_time>$moduleinstance->viewend){
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


echo $attempt_renderer->add_edit_page_links($moduleinstance, $attempt,$type,$cm);
echo html_writer::start_div(constants::M_COMPONENT .'_step' . $type);

//generic step info
$stepcontent = $moduleinstance;
$stepcontent->attemptid = $attemptid;
$stepcontent->type = $type;
$stepcontent->cmid = $cm->id;
$stepcontent->nexturl = $redirecturl;

//specific step data and then render
switch($type . 'FALSEEEE') {

    case constants::STEP_USERSELECTIONS:

        //contentitem
        $contentitem = [];
        $context = \context_module::instance($cm->id);

        //Prepare speaking topic text
        $contentitem['itemtext']=$moduleinstance->speakingtopic;

        //Prepare IFrame
        if(!empty(trim($moduleinstance->topiciframe))){
            $contentitem['itemiframe']=$moduleinstance->topiciframe;
        }

        //Prepare TTS prompt
        if(!empty(trim($moduleinstance->topictts))){
            $contentitem['itemtts']=utils::fetch_polly_url($token,$moduleinstance->region,$moduleinstance->topictts,'text',$moduleinstance->topicttsvoice);
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
                    case "png":
                    case "gif":
                    case "bmp":
                    case "svg":
                        $contentitem['itemimage'] = $mediaurl;
                        break;

                    case "mp4":
                    case "mov":
                    case "webm":
                    case "ogv":
                        $contentitem['itemvideo'] = $mediaurl;
                        break;

                    case "mp3":
                    case "ogg":
                    case "wav":
                        $contentitem['itemaudio'] = $mediaurl;
                        break;

                    default:
                        //do nothing
                }//end of extension switch
            }//end of for each
        }//end of if mediaurls
        //there is only one contentitem in the array , it just seems the neatest way to pass a big chunk of data to a partial
        $stepcontent->contentitems = [$contentitem];
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepprepare', $stepcontent);

        break;
    case constants::STEP_AUDIORECORDING:

        $width = 450;
        $height = 280;
        $media = 'audio';
        if ($media=='video') {
        $stepcontent->recordvideo = 1;
        }
        else{
            $stepcontent->recordaudio = 1;
        }

        $stepcontent->rec = utils::fetch_recorder_data($cm, $moduleinstance, $media, $token, $width, $height);
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepmediarecord', $stepcontent);
        break;

    case constants::STEP_SELFTRANSCRIBE:

        $stepcontent->audiofilename=$attempt->filename;
        if(isset($attempt->selftranscript)&&!empty($attempt->selftranscript)){
            $stepcontent->selftranscript=$attempt->selftranscript;
        }else{
            $stepcontent->selftranscript='';
        }
        echo $renderer->render_from_template(constants::M_COMPONENT . '/stepselftranscribe', $stepcontent);
        break;
    default:
        $mform->display();

}

echo html_writer::end_div();
echo $renderer->footer();