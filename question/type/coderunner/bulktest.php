<?php
// This file is part of CodeRunner - http://coderunner.org.nz
//
// CodeRunner is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// CodeRunner is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with CodeRunner.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script runs all the question tests for all deployed versions of all
 * questions in a given context and, optionally, a given question category.
 * It is a modified version of the script from the qtype_stack plugin.
 *
 * @package   qtype_coderunner
 * @copyright 2016 Richard Lobb, The University of Canterbury
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

// Get the parameters from the URL.
$contextid = required_param('contextid', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$iterations = optional_param('iterations', 1, PARAM_INT);  // Guru use only.

// Login and check permissions.
$context = context::instance_by_id($contextid);
require_login();
require_capability('moodle/question:editall', $context);
$PAGE->set_url('/question/type/coderunner/bulktest.php', array('contextid' => $context->id));
$PAGE->set_context($context);
$title = get_string('bulktesttitle', 'qtype_coderunner', $context->get_context_name());
$PAGE->set_title($title);

if ($context->contextlevel == CONTEXT_MODULE) {
    // Calling $PAGE->set_context should be enough, but it seems that it is not.
    // Therefore, we get the right $cm and $course, and set things up ourselves.
    $cm = get_coursemodule_from_id(false, $context->instanceid, 0, false, MUST_EXIST);
    $PAGE->set_cm($cm, $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST));
}

// Create the helper class.
$bulktester = new qtype_coderunner_bulk_tester();

// Release the session, so the user can do other things while this runs.
\core\session\manager::write_close();

// Display.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Run the tests.
$passes = 0;
$fails = [];
for ($i = 0; $i < $iterations; $i++) {
    list($numpasses, $failingtests, $missinganswers) = $bulktester->run_all_tests_for_context($context, $categoryid);
    $passes += $numpasses;
    $fails = array_merge($fails, $failingtests);
}
// Display the final summary.
$bulktester->print_overall_result($passes, $fails, $missinganswers);
echo $OUTPUT->footer();
