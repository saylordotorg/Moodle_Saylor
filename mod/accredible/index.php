<?php
// This file is part of the Accredible Certificate module for Moodle - http://moodle.org/
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
 * This page lists all the instances of certificate in a particular course
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

// Ensure that the course specified is valid.
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// Requires a login.
require_course_login($course);

// Strings used multiple times.
$strcertificates = get_string('modulenameplural', 'accredible');
$strname  = get_string("name");

// Print the header.
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/accredible/index.php', array('id' => $course->id));
$PAGE->navbar->add($strcertificates);
$PAGE->set_title($strcertificates);
$PAGE->set_heading($course->fullname);

// Get the certificates, if there are none display a notice.
if (!$certificates = get_all_instances_in_course('accredible', $course)) {
    echo $OUTPUT->header();
    notice(get_string('nocertificates', 'accredible'), "$CFG->wwwroot/course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit();
}

$table = new html_table();

$table->head  = array ($strname, get_string('datecreated', 'accredible'));

foreach ($certificates as $certificate) {
    $link = html_writer::tag('a',
                             $certificate->name,
                             array('href' => $CFG->wwwroot . '/mod/accredible/view.php?id=' . $certificate->coursemodule));
    $issued = date("M d, Y", $certificate->timecreated);
    $table->data[] = array ($link, $issued);
}

echo $OUTPUT->header();
echo html_writer::tag( 'h3', get_string('indexheader', 'accredible', $course->fullname) );
echo html_writer::table($table);
echo $OUTPUT->footer();
