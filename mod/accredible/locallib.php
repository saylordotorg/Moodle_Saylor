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

use mod_accredible\apiRest\apiRest;
use mod_accredible\Html2Text\Html2Text;

/**
 * Sync the selected course information with a group on Accredible - returns a group ID.
 * Optionally takes a group ID so we can set it and change the assigned group.
 *
 * @param stdClass $course
 * @param int|null $instance_id
 * @return int $groupid
 */
function sync_course_with_accredible($course, $instance_id = null, $group_id = null) {
    global $DB, $CFG;

    $apiRest = new apiRest();

    $description = Html2Text::convert($course->summary);
    if (empty($description)) {
        $description = "Recipient has compeleted the achievement.";
    }

    // Update an existing.
    if (null != $instance_id) {
        // Get the group id.
        $accredible_certificate = $DB->get_record('accredible', array('id' => $instance_id), '*', MUST_EXIST);

        // Just use the saved group ID.
        $group_id = isset($group_id) ? $group_id : $accredible_certificate->groupid;

        try {
            // Update the group.
            $group = $apiRest->update_group($group_id, null, null, null, new moodle_url('/course/view.php', array('id' => $course->id)));

            return $group->group->id;
        } catch (\Exception $e) {
            // Throw API exception.
            // Include the achievement id that triggered the error.
            // Direct the user to accredible's support.
            // Dump the achievement id to debug_info.
            throw new moodle_exception('groupsyncerror', 'accredible', 'https://help.accredible.com/hc/en-us', $course->id, $course->id);
        }
    } else {
        // Making a new group.
        try {
            // Make a new Group on Accredible - use a random number to deal with duplicate course names.
            $group = $apiRest->create_group($course->shortname . mt_rand(), $course->fullname, $description, new moodle_url('/course/view.php', array('id' => $course->id)));

            return $group->group->id;
        } catch (\Exception $e) {
            // Throw API exception.
            // Include the achievement id that triggered the error.
            // Direct the user to accredible's support.
            // Dump the achievement id to debug_info.
            throw new moodle_exception('groupsyncerror', 'accredible', 'https://help.accredible.com/hc/en-us', $course->id, $course->id);
        }
    }
}

/**
 * List all of the certificates with a specific achievement id
 *
 * @param string $group_id Limit the returned Credentials to a specific group ID.
 * @param string|null $email Limit the returned Credentials to a specific recipient's email address.
 * @return array[stdClass] $credentials
 */
function accredible_get_credentials($group_id, $email= null) {
    global $CFG;

    $page_size = 50;
    $page = 1;
    // Maximum number of pages to request to avoid possible infinite loop.
    $loop_limit = 100;

    $apiRest = new apiRest();

    try {

        $loop = true;
        $count = 0;
        $credentials = array();
        // Query the Accredible API and loop until it returns that there is no next page.
        while ($loop === true) {
            $credentials_page = $apiRest->get_credentials($group_id, $email, $page_size, $page);

            foreach ($credentials_page->credentials as $credential) {
                $credentials[] = $credential;
            }

            $page++;
            $count++;

            if ($credentials_page->meta->next_page === null || $count >= $loop_limit) {
                // If the Accredible API returns that there
                // is no next page, end the loop.
                $loop = false;
            }
        }
        return $credentials;
    } catch (\Exception $e) {
        // Throw API exception.
        // Include the achievement id that triggered the error.
        // Direct the user to accredible's support.
        // Dump the achievement id to debug_info.
        $exceptionparam = new stdClass();
        $exceptionparam->group_id = $group_id;
        $exceptionparam->email = $email;
        $exceptionparam->last_response = $credentials_page;
        throw new moodle_exception('getcredentialserror', 'accredible', 'https://help.accredible.com/hc/en-us', $exceptionparam);
    }
}

/**
 * Check's if a credential exists for an email in a particular group
 * @param int $group_id
 * @param String $email
 * @return array[stdClass] || false
 */
