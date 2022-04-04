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
 * readaloud main page
 *
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

//In the case that passage segments have not been set (usually from an upgrade from an earlier version) set those now
if($moduleinstance->passagesegments===null) {
    $olditem = false;
    list($thephonetic, $thepassagesegments) = utils::update_create_phonetic_segments($moduleinstance, $olditem);
    if (!empty($thephonetic)) {
        $DB->update_record(constants::M_TABLE, array('id' => $moduleinstance->id, 'phonetic' => $thephonetic, 'passagesegments' => $thepassagesegments));
        $moduleinstance->phonetic=$thephonetic;
        $moduleinstance->passagesegments=$thepassagesegments;
    }
}


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

//if we have no content, and its setup tab, we send to setup tab
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
    $attemptreview_html = $renderer->show_attempt_for_review($moduleinstance, $attempts,
            $have_humaneval, $have_aieval, $collapsespaces,$latestattempt, $token, $modulecontext, $passagerenderer);
    echo $attemptreview_html;
    return;
}


//show all the main parts. Many will be hidden and displayed by JS
// so here we just put them on the page in the correct sequenc


//show activity description
echo $renderer->show_intro($moduleinstance, $cm);

//show open close dates
$hasopenclosedates = $moduleinstance->viewend > 0 || $moduleinstance->viewstart>0;
if($hasopenclosedates){
    echo $renderer->show_open_close_dates($moduleinstance);
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
    if(!has_capability('mod/readaloud:preview',$modulecontext) && $closed){
        echo $renderer->footer();
        exit;
    }
}


//show small report
if($attempts) {
    if(!$latestattempt){$latestattempt = current($attempts);}
    echo $renderer->show_smallreport($moduleinstance, $latestattempt, $latest_aigrade);
}

//welcome message
$welcomemessage = get_string('welcomemenu',constants::M_COMPONENT);
if (!$canattempt) {
   $welcomemessage .= '<br>' . get_string("exceededattempts", constants::M_COMPONENT, $moduleinstance->maxattempts);
}
echo $renderer->show_welcome_menu($welcomemessage);

//if we have a problem (usually with auth/token) we display and return
if(!empty($problembox)){
    echo $problembox;
    // Finish the page
    echo $renderer->footer();
    return;
}


//activity instructions
echo $renderer->show_instructions($moduleinstance->welcome);
echo $renderer->show_previewinstructions(get_string('previewhelp',constants::M_COMPONENT));
echo $renderer->show_landrinstructions(get_string('landrhelp',constants::M_COMPONENT));

//feedback or errors
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
