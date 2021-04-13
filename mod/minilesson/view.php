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
 * Prints a particular instance of minilesson
 *
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
use \mod_minilesson\constants;
use \mod_minilesson\utils;




$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$retake = optional_param('retake', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // minilesson instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('minilesson', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record('minilesson', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record('minilesson', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('minilesson', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_url('/mod/minilesson/view.php', array('id' => $cm->id));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

// Trigger module viewed event.
$event = \mod_minilesson\event\course_module_viewed::create(array(
   'objectid' => $moduleinstance->id,
   'context' => $modulecontext
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('minilesson', $moduleinstance);
$event->trigger();


//if we got this far, we can consider the activity "viewed"
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

//are we a teacher or a student?
$mode= "view";

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

//load glide
$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/glidejs@2.1.0/dist/css/glide.core.min.css'));


//if admin allow around site and to see edit stuff
//if(has_capability('mod/' . constants::M_MODNAME . ':' . 'canmanageattempts',$modulecontext)) {
if(has_capability('mod/' . constants::M_MODNAME . ':' . 'manage',$modulecontext)) {
    $PAGE->set_pagelayout('standard');
}else{
    $PAGE->set_pagelayout($moduleinstance->pagelayout);
}

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

//Get our renderers
$renderer = $PAGE->get_renderer('mod_minilesson');

//get attempts
$attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,array('moduleid'=>$moduleinstance->id,'userid'=>$USER->id),'timecreated DESC');


//can make a new attempt ?
$canattempt = true;
$canpreview = has_capability('mod/minilesson:canpreview',$modulecontext);
if(!$canpreview && $moduleinstance->maxattempts > 0){
	if($attempts && count($attempts)>=$moduleinstance->maxattempts){
		$canattempt=false;
	}
}

//create a new attempt or just fall through to no-items or finished modes
if(!$attempts || ($canattempt && $retake==1)){
    $latestattempt = utils::create_new_attempt($moduleinstance->course, $moduleinstance->id);
}else{
    $latestattempt = reset($attempts);
}


//From here we actually display the page.
//if we are teacher we see tabs. If student we just see the quiz
if(has_capability('mod/minilesson:evaluate',$modulecontext)){
	echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('view', constants::M_COMPONENT));
}else{
	echo $renderer->notabsheader();
}

$comp_test =  new \mod_minilesson\comprehensiontest($cm);
$itemcount = $comp_test->fetch_item_count();

if($latestattempt->status==constants::M_STATE_COMPLETE){
    echo $renderer->show_finished_results($comp_test,$latestattempt, $canattempt);
}else if($itemcount > 0) {
    echo $renderer->show_quiz($comp_test);
    $previewid=0;
    echo $renderer->fetch_activity_amd($cm, $moduleinstance,$previewid,$canattempt);
}else{
    $showadditemlinks = has_capability('mod/minilesson:evaluate',$modulecontext);
    echo $renderer->show_no_items($cm,$showadditemlinks);
}

//echo $renderer->load_app($cm, $moduleinstance, $latestattempt);

//backtotop
/*echo $renderer->backtotopbutton($course->id);*/

// Finish the page
echo $renderer->footer();
