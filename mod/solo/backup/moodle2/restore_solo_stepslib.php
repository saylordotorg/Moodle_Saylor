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
 * @package   mod_solo
 * @copyright 2019 Justin Hunt poodllsupport@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod_solo\constants;

/**
 * Define all the restore steps that will be used by the restore_solo_activity_task
 */

/**
 * Structure step to restore one solo activity
 */
class restore_solo_activity_structure_step extends restore_activity_structure_step {


    protected function define_structure() {



        $paths = array();

        $userinfo = $this->get_setting_value('userinfo'); // are we including userinfo?

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - non-user data
        ////////////////////////////////////////////////////////////////////////

        // root element describing solo instance
        $oneactivity = new restore_path_element(constants::M_MODNAME, '/activity/solo');
        $topics = new restore_path_element('topics', '/activity/solo/topics/topic');
        $selectedtopics = new restore_path_element('selectedtopics', '/activity/solo/selectedtopics/selectedtopic');

        $paths[] = $oneactivity;
        $paths[] = $topics;
        $paths[] = $selectedtopics;


        // End here if no-user data has been selected
        if (!$userinfo) {
            return $this->prepare_activity_structure($paths);
        }

        ////////////////////////////////////////////////////////////////////////
        // XML interesting paths - user data
        ////////////////////////////////////////////////////////////////////////
        $attempts = new restore_path_element('attempts', '/activity/solo/attempts/attempt');
        $attemptstats = new restore_path_element('attemptstats', '/activity/solo/attempts/attempt/attemptstats/attemptstat');
        $airesults = new restore_path_element('airesults', '/activity/solo/attempts/attempt/airesults/airesult');
        $paths[] = $attempts;
        $paths[] = $attemptstats;
        $paths[] = $airesults;


        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_solo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);


        // insert the activity record
        $newid = $DB->insert_record(constants::M_TABLE, $data);
        $this->set_mapping(constants::M_MODNAME, $oldid, $newid, false);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newid);
    }

    protected function process_attempts($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);


        $data->{constants::M_MODNAME} = $this->get_new_parentid(constants::M_MODNAME);
        $newid = $DB->insert_record(constants::M_ATTEMPTSTABLE, $data);
        //store id so that attemptstats can use it
        $this->set_mapping('attempts', $oldid, $newid, false);
    }

    protected function process_attemptstats($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);


        $data->{constants::M_MODNAME} = $this->get_new_parentid(constants::M_MODNAME);
        $data->attemptid = $this->get_new_parentid('attempts');
        $newid = $DB->insert_record(constants::M_STATSTABLE, $data);
    }

    protected function process_airesults($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->timecreated = $this->apply_date_offset($data->timecreated);


        $data->moduleid = $this->get_new_parentid(constants::M_MODNAME);
        $data->attemptid = $this->get_new_parentid('attempts');
        $newid = $DB->insert_record(constants::M_AITABLE, $data);
    }

    protected function process_topics($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->courseid = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->moduleid = $this->get_new_parentid(constants::M_MODNAME);
        $existingtopic =false;
        if($data->topiclevel==constants::M_TOPICLEVEL_COURSE) {
            //we do not want to add the same course topic if users are duplicating or restoring into a course with topic data
            //so here we first check if topic exists.

            //its logically possible for a user to add more than one course level topic of same name
            //we don't complain so we just use IGNORE_MULTIPLE and take the first one
            $existingtopic = $DB->get_record(constants::M_TOPIC_TABLE,
                    array('name' => $data->name, 'topiclevel' => constants::M_TOPICLEVEL_COURSE),'*',IGNORE_MULTIPLE);
        }
        if(!$existingtopic) {
            $newid = $DB->insert_record(constants::M_TOPIC_TABLE, $data);
            $this->set_mapping('topics', $oldid, $newid, false);
        }else {
            //store id so that selectedtopics can use it
            $this->set_mapping('topics', $oldid, $existingtopic->id, false);
        }

    }

    protected function process_selectedtopics($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->moduleid = $this->get_new_parentid(constants::M_MODNAME);

        $topics = $DB->get_records(constants::M_TOPIC_TABLE,array('name'=>$data->name, 'courseid'=>$this->get_courseid()));
        $topicid=false;
        if($topics){
            //if we already have the same topic (prob. a duplicate activity in same course), we use that
            foreach($topics as $topic){
                if($topic->id == $data->topicid &&
                        $topic->topiclevel == constants::M_TOPICLEVEL_COURSE
                        && $data->topiclevel == constants::M_TOPICLEVEL_COURSE){
                    $topicid = $data->topicid;
                    break;
                }
            }
            //if we already have the same topicname available in this course, and its course level, and so are we, we use it
            if(!$topicid) {
                foreach ($topics as $topic) {
                    if ($topic->topiclevel == constants::M_TOPICLEVEL_COURSE
                            && $data->topiclevel == constants::M_TOPICLEVEL_COURSE) {
                        $topicid=$topic->id;
                        break;
                    }
                }
            }
            //if we already have the same topicname available in this module and its custom we use it
            if(!$topicid) {
                foreach ($topics as $topic) {
                    if ($topic->topiclevel == constants::M_TOPICLEVEL_CUSTOM
                            && $data->topiclevel == constants::M_TOPICLEVEL_CUSTOM &&
                            $topic->moduleid == $this->get_new_parentid(constants::M_MODNAME)) {
                        $topicid=$topic->id;
                        break;
                    }
                }
            }
            //create a new topic, if there is nothing pre-existing we should use
            if(!$topicid){
                $newtopic = new stdClass();
                $newtopic->name = $data->name;
                $newtopic->courseid = $this->get_courseid();
                $newtopic->topiclevel = $data->topiclevel;
                $newtopic->moduleid = $this->get_new_parentid(constants::M_MODNAME);
                $newtopic->fonticon = $data->fonticon;
                $newtopic->targetwords = $data->targetwords;
                $newtopic->timemodified = time();
                $topicid = $DB->insert_record(constants::M_TOPIC_TABLE,$newtopic);
            }
        }
        //Add our selected topic
        if($topicid) {
            $data->topicid = $topicid;
            $newid = $DB->insert_record(constants::M_SELECTEDTOPIC_TABLE, $data);
        }
    }

    protected function after_execute() {
        // Add module related files, no need to match by itemname (just internally handled context)
        $this->add_related_files(constants::M_COMPONENT, constants::M_FILEAREA_MODELMEDIA, null);
        $this->add_related_files(constants::M_COMPONENT, constants::M_FILEAREA_TOPICMEDIA, null);
        $this->add_related_files(constants::M_COMPONENT, 'intro', null);

		//question stuff
		 $userinfo = $this->get_setting_value('userinfo'); // are we including userinfo?
		 if($userinfo){
			//we do nothing here
         }
    }
}
