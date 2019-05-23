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
 * local_discoursesso
 *
 *
 * @package    local
 * @subpackage discoursesso
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once('../../config.php');
require_once('./locallib.php');
require_once __DIR__ . '/vendor/discourse-php/src/SSOHelper.php';

global $CFG, $DB, $USER;

$payload = required_param('sso', PARAM_RAW);
$signature = required_param('sig', PARAM_RAW);
$wantsurl = optional_param('wantsurl', NULL, PARAM_RAW);

// Print the header
$strmodulename = get_string('modulename', 'local_discoursesso');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/discoursesso/sso.php', array('sso'=>$payload, 'sig'=>$signature));
$PAGE->set_title($strmodulename);
$PAGE->set_heading($strmodulename);

if (isset($SESSION->wantsurl) && !isset($wantsurl)) {
    $wantsurl = urlencode($SESSION->wantsurl);
}

// Build wantsurl.
$redirecturl = $PAGE->url . '?sso=' . $payload . '&sig=' . $signature;
if (isset($wantsurl)) {
    $redirecturl = $redirecturl . '&wantsurl=' . urlencode($SESSION->wantsurl);
}

// Set wantsurl so user is redirected back here on login.
$SESSION->wantsurl =  $redirecturl;

// Check if user is logged and not guest. If not, redirect to login page.
if (isguestuser()) {
	redirect(get_login_url());
}
require_login(null, false);

// Reset $SESSION->wantsurl.
if (!isset($wantsurl)) {
    unset($SESSION->wantsurl);
}
else {
    $SESSION->wantsurl = urldecode($wantsurl);
}

// Create SSOHelper and configure 
$ssohelper = new Cviebrock\DiscoursePHP\SSOHelper();

$ssohelper->setSecret($CFG->discoursesso_secret_key);

// Validate the payload.
if (!($ssohelper->validatePayload($payload, $signature))) {
    // invalid, deny.
    header("HTTP/1.1 403 Forbidden");
    echo("Bad SSO request");
    $exceptionparam = new stdClass;
    $exceptionparam->payload = $payload;
    $exceptionparam->signature = $signature;
    throw new moodle_exception('badssoerror', 'discoursesso', $CFG->discoursesso_discourse_url, $exceptionparam);
    die();
}

$nonce = $ssohelper->getNonce($payload);

// Required and must be unique to your application.
$userid = $USER->id;

// Required and must be consistent with your application.
$useremail = $USER->email;

// Optional - if you don't set these, Discourse will generate suggestions
// based on the email address.

// Get the user's description.
$user = $DB->get_record('user', array('id' => $USER->id));
$userdescription = format_text($user->description, $user->descriptionformat);

$extraparams = array(
    'username'      => $USER->username,
    'name'          => fullname($USER, true),
    'bio'           => $userdescription
);

// Generate user avatar url
$userpicture = new user_picture($USER);
$userpicture->size = 1; // Size f1.
// Did the user upload an avatar or is gravatar enabled?
if (($userpicture->user->picture > 0) || !empty($CFG->enablegravatar)) {
    $useravatar = $userpicture->get_url($PAGE)->out(false);
}
// Add the avatar if set.
if (isset($useravatar)) {
    $extraparams['avatar_url'] = $useravatar;
    $extraparams['avatar_force_update'] = 'true';
}

// Get the user's locale after converting, if enabled.
if (get_config('local_discoursesso', 'locale')) {
    $discourselocale = get_discourse_locale($user->lang);

    $extraparams['locale'] = $discourselocale;
    $extraparams['locale_force_update'] = 'true';
}

// Sync admin status, if enabled.
if (get_config('local_discoursesso', 'adminsync')) {
    // Check if this user is an admin.
    if (is_siteadmin()) {
        $extraparams['admin']='true';
    }
}

// Build query string and redirect back to the Discourse site.
$query = $ssohelper->getSignInString($nonce, $userid, $useremail, $extraparams);
$url = $CFG->discoursesso_discourse_url . '/session/sso_login?' . $query;
redirect($url);
