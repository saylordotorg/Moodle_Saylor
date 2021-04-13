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
 * Defines all the backup steps that will be used by {@link backup_readaloud_activity_task}
 *
 * @package     mod_readaloud
 * @category    backup
 * @copyright   2015 Justin Hunt (poodllsupport@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;

/**
 * Defines the complete webquest structure for backup, with file and id annotations
 *
 */
class backup_readaloud_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the readaloud element inside the webquest.xml file
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // are we including userinfo?
        $userinfo = $this->get_setting_value('userinfo');

        ////////////////////////////////////////////////////////////////////////
        // XML nodes declaration - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing readaloud instance
        $oneactivity = new backup_nested_element(constants::M_MODNAME, array('id'), array(
                'course', 'name', 'intro', 'introformat', 'timelimit', 'passage','passagehash', 'passageformat', 'alternatives', 'welcome',
                'welcomeformat',
                'feedback', 'feedbackformat', 'targetwpm', 'accadjustmethod', 'accadjust', 'humanpostattempt', 'machinepostattempt',
                'grade', 'gradeoptions', 'machgrademethod','sessionscoremethod', 'maxattempts', 'mingrade',
                'ttslanguage','ttsvoice','ttsspeed','enablepreview','enableshadow','enablelandr' ,'transcriber', 'modelaudiourl','modelaudiobreaks',
                'modelaudiotrans','modelaudiofulltrans','modelaudiomatches',
                'enableai', 'allowearlyexit','expiredays' ,'region', 'activitylink','submitrawaudio','stricttranscribe','recorder','timecreated', 'timemodified'
        ));

        //attempts
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', array('id'), array(
                constants::M_MODNAME . "id", "courseid", "userid", "status", "filename", "wpm", "accuracy",
                "sessionscore", "sessiontime", "sessionerrors", "sessionendword", "errorcount", "timecreated", "timemodified"
        ));

        //ai results
        $airesults = new backup_nested_element('airesults');
        $airesult = new backup_nested_element('airesult', array('id'), array(
                constants::M_MODNAME . "id", "courseid", "attemptid", "transcript", "fulltranscript", "wpm", "accuracy",
                "sessionscore", "sessiontime", "sessionerrors", "sessionmatches", "sessionendword", "errorcount", "timecreated",
                "timemodified"
        ));

        // Build the tree.
        $oneactivity->add_child($attempts);
        $attempts->add_child($attempt);
        $attempt->add_child($airesults);
        $airesults->add_child($airesult);

        // Define sources.
        $oneactivity->set_source_table(constants::M_TABLE, array('id' => backup::VAR_ACTIVITYID));

        //sources if including user info
        if ($userinfo) {
            $attempt->set_source_table(constants::M_USERTABLE,
                    array(constants::M_MODNAME . 'id' => backup::VAR_PARENTID));
            $airesult->set_source_table(constants::M_AITABLE,
                    array(constants::M_MODNAME . 'id' => backup::VAR_ACTIVITYID,
                            'attemptid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $attempt->annotate_ids('user', 'userid');

        // Define file annotations.
        // intro file area has 0 itemid.
        $oneactivity->annotate_files(constants::M_COMPONENT, 'intro', null);
        $oneactivity->annotate_files(constants::M_COMPONENT, 'welcome', null);
        $oneactivity->annotate_files(constants::M_COMPONENT, 'passage', null);
        $oneactivity->annotate_files(constants::M_COMPONENT, 'feedback', null);

        //file annotation if including user info
        if ($userinfo) {
            $attempt->annotate_files(constants::M_COMPONENT, constants::M_FILEAREA_SUBMISSIONS, 'id');
        }

        // Return the root element, wrapped into standard activity structure.
        return $this->prepare_activity_structure($oneactivity);

    }
}
