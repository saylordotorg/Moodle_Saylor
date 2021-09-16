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
 * Grades listing for solo
 *
 * @package    mod_solo
 * @copyright  2020 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use mod_solo\constants;
use mod_solo\grades\grades AS grades;

global $DB;

// Page classes
$grades = new grades();

// Course module ID.
$id = required_param('id', PARAM_INT);


// Course and course module data.
$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
$modulecontext = context_module::instance($cm->id);
require_capability('mod/solo:grades', $modulecontext);

// Set page login data.
$PAGE->set_url(constants::M_URL . '/grades.php',array('id'=>$id));
require_login($course, true, $cm);

require_once($CFG->dirroot.'/grade/grading/lib.php');

$gradingmanager = get_grading_manager($modulecontext, 'mod_solo', 'solo');
$method = $gradingmanager->get_active_method();

// Set page meta data.
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

//Get an admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

$PAGE->requires->jquery();

// fetch groupmode/menu/id for this activity
$groupmenu = '';
if ($groupmode = groups_get_activity_groupmode($cm)) {
    $groupmenu = groups_print_activity_menu($cm, $PAGE->url, true);
    $groupmenu .= ' ';
    $groupid = groups_get_activity_group($cm);
}else{
    $groupid  = 0;
}


// Get grades list data by course module and course.
$studentgrades = $grades->getGrades($course->id, $id, $moduleinstance->id, $groupid);
foreach($studentgrades as $studentgrade){
    if($studentgrade->grade===null){
        $studentgrade->grade ='';
        $studentgrade->grader='';
    }elseif($studentgrade->manualgraded){
        $studentgrade->grader=get_string('humangraded',constants::M_COMPONENT);
    }else{
        $studentgrade->grader=get_string('autograded',constants::M_COMPONENT);
    }
    //if we do not have an accuracy score, dash dash
    if($studentgrade->aiaccuracy===null){
        $studentgrade->aiaccuracy='--';
    }
}
$data = new ArrayIterator($studentgrades);

// Render template and display page.
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$templatedata = [];
$templatedata['cmid'] = $id;
$templatedata['data'] = $data;
$templatedata['totalgradeables']=count($studentgrades);
switch($method){
    case 'rubric';
        $templatedata['methodrubric'] = true;
        break;
    case 'simple';
    default:
        $templatedata['methodsimple'] = true;
        break;
}
$gradesrenderer =
    $OUTPUT->render_from_template(constants::M_COMPONENT . '/grades', $templatedata);

echo $renderer->header($moduleinstance, $cm, "grades");
echo $groupmenu;
echo $gradesrenderer;
echo $renderer->footer();
