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
 * Defines all the backup steps that will be used by {@link backup_solo_activity_task}
 *
 * @package     mod_solo
 * @category    backup
 * @copyright   2015 Justin Hunt (poodllsupport@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;

/**
 * Defines the complete webquest structure for backup, with file and id annotations
 *
 */
class backup_solo_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the solo element inside the webquest.xml file
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // are we including userinfo?
        $userinfo = $this->get_setting_value('userinfo');

        ////////////////////////////////////////////////////////////////////////
        // XML nodes declaration - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing solo instance
        $oneactivity = new backup_nested_element(constants::M_MODNAME, array('id'), array(
            'course','name','intro','introformat','grade','gradeoptions','manualgraded','mingrade',
                'convlength','maxconvlength','speakingtopic','topictts','topicttsvoice','topiciframe','targetwords','tips','tipsformat',
            'ttslanguage','recorderskin','recordertype','enableai','enabletranscribe','expiredays','region','transcriber','multiattempts',
                'enabletranscription','enablesharing','enableautograde','gradewordgoal','autogradeoptions','completionallsteps',
                'postattemptedit','foriframe','timecreated','timemodified','viewstart','viewend'
			));

        // attempt
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', array('id'),array(
            constants::M_MODNAME, 'userid', 'type','visible','filename', 'transcript','jsontranscript','vtttranscript',
            'selftranscript','topictargetwords','mywords','convlength',
            'customtext1', 'customtext1format','completedsteps',
            'currentint1','currentint2','currentint3','currentint4',
            'allowsharing', 'timemodified','createdby','modifiedby'));

        //attemptstats
        $attemptstats = new backup_nested_element('attemptstats');
        $attemptstat = new backup_nested_element('attemptstat', array('id'),array(
                constants::M_MODNAME, 'userid', 'attemptid','turns','words','avturn', 'longestturn','targetwords','totaltargetwords',
                'uniquewords','longwords', 'autospell','autogrammar','autospellscore','autospellerrors',
                'autogrammarscore','autogrammarerrors','aiaccuracy' ,'timemodified','createdby','modifiedby'));

        //airesults
        $airesults = new backup_nested_element('airesults');
        $airesult = new backup_nested_element('airesult', array('id'),array(
                'courseid', 'moduleid', 'attemptid','transcript','passage','jsontranscript', 'wpm','accuracy','sessionscore',
                'sessiontime', 'sessionerrors', 'sessionmatches','sessionendword','errorcount',
                'timecreated', 'timemodified'));


        //topics
        $topics = new backup_nested_element('topics');
        $topic = new backup_nested_element('topic', array('id'),array(
                'topiclevel', 'courseid', 'moduleid','name','fonticon','targetwords', 'timemodified'));


        //selected topics
        $selectedtopics = new backup_nested_element('selectedtopics');
        $selectedtopic = new backup_nested_element('selectedtopic', array('id'),array(
                'moduleid','topicid', 'timemodified',
                //we include some topic data for the restore processing
                'topiclevel', 'name','fonticon','targetwords'));



        // Build the tree.
         //attempts
        $oneactivity->add_child($attempts);
        $attempts->add_child($attempt);

        //topics
        $oneactivity->add_child($topics);
        $topics->add_child($topic);

        //selected topics
        $oneactivity->add_child($selectedtopics);
        $selectedtopics->add_child($selectedtopic);

        //attempt stats
        $attempt->add_child($attemptstats);
        $attemptstats->add_child($attemptstat);

        //airesults
        $attempt->add_child($airesults);
        $airesults->add_child($airesult);


        // Define sources.
        $oneactivity->set_source_table(constants::M_TABLE, array('id' => backup::VAR_ACTIVITYID));
        $topic->set_source_table(constants::M_TOPIC_TABLE, array('moduleid' => backup::VAR_ACTIVITYID));

       // $selectedtopic->set_source_table(constants::M_TOPICSELECTED_TABLE, array('moduleid'=>backup::VAR_ACTIVITYID));
        $selectedtopic->set_source_sql('
            SELECT ts.*, topic.name, topic.topiclevel, topic.targetwords, topic.fonticon
              FROM {'. constants::M_SELECTEDTOPIC_TABLE .'} ts
              INNER JOIN {'. constants::M_TOPIC_TABLE .'} topic ON ts.topicid = topic.id
             WHERE ts.moduleid = ?',
                array(backup::VAR_ACTIVITYID));


        //sources if including user info
        if ($userinfo) {
            $attempt->set_source_table(constants::M_ATTEMPTSTABLE,
                array(constants::M_MODNAME => backup::VAR_PARENTID));
            $attemptstat->set_source_table(constants::M_STATSTABLE,
                    array('attemptid' => backup::VAR_PARENTID,
                            constants::M_MODNAME => backup::VAR_ACTIVITYID));
            $airesult->set_source_table(constants::M_AITABLE,
                    array('attemptid' => backup::VAR_PARENTID,
                            'moduleid' => backup::VAR_ACTIVITYID));
        }

        // Define id annotations.
        $attempt->annotate_ids('user', 'userid');
        $attemptstat->annotate_ids('user', 'userid');


        // Define file annotations.
        // intro file area has 0 itemid.
        $oneactivity->annotate_files(constants::M_COMPONENT, 'intro', null);
        $oneactivity->annotate_files(constants::M_COMPONENT, constants::M_FILEAREA_TOPICMEDIA, null);
		
		//file annotation if including user info
        if ($userinfo) {
            $attempt->annotate_files(constants::M_COMPONENT, constants::M_FILEAREA_SUBMISSIONS, 'id');
        }
		
        // Return the root element, wrapped into standard activity structure.
        return $this->prepare_activity_structure($oneactivity);
		

    }
}
