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
* Instance add/edit form
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/accredible/lib.php');
require_once($CFG->dirroot.'/mod/accredible/locallib.php');

class mod_accredible_mod_form extends moodleform_mod {

    function definition() {
        global $DB, $OUTPUT, $CFG;
        $updatingcert = false;
        $alreadyexists = false;
        // Make sure the API key is set
        if(!isset($CFG->accredible_api_key)) {
            print_error('Please set your API Key first in the plugin settings.');
        }
        // Update form init
        if (optional_param('update', '', PARAM_INT)) {
            $updatingcert = true;
            $cm_id = optional_param('update', '', PARAM_INT);
            $cm = get_coursemodule_from_id('accredible', $cm_id, 0, false, MUST_EXIST);
            $id = $cm->course;
            $course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST);
            $accredible_certificate = $DB->get_record('accredible', array('id'=> $cm->instance), '*', MUST_EXIST);
        } 
        // New form init
        elseif(optional_param('course', '', PARAM_INT)) {
            $id =  optional_param('course', '', PARAM_INT);
            $course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST);
            // see if other accredible certificates already exist for this course
            $alreadyexists = $DB->record_exists('accredible', array('course' => $id));
        }

        // Load user data
        $context = context_course::instance($course->id);
        $users = get_enrolled_users($context, "mod/accredible:view", null, 'u.*');

        // Load final quiz choices
        $quiz_choices = array(0 => 'None');
        if($quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $id) )) {
            foreach( $quizes as $quiz ) { 
                $quiz_choices[$quiz->id] = $quiz->name;
            }
        }

        // Form start
        $mform =& $this->_form;
        $mform->addElement('hidden', 'course', $id);
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static', 'overview', get_string('overview', 'accredible'), get_string('activitydescription', 'accredible'));
        if($alreadyexists) {
            $mform->addElement('static', 'additionalactivitiesone', '', get_string('additionalactivitiesone', 'accredible'));
        }
        $mform->addElement('text', 'name', get_string('activityname', 'accredible'), array('style'=>'width: 399px'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $course->fullname);

        if($alreadyexists) {
            $mform->addElement('static', 'additionalactivitiestwo', '', get_string('additionalactivitiestwo', 'accredible'));
        }

        // If we're updating and have a group then let the issuer choose to edit this
        if($updatingcert && $accredible_certificate->groupid){
            // Grab the list of groups available
            $templates = accredible_get_groups();
            $mform->addElement('static', 'usestemplatesdescription', '', get_string('usestemplatesdescription', 'accredible'));
            $mform->addElement('select', 'groupid', get_string('templatename', 'accredible'), $templates);
            $mform->addRule('groupid', null, 'required', null, 'client');
            $mform->setDefault('groupid', $accredible_certificate->groupid);
        }

        if($updatingcert && $accredible_certificate->achievementid){
            // Grab the list of templates available
            $templates = accredible_get_templates();
            $mform->addElement('static', 'usestemplatesdescription', '', get_string('usestemplatesdescription', 'accredible'));
            $mform->addElement('select', 'achievementid', get_string('groupselect', 'accredible'), $templates);
            $mform->addRule('achievementid', null, 'required', null, 'client');
            $mform->setDefault('achievementid', $course->shortname);

            if($alreadyexists) {
                $mform->addElement('static', 'additionalactivitiesthree', '', get_string('additionalactivitiesthree', 'accredible'));
            }
            $mform->addElement('text', 'certificatename', get_string('certificatename', 'accredible'), array('style'=>'width: 399px'));
            $mform->addRule('certificatename', null, 'required', null, 'client');
            $mform->setType('certificatename', PARAM_TEXT);
            $mform->setDefault('certificatename', $course->fullname);

            $mform->addElement('textarea', 'description', get_string('description', 'accredible'), array('cols'=>'64', 'rows'=>'10', 'wrap'=>'virtual', 'maxlength' => '1000'));
            $mform->addRule('description', null, 'required', null, 'client');
            $mform->setType('description', PARAM_RAW);
            $mform->setDefault('description', strip_tags($course->summary));
            if($updatingcert) {
                $mform->addElement('static', 'dashboardlink', get_string('dashboardlink', 'accredible'), get_string('dashboardlinktext', 'accredible'));
            }
        }

        $mform->addElement('header', 'chooseusers', get_string('manualheader', 'accredible'));
        $this->add_checkbox_controller(1, 'Select All/None');

        if($updatingcert) {
            // Grab existing certificates and cross-reference emails
            $certificates = accredible_get_issued($accredible_certificate->achievementid);
            foreach ($users as $user) {
                $cert_id = null;
                // check cert emails for this user
                foreach ($certificates as $certificate) {
                    if($certificate->recipient->email == $user->email) {
                        $cert_id = $certificate->id;
                        if($certificate->private) {
                            $cert_link = $certificate->id . '?key=' . $certificate->private_key;
                        }
                        else {
                            $cert_link = $cert_id;
                        }
                    }
                }
                // show the certificate if they have a certificate
                if( $cert_id ) {
                    $mform->addElement('static', 'certlink'.$user->id, $user->firstname . ' ' . $user->lastname, "Certificate $cert_id - <a href='https://accredible.com/$cert_link' target='_blank'>link</a>");
                } // show a checkbox if they don't
                else {
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname, null, array('group' => 1));
                }
            }
        }
        // For new modules, just list all the users
        else {
            foreach( $users as $user ) { 
                $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname, null, array('group' => 1));
            }
        }


        $mform->addElement('header', 'gradeissue', get_string('gradeissueheader', 'accredible'));
        $mform->addElement('select', 'finalquiz', get_string('chooseexam', 'accredible'), $quiz_choices);
        $mform->addElement('text', 'passinggrade', get_string('passinggrade', 'accredible'));
        $mform->setType('passinggrade', PARAM_INT);
        $mform->setDefault('passinggrade', 70);



        $mform->addElement('header', 'completionissue', get_string('completionissueheader', 'accredible'));
        $this->add_checkbox_controller(2, 'Select All/None');
        if($updatingcert) {
            $completion_activity_ids = unserialize_completion_array($accredible_certificate->completionactivities);
            foreach ($quizes as $quiz) {
                $mform->addElement('advcheckbox', 'activities['.$quiz->id.']', 'Quiz', $quiz->name, array('group' => 2));
                if(isset( $completion_activity_ids[$quiz->id] )) {
                    $mform->setDefault('activities['.$quiz->id.']', 1);
                }
            }
        } else {
            if($quizes) {
                foreach ($quizes as $quiz) {
                    $mform->addElement('advcheckbox', 'activities['.$quiz->id.']', 'Quiz', $quiz->name, array('group' => 2));
                }
            }   
        }


        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
