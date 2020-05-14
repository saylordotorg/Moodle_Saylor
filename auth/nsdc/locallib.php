<?php
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
 * auth_nsdc
 *
 *
 * @package    auth
 * @subpackage nsdc
 * @copyright  2020 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace auth_nsdc;

use core_completion\progress;

use stdClass;
use DateTime;
use DateTimeZone;

use core;
use moodle_database;
use moodle_exception;


defined('MOODLE_INTERNAL') || die;

/**
 * Decrypt and verify the incoming payload.
 *
 * @param type $string payload
 * @return obj decrypted payload
 */
function decrypt_payload($encryptedpayload) {
    // Get config and decrypt.
    $pluginconfig = get_config('auth_nsdc'); 

    if (empty($pluginconfig->key)) {
        throw new \moodle_exception('nsdc_no_key', 'auth_nsdc');
    }
    if (empty($pluginconfig->iv)) {
        throw new \moodle_exception('nsdc_no_iv', 'auth_nsdc');
    }

    $key = base64_decode($pluginconfig->key);
    $iv = base64_decode($pluginconfig->iv);

    $decryptedpayload = openssl_decrypt($encryptedpayload, 'AES-256-CBC', $key, 0, $iv);

    // Convert to object.
    $data = json_decode($decryptedpayload);

    // Verify. Check if the timestamp is less than 30 seconds old.
    if ((time() - strtotime($data->time_stamp)) > 30 ) {
        // Time stamp verification has failed. Possible replay attack.
        throw new moodle_exception('timeverificationfailed', 'auth_nsdc');
    }

    return $data;
}

/**
 * Check if the user already has an account.
 *
 * @param type $int candidate_id
 * @return bool has_account
 */
function has_account($candidate_id) {
    global $DB;
    $linkedlogin = $DB->get_record('auth_nsdc_linked_login', array('nsdcid' => $candidate_id), '*', IGNORE_MISSING);

    if ($linkedlogin == false) {
        // User does not have an account through this plugin.
        $has_account = false;
    }
    else {
        $has_account = true;
    }

    return $has_account;
}

/**
 * Create a fake email for user's who do not have an email address
 * using the baseemail and their candidate_id. Format is:
 * baseuser+candidate_id@basedomain.example
 *
 * @param type $int candidate_id
 * @return string newemail
 */
function generate_email($candidate_id) {

    $pluginconfig = get_config('auth_nsdc');
    $baseemail = $pluginconfig->baseemail;

    $basedomain = strstr($baseemail, '@');
    $baseuser = strstr($baseemail, '@', true);

    return $baseuser . '+' . $candidate_id . $basedomain;
}

/**
 * Get user info for the linked login.
 *
 * @param type $obj data
 * @return bool success
 */
function create_account($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/user/profile/lib.php');
    require_once($CFG->dirroot.'/user/lib.php');

    $user = new stdClass();
    $user->username = 'nsdc_'.$data->candidate_id;

    $user->auth = 'nsdc';
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->lastname = $data->last_name ?? '';
    $user->firstname = $data->candidate_name ?? '';
    $user->url = '';
    $user->alternatename = '';
    $user->secret = random_string(15);

    // Users may or may not have an email address.
    // Generate an email address if they do not have one.
    $user->email = $data->candidate_email ?? generate_email($data->candidate_id); 

    $user->password = '';
    // This user is confirmed.
    $user->confirmed = 1;

    // Do a final check to make sure that there is not already a user.
    // Check email.
    $emailcheck = $DB->get_record('user', array('email' => $user->email), '*', IGNORE_MISSING);
    if ($emailcheck !== false) {
        // Someone already has this email address.
        throw new moodle_exception('could_not_create_account_exists', 'auth_nsdc');
    } 

    $user->id = user_create_user($user, false, true);

    // Create a linked login in the linked login table.
    $success = link_login($user, $data);

    return $success;
}

/**
 * Create a linked login record.
 * This is to have a record linking the candidate_id to a user.
 *
 * @param obj $user
 * @param obj $data User data from NSDC
 * @return bool success
 */
function link_login($user, $data) {
    global $DB, $USER;

    // Double check that this user doesn't already exist in the table.
    $linkedlogin = $DB->get_record('auth_nsdc_linked_login', array('userid' => $user->id), '*', IGNORE_MISSING);
    
    if ($linkedlogin == false) {
        // Linked login is not already in db, create linked login record.
        $record = new stdClass();
        $record->timecreated = $user->timecreated;
        $record->timemodified = $user->timemodified;
        $record->userid = $user->id;
        $record->nsdcid = $data->candidate_id;
        $record->username = $user->username;
        $record->email = $user->email;
        // If the candidate_email is not set, it is a phone only user.
        if (!isset($data->candidate_email)) {
            $record->phoneonly = true;
        }
        else {
            $record->phoneonly = false;
        }

        // Insert the record.
        $linkedlogin = $DB->insert_record('auth_nsdc_linked_login', $record, false);

    }

    return $linkedlogin;
}

