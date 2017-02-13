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
 * Unit tests for the quizaccess_honestycheck plugin.
 *
 * @package   quizaccess_honestycheck
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/honestycheck/rule.php');


/**
 * Unit tests for the quizaccess_honestycheck plugin.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_honestycheck_test extends basic_testcase {
    public function test_honestycheck_rule() {
        $quiz = new stdClass();
        $quiz->attempts = 3;
        $quiz->questions = '';
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = quizaccess_honestycheck::make($quizobj, 0, false);
        $this->assertNull($rule);

        $quiz->honestycheckrequired = true;
        $rule = quizaccess_honestycheck::make($quizobj, 0, false);
        $this->assertInstanceOf('quizaccess_honestycheck', $rule);
        $this->assertTrue($rule->is_preflight_check_required(null));

        $this->assertFalse($rule->is_preflight_check_required(1));

        $errors = $rule->validate_preflight_check(array(), null, array(), 1);
        $this->assertArrayHasKey('honestycheck', $errors);

        $errors = $rule->validate_preflight_check(array('honestycheck' => 1), null, array(), 1);
        $this->assertEmpty($errors);
    }
}
