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

// Designed to be redirected from moodle/login/index.php

require_once('../../config.php');
require_once($CFG->dirroot . '/auth/nsdc/locallib.php'); 

$encryptedpayload = required_param('encrypt_data', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_url('/auth/nsdc/login.php');
$PAGE->set_context($context);

$pluginconfig = get_config('auth_nsdc');  

// Check whether keys are set up in the settings
if (empty($pluginconfig->key)) {
    throw new \moodle_exception('nsdc_no_key', 'auth_nsdc');
}
if (empty($pluginconfig->iv)) {
    throw new \moodle_exception('nsdc_no_iv', 'auth_nsdc');
}
if (empty($pluginconfig->baseemail)) {
    throw new \moodle_exception('nsdc_no_baseemail', 'auth_nsdc');
}

// Set whether we are using the production environment.
$production = (bool) $pluginconfig->production;

// Decrypt the payload and verify.
$data = \auth_nsdc\decrypt_payload($encryptedpayload);

// Check if user has an account.
$hasaccount = \auth_nsdc\has_account($data->candidate_id);
if (!$hasaccount) {
    // There was no record of this user connecting before; create account.
    // Gather user information.
    if (!\auth_nsdc\create_account($data)) {
        throw new \moodle_exception('could_not_create_account', 'auth_nsdc');
    }
}

// Get the userdata from Moodle.
$user = \auth_nsdc\get_user($data);

// Check course enrollment and enroll.
// Do this before logging in or new users won't be enrolled.
$coursecontext = context_course::instance($data->kp_course_id);
if (!is_enrolled($coursecontext, $user->id)) {
    // This user is not enrolled in the course; enroll.
    \auth_nsdc\enrol_nsdc_user($user, $data->kp_course_id);
}

// Get course and progress info; send to NSDC.
\auth_nsdc\course_status_update($data, $user, $production);

// Complete the user login.
// If a new user or missing required profile fields, they'll be redirected to /user/edit.php.
\auth_nsdc\complete_login($user);

// Redirect to course.
$redirecturl = new moodle_url('/course/view.php?id='.$data->kp_course_id);
redirect($redirecturl);

