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

require_once($CFG->libdir . '/eventslib.php');

// For composer dependencies
require __DIR__ . '/vendor/autoload.php';

use ACMS\Api;

/**
 * Sync the selected course information with a group on Accredible - returns a group ID
 *
 * @param stdClass $course 
 * @param int|null $instance_id
 * @return int $groupid
 */
function sync_course_with_accredible($course, $instance_id = null) {
	global $DB, $CFG;

	$api = new Api($CFG->accredible_api_key);

	// Update an existing
	if(null != $instance_id){
		// get the group id
		$accredible_certificate = $DB->get_record('accredible', array('id'=> $instance_id), '*', MUST_EXIST);

		try {
		    // Update the group
			$group = $api->update_group($accredible_certificate->groupid, null, $course->fullname, $course->summary, new moodle_url('/course/view.php', array('id' => $course->id)));

			return $group->group->id;
		} catch (ClientException $e) {
		    // throw API exception
		  	// include the achievement id that triggered the error
		  	// direct the user to accredible's support
		  	// dump the achievement id to debug_info
		  	throw new moodle_exception('groupsyncerror', 'accredible', 'https://accredible.com/contact/support', $course->id, $course->id);
		}
	// making a new group
	} else {
		try {
		    // Make a new Group on Accredible - use a random number to deal with duplicate course names.
			$group = $api->create_group($course->shortname . mt_rand(), $course->fullname, $course->summary, new moodle_url('/course/view.php', array('id' => $course->id)));

			return $group->group->id;
		} catch (ClientException $e) {
		    // throw API exception
		  	// include the achievement id that triggered the error
		  	// direct the user to accredible's support
		  	// dump the achievement id to debug_info
		  	throw new moodle_exception('groupsyncerror', 'accredible', 'https://accredible.com/contact/support', $course->id, $course->id);
		}
	}
}

/**
 * List all of the ceritificates with a specific achievement id
 *
 * @param string $group_id
 * @return array[stdClass] $credentials
 */
function accredible_get_credentials($group_id) {
	global $CFG;

	$api = new Api($CFG->accredible_api_key);

	try {
		$credentials = $api->get_credentials($group_id);

		return $credentials->credentials;
	} catch (ClientException $e) {
	    // throw API exception
	  	// include the achievement id that triggered the error
	  	// direct the user to accredible's support
	  	// dump the achievement id to debug_info
	  	throw new moodle_exception('groupsyncerror', 'accredible', 'https://accredible.com/contact/support', $group_id, $group_id);
	}
}	

/**
 * Check's if a credential exists for an email in a particular group
 * @param int $group_id 
 * @param String $email 
 * @return array[stdClass] || false
 */
function accredible_check_for_existing_credential($group_id, $email) {
	global $DB, $CFG;

	$api = new Api($CFG->accredible_api_key);

	try {
		$credentials = $api->get_credentials($group_id, $email);

		if($credentials->credentials and $credentials->credentials[0]){
			return $credentials->credentials[0];
		} else {
			return false;
		}
		
	} catch (ClientException $e) {
	    // throw API exception
	  	// include the achievement id that triggered the error
	  	// direct the user to accredible's support
	  	// dump the achievement id to debug_info
	  	throw new moodle_exception('groupsyncerror', 'accredible', 'https://accredible.com/contact/support', $group_id, $group_id);
	}
}

/**
 * Create a credential given a user and an existing group
 * @param stdObject $user 
 * @param int $group_id 
 * @return stdObject
 */
function create_credential($user, $group_id, $event = null){
	global $CFG;

	$api = new Api($CFG->accredible_api_key);

	try {
		$credential = $api->create_credential(fullname($user), $user->email, $group_id);

		// log an event now we've created the credential if possible
		if($event != null){
			$certificate_event = \mod_accredible\event\certificate_created::create(array(
								  'objectid' => $credential->credential->id,
								  'context' => context_module::instance($event->contextinstanceid),
								  'relateduserid' => $event->relateduserid
								));
			$certificate_event->trigger();
		}
		
		return $credential->credential;

	} catch (ClientException $e) {
	    // throw API exception
	  	// include the achievement id that triggered the error
	  	// direct the user to accredible's support
	  	// dump the achievement id to debug_info
	  	throw new moodle_exception('credentialcreateerror', 'accredible', 'https://accredible.com/contact/support', $user->email, $group_id);
	}
}

/**
 * Get the groups for the issuer
 * @return type
 */
function accredible_get_groups() {
	global $CFG;

	$api = new Api($CFG->accredible_api_key);

	try {
		$response = $api->get_groups(10000,1);

		$groups = array();
		for($i = 0, $size = count($response->groups); $i < $size; ++$i) {
			$groups[$response->groups[$i]->id] = $response->groups[$i]->name;
		}
		return $groups;

	} catch (ClientException $e) {
	    // throw API exception
	  	// include the achievement id that triggered the error
	  	// direct the user to accredible's support
	  	// dump the achievement id to debug_info
	  	throw new moodle_exception('getgroupserror', 'accredible', 'https://accredible.com/contact/support');
	}
}

