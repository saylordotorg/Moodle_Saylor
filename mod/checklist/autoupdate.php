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
 * Automatically update checklist
 * @copyright Davo Smith <moodle@davosmith.co.uk>
 * @package mod_checklist
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_checklist\local\checklist_check;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/checklist/lib.php');

/**
 * Automatically update checklist
 * @param int $userid
 * @param array $itemchecks
 * @param bool $newstate
 * @return int
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function checklist_completion_update_checks($userid, $itemchecks, $newstate) {
    global $DB;

    $updatecount = 0;
    $updatechecklists = array();
    foreach ($itemchecks as $itemcheck) {
        list(, $cm) = get_course_and_cm_from_instance($itemcheck->checklist, 'checklist', $itemcheck->course);
        $context = context_module::instance($cm->id);
        if (!has_capability('mod/checklist:updateown', $context, $userid)) {
            continue; // User can't update checklist, so don't save the value.
        }
        if ($itemcheck->id) {
            $check = new checklist_check((array)$itemcheck, false);
        } else {
            $check = new checklist_check(['item' => $itemcheck->itemid, 'userid' => $userid], false);
        }
        if ($itemcheck->teacheredit == CHECKLIST_MARKING_TEACHER) {
            if ($check->is_checked_teacher() != $newstate) {
                $check->set_teachermark(CHECKLIST_TEACHERMARK_YES, null);
                $check->save();
                $updatechecklists[] = $itemcheck->checklist;
                $updatecount++;
            }
        } else {
            if ($newstate != $check->is_checked_student()) {
                $check->set_checked_student($newstate);
                $check->save();
                $updatechecklists[] = $itemcheck->checklist;
                $updatecount++;
            }
        }
    }
    if (!empty($updatechecklists)) {
        $updatechecklists = array_unique($updatechecklists);
        list($csql, $cparams) = $DB->get_in_or_equal($updatechecklists);
        $checklists = $DB->get_records_select('checklist', 'id '.$csql, $cparams);
        foreach ($checklists as $checklist) {
            checklist_update_grades($checklist, $userid);
        }
    }

    return $updatecount;
}

/**
 * Internal function to update checkmarks associated with an activity
 * @param int $courseid
 * @param string $module
 * @param int $cmid
 * @param int $userid
 * @return int
 */
function checklist_autoupdate_internal($courseid, $module, $cmid, $userid) {
    global $DB;

    if ($userid == 0) {
        return 0;
    }

    if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
        mtrace("Possible update needed - courseid: $courseid, module: $module, ".
               "cmid: $cmid, userid: $userid");
    }

    $completion = new completion_info((object)['id' => $courseid]);
    $cm = $DB->get_record('course_modules', ['id' => $cmid], 'id, completion');
    if ($completion->is_enabled($cm)) {
        if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
            mtrace("This course module has completion enabled - allow that to control any checklist items");
        }
        return 0;
    }

    $sql = "SELECT i.id AS itemid, i.checklist, cl.teacheredit, ck.*, cl.course
              FROM {checklist_item} i
              JOIN {checklist} cl ON i.checklist = cl.id
              LEFT JOIN {checklist_check} ck ON (ck.item = i.id AND ck.userid = :userid)
             WHERE cl.autoupdate > 0 AND i.moduleid = :cmid AND i.itemoptional < :heading";
    $itemchecks = $DB->get_records_sql($sql, ['userid' => $userid, 'cmid' => $cmid, 'heading' => CHECKLIST_OPTIONAL_HEADING]);
    if (empty($itemchecks)) {
        if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
            mtrace("No checklist items linked to this course module");
        }
        return 0;
    }

    $updatecount = checklist_completion_update_checks($userid, $itemchecks, true);
    if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
        mtrace("$updatecount checklist items updated from this log entry");
    }

    return 0;
}

/**
 * Automatically update a checklist checkmarks
 * @param int $cmid
 * @param int $userid
 * @param bool $newstate
 * @return int
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function checklist_completion_autoupdate($cmid, $userid, $newstate) {
    global $DB, $USER;

    if ($userid == 0) {
        $userid = $USER->id;
    }

    if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
        mtrace("Completion status change for cmid: $cmid, userid: $userid, newstate: $newstate");
    }

    $sql = "SELECT i.id AS itemid, i.checklist, cl.teacheredit, ck.*, cl.course
              FROM {checklist_item} i
              JOIN {checklist} cl ON i.checklist = cl.id
              LEFT JOIN {checklist_check} ck ON (ck.item = i.id AND ck.userid = :userid)
             WHERE cl.autoupdate > 0 AND i.moduleid = :cmid AND i.itemoptional < :heading";
    $itemchecks = $DB->get_records_sql($sql, ['userid' => $userid, 'cmid' => $cmid, 'heading' => CHECKLIST_OPTIONAL_HEADING]);
    if (empty($itemchecks)) {
        if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
            mtrace("No checklist items linked to this course module");
        }
        return 0;
    }

    $newstate = ($newstate == COMPLETION_COMPLETE || $newstate == COMPLETION_COMPLETE_PASS); // Not complete if failed.
    $updatecount = checklist_completion_update_checks($userid, $itemchecks, $newstate);

    if (defined("DEBUG_CHECKLIST_AUTOUPDATE")) {
        mtrace("Updated $updatecount checklist items from this completion status change");
    }

    return $updatecount;
}

/**
 * Update automatic checkmarks for the user on the course
 * @param int $courseid
 * @param int $userid
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function checklist_course_completion_autoupdate($courseid, $userid) {
    global $DB;

    $sql = "SELECT i.id AS itemid, i.checklist, cl.teacheredit, ck.*, cl.course
              FROM {checklist_item} i
              JOIN {checklist} cl ON cl.id = i.checklist
              LEFT JOIN {checklist_check} ck ON ck.item = i.id AND ck.userid = :userid
             WHERE cl.autoupdate > 0 AND i.linkcourseid = :courseid AND i.itemoptional < :heading";
    $params = ['userid' => $userid, 'courseid' => $courseid, 'heading' => 2];
    $itemchecks = $DB->get_records_sql($sql, $params);
    if (!$itemchecks) {
        return;
    }

    checklist_completion_update_checks($userid, $itemchecks, true);
}
