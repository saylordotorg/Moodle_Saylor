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

require_once __DIR__ . '/vendor/discourse-php/src/SSOHelper.php';

global $CFG, $DB, $USER;

$payload = required_param('sso', PARAM_RAW);
$signature = required_param('sig', PARAM_RAW);
$wantsurl = optional_param('wantsurl', NULL, PARAM_RAW);

// Print the header
$strmodulename = get_string('modulename', 'local_discoursesso');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/discoursesso/index.php', array('sso'=>$payload, 'sig'=>$signature));
//$PAGE->navbar->add($strcertificates);
$PAGE->set_title($strmodulename);
$PAGE->set_heading($strmodulename);

$context = context_course::instance(1);

if (isset($SESSION->wantsurl) && !isset($wantsurl)) {
    $wantsurl = urlencode($SESSION->wantsurl);
}

//Build wantsurl
$redirecturl = $PAGE->url . '?sso=' . $payload . '&sig=' . $signature;
if (isset($wantsurl)) {
    $redirecturl = $redirecturl . '&wantsurl=' . urlencode($SESSION->wantsurl);
}

// Set wantsurl so user is redirected back here on login
$SESSION->wantsurl =  $redirecturl;

// Check if user is logged and not guest. If not, redirect to login page.
if (isguestuser()) {
	redirect(get_login_url());
}
require_login(null, false);

// Reset $SESSION->wantsurl
if (!isset($wantsurl)) {
    unset($SESSION->wantsurl);
}
else {
    $SESSION->wantsurl = urldecode($wantsurl);
}

// Create SSOHelper and configure 
$ssohelper = new Cviebrock\DiscoursePHP\SSOHelper();

$ssohelper->setSecret($CFG->discoursesso_secret_key);

// validate the payload
if (!($ssohelper->validatePayload($payload, $signature))) {
    // invaild, deny
    header("HTTP/1.1 403 Forbidden");
    echo("Bad SSO request");
    $exceptionparam->payload = $payload;
    $exceptionparam->signature = $signature;
    throw new moodle_exception('badssoerror', 'discoursesso', $CFG->discoursesso_discourse_url, $exceptionparam);
    die();
}

$nonce = $ssohelper->getNonce($payload);

// Required and must be unique to your application
$userId = $USER->id;

// Required and must be consistent with your application
$userEmail = $USER->email;

// Optional - if you don't set these, Discourse will generate suggestions
// based on the email address

// Generate user avatar url
$userpicture = new user_picture($USER);
$userpicture->size = 1; // Size f1.
$userAvatar = $userpicture->get_url($PAGE)->out(false);

// Get the user's description
$user = $DB->get_record('user', array('id' => $USER->id));
$userDescription = format_text($user->description, $user->descriptionformat);

$extraParameters = array(
    'username' => $USER->username,
    'name'     => fullname($USER, true),
    'avatar_url' => $userAvatar,
    'bio'      => $userDescription
);

// build query string and redirect back to the Discourse site
$query = $ssohelper->getSignInString($nonce, $userId, $userEmail, $extraParameters);
$url = $CFG->discoursesso_discourse_url . '/session/sso_login?' . $query;
redirect($url);
