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

use mod_accredible\Html2Text\Html2Text;

class mod_accredible_mod_form extends moodleform_mod {

    function definition() {
        global $DB, $COURSE, $CFG;
        $updatingcert = false;
        $alreadyexists = false;

        if (!extension_loaded('mbstring')) {
            print_error('You or administrator must install mbstring extensions of php.');
        }

        if (!extension_loaded('dom')) {
            print_error('You or administrator must install dom extensions of php.');
        }

        $description = Html2Text::convert($COURSE->summary);
        if(empty($description)){
            $description = "Recipient has compeleted the achievement.";
        }

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
        } else if(optional_param('course', '', PARAM_INT)) { // New form init
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
            $mform->setDefault('description', $description);
            if($updatingcert) {
                $mform->addElement('static', 'dashboardlink', get_string('dashboardlink', 'accredible'), get_string('dashboardlinktext', 'accredible'));
            }
        }

        // Get certificates if updating
        if ($updatingcert) {
            // Grab existing certificates and cross-reference emails
            if ($accredible_certificate->achievementid) {
                $certificates = accredible_get_credentials($accredible_certificate->achievementid);
            }
            elseif ($accredible_certificate->groupid) {
                $certificates = accredible_get_credentials($accredible_certificate->groupid);
            }
        }

        // Generate list of users who have earned a certificate, if updating
        if ($updatingcert) {
            foreach ($users as $user) {
                // If this user has completed the criteria to earn a certificate, add to $users_earned_certificate
                if(accredible_check_if_cert_earned($accredible_certificate, $course, $user)) {
                    $users_earned_certificate[$user->id] = $user;
                }
            }
        }

        // Unissued certificates header
        if (isset($users_earned_certificate) && count($users_earned_certificate) > 0) {
            $unissued_header = false;
            foreach ($users_earned_certificate as $user) {
                $existing_certificate = false;

                foreach ($certificates as $certificate) {
                    // Search through the certificates to see if this user has one existing
                    if ($certificate->recipient->email == $user->email) {
                        // This user has an existing certificate, no need to continue searching
                        $existing_certificate = true;
                        break;
                    }
                }

                if (!$existing_certificate) {
                    if (!$unissued_header) {
                        // The header has not been added to the form yet and is needed
                        $mform->addElement('header', 'chooseunissuedusers', get_string('unissuedheader', 'accredible'));
                        $mform->addElement('static', 'unissueddescription', '', get_string('unissueddescription', 'accredible'));
                        $this->add_checkbox_controller(2, 'Select All/None');
                        $unissued_header = true;
                    }
                    // No existing certificate, add this user to the unissued users list
                    $mform->addElement('advcheckbox', 'unissuedusers['.$user->id.']', $user->firstname . ' ' . $user->lastname . '    ' . $user->email, null, array('group' => 2));
                }

            }
        }

        // Manually issue certificates header
        $mform->addElement('header', 'chooseusers', get_string('manualheader', 'accredible'));
        $this->add_checkbox_controller(1, 'Select All/None');

        if($updatingcert) {

            // Grab existing credentials and cross-reference emails
            if($accredible_certificate->achievementid){
                $certificates = accredible_get_credentials($accredible_certificate->achievementid);
            } else {
                $certificates = accredible_get_credentials($accredible_certificate->groupid);
            }
            
            foreach ($users as $user) {
                $cert_id = null;
                // check cert emails for this user
                foreach ($certificates as $certificate) {
                    if($certificate->recipient->email == $user->email) {
                        $cert_id = $certificate->id;
                        if(isset($certificate->url)) {
                            $cert_link = $certificate->url;
                        } else {
                            $cert_link = 'https://www.credential.net/'.$cert_id;
                        }
                    }
                }
                // show the certificate if they have a certificate
                if( $cert_id ) {
                    $mform->addElement('static', 'certlink'.$user->id, $user->firstname . ' ' . $user->lastname . '    ' . $user->email, "Certificate $cert_id - <a href='$cert_link' target='_blank'>link</a>");
                } // show a checkbox if they don't
                else {
                    $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname . '    ' . $user->email, null, array('group' => 1));
                }
            }
        }
        // For new modules, just list all the users
        else {
            foreach( $users as $user ) { 
                $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname . '    ' . $user->email, null, array('group' => 1));
            }
        }

        $mform->addElement('header', 'gradeissue', get_string('gradeissueheader', 'accredible'));
        $mform->addElement('select', 'finalquiz', get_string('chooseexam', 'accredible'), $quiz_choices);
        $mform->addElement('text', 'passinggrade', get_string('passinggrade', 'accredible'));
        $mform->setType('passinggrade', PARAM_INT);
        $mform->setDefault('passinggrade', 70);

        $mform->addElement('header', 'completionissue', get_string('completionissueheader', 'accredible'));
        if($updatingcert) {

            $mform->addElement('checkbox', 'completionactivities', get_string('completionissuecheckbox', 'accredible'));
            if(isset( $accredible_certificate->completionactivities )) {
                $mform->setDefault('completionactivities', 1);
            }
        } else {
            $mform->addElement('checkbox', 'completionactivities', get_string('completionissuecheckbox', 'accredible'));
            // $mform->addElement('advcheckbox', 'activities['.$quiz->id.']', 'Quiz', $quiz->name, array('group' => 2));
            // $mform->addElement('advcheckbox', 'users['.$user->id.']', $user->firstname . ' ' . $user->lastname, null, array('group' => 1));

            // if($quizes) {
            //     foreach ($quizes as $quiz) {
            //         $mform->addElement('advcheckbox', 'activities['.$quiz->id.']', 'Quiz', $quiz->name, array('group' => 2));
            //     }
            // }   
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
