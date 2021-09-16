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
 * Prints a particular instance of readaloud
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use \mod_readaloud\constants;
use \mod_readaloud\utils;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$reviewattempts= optional_param('reviewattempts', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // readaloud instance ID - it should be named as the first character of the module
$debug = optional_param('debug', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('readaloud', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('readaloud', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $moduleinstance = $DB->get_record('readaloud', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('readaloud', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(0,get_string('nocourseid',constants::M_COMPONENT));
}

$PAGE->set_url('/mod/readaloud/view.php', array('id' => $cm->id,'reviewattempts'=>$reviewattempts));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

// Trigger module viewed event.
$event = \mod_readaloud\event\course_module_viewed::create(array(
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('readaloud', $moduleinstance);
$event->trigger();

//if we got this far, we can consider the activity "viewed"
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

//are we a teacher or a student?
$mode = "view";


//Get an admin settings
$config = get_config(constants::M_COMPONENT);

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

//we need to load jquery for some old themes (Essential mainly)
$PAGE->requires->jquery();


//Get our renderers
$renderer = $PAGE->get_renderer('mod_readaloud');
$passagerenderer = $PAGE->get_renderer(constants::M_COMPONENT, 'passage');
$modelaudiorenderer = $PAGE->get_renderer(constants::M_COMPONENT, 'modelaudio');

//do we have attempts and ai data
$attempts = $DB->get_records(constants::M_USERTABLE, array('userid' => $USER->id, 'readaloudid' => $moduleinstance->id), 'timecreated DESC');
$ai_evals = \mod_readaloud\utils::get_aieval_byuser($moduleinstance->id, $USER->id);

//can attempt ?
$canattempt = true;
$canpreview = has_capability('mod/readaloud:preview', $modulecontext);
if (!$canpreview && $moduleinstance->maxattempts > 0) {
    if ($attempts && count($attempts) >= $moduleinstance->maxattempts) {
        $canattempt = false;
    }
}

//debug mode is for teachers only
if (!$canpreview) {
    $debug = false;
}


//for Japanese (and later other languages we collapse spaces)
$collapsespaces=false;
if($moduleinstance->ttslanguage==constants::M_LANG_JAJP){
    $collapsespaces=true;
}

//fetch a token and report a failure to a display item: $problembox
$problembox='';
$token="";
if(empty($config->apiuser) || empty($config->apisecret)){
    $message = get_string('nocredentials',constants::M_COMPONENT,
            $CFG->wwwroot . constants::M_PLUGINSETTINGS);
    $problembox=$renderer->show_problembox($message);
}else {
    //fetch token
    $token = utils::fetch_token($config->apiuser, $config->apisecret);

    //check token authenticated and no errors in it
    $errormessage = utils::fetch_token_error($token);
    if(!empty($errormessage)){
        $problembox = $renderer->show_problembox($errormessage);
    }
}

//fetch attempt information
if($attempts) {
    $latestattempt = current($attempts);

    if (\mod_readaloud\utils::can_transcribe($moduleinstance)) {
        $latest_aigrade = new \mod_readaloud\aigrade($latestattempt->id, $modulecontext->id);
    } else {
        $latest_aigrade = false;
    }

    $have_humaneval = $latestattempt->sessiontime != null;
    $have_aieval = $latest_aigrade && $latest_aigrade->has_transcripts();
}else{
    $latestattempt = false;
    $have_humaneval = false;
    $have_aieval =false;
    $latest_aigrade = false;
}

//From here we actually display the page.

//if we are teacher we see tabs. If student we just see the activity
echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('view', constants::M_COMPONENT));


if($config->enablesetuptab && empty($moduleinstance->passage)) {
    if (has_capability('mod/readaloud:manage', $modulecontext)) {
        echo $renderer->show_no_content($cm,true);
    }else{
        echo $renderer->show_no_content($cm,false);
    }
    echo $renderer->footer();
    return;
}

//If we are reviewing attempts we do that here and return.
//If we are going to the dashboard we output that below
if ($attempts && $reviewattempts) {

    //show an attempt summary if we have more than one attempt
    if(count($attempts)>1) {
        $showgradesinchart=true;
        switch ($moduleinstance->humanpostattempt) {
            case constants::POSTATTEMPT_NONE:
                //no progress charts if not showing errors
                break;

            case constants::POSTATTEMPT_EVALERRORSNOGRADE:
                $showgradesinchart=false;
            case constants::POSTATTEMPT_EVAL:
            case constants::POSTATTEMPT_EVALERRORS:
                $attemptsummary = utils::fetch_attempt_summary($moduleinstance);
                echo $renderer->show_attempt_summary($attemptsummary,$showgradesinchart);
                $chartdata = utils::fetch_attempt_chartdata($moduleinstance);
                echo $renderer->show_progress_chart($chartdata,$showgradesinchart);
        }
    }

    //show feedback summary
    echo $renderer->show_feedback_postattempt($moduleinstance);

    //if we have token problems show them here
    if(!empty($problembox)) {
        echo $problembox;
    }

    if ($have_humaneval || $have_aieval) {
        //we useed to distingush between humanpostattempt and machinepostattempt but we simplified it,
        // /and just use the human value for all
        switch ($moduleinstance->humanpostattempt) {
            case constants::POSTATTEMPT_NONE:
                //we need more control over passage display than a word dump allows so we user gradenow renderer
                //echo $renderer->show_passage_postattempt($moduleinstance,$collapsespaces);
                $extraclasses = 'readmode';
                if($collapsespaces){
                    $extraclasses = ' collapsespaces';
                }
                echo $passagerenderer->render_passage($moduleinstance->passagesegments,$moduleinstance->ttslanguage,constants::M_PASSAGE_CONTAINER, $extraclasses);
                echo $renderer->fetch_clicktohear_amd($moduleinstance,$token);
                echo $renderer->render_hiddenaudioplayer();
                break;
            case constants::POSTATTEMPT_EVAL:
                echo $renderer->show_evaluated_message();
                if ($have_humaneval) {
                    $force_aidata = false;
                } else {
                    $force_aidata = true;
                }
                $passagehelper = new \mod_readaloud\passagehelper($latestattempt->id, $modulecontext->id);
                $reviewmode = constants::REVIEWMODE_SCORESONLY;

                $readonly = true;
                echo $passagehelper->prepare_javascript($reviewmode, $force_aidata, $readonly);
                echo $renderer->fetch_clicktohear_amd($moduleinstance,$token);
                echo $renderer->render_hiddenaudioplayer();
                echo $passagerenderer->render_userreview($passagehelper,$moduleinstance->ttslanguage,$collapsespaces);

                break;

            case constants::POSTATTEMPT_EVALERRORS:
                echo $renderer->show_evaluated_message();
                if ($have_humaneval) {
                    $reviewmode = constants::REVIEWMODE_HUMAN;
                    $force_aidata = false;
                } else {
                    $reviewmode = constants::REVIEWMODE_MACHINE;
                    $force_aidata = true;
                }
                $passagehelper = new \mod_readaloud\passagehelper($latestattempt->id, $modulecontext->id);
                $readonly = true;
                echo $passagehelper->prepare_javascript($reviewmode, $force_aidata, $readonly);
                echo $renderer->fetch_clicktohear_amd($moduleinstance,$token);
                echo $renderer->render_hiddenaudioplayer();
                echo $passagerenderer->render_userreview($passagehelper,$moduleinstance->ttslanguage,$collapsespaces);
                break;

            case constants::POSTATTEMPT_EVALERRORSNOGRADE:
                echo $renderer->show_evaluated_message();
                if ($have_humaneval) {
                    $reviewmode = constants::REVIEWMODE_HUMAN;
                    $force_aidata = false;
                } else {
                    $reviewmode = constants::REVIEWMODE_MACHINE;
                    $force_aidata = true;
                }
                $passagehelper = new \mod_readaloud\passagehelper($latestattempt->id, $modulecontext->id);
                $readonly = true;
                echo $passagehelper->prepare_javascript($reviewmode, $force_aidata, $readonly);
                echo $renderer->fetch_clicktohear_amd($moduleinstance,$token);
                echo $renderer->render_hiddenaudioplayer();
                $nograde=true;
                echo $passagerenderer->render_userreview($passagehelper,$moduleinstance->ttslanguage,$collapsespaces,$nograde);
                break;
        }
    } else {
        echo $renderer->show_ungradedyet();
        echo $renderer->fetch_clicktohear_amd($moduleinstance,$token);
        echo $renderer->render_hiddenaudioplayer();
        //we need more control over passage display than a word dump allows so we user gradenow renderer
        //echo $renderer->show_passage_postattempt($moduleinstance,$collapsespaces);
        $extraclasses = 'readmode';
        if($collapsespaces){
            $extraclasses = ' collapsespaces';
        }
        echo $passagerenderer->render_passage($moduleinstance->passagesegments,$moduleinstance->ttslanguage,constants::M_PASSAGE_CONTAINER, $extraclasses);

    }

    //TO DO move logic to menu dashboard
    //show  button or a label depending on of can retake
    /*
    if ($canattempt) {
        echo $renderer->reattemptbutton($moduleinstance);
    } else {
        echo $renderer->exceededattempts($moduleinstance);
    }
    */
    echo $renderer->jump_tomenubutton($moduleinstance);
    echo $renderer->footer();
    return;
}

//show activity description
echo $renderer->show_intro($moduleinstance, $cm);

//show small report
if($attempts) {
    if(!$latestattempt){$latestattempt = current($attempts);}
    echo $renderer->show_smallreport($moduleinstance, $latestattempt, $latest_aigrade);
}

//show all the main parts. Many will be hidden and displayed by JS
$welcomemessage = get_string('welcomemenu',constants::M_COMPONENT);

if (!$canattempt) {
   $welcomemessage .= '<br>' . get_string("exceededattempts", constants::M_COMPONENT, $moduleinstance->maxattempts);
}

echo $renderer->show_welcome_menu($welcomemessage);
if(!empty($problembox)){
    echo $problembox;
    // Finish the page
    echo $renderer->footer();
    return;
}


echo $renderer->show_instructions($moduleinstance->welcome);
echo $renderer->show_previewinstructions(get_string('previewhelp',constants::M_COMPONENT));
echo $renderer->show_landrinstructions(get_string('landrhelp',constants::M_COMPONENT));

echo $renderer->show_feedback($moduleinstance);
echo $renderer->show_error($moduleinstance, $cm);


//show menu buttons
echo $renderer->show_menubuttons($moduleinstance,$canattempt);

//Show model audio player
$visible=false;
echo $modelaudiorenderer->render_modelaudio_player($moduleinstance, $token, $visible);

//show stop and play buttons
echo $renderer->show_stopandplay($moduleinstance);

//we put some CSS at the top of the passage container to control things like padding word separation etc
$extraclasses = 'readmode';
//for Japanese (and later other languages we collapse spaces)
if($collapsespaces){
    $extraclasses .= ' collapsespaces';
}

//hide on load, and we can show from ajax
$extraclasses .= ' hide';
echo $passagerenderer->render_passage($moduleinstance->passagesegments,$moduleinstance->ttslanguage, constants::M_PASSAGE_CONTAINER, $extraclasses);

//lets fetch recorder
echo $renderer->show_recorder($moduleinstance, $token, $debug);

echo $renderer->show_progress($moduleinstance, $cm);
echo $renderer->show_wheretonext($moduleinstance);

//show listen and repeat dialog
echo $renderer->show_landr($moduleinstance, $token);

echo $renderer->fetch_activity_amd($cm, $moduleinstance,$token);

//return to menu button
echo "<hr/>";
echo $renderer->show_returntomenu_button();

// Finish the page
echo $renderer->footer();