function accredible_check_for_existing_credential($group_id, $email) {
    global $CFG;

    $apiRest = new apiRest();
    try {
        $credentials = $apiRest->get_credentials($group_id, $email);

        if ($credentials->credentials and $credentials->credentials[0]) {
            return $credentials->credentials[0];
        } else {
            return false;
        }
    } catch (\Exception $e) {
        // throw API exception
          // include the achievement id that triggered the error
          // direct the user to accredible's support
          // dump the achievement id to debug_info
          throw new moodle_exception('groupsyncerror', 'accredible', 'https://help.accredible.com/hc/en-us', $group_id, $group_id);
    }
}

/**
 * Checks if a user has earned a specific credential according to the activity settings
 * @param stdObject $record An Accredible activity record
 * @param stdObject $course
 * @param stdObject user
 * @return bool
 */
function accredible_check_if_cert_earned($record, $course, $user) {
    global $DB;

    $earned = false;

    // Check for the existence of an activity instance and an auto-issue rule.
    if ( $record and ($record->finalquiz or $record->completionactivities) ) {

        // Check if we have a groupid or achievementid. Logic is same for both.
        if ($record->groupid) {
            $groupid = $record->groupid;
        } else if ($record->achievementid) {
            $groupid = $record->achievementid;
        }

        if ($record->finalquiz) {
            $quiz = $DB->get_record('quiz', array('id' => $record->finalquiz), '*', MUST_EXIST);

            // Create that credential if it doesn't exist.
            $users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
            $grade_is_high_enough = ($users_grade >= $record->passinggrade);

            // Check for pass.
            if ($grade_is_high_enough) {
                // Student earned certificate through final quiz.
                $earned = true;
            }
        }

        $completion_activities = unserialize_completion_array($record->completionactivities);

        if (!empty($quiz)) {
            // If this quiz is in the completion activities.
            if ( isset($completion_activities[$quiz->id]) ) {
                $completion_activities[$quiz->id] = true;
                $quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished'));
                foreach ($quiz_attempts as $quiz_attempt) {
                    // If this quiz was already attempted, then we shouldn't be issuing a certificate.
                    if ( $quiz_attempt->quiz == $quiz->id && $quiz_attempt->attempt > 1 ) {
                        return null;
                    }
                    // Otherwise, set this quiz as completed.
                    if ( isset($completion_activities[$quiz_attempt->quiz]) ) {
                        $completion_activities[$quiz_attempt->quiz] = true;
                    }
                }

                // But was this the last required activity that was completed?
                $course_complete = true;
                foreach ($completion_activities as $is_complete) {
                    if (!$is_complete) {
                        $course_complete = false;
                    }
                }
                // If it was the final activity.
                if ($course_complete) {
                    // Student earned certificate by completing completion activities.
                    $earned = true;
                }
            }
        }
    }
    return $earned;
}

/**
 * Create a credential given a user and an existing group
 * @param stdObject $user
 * @param int $group_id
 * @return stdObject
 */
function create_credential($user, $group_id, $event = null, $issued_on = null) {
    global $CFG;

    $apiRest = new apiRest();

    try {
        $credential = $apiRest->create_credential(fullname($user), $user->email, $group_id, $issued_on);

        // Log an event now we've created the credential if possible.
        if ($event != null) {
            $certificate_event = \mod_accredible\event\certificate_created::create(array(
                                  'objectid' => $credential->credential->id,
                                  'context' => context_module::instance($event->contextinstanceid),
                                  'relateduserid' => $event->relateduserid,
                                  'issued_on' => $issued_on
                                ));
            $certificate_event->trigger();
        }

        return $credential->credential;

    } catch (\Exception $e) {
        // Throw API exception.
        // Include the achievement id that triggered the error.
        // Direct the user to accredible's support.
        // Dump the achievement id to debug_info.
        throw new moodle_exception('credentialcreateerror', 'accredible', 'https://help.accredible.com/hc/en-us', $user->email, $group_id);
    }
}

/**
 * Create a credential given a user and an existing group
 * @param stdObject $user
 * @param int $group_id
 * @return stdObject
 */