/**
 * Get the user.
 *
 * @param obj $data User data from NSDC
 * @return obj user
 */
function get_user($data) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/lib/moodlelib.php');

    // Get the user account from the linked login.
    $linkedlogin = $DB->get_record('auth_nsdc_linked_login', array('nsdcid' => $data->candidate_id), '*', IGNORE_MISSING);
    $user = \get_complete_user_data('id', $linkedlogin->userid);

    return $user;
}

/**
 * Log in the user.
 *
 * @param obj $user
 * @return bool success
 */
function complete_login($user) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/lib/moodlelib.php');

    // Log in the user.
    \complete_user_login($user);

    return true;
}

/**
 * Enroll the user in the specified course.
 *
 * @param obj $user
 * @param int $courseid 
 * @return bool success
 */
function enrol_nsdc_user($user, $courseid) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/lib/enrollib.php');

    // Grab the student role id from the DB.
    $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));

    // Enroll the user.
    // I'm using manual enrollment for now. If we want to use self
    // enrollment in the future (so students can unenroll from courses,
    //  try enrol_user(). 
    if (!\enrol_try_internal_enrol($courseid, $user->id, $roleid, time())) {
        // There's a problem.
        throw new moodle_exception('enrolerror', 'auth_nsdc');
    }

    return true;
}

/**
 * Create the request to update course status at NSDC.
 *
 * @param obj $data The data payload supplied by NSDC.
 * @param obj $user The moodle user data object.
 * @param bool $production Whether we are sending status updates to the NSDC production environment.
 * @return bool Return true on success.
 */
function course_status_update($data, $user, $production = false) {
    global $CFG, $DB;

    // Get the url to send to.
    $pluginconfig = get_config('auth_nsdc');

    $domain = 'http://test.nsdccindia.org';
    $endpoint = '/API/user_update';
    if ($production) {
        $domain = 'https://eskillindia.org';
    }

    // Hardcoding this for now.
    $certificationtype = "completion";

    $candidatedata = new stdClass();

    $candidatedata->candidate_id = $data->candidate_id;
    $candidatedata->course_id = $data->course_id;
    $candidatedata->course_status = get_nsdc_course_status($user->id, $data->kp_course_id);
    $candidatedata->course_completion_pencentage = get_course_progress_percentage($user->id, $data->kp_course_id);
    $candidatedata->course_enrollment_date = get_course_enrollment_date($user->id, $data->kp_course_id);
    $candidatedata->course_last_date = get_course_lastaccess_date($user->id, $data->kp_course_id);
    $candidatedata->certification_status = get_certification_status($user->id, $data->kp_course_id);
    if ($candidatedata->certification_status == 1) {
         // If the user has completed the course, grab date and grade.
        $candidatedata->certification_issue_date = get_certification_date($user->id, $data->kp_course_id);
        $candidatedata->certification_type = $certificationtype;
        $candidatedata->certification_percentage = get_certification_percentage($user->id, $data->kp_course_id);
        // Also, set the course completion to 100%.
        $candidatedata->course_completion_pencentage = 100;
    }

    // Format the data to ready for json encoding.
    $params['candidate_data'] = array($candidatedata);

    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL, $domain.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-api-key: '.$pluginconfig->apikey,
        'Content-Type: application/json'
    ));
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($params));

    // So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

    // Collect info.
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    // Execute.
    $response = json_decode(curl_exec($ch));
    $information = curl_getinfo($ch);

    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($statuscode !== 200) {
        // NSDC reports an error.
        $error = ' ('.$statuscode.') '.$response->error;
        throw new moodle_exception('nsdc_api_returned_error', 'auth_nsdc', '', $error);
    }

    return true;
}

/**
 * Get the user's course completion status and return a NSDC status.
 *
 * @param int $userid
 * @param int $courseid 
 * @return int $status The NSDC course status code.
 */
function get_nsdc_course_status($userid, $courseid) {
    global $CFG;

    // NSDC status codes:
    // -1 = Quit
    //  0 = Enrolled
    //  1 = In Progress
    //  2 = Assessment Complete
    //  3 = Certification Complete
    //  4 = Course Complete
    require_once($CFG->dirroot.'/lib/completionlib.php');

    $status = 0; // Basically, they have to be enrolled in the course at this point.

    // Create a course object and create a course completion object.
    $course = new stdClass();
    $course->id = $courseid;
    $completioninfo = new \completion_info($course);

    if ($completioninfo->is_course_complete($userid)) {
        // The user has completed the course.
        $status = 4;
    }
    else {
        // Course completion will be false.
        // TODO: If we want to be more granular, we can set to 
        // In Progress only once the user's progress is more than 0%.
        $status = 1;
    }

    return $status;
}

