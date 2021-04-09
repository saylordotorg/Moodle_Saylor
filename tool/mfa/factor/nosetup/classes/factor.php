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
 * No setup factor class.
 *
 * @package     tool_mfa
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace factor_nosetup;

defined('MOODLE_INTERNAL') || die();

use tool_mfa\local\factor\object_factor_base;

class factor extends object_factor_base {

    /**
     * No Setup Factor implementation.
     * Factor is a singleton, can only be one instance.
     *
     * {@inheritDoc}
     */
    public function get_all_user_factors($user) {
        global $DB;
        $records = $DB->get_records('tool_mfa', array('userid' => $user->id, 'factor' => $this->name));

        if (!empty($records)) {
            return $records;
        }

        // Null records returned, build new record.
        $record = array(
            'userid' => $user->id,
            'factor' => $this->name,
            'timecreated' => time(),
            'createdfromip' => $user->lastip,
            'timemodified' => time(),
            'revoked' => 0,
        );
        $record['id'] = $DB->insert_record('tool_mfa', $record, true);
        return [(object) $record];
    }

    /**
     * No Setup Factor implementation.
     * Factor does not have input.
     *
     * {@inheritDoc}
     */
    public function has_input() {
        return false;
    }

    /**
     * No Setup Factor implementation.
     * State check is performed here, as there is no form to do it in.
     *
     * {@inheritDoc}
     */
    public function get_state() {
        // Check if user has any other input or setup factors active.
        $factors = \tool_mfa\plugininfo\factor::get_active_user_factor_types();
        foreach ($factors as $factor) {
            if ($factor->has_input() || $factor->has_setup()) {
                return \tool_mfa\plugininfo\factor::STATE_NEUTRAL;
            }
        }

        return \tool_mfa\plugininfo\factor::STATE_PASS;
    }

    /**
     * No setup implementation.
     * Copy of get_state, but can take other user..
     *
     * @param stdClass $user
     * @return void
     */
    public function possible_states($user) {
        // Check if user has any other input or setup factors active.
        $factors = \tool_mfa\plugininfo\factor::get_active_other_user_factor_types($user);
        foreach ($factors as $factor) {
            if ($factor->has_input() || $factor->has_setup()) {
                return array(\tool_mfa\plugininfo\factor::STATE_NEUTRAL);
            }
        }

        return array(\tool_mfa\plugininfo\factor::STATE_PASS);
    }

    /**
     * No Setup Factor implementation.
     * The state can never be set. Always return true.
     *
     * {@inheritDoc}
     */
    public function set_state($state) {
        return true;
    }

    /**
     * No Setup Factor implementation.
     * nosetup should not be a valid combination with another factor.
     *
     * {@inheritDoc}
     */
    public function check_combination($combination) {
        // If this combination has more than 1 factor that has setup or input, not valid.
        foreach ($combination as $factor) {
            if ($factor->has_setup() || $factor->has_input()) {
                return false;
            }
        }
        return true;
    }
}