function create_credential_legacy($user, $achievement_name, $course_name, $course_description, $course_link, $issued_on, $event = null){
    global $CFG;

    $apiRest = new apiRest();

    try {
        $credential = $apiRest->create_credential_legacy(fullname($user), $user->email, $achievement_name, $issued_on, null, $course_name, $course_description, $course_link);
        // log an event now we've created the credential if possible
        if ($event != null) {
            $certificate_event = \mod_accredible\event\certificate_created::create(array(
                                  'objectid' => $credential->credential->id,
                                  'context' => context_module::instance($event->contextinstanceid),
                                  'relateduserid' => $event->relateduserid
                                ));
            $certificate_event->trigger();
        }

        return $credential->credential;

    } catch (\Exception $e) {
        // Throw API exception.
        // Include the achievement id that triggered the error.
        // Direct the user to accredible's support.
        // Dump the achievement id to debug_info.
        throw new moodle_exception('credentialcreateerror', 'accredible', 'https://help.accredible.com/hc/en-us', $user->email, $credential->credential->group_id);
    }
}

/**
 * Get the groups for the issuer
 * @return type
 */
function accredible_get_groups() {
    global $CFG;

    $apiRest = new apiRest();
    try {
        $response = $apiRest->get_groups(10000, 1);

        $groups = array();
        for ($i = 0, $size = count($response->groups); $i < $size; ++$i) {
            $groups[$response->groups[$i]->id] = $response->groups[$i]->name;
        }
        return $groups;

    } catch (\Exception $e) {
        // Throw API exception.
        // Include the achievement id that triggered the error.
        // Direct the user to accredible's support.
        // Dump the achievement id to debug_info.
        throw new moodle_exception('getgroupserror', 'accredible', 'https://help.accredible.com/hc/en-us');
    }
}

/**
 * Get the SSO link for a recipient
 * @return type
 */
function accredible_get_recipient_sso_linik($group_id, $email) {
    global $CFG;

    $apiRest = new apiRest();

    try {
        $response = $apiRest->recipient_sso_link(null, null, $email, null, $group_id, null);

        return $response->link;

    } catch (Exception $e) {
        return null;
    }
}

// old below here

/**
 * List all of the issuer's templates
 *
 * @param apiRest $apiRest
 * @return array[stdClass] $templates
 */
function accredible_get_templates($apiRest = null) {
    // An apiRest with a mock client is passed when unit testing.
    if(!$apiRest) {
        $apiRest = new apiRest();
    }
    $response = $apiRest->search_groups(10000, 1);
    if (!isset($response->groups)) {
        // Throw API exception.
        // Include the achievement id that triggered the error.
        // Direct the user to accredible's support.
        // Dump the achievement id to debug_info.
        throw new moodle_exception('gettemplateserror', 'accredible', 'https://help.accredible.com/hc/en-us');
    }

    $groups = $response->groups;
    $templates = array();
    foreach ($groups as $group) { $templates[$group->name] = $group->name; }
    return $templates;
}

/*
 * accredible_issue_default_certificate
 *
 */
function accredible_issue_default_certificate($user_id, $certificate_id, $name, $email, $grade, $quiz_name, $completed_timestamp = null) {
    global $DB, $CFG;

    if (!isset($completed_timestamp)) {
        $completed_timestamp = time();
    }
    $issued_on = date('Y-m-d', (int) $completed_timestamp);

    // Issue certs
    $accredible_certificate = $DB->get_record('accredible', array('id' => $certificate_id));

    $course_url = new moodle_url('/course/view.php', array('id' => $accredible_certificate->course));
    $course_link = $course_url->__toString();

    $restApi = new apiRest();
    $credential = $restApi->create_credential_legacy($name, $email, $accredible_certificate->achievementid, $issued_on, null, $accredible_certificate->certificatename, $accredible_certificate->description, $course_link);

    // Evidence item posts.
    $credential_id = $credential->credential->id;
    if ($grade) {
        if ($grade < 50) {
            $hidden = true;
        } else {
            $hidden = false;
        }

        $response = $restApi->create_evidence_item_grade($grade, $quiz_name, $credential_id, $hidden);
    }

    if ($transcript = accredible_get_transcript($accredible_certificate->course, $user_id, $accredible_certificate->finalquiz)) {
        accredible_post_evidence($credential_id, $transcript, false);
    }

    accredible_post_essay_answers($user_id, $accredible_certificate->course, $credential_id);
    accredible_course_duration_evidence($user_id, $accredible_certificate->course, $credential_id, $completed_timestamp);

    return json_decode($result);
}

