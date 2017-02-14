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
 * Event observers used in auth mcae plugin.
 * @copyright  2016 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class auth_mcae_observer {
    /**
     * Observer function to handle the user created event
     * @param \core\event\user_created $event
     */
    public static function user_created(\core\event\user_created $event) {
        global $CFG, $DB;
        if (is_enabled_auth('mcae')) {
            require_once($CFG->dirroot . '/auth/mcae/auth.php');
            $eventdata = $event->get_data();
            $auth = get_auth_plugin('mcae');
            if ($user = $DB->get_record('user', array('id' => $eventdata['relateduserid']))) {
                $auth->user_authenticated_hook($user, $user->username, '');
            }
        }
    }

    /**
     * Observer function to handle the user updated event
     * @param \core\event\user_created $event
     */
    public static function user_updated(\core\event\user_updated $event) {
        global $CFG, $DB;
        if (is_enabled_auth('mcae')) {
            require_once($CFG->dirroot . '/auth/mcae/auth.php');
            $eventdata = $event->get_data();
            $auth = get_auth_plugin('mcae');
            if ($user = $DB->get_record('user', array('id' => $eventdata['relateduserid']))) {
                $auth->user_authenticated_hook($user, $user->username, '');
            }
        }
    }

}