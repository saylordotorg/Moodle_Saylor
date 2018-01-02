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
 * Unit tests for the short answer question definition class.
 *
 * @package    qtype_algebra
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/algebra/tests/helper.php');


/**
 * Unit tests for the algebra question definition class.
 *
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_algebra_question_test extends advanced_testcase {
    /**
     * @return qtype_formulas_question the requested question object.
     */
    protected function get_test_algebra_question($which = null) {
        return test_question_maker::make_question('algebra', $which);
    }

    public function test_is_gradable_response() {
        $question = $this->get_test_algebra_question('simplemath');

        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertFalse($question->is_gradable_response(array('answer' => '')));
        $this->assertTrue($question->is_gradable_response(array('answer' => '0')));
        $this->assertTrue($question->is_gradable_response(array('answer' => '0.0')));
        $this->assertTrue($question->is_gradable_response(array('answer' => 'x')));
    }

    public function test_grading_test0() {
        $question = $this->get_test_algebra_question('simplemath');

        $this->assertEquals(array(0, question_state::$gradedwrong),
                $question->grade_response(array('answer' => 'x')));
        $this->assertEquals(array(0, question_state::$gradedwrong),
                $question->grade_response(array('answer' => '0')));
        $this->assertEquals(array(0, question_state::$gradedwrong),
                $question->grade_response(array('answer' => '5*x')));
        $this->assertEquals(array(1, question_state::$gradedright),
                $question->grade_response(array('answer' => '7*x')));
    }

    public function test_grading_test1() {
        $question = $this->get_test_algebra_question('derive');

        $this->assertEquals(array(0.2, question_state::$gradedpartial),
                $question->grade_response(array('answer' => 'x')));
        $this->assertEquals(array(0, question_state::$gradedwrong),
                $question->grade_response(array('answer' => '0')));
        $this->assertEquals(array(1, question_state::$gradedright),
                $question->grade_response(array('answer' => '2*x')));
        $this->assertEquals(array(1, question_state::$gradedright),
                $question->grade_response(array('answer' => 'x+x')));
    }
    public function test_get_correct_response() {
        $question = $this->get_test_algebra_question('simplemath');

        $this->assertEquals(array('answer' => '7*x'),
                $question->get_correct_response());
    }

    public function test_get_question_summary() {
        $question = $this->get_test_algebra_question('derive');
        $qsummary = $question->get_question_summary();
        $this->assertEquals('What is the derivative of the function \(f(x) = x^2\) ?', $qsummary);
    }

    public function test_summarise_response() {
        $question = $this->get_test_algebra_question('derive');
        $summary = $question->summarise_response(array('answer' => '2*x'));
        $this->assertEquals('2*x', $summary);
    }

    public function test_classify_response() {
        $question = $this->get_test_algebra_question('derive');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertEquals(array(
                new question_classified_response(13, '2*x', 1.0)),
                $question->classify_response(array('answer' => '2*x')));
        $this->assertEquals(array(
                new question_classified_response(0, '5*x', 0)),
                $question->classify_response(array('answer' => '5*x')));
        $this->assertEquals(array(
                question_classified_response::no_response()),
                $question->classify_response(array('answer' => '')));
    }

}