/*
 * accredible_log_creation
 */
function accredible_log_creation($certificate_id, $user_id, $course_id, $cm_id) {
    global $DB;

    // Get context.
    $accredible_mod = $DB->get_record('modules', array('name' => 'accredible'), '*', MUST_EXIST);
    if ($cm_id) {
        $cm = $DB->get_record('course_modules', array('id' => (int) $cm_id), '*');
    } else { // This is an activity add, so we have to use $course_id.
        $course_modules = $DB->get_records('course_modules', array('course' => $course_id, 'module' => $accredible_mod->id));
        $cm = end($course_modules);
    }
    $context = context_module::instance($cm->id);

    return \mod_accredible\event\certificate_created::create(array(
        'objectid' => $certificate_id,
        'context' => $context,
        'relateduserid' => $user_id
    ));
}

/*
 * Quiz submission handler (checks for a completed course)
 *
 * @param core/event $event quiz mod attempt_submitted event
 */
function accredible_quiz_submission_handler($event) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/quiz/lib.php');

    $api = new apiRest();

    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);

    $quiz = $event->get_record_snapshot('quiz', $attempt->quiz);
    $user = $DB->get_record('user', array('id' => $event->relateduserid));
    if ($accredible_certificate_records = $DB->get_records('accredible', array('course' => $event->courseid))) {
        foreach ($accredible_certificate_records as $record) {
            // Check for the existence of an activity instance and an auto-issue rule.
            if ( $record and ($record->finalquiz or $record->completionactivities) ) {
                // Check if we have a group mapping - if not use the old logic.
                if ($record->groupid) {
                    // Check which quiz is used as the deciding factor in this course.
                    if ($quiz->id == $record->finalquiz) {
                        // Check for an existing certificate.
                        $existing_certificate = accredible_check_for_existing_credential($record->groupid, $user->email);

                        // Create that credential if it doesn't exist.
                        if (!$existing_certificate) {
                            $users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                            $grade_is_high_enough = ($users_grade >= $record->passinggrade);

                            // Check for pass.
                            if ($grade_is_high_enough) {
                                // Issue a ceritificate.
                                create_credential($user, $record->groupid);
                            }
                        } else {
                            // Check the existing grade to see if this one is higher and update the credential if so.                   
                            $credential = $api->get_credential($existing_certificate->id)->credential;
                            foreach ($credential->evidence_items as $evidence_item) {
                                if ($evidence_item->type == "grade") {
                                    $highest_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                                    $api_grade = intval($evidence_item->string_object->grade);
                                    if ($api_grade < $highest_grade) {
                                        $api->update_evidence_item_grade($existing_certificate->id, $evidence_item->id, $highest_grade);
                                    }
                                }
                            }
                        }
                    }

                    $completion_activities = unserialize_completion_array($record->completionactivities);
                    // If this quiz is in the completion activities.
                    if ( isset($completion_activities[$quiz->id]) ) {
                        $completion_activities[$quiz->id] = true;
                        $quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished'));
                        foreach ($quiz_attempts as $quiz_attempt) {
                            // If this quiz was already attempted, then we shouldn't be issuing a certificate.
                            if ( $quiz_attempt->quiz == $quiz->id && $quiz_attempt->attempt > 1 ) {
                                return null;
                            }
                            // Otherwise, set this quiz as completed.
                            if ( isset($completion_activities[$quiz_attempt->quiz]) ) {
                                $completion_activities[$quiz_attempt->quiz] = true;
                            }
                        }

                        // But was this the last required activity that was completed?
                        $course_complete = true;
                        foreach ($completion_activities as $is_complete) {
                            if (!$is_complete) {
                                $course_complete = false;
                            }
                        }
                        // If it was the final activity.
                        if ($course_complete) {
                            $existing_certificate = accredible_check_for_existing_credential($record->groupid, $user->email);
                            // make sure there isn't already a certificate
                            if (!$existing_certificate) {
                                // issue a ceritificate
                                create_credential($user, $record->groupid);
                            }
                        }
                    }

                } else {
                    // Check which quiz is used as the deciding factor in this course.
                    if ($quiz->id == $record->finalquiz) {
                        $existing_certificate = accredible_check_for_existing_certificate (
                            $record->achievementid, $user
                        );

                        // Check for an existing certificate.
                        if (!$existing_certificate) {
                            $users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                            $grade_is_high_enough = ($users_grade >= $record->passinggrade);

                            // Check for pass.
                            if ($grade_is_high_enough) {
                                // Issue a ceritificate.
                                $api_response = accredible_issue_default_certificate( $user->id, $record->id, fullname($user), $user->email, $users_grade, $quiz->name);
                                $certificate_event = \mod_accredible\event\certificate_created::create(array(
                                  'objectid' => $api_response->credential->id,
                                  'context' => context_module::instance($event->contextinstanceid),
                                  'relateduserid' => $event->relateduserid
                                ));
                                $certificate_event->trigger();
                            }
                        } else {
                            // Check the existing grade to see if this one is higher.
                            $credential = $api->get_credential($existing_certificate->id)->credential;
                            foreach ($credential->evidence_items as $evidence_item) {
                                if ($evidence_item->type == "grade") {
                                    $highest_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
                                    $api_grade = intval($evidence_item->string_object->grade);
                                    if ($api_grade < $highest_grade) {
                                        $api->update_evidence_item_grade($existing_certificate->id, $evidence_item->id, $highest_grade);
                                    }
                                }
                            }
                        }
                    }

                    $completion_activities = unserialize_completion_array($record->completionactivities);
                    // if this quiz is in the completion activities
                    if ( isset($completion_activities[$quiz->id]) ) {
                        $completion_activities[$quiz->id] = true;
                        $quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished'));
                        foreach ($quiz_attempts as $quiz_attempt) {
                            // if this quiz was already attempted, then we shouldn't be issuing a certificate
                            if ( $quiz_attempt->quiz == $quiz->id && $quiz_attempt->attempt > 1 ) {
                                return null;
                            }
                            // otherwise, set this quiz as completed
                            if ( isset($completion_activities[$quiz_attempt->quiz]) ) {
                                $completion_activities[$quiz_attempt->quiz] = true;
                            }
                        }

                        // but was this the last required activity that was completed?
                        $course_complete = true;
                        foreach ($completion_activities as $is_complete) {
                            if (!$is_complete) {
                                $course_complete = false;
                            }
                        }
                        // if it was the final activity
                        if ($course_complete) {
                            $existing_certificate = accredible_check_for_existing_certificate (
                                $record->achievementid, $user
                            );
                            // make sure there isn't already a certificate
                            if (!$existing_certificate) {
                                // and issue a ceritificate
                                $api_response = accredible_issue_default_certificate( $user->id, $record->id, fullname($user), $user->email, null, null);
                                $certificate_event = \mod_accredible\event\certificate_created::create(array(
                                  'objectid' => $api_response->credential->id,
                                  'context' => context_module::instance($event->contextinstanceid),
                                  'relateduserid' => $event->relateduserid
                                ));
                                $certificate_event->trigger();
                            }
                        }
                    }
                }

            }
        }
    }
}


