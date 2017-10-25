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
 * Sayonara
 *
 * This fork of Goodbye is designed to work with Moodle 3.2+ and the Boost theme.
 * The option to delete will be in the user's profile.
 *
 * @package    local
 * @subpackage sayonara
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Goodbye
 *
 * This module has been created to provide users the option to delete their account
 *
 * @package    local
 * @subpackage goodbye, delete your moodle account
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/local/sayonara/check_account_form.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/sayonara/index.php');
$PAGE->set_title(format_string(get_string('deleteaccount', 'local_sayonara')));
$PAGE->set_heading(format_string(get_string('userpass', 'local_sayonara')));

$systemcontext = context_system::instance();
$enabled = get_config('local_sayonara', 'enabled');
$error = '';

global $USER;

if ($enabled) {
    if (isloggedin() && !isguestuser($USER) && !is_mnet_remote_user($USER) && confirm_sesskey()) {
        require_capability('moodle/user:editownprofile', $systemcontext);
        $url = new moodle_url('/local/sayonara/index.php', array('sesskey'=>$USER->sesskey));
        $checkaccount = new check_account_form($url);

        if ($local_user = $checkaccount->get_data()) {
            if ($local_user->username != '' && $local_user->password != '') {
                if ($user = $DB->get_record('user', array('username'=>$local_user->username))) {
                    // User Exists, Check pass.
                    if ($user->id == $USER->id) { // User credentials authenticating with MUST match the credentials they are already logged in with.
                        if ($user = authenticate_user_login($local_user->username, $local_user->password) ) {
                            complete_user_login($user);
                            if ($user->id == $USER->id && ($USER->auth == 'email' || $USER->auth == 'manual')) {
                                delete_user($user);

                                $authsequence = get_enabled_auth_plugins(); // auths, in sequence
                                foreach($authsequence as $authname) {
                                    $authplugin = get_auth_plugin($authname);
                                    $authplugin->logoutpage_hook();
                                }

                                require_logout();
                                echo $OUTPUT->header(get_string('deleteaccount', 'local_sayonara'));
                                echo $OUTPUT->notification(get_string('useraccountdeleted', 'local_sayonara'), 'notifysuccess');
                                echo $OUTPUT->footer();
                                exit;
                            } else {
                                $error = $OUTPUT->notificatin(get_string('noself', 'local_sayonara'));
                            }
                        } else {
                            $error = $OUTPUT->notification(get_string('loginerror', 'local_sayonara'));
                        }
                    } else {
                        $error = $OUTPUT->notification(get_string('loginerror', 'local_sayonara'));
                    }
                } else {
                    $error = $OUTPUT->notification(get_string('loginerror', 'local_sayonara'));
                }
            }
        }

        echo $OUTPUT->header(get_string('deleteaccount', 'local_sayonara'));
        echo $error;
        $checkaccount->display();
    } else {
        echo $OUTPUT->header(get_string('loginsessionerror', 'local_sayonara'));
        echo $OUTPUT->notification(get_string('loginsessionerror', 'local_sayonara'));
    }
} else {
    echo $OUTPUT->header(get_string('disabled', 'local_sayonara'));
}

echo $OUTPUT->footer();



