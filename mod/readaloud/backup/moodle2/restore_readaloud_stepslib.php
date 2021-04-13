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

use \mod_readaloud\constants;

/**
 * Define all the restore steps that will be used by the restore_readaloud_activity_task
 */

/**
 * Structure step to restore one readaloud activity
 */
class restore_readaloud_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $userinfo = $this->get_setting_value('userinfo'); // are we including userinfo?

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing readaloud instance
        $oneactivity = new restore_path_element(constants::M_MODNAME, '/activity/readaloud');
        $paths[] = $oneactivity;

        // End here if no-user data has been selected
        if (!$userinfo) {
            return $this->prepare_activity_structure($paths);
        }

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - user data
        ////////////////////////////////////////////////////////////////////////
        //attempts
        $attempts = new restore_path_element(constants::M_USERTABLE,
                '/activity/readaloud/attempts/attempt');
        $paths[] = $attempts;

        //airesults
        $airesults = new restore_path_element(constants::M_AITABLE,
                '/activity/readaloud/attempts/attempt/airesults/airesult');
        $paths[] = $airesults;

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_readaloud($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        // insert the activity record
        $newitemid = $DB->insert_record(constants::M_TABLE, $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_readaloud_attempt($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->{constants::M_MODNAME . 'id'} = $this->get_new_parentid(constants::M_MODNAME);
        $newitemid = $DB->insert_record(constants::M_USERTABLE, $data);

        // Mapping without files
        //here we set the table name as the "key" to the mapping, but its actually arbitrary
        //'we would need to use the "key" later when calling add_related_files for the itemid in the moodle files area
        //IF we had files for this set of data. )
        $this->set_mapping(constants::M_USERTABLE, $oldid, $newitemid, true);
    }

    protected function process_readaloud_ai_result($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->{constants::M_MODNAME . 'id'} = $this->get_new_parentid(constants::M_MODNAME);
        $data->attemptid = $this->get_new_parentid(constants::M_USERTABLE);
        $newitemid = $DB->insert_record(constants::M_AITABLE, $data);

        // Mapping without files
        //here we set the table name as the "key" to the mapping, but its actually arbitrary
        //'we would need to use the "key" later when calling add_related_files for the itemid in the moodle files area
        //IF we had files for this set of data. )
        $this->set_mapping(constants::M_AITABLE, $oldid, $newitemid, true);
    }

    protected function after_execute() {
        // Add module related files, no need to match by itemname (just internally handled context)
        $this->add_related_files(constants::M_COMPONENT, 'intro', null);
        $this->add_related_files(constants::M_COMPONENT, 'welcome', null);
        $this->add_related_files(constants::M_COMPONENT, 'passage', null);
        $this->add_related_files(constants::M_COMPONENT, 'feedback', null);

        $userinfo = $this->get_setting_value('userinfo'); // are we including userinfo?
        if ($userinfo) {
            $this->add_related_files(constants::M_COMPONENT, constants::M_FILEAREA_SUBMISSIONS, constants::M_USERTABLE);
        }
    }
}