// old below here


/**
 * List all of the ceritificates with a specific achievement id
 *
 * @param string $achievement_id
 * @return array[stdClass] $certificates
 */
function accredible_get_issued($achievement_id) {
	global $CFG;

	$curl = curl_init('https://api.accredible.com/v1/credentials?full_view=true&achievement_id='.urlencode($achievement_id));
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if(!$result = json_decode( curl_exec($curl) )) {
	  // throw API exception
	  // include the achievement id that triggered the error
	  // direct the user to accredible's support
	  // dump the achievement id to debug_info
	  throw new moodle_exception('getissuederror', 'accredible', 'https://accredible.com/contact/support', $achievement_id, $achievement_id);
	}
	curl_close($curl);
	return $result->credentials;
}

/**
 * List all of the ceritificates with a specific achievement id & email
 *
 * @param string $achievement_id
 * @return array[stdClass] $certificates
 */
function accredible_get_issued($achievement_id, $email) {
	global $CFG;

	$curl = curl_init('https://api.accredible.com/v1/all_credentials?group_id='.urlencode($achievement_id).'&email='.$email);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if(!$result = json_decode( curl_exec($curl) )) {
	  // throw API exception
	  // include the achievement id that triggered the error
	  // direct the user to accredible's support
	  // dump the achievement id to debug_info
	  throw new moodle_exception('getissuederror', 'accredible', 'https://accredible.com/contact/support', $achievement_id, $achievement_id);
	}
	curl_close($curl);
	return $result->credentials;
}

/**
 * List all of the issuer's templates
 *
 * @return array[stdClass] $templates
 */
function accredible_get_templates() {
	global $CFG;

	$curl = curl_init('https://api.accredible.com/v1/issuer/templates');
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if(!$result = json_decode( curl_exec($curl) )) {
	  // throw API exception
	  // direct the user to accredible's support
	  // dump the achievement id to debug_info
	  throw new moodle_exception('gettemplateserror', 'accredible', 'https://accredible.com/contact/support');
	}
	curl_close($curl);
	$templates = array();
	for($i = 0, $size = count($result->templates); $i < $size; ++$i) {
		$templates[$result->templates[$i]->name] = $result->templates[$i]->name;
	}
	return $templates;
}

/*
 * accredible_issue_default_certificate
 * 
 */
function accredible_issue_default_certificate($user_id, $certificate_id, $name, $email, $grade, $quiz_name) {
	global $DB, $CFG;

	// Issue certs
	$accredible_certificate = $DB->get_record('accredible', array('id'=>$certificate_id));

	$certificate = array();
  $course_url = new moodle_url('/course/view.php', array('id' => $accredible_certificate->course));
	$certificate['name'] = $accredible_certificate->certificatename;
	$certificate['template_name'] = $accredible_certificate->achievementid;
	$certificate['description'] = $accredible_certificate->description;
  $certificate['course_link'] = $course_url->__toString();
	$certificate['recipient'] = array('name' => $name, 'email'=> $email);

	$curl = curl_init('https://api.accredible.com/v1/credentials');
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( array('credential' => $certificate) ));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );
	if(!$result = curl_exec($curl)) {
		// TODO - log this because an exception cannot be thrown in an event callback
	}
	curl_close($curl);

	// evidence item posts
	$credential_id = json_decode($result)->credential->id;
	if($grade) {
		$grade_evidence = array('string_object' => (string) $grade, 'description' => $quiz_name, 'custom'=> true, 'category' => 'grade' );
		if($grade < 50) {
		    $grade_evidence['hidden'] = true;
		}
	  accredible_post_evidence($credential_id, $grade_evidence, false);
	}
  if($transcript = accredible_get_transcript($accredible_certificate->course, $user_id, $accredible_certificate->finalquiz)) {
	  accredible_post_evidence($credential_id, $transcript, false);
	}
	accredible_post_essay_answers($user_id, $accredible_certificate->course, $credential_id);
	accredible_course_duration_evidence($user_id, $accredible_certificate->course, $credential_id);

	return json_decode($result);
}

/*
 * accredible_log_creation
 */