/**
 * Get the user's course completion progress.
 *
 * @param int $userid
 * @param int $courseid 
 * @return int $percentage
 */
function get_course_progress_percentage($userid, $courseid) {
    $course = new stdClass();
    $course->id = $courseid;

    $percentage = intval(progress::get_course_progress_percentage($course, $userid));

    if ($percentage === 0) {
        $percentage = (int) 1;
    }

    return $percentage;
}

/**
 * Get the user's enrollment time.
 *
 * @param int $userid
 * @param int $courseid 
 * @return string $enrolldate
 */
function get_course_enrollment_date($userid, $courseid) {
    global $DB;

    // Get the enrol id for the enrollment instance.
    $userenrollments = $DB->get_records('user_enrolments', array('userid' => $userid), '', '*');

    // Iterate through the enrollments until the enrollment for the course is found.
    foreach ($userenrollments as $userenrollment) {
        // Get the course id from the corresponding mdl_enrol enrollment.
        $enrollmentcourse = $DB->get_field('enrol', 'courseid', array('id' => $userenrollment->enrolid));

        if ($enrollmentcourse == $courseid) {
            // This is the enrollment record for the course.
            $enrolltimestamp = $userenrollment->timecreated;
            break;
        }
    }

    // Convert to DateTime.
    $enrolldate = new DateTime();
    $enrolldate->setTimestamp($enrolltimestamp);

    // Set the timezone to IST (NSDC is in India).
    $timezone = new DateTimeZone('Asia/Kolkata');
    $enrolldate->setTimezone($timezone);
    
    // Return in DD-MM-YYYY format.
    return $enrolldate->format("d-m-Y");
}

/**
 * Get the user's last access time.
 *
 * @param int $userid
 * @param int $courseid 
 * @return string $lastaccess
 */
function get_course_lastaccess_date($userid, $courseid) {
    global $DB;

    // Get the last access time for the course. This is a UNIX timestamp.
    $lastaccesstimestamp = $DB->get_field('user_lastaccess', 'timeaccess', array('userid' => $userid, 'courseid' => $courseid));

    // If null, the user hasn't gone into the course yet. Most likely logging in for the first time.
    // Spoof the data with today's date.
    if ($lastaccesstimestamp == null) {
        $lastaccesstimestamp = time();
    }

    // Convert to DateTime.
    $lastaccess = new DateTime();
    $lastaccess->setTimestamp($lastaccesstimestamp);

    // Set the timezone to IST (NSDC is in India).
    $timezone = new DateTimeZone('Asia/Kolkata');
    $lastaccess->setTimezone($timezone);
    
    // Return in DD-MM-YYYY format.
    return $lastaccess->format("d-m-Y");
}

/**
 * Get the user's certification status.
 *
 * @param int $userid
 * @param int $courseid 
 * @return int $status 1 if certificate issued (course is completed), 0 if not.
 */
function get_certification_status($userid, $courseid) {
    global $CFG;

    require_once($CFG->dirroot.'/lib/completionlib.php');
    
    // Create a course object and create a course completion object.
    $course = new stdClass();
    $course->id = $courseid;
    $completioninfo = new \completion_info($course);

    $status = 0;

    if ($completioninfo->is_course_complete($userid)) {
        // The user has completed the course.
        $status = 1;
    }

    return $status;
}

/**
 * Get the date that a user got a certification - the date they completed a course.
 *
 * @param int $userid
 * @param int $courseid 
 * @return string $completiondate Date of completion in DD-MM-YYYY format.
 */
function get_certification_date($userid, $courseid) {
    global $CFG, $DB;

    // Get the user's completion info for the course
    $completioninfo = $DB->get_record('course_completions', array('userid' => $userid, 'course' => $courseid), '*', IGNORE_MISSING);

    $completiontimestamp = $completioninfo->timecompleted;

    // Convert to DateTime.
    $completiondate = new DateTime();
    $completiondate->setTimestamp($completiontimestamp);

    // Set the timezone to IST (NSDC is in India).
    $timezone = new DateTimeZone('Asia/Kolkata');
    $completiondate->setTimezone($timezone);    

    // Return in DD-MM-YYYY format.
    return $completiondate->format("d-m-Y");
}

/**
 * Get the grade of a completed course for a user.
 *
 * @param int $userid
 * @param int $courseid 
 * @return int $grade
 */
function get_certification_percentage($userid, $courseid) {
    global $CFG;

    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/grade/querylib.php');

    $gradeitem = \grade_get_course_grades($courseid, $userid);

    // Format the grade.
    $grade = intval($gradeitem->grades[$userid]->grade);

    return $grade;
}