/*
 * Course completion handler
 *
 * @param core/event $event
 */
function accredible_course_completed_handler($event) {

    global $DB, $CFG;

    $user = $DB->get_record('user', array('id' => $event->relateduserid));

    // Check we have a course record
    if ($accredible_certificate_records = $DB->get_records('accredible', array('course' => $event->courseid))) {
        foreach ($accredible_certificate_records as $record) {
            // check for the existence of an activity instance and an auto-issue rule
            if ( $record and ($record->completionactivities && $record->completionactivities != 0) ) {

                // Check if we have a group mapping - if not use the old logic
                if ($record->groupid) {
                    // create the credential
                    create_credential($user, $record->groupid);

                } else {
                    $api_response = accredible_issue_default_certificate( $user->id, $record->id, fullname($user), $user->email, null, null);
                    $certificate_event = \mod_accredible\event\certificate_created::create(array(
                      'objectid' => $api_response->credential->id,
                      'context' => context_module::instance($event->contextinstanceid),
                      'relateduserid' => $event->relateduserid
                    ));
                    $certificate_event->trigger();
                }

            }
        }
    }
}

function accredible_get_transcript($course_id, $user_id, $final_quiz_id) {
    global $DB, $CFG;

    $total_items = 0;
    $total_score = 0;
    $items_completed = 0;
    $transcript_items = array();
    $quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $course_id) );

    // grab the grades for all quizes
    foreach ($quizes as $quiz) {
        if ($quiz->id !== $final_quiz_id) {
            $highest_grade = quiz_get_best_grade($quiz, $user_id);
            if ($highest_grade) {
                $items_completed += 1;
                array_push($transcript_items, array(
                    'category' => $quiz->name,
                    'percent' => min( ( $highest_grade / $quiz->grade ) * 100, 100 )
                ));
                $total_score += min( ( $highest_grade / $quiz->grade ) * 100, 100);
            }
            $total_items += 1;
        }
    }

    // if they've completed over 2/3 of items
    // and have a passing average, make a transcript
    if ( $total_items !== 0 && $items_completed !== 0 && $items_completed / $total_items >= 0.66 && $total_score / $items_completed > 50 ) {
        return array(
                'description' => 'Course Transcript',
                'string_object' => json_encode($transcript_items),
                'category' => 'transcript',
                'custom' => true,
                'hidden' => true
            );
    } else {
        return false;
    }
}

