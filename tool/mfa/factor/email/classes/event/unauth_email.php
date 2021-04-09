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
 * Event for unauthorised email being received from MFA.
 *
 * @package     factor_email
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace factor_email\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a user receives an unauthorised email from MFA.
 *
 * @property-read array $other {
 *      Extra information about event.
 * }
 *
 * @package     factor_email
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class unauth_email extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param stdClass $user the User object of the User who passed all MFA factor checks.
     * @param string $ip the ip address the unauthorised email came from.
     * @param string $useragent the browser fingerpring the unauthorised email came from.
     *
     * @return user_passed_mfa the user_passed_mfa event
     *
     * @throws \coding_exception
     */
    public static function unauth_email_event($user, $ip, $useragent) {

        $data = array(
            'relateduserid' => null,
            'context' => \context_user::instance($user->id),
            'other' => array (
                'userid' => $user->id,
                'ip' => $ip,
                'useragent' => $useragent
            )
        );

        return self::create($data);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '{$this->other['userid']}' made an unauthorised login attempt using email verification from
            IP '{$this->other['ip']}' with browser agent '{$this->other['useragent']}'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('event:unauthemail', 'factor_email');
    }
}
