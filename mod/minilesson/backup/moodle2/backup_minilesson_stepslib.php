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
 * Defines all the backup steps that will be used by {@link backup_minilesson_activity_task}
 *
 * @package     mod_minilesson
 * @category    backup
 * @copyright   2015 Justin Hunt (poodllsupport@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_minilesson\constants;

/**
 * Defines the complete webquest structure for backup, with file and id annotations
 *
 */
class backup_minilesson_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the minilesson element inside the webquest.xml file
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // are we including userinfo?
        $userinfo = $this->get_setting_value('userinfo');

        ////////////////////////////////////////////////////////////////////////
        // XML nodes declaration - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing minilesson instance
        $oneactivity = new backup_nested_element(constants::M_MODNAME, array('id'), array(
            'course','name','intro','introformat','timelimit',
                //'welcome','welcomeformat',
            'grade','gradeoptions','maxattempts','mingrade','richtextprompt',
            'ttslanguage','transcriber','region','activitylink','pagelayout','showqtitles','timecreated','timemodified'
			));

		
		//attempts
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', array('id'),array(
			"moduleid","courseid","userid","status",
			"sessionscore","sessiontime","sessiondata","sessionend","errorcount",
            "notes","qtextanswer1","qtextscore1","timecreated","timemodified"
		));



        // rsquestion
        $rsquestions = new backup_nested_element('rsquestions');
        $rsquestion = new backup_nested_element('rsquestion', array('id'),array(
            constants::M_MODNAME, 'name','itemorder', 'type','visible','itemtext', 'itemtextformat','itemtts','itemttsvoice','itemttsoption',
                'itemaudiofname','itemtextarea', 'customtext1', 'customtext1format','customtext2', 'customtext2format','customtext3',
            'customtext3format','customtext4', 'customtext4format','customtext5', 'customtext5format',
                'customdata1','customdata2', 'customdata3','customdata4', 'customdata5',
                'customint1','customint2', 'customint3','customint4', 'customint5','correctanswer',
            'timemodified','rsquestionkey','passagehash','createdby','modifiedby'));

		
		// Build the tree.
        $oneactivity->add_child($attempts);
        $attempts->add_child($attempt);

        //questions
        $oneactivity->add_child($rsquestions);
        $rsquestions->add_child($rsquestion);


        // Define sources.
        $oneactivity->set_source_table(constants::M_TABLE, array('id' => backup::VAR_ACTIVITYID));
        $rsquestion->set_source_table(constants::M_QTABLE,
            array(constants::M_MODNAME => backup::VAR_PARENTID));

        //sources if including user info
        if ($userinfo) {
			$attempt->set_source_table(constants::M_ATTEMPTSTABLE,
											array('moduleid' => backup::VAR_PARENTID));

        }

        // Define id annotations.
        $attempt->annotate_ids('user', 'userid');


        // Define file annotations.
        // intro file area has 0 itemid.
        $oneactivity->annotate_files(constants::M_COMPONENT, 'intro', null);
		//$oneactivity->annotate_files(constants::M_COMPONENT, 'welcome', null);

		//question stuff
        $rsquestion->annotate_files(constants::M_COMPONENT, constants::TEXTQUESTION_FILEAREA, 'id');
        $rsquestion->annotate_files(constants::M_COMPONENT, constants::MEDIAQUESTION, 'id');

        for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
            $rsquestion->annotate_files(constants::M_COMPONENT, constants::TEXTANSWER_FILEAREA . $anumber, 'id');
        }
		
		//file annotation if including user info
        if ($userinfo) {
			//$attempt->annotate_files(constants::M_COMPONENT, constants::M_FILEAREA_SUBMISSIONS, 'id');
        }
		
        // Return the root element, wrapped into standard activity structure.
        return $this->prepare_activity_structure($oneactivity);
		

    }
}
