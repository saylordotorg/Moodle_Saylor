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
 * Test helpers for the algebra question type.
 *
 * @package    qtype_algebra
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Test helper class for the algebra question type.
 *
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebra_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('simplemath', 'derive');
    }

    /**
     * Makes a algebra question with correct ansewer 7 and defaultmark 1.
     * This question also has one variable.
     * @return qtype_algebra_question
     */
    public function make_algebra_question_simplemath() {
        question_bank::load_question_definition_classes('algebra');
        $q = new qtype_algebra_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Algebra question';
        $q->questiontext = 'P(x) = 3x and Q(x) = 4x. Calculate (P + Q)(x)';
        $q->generalfeedback = 'Generalfeedback: (P + Q)(x) = 7x.';
        $q->compareby = 'eval';
        $q->nchecks = 10;
        $q->tolerance = 0.001;
        $q->disallow = '';
        $q->allowedfuncs = '';
        $q->answerprefix = '';
        $q->answers = array(
            13 => new question_answer(13, '7*x', 1.0, 'This is a very good answer.', FORMAT_HTML),
        );
        $q->variables = array(
            7 => new qtype_algebra_variable(7, 'x', -5, 5)
        );
        $q->qtype = question_bank::get_qtype('algebra');
        return $q;
    }

    /**
     * Gets the question data for a algebra question with with correct
     * ansewer 7 and defaultmark 1.
     * This question also has one variable.
     * @return stdClass
     */
    public function get_algebra_question_data_simplemath() {
        $qdata = new stdClass();
        test_question_maker::initialise_question_data($qdata);

        $qdata->qtype = 'algebra';
        $qdata->name = 'Algebra question';
        $qdata->questiontext = 'P(x) = 3x and Q(x) = 4x. Calculate (P + Q)(x)';
        $qdata->generalfeedback = 'Generalfeedback: (P + Q)(x) = 7x.';

        $qdata->options = new stdClass();
        $qdata->options->compareby = 'eval';
        $qdata->options->nchecks = '10';
        $qdata->options->tolerance = 0.001;
        $qdata->options->disallow = '';
        $qdata->options->allowedfuncs = array();
        $qdata->options->answerprefix = '';
        $qdata->options->answers = array(
            13 => new question_answer(13, '7*x', 1.0, 'This is a very good answer.', FORMAT_HTML),
        );
        $qdata->options->variables = array(
            7 => new qtype_algebra_variable(7, 'x', -5, 5)
        );

        return $qdata;
    }

    /**
     * Gets the question form data for a algebra question with with correct
     * answer '7*x' and defaultmark 1.
     * @return stdClass
     */
    public function get_algebra_question_form_data_simplemath() {
        $form = new stdClass();

        $form->name = 'Algebra question';
        $form->questiontext = array('text' => 'P(x) = 3x and Q(x) = 4x. Calculate (P + Q)(x)',
                'format' => FORMAT_HTML);
        $form->defaultmark = 1.0;
        $form->generalfeedback = array('text' => 'Generalfeedback: (P + Q)(x) = 7x.', 'format' => FORMAT_HTML);
        $form->compareby = 'eval';
        $form->nchecks = '10';
        $form->tolerance = 0.001;
        $form->allowedfuncs = array('all' => 1);
        $form->disallow = '';
        $form->answerprefix = '';
        $form->answer = array('7*x');
        $form->fraction = array('1.0');
        $form->variable = array('x');
        $form->varmin = array('-5');
        $form->varmax = array('5');
        $form->feedback = array(
            array('text' => 'This is a very good answer.', 'format' => FORMAT_HTML),
        );
        $form->penalty = 0.3333333;

        return $form;
    }

    /**
     * Makes a algebra question with one correct and one partially correct answer.
     * @return qtype_algebra_question
     */
    public function make_algebra_question_derive() {
        question_bank::load_question_definition_classes('algebra');
        $q = new qtype_algebra_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Algebra question';
        $q->questiontext = 'What is the derivative of the function \(f(x) = x^2\) ?';
        $q->generalfeedback = 'Generalfeedback: 2*x is the correct answer.';
        $q->compareby = 'eval';
        $q->nchecks = 10;
        $q->tolerance = 0.001;
        $q->disallow = '';
        $q->allowedfuncs = '';
        $q->answerprefix = 'f(x) =';
        $q->answers = array(
            13 => new question_answer(13, '2*x', 1.0, 'Correct.', FORMAT_HTML),
            14 => new question_answer(14, 'x', 0.2, 'It seems that you forgot something.', FORMAT_HTML),
        );
        $q->variables = array(
            7 => new qtype_algebra_variable(7, 'x', -5, 5)
        );
        $q->qtype = question_bank::get_qtype('algebra');

        return $q;
    }

    /**
     * Gets the question data for a algebra question with one correct and
     * one partially correct answer.
     * @return stdClass
     */
    public function get_algebra_question_data_derive() {
        $qdata = new stdClass();
        test_question_maker::initialise_question_data($qdata);

        $qdata->qtype = 'algebra';
        $qdata->name = 'Algebra question';
        $qdata->questiontext = 'What is the derivative of the function \(f(x) = x^2\) ?';
        $qdata->generalfeedback = 'Generalfeedback: 2*x is the correct answer.';

        $qdata->options = new stdClass();
        $qdata->options->compareby = 'eval';
        $qdata->options->nchecks = 10;
        $qdata->options->tolerance = 0.001;
        $qdata->options->disallow = '';
        $qdata->options->allowedfuncs = '';
        $qdata->options->answerprefix = 'f(x) =';
        $qdata->options->answers = array(
            13 => new question_answer(13, '2*x', 1.0, 'Correct.', FORMAT_HTML),
            14 => new question_answer(14, 'x', 0.2, 'It seems that you forgot something.', FORMAT_HTML),
        );
        $qdata->options->variables = array(
            7 => new qtype_algebra_variable(7, 'x', -5, 5)
        );

        return $qdata;
    }
}
