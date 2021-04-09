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
 * Developer tools for solo
 *
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_solo\constants;
use \mod_solo\utils;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // solo instance ID

$action = optional_param('action', 'none', PARAM_TEXT); // report type




if ($id) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(0,'You must specify a course_module ID or an instance ID');
}

$PAGE->set_url(constants::M_URL . '/developer.php',
	array('id' => $cm->id,'action'=>$action));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/solo:viewreports', $modulecontext);

//Get an admin settings
$config = get_config(constants::M_COMPONENT);




/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('course');
$PAGE->requires->jquery();



//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$mode = 'view';
$header = $renderer->header($moduleinstance, $cm, $mode, null, get_string('developer', constants::M_COMPONENT));

//Process Actions.

switch ($action){

	//not a true report, separate implementation in renderer
	case 'generatedata':
	    $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,
            array('solo'=>  $moduleinstance->id,'completedsteps'=>constants::STEP_SELFTRANSCRIBE),'timemodified DESC','*',0,1);
	    if(!$attempts){
            echo $header;
	        echo '<h3>No attempt to generate data from</h3>';
            echo $renderer->footer();
            return;
        }else{
	        $latestattempt = array_shift($attempts);
	        $stats = $DB->get_record(constants::M_STATSTABLE, array('attemptid'=>$latestattempt->id));
            $ai = $DB->get_record(constants::M_AITABLE, array('attemptid'=>$latestattempt->id));
            if(!$ai) {
                echo $header;
                echo '<h3>No AI for the latest attempt</h3>';
                echo $renderer->footer();
                return;
            }
            if(!$stats) {
                echo $header;
                echo '<h3>No stats for the latest attempt</h3>';
                echo $renderer->footer();
                return;
            }

	        $users = get_enrolled_users($modulecontext);
	        //reindex array
	        $users = array_values($users);
	        $created = 0;
	        for($x=0;$x<count($users);$x++){
                $user1 = $users[$x];
	            $ret = copyAttempt($latestattempt,$stats,$ai,$user1);
                $user1 = $users[$x];
            }//end of user loop
            redirect(new \moodle_url(constants::M_URL . '/developer.php',
                array('id' => $cm->id)),'Created Attempts:' . $created);

        }

		return;

	case 'none':
    default:
}

//output the page
echo $header;
$items = $renderer->developerpage($cm->id,$moduleinstance->id);
foreach($items as $item){
    echo $item;
}
echo $renderer->footer();

function copyAttempt($attempt, $stats, $ai, $user){
    global $DB;
    $newatt = $attempt;
    $newstats = $stats;
    $newai = $ai;

    //attempt
    $newatt->id = null;
    $newatt->timemodified = time();
    $newatt->userid=$user->id;
    $attemptid = $DB->insert_record(constants::M_ATTEMPTSTABLE,$newatt);
    if(!$attemptid){return false;}

    //stats
    $newstats->id  = null;
    $newstats->timemodified = time();
    $newstats->userid=$user->id;
    $newstats->attemptid=$attemptid;
    $statsid = $DB->insert_record(constants::M_STATSTABLE,$newstats);
    if(!$statsid){return false;}

    //ai
    $newai->id  = null;
    $newai->timemodified = time();
    $newai->attemptid=$attemptid;
    $aiid = $DB->insert_record(constants::M_AITABLE,$newai);
    if(!$aiid){return false;}
    return true;
}