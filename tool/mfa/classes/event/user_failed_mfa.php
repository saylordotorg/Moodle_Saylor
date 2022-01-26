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
 * Event for successful MFA authorization.
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_mfa\event;

/**
 * Event for when user successfully passed all MFA factor checks.
 *
 * @property-read array $other {
 *      Extra information about event.
 * }
 *
 * @package     tool_mfa
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class user_failed_mfa extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param int $user the User object of the User who failed MFA authentication.
     *
     * @return user_failed_mfa the user_passed_mfa event
     *
     * @throws \coding_exception
     */
    public static function user_failed_mfa_event($user) {
        // Build debug info string.
        $factors = \tool_mfa\plugininfo\factor::get_active_user_factor_types();
        $debug = '';
        $failurereason = get_string('event:failnotenoughfactors', 'tool_mfa');
        foreach ($factors as $factor) {
            $debug .= "<br> Factor {$factor->name} status: {$factor->get_state()}";
            if ($factor->get_state() === \tool_mfa\plugininfo\factor::STATE_FAIL) {
                $failurereason = get_string('event:failfactor', 'tool_mfa');
            } else if ($factor->get_state() === \tool_mfa\plugininfo\factor::STATE_LOCKED) {
                $failurereason = get_string('event:faillockout', 'tool_mfa');
            }
        }

        $data = array(
            'relateduserid' => null,
            'context' => \context_user::instance($user->id),
            'other' => array (
                'userid' => $user->id,
                'debug' => $debug,
                'failurereason' => $failurereason
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
        return "The user with id '{$this->other['userid']}' failed authenticating with MFA.
            <br> Information: {$this->other['failurereason']}{$this->other['debug']}";
    }

    /**
     * Return localised event name.
     *
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('event:userfailedmfa', 'tool_mfa');
    }
}
