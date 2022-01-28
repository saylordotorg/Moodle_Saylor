<?php
// This file is part of the Checklist plugin for Moodle - http://moodle.org/
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
 * Edit a checklist.
 *
 * @copyright Davo Smith <moodle@davosmith.co.uk>
 * @package mod_checklist
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/mod/checklist/lib.php');
require_once($CFG->dirroot.'/mod/checklist/locallib.php');

global $DB, $PAGE;

$id = required_param('id', PARAM_INT); // Course_module ID.

$url = new moodle_url('/mod/checklist/edit.php', array('id' => $id));

$cm = get_coursemodule_from_id('checklist', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$checklist = $DB->get_record('checklist', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_url($url);
require_login($course, true, $cm);

if ($chk = new checklist_class($cm->id, 0, $checklist, $cm, $course)) {
    $chk->edit();
}
