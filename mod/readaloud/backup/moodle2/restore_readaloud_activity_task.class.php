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
 * @package   mod_readaloud
 * @copyright 2014 Justin Hunt poodllsupport@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;

require_once($CFG->dirroot . '/mod/readaloud/backup/moodle2/restore_readaloud_stepslib.php'); // Because it exists (must)

/**
 * readaloud restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_readaloud_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_readaloud_activity_structure_step('readaloud_structure', 'readaloud.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content(constants::M_MODNAME,
                array('intro'), constants::M_MODNAME);
        $contents[] = new restore_decode_content(constants::M_MODNAME,
                array('welcome'), constants::M_MODNAME);
        $contents[] = new restore_decode_content(constants::M_MODNAME,
                array('passage'), constants::M_MODNAME);
        $contents[] = new restore_decode_content(constants::M_MODNAME,
                array('feedback'), constants::M_MODNAME);

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('READALOUDVIEWBYID', '/mod/readaloud/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('READALOUDINDEX', '/mod/readaloud/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * englishcentral logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule(constants::M_MODNAME, 'add', 'view.php?id={course_module}', '{' . constants::M_TABLE . '}');
        $rules[] =
                new restore_log_rule(constants::M_MODNAME, 'update', 'view.php?id={course_module}', '{' . constants::M_TABLE . '}');
        $rules[] =
                new restore_log_rule(constants::M_MODNAME, 'view', 'view.php?id={course_module}', '{' . constants::M_TABLE . '}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();
        $rules[] = new restore_log_rule(constants::M_MODNAME, 'view all', 'index.php?id={course}', null);
        return $rules;
    }

    /**
     * Re-map the  activitylink information
     * If activitylink has no mapping in the backup data then it could either be a duplication of a
     * readaloud, or a backup/restore of a single one. We have no way to determine which and whether this is the
     * same site and/or course. Therefore we try and retrieve a mapping, but fallback to the original value if one
     * was not found. We then test to see whether the value found is valid for the course being restored into.
     */
    public function after_restore() {
        global $DB;

        $readaloud = $DB->get_record(constants::M_TABLE, array('id' => $this->get_activityid()), 'id, course, activitylink');
        $updaterequired = false;

        if (!empty($readaloud->activitylink)) {
            $updaterequired = true;
            if ($newitem =
                    restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', $readaloud->activitylink)) {
                $readaloud->activitylink = $newitem->newitemid;
            }
            if (!$DB->record_exists('course_modules', array('id' => $readaloud->activitylink, 'course' => $readaloud->course))) {
                $readaloud->activitylink = 0;
            }
        }

        if ($updaterequired) {
            $DB->update_record('readaloud', $readaloud);
        }
    }
}