function accredible_post_evidence($credential_id, $evidence_item, $throw_error = false) {
    $api = new apiRest();
    $api->create_evidence_item(array('evidence_item' => $evidence_item), $credential_id, $throw_error);
}

function accredible_check_for_existing_certificate($achievement_id, $user) {
    global $DB;
    $existing_certificate = false;
    $certificates = accredible_get_credentials($achievement_id, $user->email);

    foreach ($certificates as $certificate) {
        if ($certificate->recipient->email == $user->email) {
            $existing_certificate = $certificate;
        }
    }
    return $existing_certificate;
}

function serialize_completion_array($completion_array) {
    return base64_encode(serialize( (array)$completion_array ));
}

function unserialize_completion_array($completion_object) {
    return (array)unserialize(base64_decode( $completion_object ));
}

function accredible_post_essay_answers($user_id, $course_id, $credential_id) {
    global $DB, $CFG;

    // Grab the course quizes.
    if ($quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $course_id)) ) {
        foreach ($quizes as $quiz) {
            $evidence_item = array('description' => $quiz->name);
            // Grab quiz attempts.
            $quiz_attempt = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $user_id), '-attempt', '*', 0, 1);

            if ($quiz_attempt) {
                $sql = "SELECT
                        qa.id,
                        quiza.quiz,
                        quiza.id AS quizattemptid,
                        quiza.timestart,
                        quiza.timefinish,
                        qa.slot,
                        qa.behaviour,
                        qa.questionsummary AS question,
                        qa.responsesummary AS answer

                FROM ".$CFG->prefix."quiz_attempts quiza
                JOIN ".$CFG->prefix."question_usages qu ON qu.id = quiza.uniqueid
                JOIN ".$CFG->prefix."question_attempts qa ON qa.questionusageid = qu.id

                WHERE quiza.id = ? && qa.behaviour = 'manualgraded'

                ORDER BY quiza.userid, quiza.attempt, qa.slot";

                if ( $questions = $DB->get_records_sql($sql, array(reset($quiz_attempt)->id)) ) {
                    $questions_output = "<style>#main {	max-width: 780px;margin-left: auto;margin-right: auto;margin-top: 50px;margin-bottom: 80px; font-family: Arial;} h1, h5 {	text-align: center;} .answer { border: 1px solid grey; padding: 20px; font-size: 14px; line-height: 22px; margin-bottom:30px; margin-top:30px;} p {font-size: 14px; line-height: 18px;} </style>";
                    $questions_output .= "<div id='main'>";
                    $questions_output .= "<h1>" . $quiz->name . "</h1>";
                    $questions_output .= "<h5>Time Taken: ". seconds_to_str( current($questions)->timefinish - current($questions)->timestart ) ."</h5>";

                    foreach ($questions as $questionattempt) {
                        $questions_output .= $questionattempt->question;
                        $questions_output .= "<div class='answer'>".$questionattempt->answer."</div>";
                    }

                    $questions_output .= "</div>";

                    $evidence_item['string_object'] = $questions_output;
                    $evidence_item['hidden'] = true;

                    // post the evidence
                    accredible_post_evidence($credential_id, $evidence_item, false);
                }
            }
        }
    }
}


