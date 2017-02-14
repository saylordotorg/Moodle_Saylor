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
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

if (!is_enabled_auth('mcae')) {
    cli_error('auth_mcae plugin is disabled, synchronisation stopped', 2);
}

$username = cli_input('Username to update');

if ($username) {
    $auth = get_auth_plugin('mcae');
    if ($user = $DB->get_record('user', array('username' => $username))) {
        echo "Update user $username complete\n";
        $auth->user_authenticated_hook($user, $username, '');
    }
}
exit("\nFinish\n");
