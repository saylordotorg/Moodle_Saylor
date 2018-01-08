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
 * Unit tests for the algebra question type class.
 *
 * @package    qtype_algebra
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/algebra/questiontype.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');
require_once($CFG->dirroot . '/question/type/algebra/edit_algebra_form.php');

/**
 * Unit tests for the algebra question type class.
 *
 * @copyright  2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebra_test extends advanced_testcase {
    public static $includecoverage = array(
        'question/type/questiontypebase.php',
        'question/type/algebra/questiontype.php',
    );

    protected $qtype;

    protected function setUp() {
        $this->qtype = new qtype_algebra();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    protected function get_test_question_data() {
        return test_question_maker::get_question_data('algebra', 'simplemath');
    }

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'algebra');
    }

    public function test_can_analyse_responses() {
        $this->assertTrue($this->qtype->can_analyse_responses());
    }

    public function test_get_random_guess_score() {
        $q = test_question_maker::get_question_data('algebra');
        $this->assertEquals(0, $this->qtype->get_random_guess_score($q));
    }

    public function test_get_possible_responses() {
        $q = test_question_maker::get_question_data('algebra', 'derive');

        $this->assertEquals(array(
            $q->id => array(
                13 => new question_possible_response('2*x', 1),
                14 => new question_possible_response('x', 0.2),
                null => question_possible_response::no_response(),
                0 => new question_possible_response(get_string('didnotmatchanyanswer', 'question'), 0),
            ),
        ), $this->qtype->get_possible_responses($q));
    }

    public function test_question_saving_simplemath() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $questiondata = test_question_maker::get_question_data('algebra', 'simplemath');
        $formdata = test_question_maker::get_question_form_data('algebra', 'simplemath');

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());

        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_algebra_edit_form::mock_submit((array)$formdata);

        $form = qtype_algebra_test_helper::get_question_editing_form($cat, $questiondata);

        $this->assertTrue($form->is_validated());

        $fromform = $form->get_data();

        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions(array($returnedfromsave->id));
        $actualquestiondata = end($actualquestionsdata);

        foreach ($questiondata as $property => $value) {
            if (!in_array($property, array('id', 'version', 'timemodified', 'timecreated', 'options'))) {
                $this->assertAttributeEquals($value, $property, $actualquestiondata);
            }
        }

        foreach ($questiondata->options as $optionname => $value) {
            if (!in_array($optionname, array('answers', 'variables'))) {
                $this->assertAttributeEquals($value, $optionname, $actualquestiondata->options);
            }
        }

        foreach ($questiondata->options->answers as $answer) {
            $actualanswer = array_shift($actualquestiondata->options->answers);
            foreach ($answer as $ansproperty => $ansvalue) {
                // This question does not use 'answerformat', will ignore it.
                if (!in_array($ansproperty, array('id', 'question', 'answerformat'))) {
                    $this->assertAttributeEquals($ansvalue, $ansproperty, $actualanswer);
                }
            }
        }
    }

    public function test_question_saving_trims_answers() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $questiondata = test_question_maker::get_question_data('algebra', 'simplemath');
        $formdata = test_question_maker::get_question_form_data('algebra', 'simplemath');

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());

        $formdata->category = "{$cat->id},{$cat->contextid}";
        $formdata->answer[0] = '   7*x   ';
        qtype_algebra_edit_form::mock_submit((array)$formdata);

        $form = qtype_algebra_test_helper::get_question_editing_form($cat, $questiondata);

        $this->assertTrue($form->is_validated());

        $fromform = $form->get_data();

        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions(array($returnedfromsave->id));
        $actualquestiondata = end($actualquestionsdata);

        $firstsavedanswer = reset($questiondata->options->answers);
        $this->assertEquals('7*x', $firstsavedanswer->answer);
    }

    public function test_extra_question_fields() {
        $extraquestionfields = array('qtype_algebra_options', 'compareby', 'nchecks',
            'tolerance', 'allowedfuncs', 'disallow', 'answerprefix');
        $this->assertEquals($this->qtype->extra_question_fields(), $extraquestionfields);
    }
}
