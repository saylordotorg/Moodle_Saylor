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
 * Unit tests for the gapfill question definition class.
 *
 * @package    qtype_gapfill
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

require_once($CFG->dirroot . '/question/type/gapfill/question.php');

/**
 * Unit tests for the gapfill question definition class.
 *
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_question_test extends advanced_testcase {
    public function test_get_expected_data() {

        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $expecteddata = array('p1' => 'raw_trimmed', 'p2' => 'raw_trimmed');
        $this->assertEquals($question->get_expected_data(), $expecteddata);
    }

    public function test_field() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $this->assertEquals($question->field('1'), 'p1');
    }

    public function test_summarise_response_() {
         $question = qtype_gapfill_test_helper::make_question('gapfill');
         $response = array('p1' => 'cat', 'p2' => 'dog');
         $this->assertEquals($question->summarise_response($response), " cat  dog ");
    }

    public function test_grade_response() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');

        $response = array('p1' => 'cat', 'p2' => 'dog');
        list($fraction, $state) = $question->grade_response($response);

        /* with two fields, if you have one wrong the score (fraction)
         will be .5. Fraction is always a a fractional part of one.*/
        $this->assertEquals($fraction, .5);

        $response = array('p1' => 'cat', 'p2' => 'mat');
        list($fraction, $state) = $question->grade_response($response);

        // If you have all correct score (fraction)
        // will be 1. Fraction is always a a fractional part of one.
        $this->assertEquals($fraction, 1);
    }

    public function test_compute_final_grade() {
        $question = qtype_gapfill_test_helper::make_question2();
        $responses = [
            0 => ['p1' => 'cat', 'p2' => 'cat'],
            1 => ['p1' => 'cat', 'p2' => 'cat'],
            2 => ['p1' => 'cat', 'p2' => 'cat']
        ];
        $fraction = $question->compute_final_grade($responses, 3);
        /* With a default mark of 2 this would show a mark of 1
        This was compared with how the ddwtos question marked */
        $this->assertEquals($fraction, .5);
    }
    public function test_discard_duplicates() {
        $options = [
            "noduplicates" => 1,
            'disableregex' => 0,
            'delimitchars' => '[]'
        ];
        $questiontext = 'The [cat] sat on the [mat]';
        $question = qtype_gapfill_test_helper::make_question2($questiontext, false, $options);
        $response = array('p1' => 'cat', 'p2' => 'cat');
        $ddresponse = $question->discard_duplicates($response);
        $numpartsright = $question->get_num_parts_right($ddresponse);
        $this->assertEquals($numpartsright, 1);
    }


    public function test_is_complete_response() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $response = array('p1' => 'cat', 'p2' => 'mat');

        $this->assertTrue($question->is_complete_response($response));

        $response = array('p1' => 'cat');
        $question->gapcount = 2;
        $this->assertFalse($question->is_complete_response($response));

        $this->assertFalse($question->is_complete_response(array()));
    }
    public function test_get_correct_response() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $this->assertEquals($question->get_correct_response(), array('p1' => 'cat', 'p2' => 'mat'));
    }

    public function test_get_validation_error() {
        $questiontext = 'The [cat] sat on the [mat]';
        $question = qtype_gapfill_test_helper::make_question2($questiontext);
        $question->gapcount = 2;
        $this->assertTrue(is_string($question->get_validation_error( array('p1' => '') ) ));
    }


    public function test_is_correct_response() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $question->casesensitive = 0;
        $answergiven = 'CAT';
        $rightanswer = 'cat';
        $this->assertTrue($question->is_correct_response($answergiven, $rightanswer));

        $question->casesensitive = 1;
        $this->assertFalse($question->is_correct_response($answergiven, $rightanswer));

        $answergiven = 'dog';
        $rightanswer = 'cat';
        $this->assertFalse($question->is_correct_response($answergiven, $rightanswer));

        $answergiven = 'cat';
        $rightanswer = 'cat';
        $this->assertTrue($question->is_correct_response($answergiven, $rightanswer));

    }

    public function test_get_right_choice_for_place() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $this->assertEquals($question->get_right_choice_for(1), 'cat');
        $this->assertNotEquals($question->get_right_choice_for(2), 'cat');
    }

    public function test_is_same_response() {
        $question = qtype_gapfill_test_helper::make_question('gapfill');
        $prevresponse = array();
        $newresponse = array('p1' => 'cat', 'p2' => 'mat');
        $this->assertFalse($question->is_same_response($prevresponse, $newresponse));
        $prevresponse = array('p1' => 'cat', 'p2' => 'mat');
        $newresponse = array('p1' => 'cat', 'p2' => 'mat');
        $this->assertTrue($question->is_same_response($prevresponse, $newresponse));
    }

}
