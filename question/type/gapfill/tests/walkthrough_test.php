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
 * These tests walks a question through the interactive behaviour
 *
 * @package    qtype_gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/gapfill/tests/helper.php');

/**
 * Unit tests for the gapfill question type.
 *
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_walkthrough_test extends qbehaviour_walkthrough_test_base {

    public function test_draggable_items() {
        /* This checks that the bug from line 130 of questiontype is fixed
          that line should read
          if (!in_array($a->answer, $question->allanswers,true)) {
          Without the final true only one draggable will display */
        $questiontext = "[1.0] [1.00]";
        $options = array(
            'disableregex' => 1,
            'noduplicates' => 0,
            'delimitchars' => '[]'
        );
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, null, $options);
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        $this->check_output_contains('1.0');
        $this->check_output_contains('1.00');
    }

    public function test_deferred_feedback_unanswered() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        /* Check the initial state. */
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        // Save an  correct response.
        $this->process_submission(array('p1' => '', 'p2' => ''));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$todo);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gaveup);
        $this->check_current_mark(null);
    }

    public function test_deferred_with_correct() {
        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(2);
        $this->quba->finish_all_questions();
    }

    public function test_deferred_with_incorrect() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'dog', 'p2' => 'cat'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedwrong);
        $this->check_current_mark(0);
    }

    public function test_no_regex_or() {
        /* Tests a feature introduced with Gapfill 1.8 where
         * the | operator will be recognised as a separator for
         * multiple options when regex is turned off. Useful where
         * the answers contain characters considered special by
         * the regex parser.
         */

        $questiontext = "A programming question with multiple
                correct answers to a single field
                [getSize();| printSize();| 7/2]. The handling of
                | is done  with regex disabled so the (), ; and / cause
                no problems";

        $options = array(
            'disableregex' => 1,
            'noduplicates' => 0,
            'delimitchars' => '[]'
            );

        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, null, $options);
        $maxmark = 1;
            $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);
              $this->process_submission(array('p1' => 'getSize();'));
        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1);
    }

    public function test_deferred_with_partially_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'cat', 'p2' => 'dog'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(1);
    }

    public function test_deferred_with_blanks() {
        // Create a gapfill question.
        $questiontext = "The [cat] sat on the [mat]";
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext);
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);
        $this->process_submission(array('p1' => 'cat', 'p2' => ''));
        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(1);
    }

    public function test_extended_characters() {
        /* Testing extended characters, including a word that ends with an accent
         * there were issues using strtolower which only works with plain ascci.
         * This has been replaced with Moodles own core_text::strtolower($answergiven,'UTF-8');
         * A full test would include regex on and off and case sensitivity on and off
         */
        $questiontext = "Moscow is the capital of [Россия], The French word for boy is [garçon]. A word that
                ends with an accent is [andrà]  ";
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext);
        $maxmark = 3;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);
        $this->process_submission(array('p1' => 'Россия', 'p2' => 'garçon', 'p3' => 'andrà' ));
        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(3);
    }

    public function test_interactive_with_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'mat'));
        $this->check_step_count(2);

        $this->check_current_state(question_state::$todo);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        // Submit saved response.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(3);
        // Verify.
        $this->check_current_state(question_state::$gradedright);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $this->check_current_mark(2);
        // Finish the attempt.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
    }
    public function test_interactive_wildcard_with_correct() {
        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill', array('cat|dog', 'mat'));
        $maxmark = 2;

        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'mat'));
        $this->check_step_count(2);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $this->check_current_state(question_state::$todo);

        /*There was a word boundary bug in the regex previously that has been addressed by adding
        *a leading \b( and trailing )\b where the | character is found in the gap. This could be
        *checked further by processing adog as an answer. acat and doga would have been spotted previously
        *because of the leading ^ and trailing $ in the regex */
        $this->process_submission(array('-submit' => 1, 'p1' => 'catty', 'p2' => 'mat'));
        $this->check_step_count(3);

        // Verify.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedpartial);

        $this->check_current_mark(1);
        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);
        // Submit correct resonse.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(2);

        // Verify.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(2);

        // Finish the attempt.
    }

    public function test_disableregex() {
        $questiontext = 'for([$i]=0;$<10;$i++)';
        $options = array();
        $options['noduplicates'] = 0;
        $options['disableregex'] = 1;
        $options['delimitchars'] = '[]';
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false, $options);
        $this->start_attempt_at_question($gapfill, 'interactive', $gapfill->gapstofill);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);
        $this->process_submission(array('-submit' => 1, 'p1' => '$i'));

        $this->check_step_count(2);

        $this->quba->finish_all_questions();

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1);
        $this->quba->finish_all_questions();
    }

    public function test_interactive_discard_duplicates() {
        /* this is for the scenario where you have multiple fields
         * and each field could take any value. The marking is designed
         * to asssure that the student cannot get credited more than once
         * for each value, i.e. so if the answer is gold,silver, bronze
         * they cannot get 3 marks by entereing gold, gold and gold
         */

        /* Create a gapfill question and set noduplicates to true */
        $questiontext = '
What are the colors of the Olympic medals?

[gold|silver|bronze]
[gold|silver|bronze]
[gold|silver|bronze]  ';

        $options = array();
        $options['noduplicates'] = 1;
        $options['disableregex'] = 0;
        $options['delimitchars'] = '[]';
        /* answer with duplicate values, only one of each duplicate should get a mark */
        $submission = array('-submit' => 1, 'p1' => 'gold', 'p2' => 'silver', 'p3' => 'silver');

        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false, $options);

        $this->start_attempt_at_question($gapfill, 'interactive', $gapfill->gapstofill);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_no_hint_visible_expectation());

        $this->check_current_mark(null);

        // Save a  correct response.
        $this->process_submission($submission);

        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedpartial);

        $this->check_current_mark(2);
        $this->check_step_count(2);
    }

    public function test_interactive_grade_for_blank() {
        /* this is for the scenario where you have multiple fields
         * and each field could take any value. The marking is designed
         * to asssure that the student cannot get credited more than once
         * for each value, i.e. so if the answer is gold,silver, bronze
         * they cannot get 3 marks by entereing gold, gold and gold
         */

        /* Create a gapfill question that gives a mark where one response
         * is designed to be blank, i.e. [!!] */
        $questiontext = '
 [one] sat on the [two] [!!] ';

        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false);

        $this->start_attempt_at_question($gapfill, 'interactive', $gapfill->gapstofill);

        /* answer with duplicate values, only one of each duplicate should get a mark */
        /* save answer */
        $submission = array('p1' => 'one', 'p2' => 'two', 'p3' => '');

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_step_count(1);
        // Save a  correct response.
        $this->process_submission($submission);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation()
                , $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $this->check_current_mark(null);

        // Save a  correct response.
        $submission = array('-submit' => 1, 'p1' => 'one', 'p2' => 'two', 'p3' => '');

        $this->process_submission($submission);
        $this->check_current_mark(3);
        $this->check_current_state(question_state::$gradedright);

        /* start again but put a value in the field expecting a blank */
        $this->start_attempt_at_question($gapfill, 'interactive', $gapfill->gapstofill);
        $submission = array('p1' => 'one', 'p2' => 'two', 'p3' => "three");
        $this->process_submission($submission);
        $this->check_step_count(2);

        $this->check_current_mark(null);
        $this->check_current_state(question_state::$todo);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $submission = array('-submit' => 1, 'p1' => 'one', 'p2' => 'two', 'p3' => "three");
        $this->process_submission($submission);
        $this->check_current_mark(2);
        $this->check_current_state(question_state::$gradedpartial);

        $this->check_step_count(3);

        $this->quba->finish_all_questions();
    }

    public function test_get_aftergap_text() {
        $questiontext = "The [cat] sat on the [mat]";
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext);
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'immediatefeedback', $maxmark);
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'dog'));
        $html = $this->quba->render_question($this->slot, $this->displayoptions);
        $this->assertContains("[mat]", $html );

        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext);
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'immediatefeedback', $maxmark);
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $html = $this->quba->render_question($this->slot, $this->displayoptions);
        $this->assertNotContains("[mat]", $html );

    }
    public function test_deferred_grade_for_blank() {
        /* this is for the scenario where you have multiple fields
         * and each field could take any value. The marking is designed
         * to asssure that the student cannot get credited more than once
         * for each value, i.e. so if the answer is gold,silver, bronze
         * they cannot get 3 marks by entereing gold, gold and gold
         */

        /* Create a gapfill question that gives a mark where one response
         * is designed to be blank, i.e. [!!] */
        $questiontext = '
 [one] sat on the [two] [!!] ';

        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false);

        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $gapfill->gapstofill);
        /* A mark for a blank submission where the gap is [!!] */
        $submission = array('p1' => 'one', 'p2' => 'two', 'p3' => '');

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_step_count(1);
        // Save a  correct response.
        $this->process_submission($submission);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $this->process_submission(array('-finish' => 1));

        $this->check_current_mark(3);
        $this->check_current_state(question_state::$gradedright);
        $this->quba->finish_all_questions();
    }

    public function test_immediatefeedback_with_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;

        $gapfill->showanswers = true;
        $this->start_attempt_at_question($gapfill, 'immediatefeedback', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'cat'));
        $this->check_step_count(2);
        $this->check_current_mark(null);

        $this->check_current_state(question_state::$todo);
        // Submit saved response.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(3);
        // Verify.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);

        $this->check_current_mark(2);
        // Finish the attempt.
    }
    public function test_get_gapsize() {
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', "", false);
        $this->assertEquals($gapfill->get_size("one"), 3);
        $this->assertEquals($gapfill->get_size("one|twleve"), 6);
    }

}
