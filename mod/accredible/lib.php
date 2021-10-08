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
 * Certificate module core interaction API
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/accredible/locallib.php');

/**
 * Add certificate instance.
 *
 * @param array $certificate
 * @return array $certificate new certificate object
 */
function accredible_add_instance($post) {
    global $DB;

    $course = $DB->get_record('course', array('id' => $post->course), '*', MUST_EXIST);

    $post->groupid = isset($post->groupid) ? $post->groupid : null;

    $post->instance = isset($post->instance) ? $post->instance : null;

    $groupid = sync_course_with_accredible($course, $post->instance, $post->groupid);

    // Issue certs.
    if ( isset($post->users) ) {
        // Checklist array from the form comes in the format:
        // Int userid => boolean issuecertificate.
        foreach ($post->users as $userid => $issuecertificate) {
            if ($issuecertificate) {
                $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

                $credential = create_credential($user, $groupid);

                // Evidence item posts.
                $credentialid = $credential->id;
                if ($post->finalquiz) {
                    $quiz = $DB->get_record('quiz', array('id' => $post->finalquiz), '*', MUST_EXIST);
                    $usersgrade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                    $gradeevidence = array('string_object' => (string) $usersgrade,
                        'description' => $quiz->name, 'custom' => true, 'category' => 'grade');
                    if ($usersgrade < 50) {
                        $gradeevidence['hidden'] = true;
                    }
                    accredible_post_evidence($credentialid, $gradeevidence, true);
                }
                if ($transcript = accredible_get_transcript($post->course, $userid, $post->finalquiz)) {
                    accredible_post_evidence($credentialid, $transcript, true);
                }
                accredible_post_essay_answers($userid, $post->course, $credentialid);
                accredible_course_duration_evidence($userid, $post->course, $credentialid);
            }
        }
    }

    // Save record.
    $dbrecord = new stdClass();
    $dbrecord->completionactivities = isset($post->completionactivities) ? $post->completionactivities : null;
    $dbrecord->name = $post->name;
    $dbrecord->course = $post->course;
    $dbrecord->finalquiz = $post->finalquiz;
    $dbrecord->passinggrade = $post->passinggrade;
    $dbrecord->timecreated = time();
    $dbrecord->groupid = $groupid;

    return $DB->insert_record('accredible', $dbrecord);
}

/**
 * Update certificate instance.
 *
 * @param stdClass $post
 * @return stdClass $certificate updated
 */
