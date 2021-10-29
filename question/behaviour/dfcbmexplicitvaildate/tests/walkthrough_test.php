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
 * This file contains tests that walks a question through the deferred feedback
 * with CBM and explicit validation behaviour.
 *
 * @package    qbehaviour_dfcbmexplicitvaildate
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(dirname(__FILE__) . '/../../../engine/lib.php');
require_once(dirname(__FILE__) . '/../../../engine/tests/helpers.php');


/**
 * Unit tests for the deferred feedback with explicit validation behaviour.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_dfcbmexplicitvaildate_walkthrough_test extends qbehaviour_walkthrough_test_base {
    public function test_dummy() {
        // At the moment, there are extensive tests for this behaviour in
        // qtype_stack, and no other qtypes use this behaviour. Therefore, we
        // don't have any real tests here.
        // See https://github.com/sangwinc/moodle-qtype_stack/blob/master/tests/walkthrough_deferred_cbm_test.php
        // for the tests that exist.
        $this->assertTrue(true);
    }
}
