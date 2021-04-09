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

namespace tool_mfa\tests;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__ . '/../lib.php');

abstract class tool_mfa_testcase extends \advanced_testcase {

    protected function set_factor_state($factorname, $enabled = 0, $weight = 100) {
        $factor = \tool_mfa\plugininfo\factor::get_factor($factorname);
        $this->set_factor_config($factor, 'enabled', $enabled);
        $this->set_factor_config($factor, 'weight', $weight);
    }

    protected function set_factor_config($factor, $key, $value) {
        \tool_mfa\manager::set_factor_config(array($key => $value), 'factor_' . $factor->name);

        if ($key == 'enabled') {
            if ($value == 1) {
                \tool_mfa\manager::do_factor_action($factor->name, 'enable');
            } else {
                \tool_mfa\manager::do_factor_action($factor->name, 'disable');
            }
        }
    }
}
