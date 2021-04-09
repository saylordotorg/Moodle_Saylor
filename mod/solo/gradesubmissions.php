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
 * Grades submission page for solo
 *
 * @package    mod_solo
 * @copyright  2020 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/grade/grading/lib.php');
require_once('rubric_grade_form.php');
require_once('simple_grade_form.php');

use mod_solo\constants;
use mod_solo\grades\gradesubmissions as gradesubmissions;

global $DB;

// Page level constants
// Min and max number of grades to display on a page; 0 based.
define('MIN_GRADE_DISPLAY', 0);
define('MAX_GRADE_DISPLAY', 3);

// Page classes
$gradesubmissions = new gradesubmissions();

// Course module ID.
$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$grademethod = required_param('grademethod', PARAM_TEXT);
// Course and course module data.
$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, IGNORE_MISSING);
$course = $DB->get_record('course', array('id' => $cm->course), '*', IGNORE_MISSING);
$moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', IGNORE_MISSING);
$modulecontext = context_module::instance($cm->id);
require_capability('mod/solo:grades', $modulecontext);

// Set page login data.
$PAGE->set_url(constants::M_URL . '/gradesubmissions.php',array('id'=>$id,'userid'=>$userid,'grademethod'=>$grademethod));
require_login($course, true, $cm);

// Get student grade data - all students who completed an attempt
$studentlist = $gradesubmissions->getStudentsToGrade($moduleinstance);
//get pages of 3 students (array of 3 userids) and the current students page number
list($pagesofstudents,$currentstudentpage) = $gradesubmissions->getPageOfStudents($studentlist,$userid);
//get the page of students (array od f 3 student ids) for current student
$students = $pagesofstudents[$currentstudentpage];
//if there are fewer than 3 students, pad the array with X empty strings
$studentsToGrade = new ArrayIterator(array_pad($students, MAX_GRADE_DISPLAY, ''));
//get all enroled students for the course
$submissionCandidates = get_enrolled_users($modulecontext, 'mod/solo:submit');
// Select students in page in drop down list.
array_walk($submissionCandidates, function ($candidate) use ($studentlist) {
    if (in_array($candidate->id, $studentlist, true)) {
        $candidate->selected = "selected='selected'";
    }
});
$submissionCandidates = new ArrayIterator($submissionCandidates);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('course');
$PAGE->requires->jquery();

// Render template and display page.
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$context = context_course::instance($course->id);

$gradesrenderer =
    $OUTPUT->render_from_template(
        constants::M_COMPONENT . '/gradesubmissions',
        array(
            'studentsToGrade' => $studentsToGrade,
            'submissionCandidates' => $submissionCandidates,
            'grademethod' => $grademethod,
            'contextid' => $context->id,
            'cmid' => $cm->id,
            'currentpage'=>$currentstudentpage,
            'pages'=>json_encode($pagesofstudents)
        )
    );

echo $renderer->header($moduleinstance, $cm, "gradesubmissions");
echo $gradesrenderer;
echo $renderer->footer();