function accredible_log_creation($certificate_id, $user_id, $course_id, $cm_id) {
	global $DB;

	// Get context
	$accredible_mod = $DB->get_record('modules', array('name' => 'accredible'), '*', MUST_EXIST);
	if($cm_id) {
		$cm = $DB->get_record('course_modules', array('id' => (int) $cm_id), '*');
	} else { // this is an activity add, so we have to use $course_id
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

	$attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);

	$quiz    = $event->get_record_snapshot('quiz', $attempt->quiz);
	$user 	 = $DB->get_record('user', array('id' => $event->relateduserid));
	if($accredible_certificate_records = $DB->get_records('accredible', array('course' => $event->courseid))) {
		foreach ($accredible_certificate_records as $record) {
			// check for the existence of an activity instance and an auto-issue rule
			if( $record and ($record->finalquiz or $record->completionactivities) ) {

				// Check if we have a group mapping - if not use the old logic
				if($record->groupid){
					// check which quiz is used as the deciding factor in this course
					if($quiz->id == $record->finalquiz) {
						// check for an existing certificate
						$existing_certificate = accredible_check_for_existing_credential($record->groupid, $user->email);
						
						// create that credential if it doesn't exist
						if(!$existing_certificate) {
							$users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
							$grade_is_high_enough = ($users_grade >= $record->passinggrade);

							// check for pass
							if($grade_is_high_enough) {
								// issue a ceritificate
								create_credential($user, $record->groupid);
							}
						} 
						// check the existing grade to see if this one is higher and update the credential if so
						else {
							foreach ($existing_certificate->evidence_items as $evidence_item) {
								if($evidence_item->type == "grade") {
									$highest_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
									// only update if higher
									if($evidence_item->string_object->grade < $highest_grade) {
										accredible_update_certificate_grade($existing_certificate->id, $evidence_item->id, $highest_grade);
									}
								}
							}
						}
					}

					$completion_activities = unserialize_completion_array($record->completionactivities);
					// if this quiz is in the completion activities
					if( isset($completion_activities[$quiz->id]) ) {
						$completion_activities[$quiz->id] = true;
						$quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished'));
						foreach($quiz_attempts as $quiz_attempt) {
							// if this quiz was already attempted, then we shouldn't be issuing a certificate
							if( $quiz_attempt->quiz == $quiz->id && $quiz_attempt->attempt > 1 ) {
								return null;
							}
							// otherwise, set this quiz as completed
							if( isset($completion_activities[$quiz_attempt->quiz]) ) {
								$completion_activities[$quiz_attempt->quiz] = true;
							}
						}

						// but was this the last required activity that was completed?
						$course_complete = true;
						foreach($completion_activities as $is_complete) {
							if(!$is_complete) {
								$course_complete = false;
							}
						}
						// if it was the final activity
						if($course_complete) {
							$existing_certificate = accredible_check_for_existing_credential($record->groupid, $user->email);
							// make sure there isn't already a certificate
							if(!$existing_certificate) {
								// issue a ceritificate
								create_credential($user, $record->groupid);
							}
						}
					}


				} else {
					// check which quiz is used as the deciding factor in this course
					if($quiz->id == $record->finalquiz) {
						$existing_certificate = accredible_check_for_existing_certificate (
							$record->achievementid, $user
						);

						// check for an existing certificate
						if(!$existing_certificate) {
							$users_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
							$grade_is_high_enough = ($users_grade >= $record->passinggrade);

							// check for pass
							if($grade_is_high_enough) {
								// issue a ceritificate
								$api_response = accredible_issue_default_certificate( $user->id, $record->id, fullname($user), $user->email, $users_grade, $quiz->name);
								$certificate_event = \mod_accredible\event\certificate_created::create(array(
								  'objectid' => $api_response->credential->id,
								  'context' => context_module::instance($event->contextinstanceid),
								  'relateduserid' => $event->relateduserid
								));
								$certificate_event->trigger();
							}
						} 
						// check the existing grade to see if this one is higher
						else {
							foreach ($existing_certificate->evidence_items as $evidence_item) {
								if($evidence_item->type == "grade") {
									$highest_grade = min( ( quiz_get_best_grade($quiz, $user->id) / $quiz->grade ) * 100, 100);
									// only update if higher
									if($evidence_item->string_object->grade < $highest_grade) {
										accredible_update_certificate_grade($existing_certificate->id, $evidence_item->id, $highest_grade);
									}
								}
							}
						}
					}

					$completion_activities = unserialize_completion_array($record->completionactivities);
					// if this quiz is in the completion activities
					if( isset($completion_activities[$quiz->id]) ) {
						$completion_activities[$quiz->id] = true;
						$quiz_attempts = $DB->get_records('quiz_attempts', array('userid' => $user->id, 'state' => 'finished'));
						foreach($quiz_attempts as $quiz_attempt) {
							// if this quiz was already attempted, then we shouldn't be issuing a certificate
							if( $quiz_attempt->quiz == $quiz->id && $quiz_attempt->attempt > 1 ) {
								return null;
							}
							// otherwise, set this quiz as completed
							if( isset($completion_activities[$quiz_attempt->quiz]) ) {
								$completion_activities[$quiz_attempt->quiz] = true;
							}
						}

						// but was this the last required activity that was completed?
						$course_complete = true;
						foreach($completion_activities as $is_complete) {
							if(!$is_complete) {
								$course_complete = false;
							}
						}
						// if it was the final activity
						if($course_complete) {
							$existing_certificate = accredible_check_for_existing_certificate (
								$record->achievementid, $user
							);
							// make sure there isn't already a certificate
							if(!$existing_certificate) {
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
	if($accredible_certificate_records = $DB->get_records('accredible', array('course' => $event->courseid))) {
		foreach ($accredible_certificate_records as $record) {
			// check for the existence of an activity instance and an auto-issue rule
			if( $record and ($record->completionactivities && $record->completionactivities != 0) ) {

				// Check if we have a group mapping - if not use the old logic
				if($record->groupid){
					// check for an existing certificate
					$existing_certificate = accredible_check_for_existing_credential($record->groupid, $user->email);
					
					// create that credential if it doesn't exist
					if(!$existing_certificate) {
						create_credential($user, $record->groupid);
					}

				} else {
					$existing_certificate = accredible_check_for_existing_certificate ($record->achievementid, $user);

					// check for an existing certificate
					if(!$existing_certificate) {
						// issue a ceritificate
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


function accredible_update_certificate_grade($certificate_id, $evidence_item_id, $grade) {
  global $CFG;

	$curl = curl_init('https://api.accredible.com/v1/credentials/' . $certificate_id . '/evidence_items/'.$evidence_item_id);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( array('evidence_item' => array( 'string_object' => $grade ) ) ));
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );

	$result = curl_exec($curl);
	return $result;
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
		if($quiz->id !== $final_quiz_id) {
			$highest_grade = quiz_get_best_grade($quiz, $user_id);
			if($highest_grade) {
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
	if( $items_completed / $total_items >= 0.66 && $total_score / $items_completed > 50 ) {
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

function accredible_post_evidence($credential_id, $evidence_item, $allow_exceptions) {
	global $CFG;

	$curl = curl_init('https://api.accredible.com/v1/credentials/' . $credential_id . '/evidence_items');
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( array('evidence_item' => $evidence_item) ));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Token token="'.$CFG->accredible_api_key.'"' ) );
	$result = curl_exec($curl);
	if(!$result && $allow_exceptions) {
    // throw API exception
    // include the user id that triggered the error
    // direct the user to accredible's support
    // dump the post to debug_info
    throw new moodle_exception('evidenceadderror', 'accredible', 'https://accredible.com/contact/support', $credential_id, curl_error($curl));
	}
	curl_close($curl);
}

function accredible_check_for_existing_certificate($achievement_id, $user) {
	global $DB;
	$existing_certificate = false;
	$certificates = accredible_get_issued($achievement_id, $user->email);

	foreach ($certificates as $certificate) {
		if($certificate->recipient->email == $user->email) {
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
	global $DB;

	// grab the course quizes
	if($quizes = $DB->get_records_select('quiz', 'course = :course_id', array('course_id' => $course_id)) ) {
		foreach ($quizes as $quiz) {
			$evidence_item = array('description' => $quiz->name);
			// grab quiz attempts
			$quiz_attempt = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $user_id), '-attempt', '*', 0, 1);		
			
			if($quiz_attempt) {
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
				 
				FROM mdl_quiz_attempts quiza
				JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
				JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
				 
				WHERE quiza.id = ? && qa.behaviour = 'manualgraded'
				 
				ORDER BY quiza.userid, quiza.attempt, qa.slot";

				if( $questions = $DB->get_records_sql($sql, array(reset($quiz_attempt)->id)) ) {
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

function accredible_course_duration_evidence($user_id, $course_id, $credential_id) {
	global $DB;

	$sql = "SELECT enrol.id, ue.timestart
					FROM mdl_enrol enrol, mdl_user_enrolments ue 
					WHERE enrol.id = ue.enrolid AND ue.userid = ? AND enrol.courseid = ?";
	$enrolment = $DB->get_record_sql($sql, array($user_id, $course_id));
	$enrolment_timestamp = $enrolment->timestart;

	$duration_info = array(
		'start_date' =>  date("Y-m-d", $enrolment_timestamp),
		'end_date' => date("Y-m-d"),
		'duration_in_days' => floor( (time() - $enrolment_timestamp) / 86400)
	);

	$evidence_item = array(
		'description' => 'Completed in ' . $duration_info['duration_in_days'] . ' days', 
		'category' => 'course_duration'
	);
	$evidence_item['string_object'] = json_encode($duration_info);
	$evidence_item['hidden'] = true;

	// post the evidence if the startdate exists and isn't 0 (epoch)
	if($enrolment_timestamp && $enrolment_timestamp != 0){
		accredible_post_evidence($credential_id, $evidence_item, false);	
	}
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
