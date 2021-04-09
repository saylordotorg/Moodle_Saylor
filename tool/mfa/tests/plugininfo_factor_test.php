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
 * Tests for plugininfo.
 *
 * @package     tool_mfa
 * @author      Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class tool_mfa_plugininfo_factor_testcase extends advanced_testcase {

    public function test_get_next_user_factor() {

        $this->resetAfterTest(true);

        // Create and login a user.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Test that with no enabled factors, fallback is returned.
        $this->assertEquals(\tool_mfa\plugininfo\factor::get_next_user_factor()->name, 'fallback');

        // Setup enabled totp factor for user.
        set_config('enabled', 1, 'factor_totp');
        $totpfactor = \tool_mfa\plugininfo\factor::get_factor('totp');
        $totpdata = [
            'secret' => 'fakekey',
            'devicename' => 'fakedevice'
        ];
        $this->assertNotEmpty($totpfactor->setup_user_factor((object) $totpdata));

        // Test that factor now appears (from STATE_UNKNOWN).
        $this->assertEquals(\tool_mfa\plugininfo\factor::get_next_user_factor()->name, 'totp');

        // Now pass this factor, check for fallback.
        $totpfactor->set_state(\tool_mfa\plugininfo\factor::STATE_PASS);
        $this->assertEquals(\tool_mfa\plugininfo\factor::get_next_user_factor()->name, 'fallback');

        // Add in a no-input factor.
        set_config('enabled', 1, 'factor_auth');
        $this->assertEquals(2, count(\tool_mfa\plugininfo\factor::get_enabled_factors()));

        $authfactor = \tool_mfa\plugininfo\factor::get_factor('auth');
        $this->assertTrue($authfactor->is_enabled());
        $this->assertFalse($authfactor->has_setup());

        // Check that the next factor is still the fallback factor.
        $this->assertEquals(2, count(\tool_mfa\plugininfo\factor::get_active_user_factor_types()));
        $this->assertEquals(\tool_mfa\plugininfo\factor::get_next_user_factor()->name, 'fallback');
    }
}