function accredible_update_instance($post) {
    // To update your certificate details, go to accredible.com.
    global $DB;

    $accrediblecertificate = $DB->get_record('accredible', array('id' => $post->instance), '*', MUST_EXIST);

    $course = $DB->get_record('course', array('id' => $post->course), '*', MUST_EXIST);

    // Update the group if we have one to sync with.
    if ($accrediblecertificate->groupid) {
        sync_course_with_accredible($course, $post->instance, $post->groupid);
    }

    // Issue certs for unissued users.
    if (isset($post->unissuedusers)) {
        // Checklist array from the form comes in the format:
        // Int userid => boolean issuecertificate.
        if ($accrediblecertificate->achievementid) {
            $groupid = $accrediblecertificate->achievementid;
        } else if ($accrediblecertificate->groupid) {
            $groupid = $accrediblecertificate->groupid;
        }
        foreach ($post->unissuedusers as $userid => $issuecertificate) {
            if ($issuecertificate) {
                $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                $completedtimestamp = accredible_manual_issue_completion_timestamp($accrediblecertificate, $user);
                $completeddate = date('Y-m-d', (int) $completedtimestamp);
                if ($accrediblecertificate->groupid) {
                    // Create the credential.
                    $result = create_credential($user, $groupid, null, $completeddate);
                    $credentialid = $result->id;
                    // Evidence item posts.
                    if ($post->finalquiz) {
                        $quiz = $DB->get_record('quiz', array('id' => $post->finalquiz), '*', MUST_EXIST);
                        $usersgrade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                        $gradeevidence = array('string_object' => (string) $usersgrade,
                            'description' => $quiz->name, 'custom' => true, 'category' => 'grade');
                        if ($usersgrade < 50) {
                            $gradeevidence['hidden'] = true;
                        }
                        accredible_post_evidence($credentialid, $gradeevidence, true);
                    }
                    if ($transcript = accredible_get_transcript($post->course, $userid, $post->finalquiz)) {
                        accredible_post_evidence($credentialid, $transcript, true);
                    }
                    accredible_post_essay_answers($userid, $post->course, $credentialid);
                    accredible_course_duration_evidence($userid, $post->course, $credentialid, $completedtimestamp);
                } else if ($accrediblecertificate->achievementid) {
                    if ($post->finalquiz) {
                        $quiz = $DB->get_record('quiz', array('id' => $post->finalquiz), '*', MUST_EXIST);
                        $grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                    }
                    // TODO: testing.
                    $result = accredible_issue_default_certificate($user->id,
                        $accrediblecertificate->id, fullname($user), $user->email,
                        $grade, $quiz->name, $completedtimestamp);
                    $credentialid = $result->credential->id;
                }
                // Log the creation.
                $event = accredible_log_creation(
                    $credentialid,
                    $user->id,
                    null,
                    $post->coursemodule
                );
                $event->trigger();
            }
        }
    }

    // Issue certs.
    if ( isset($post->users) ) {
        // Checklist array from the form comes in the format:
        // Int userid => boolean issuecertificate.
        foreach ($post->users as $userid => $issuecertificate) {
            if ($issuecertificate) {
                $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                $completedtimestamp = accredible_manual_issue_completion_timestamp($accrediblecertificate, $user);
                $completeddate = date('Y-m-d', (int) $completedtimestamp);
                if ($accrediblecertificate->achievementid) {

                    $courseurl = new moodle_url('/course/view.php', array('id' => $post->course));
                    $courselink = $courseurl->__toString();

                    $credential = create_credential_legacy($user, $post->achievementid,
                        $post->certificatename, $post->description, $courselink, $completeddate);
                } else {
                    $credential = create_credential($user, $accrediblecertificate->groupid, null, $completeddate);
                }

                // Evidence item posts.
                $credentialid = $credential->id;
                if ($post->finalquiz) {
                    $quiz = $DB->get_record('quiz', array('id' => $post->finalquiz), '*', MUST_EXIST);
                    $usersgrade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                    $gradeevidence = array('string_object' => (string) $usersgrade,
                        'description' => $quiz->name, 'custom' => true, 'category' => 'grade');
                    if ($usersgrade < 50) {
                        $gradeevidence['hidden'] = true;
                    }
                    accredible_post_evidence($credentialid, $gradeevidence, true);
                }
                if ($transcript = accredible_get_transcript($post->course, $userid, $post->finalquiz)) {
                    accredible_post_evidence($credentialid, $transcript, true);
                }
                accredible_post_essay_answers($userid, $post->course, $credentialid);
                accredible_course_duration_evidence($userid, $post->course, $credentialid, $completedtimestamp);

                // Log the creation.
                $event = accredible_log_creation(
                    $credentialid,
                    $userid,
                    null,
                    $post->coursemodule
                );
                $event->trigger();
            }
        }
    }

    // If the group was changed we should save that.
    if (!$accrediblecertificate->achievementid && $post->groupid) {
        $groupid = $post->groupid;
    } else {
        $groupid = $accrediblecertificate->groupid;
    }

    // Set completion activitied to 0 if unchecked.
    if (!property_exists($post, 'completionactivities')) {
        $post->completionactivities = 0;
    }

    // Save record.
    if ($accrediblecertificate->achievementid) {
        $dbrecord = new stdClass();
        $dbrecord->id = $post->instance;
        $dbrecord->achievementid = $post->achievementid;
        $dbrecord->completionactivities = $post->completionactivities;
        $dbrecord->name = $post->name;
        $dbrecord->certificatename = $post->certificatename;
        $dbrecord->description = $post->description;
        $dbrecord->passinggrade = $post->passinggrade;
        $dbrecord->finalquiz = $post->finalquiz;
    } else {
        $dbrecord = new stdClass();
        $dbrecord->id = $post->instance;
        $dbrecord->completionactivities = $post->completionactivities;
        $dbrecord->name = $post->name;
        $dbrecord->course = $post->course;
        $dbrecord->finalquiz = $post->finalquiz;
        $dbrecord->passinggrade = $post->passinggrade;
        $dbrecord->timecreated = time();
        $dbrecord->groupid = $groupid;
    }

    return $DB->update_record('accredible', $dbrecord);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance.
 *
 * @param int $id
 * @return bool true if successful
 */
function accredible_delete_instance($id) {
    global $DB;

    // Ensure the certificate exists.
    if (!$certificate = $DB->get_record('accredible', array('id' => $id))) {
        return false;
    }

    return $DB->delete_records('accredible', array('id' => $id));
}

/**
 * Supported feature list
 *
 * @uses FEATURE_MOD_INTRO
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function accredible_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return false;
        default:
            return null;
    }
}