function accredible_course_duration_evidence($user_id, $course_id, $credential_id, $completed_timestamp = null) {
    global $DB, $CFG;

    $sql = "SELECT enrol.id, ue.timestart
                    FROM ".$CFG->prefix."enrol enrol, ".$CFG->prefix."user_enrolments ue
                    WHERE enrol.id = ue.enrolid AND ue.userid = ? AND enrol.courseid = ?";
    $enrolment = $DB->get_record_sql($sql, array($user_id, $course_id));
    $enrolment_timestamp = $enrolment->timestart;

    if (!isset($completed_timestamp)) {
        $completed_timestamp = date("Y-m-d");
    }

    if ($enrolment_timestamp && $enrolment_timestamp != 0 && (strtotime($enrolment_timestamp) < strtotime($completed_timestamp))) {
        $apiRest = new apiRest();

        $apiRest->create_evidence_item_duration($enrolment_timestamp, $completed_timestamp, $credential_id, true);
    }
}

/* accredible_manual_issue_completion_timestamp()
 *
 *  Get a timestamp for when a student completed a course. This is
 *  used when manually issuing certs to get a proper issue date and
 *  for the course duration item. Currently checking for the date of
 *  the highest quiz attempt for the final quiz specified for that
 *  accredible activity.
 */
function accredible_manual_issue_completion_timestamp($accredible_record, $user) {
    global $DB;

    $completed_timestamp = false;

    if ($accredible_record->finalquiz) {
        // If there is a finalquiz set, that governs when the course is complete.

        $quiz = $DB->get_record('quiz', array('id' => $accredible_record->finalquiz), '*', MUST_EXIST);
        $totalrawscore = $quiz->sumgrades;
        $highest_attempt = null;

        $quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished', 'quiz' => $accredible_record->finalquiz));
        foreach ($quiz_attempts as $quiz_attempt) {
            if (!isset($highest_attempt)) {
                // First attempt in the loop, so currently the highest.
                $highest_attempt = $quiz_attempt;
                continue;
            }

            if ($quiz_attempt->sumgrades >= $highest_attempt->sumgrades) {
                // Compare raw sumgrades from attempt. It seems that moodle
                // doesn't allow the amount that questions are worth in a quiz
                // to change so this should be ok - the scale should be constant
                // across attempts
                $highest_attempt = $quiz_attempt;
            }
        }

        if (isset($highest_attempt)) {
            // At least one attempt was found.
            $attemptrawscore = $highest_attempt->sumgrades;
            $grade = ($attemptrawscore / $totalrawscore) * 100;
            // Check if the grade is passing, and if so set completion time to the attempt timefinish.
            if ($grade >= $accredible_record->passinggrade) {
                $completed_timestamp = $highest_attempt->timefinish;
            }
        }

    }

    // TODO: When is the completion if there are completion activities set?

    // Set timestamp to now if no good timestamp was found.
    if ($completed_timestamp === false) {
        $completed_timestamp = time();
    }

    return (int) $completed_timestamp;
}

function number_ending ($number) {
    return ($number > 1) ? 's' : '';
}

function seconds_to_str ($seconds) {
    $hours = floor(($seconds %= 86400) / 3600);
    if ($hours) {
        return $hours . ' hour' . number_ending($hours);
    }
    $minutes = floor(($seconds %= 3600) / 60);
    if ($minutes) {
        return $minutes . ' minute' . number_ending($minutes);
    }
    return $seconds . ' second' . number_ending($seconds);
}
