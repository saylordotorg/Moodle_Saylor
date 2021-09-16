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
 * Unit tests for the gapfill question type class.
 *
 * @package    qtype_gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/gapfill/questiontype.php');
require_once($CFG->dirroot . '/question/type/gapfill/tests/helper.php');


/**
 * Unit tests for the gapfill question type class.
 *
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questiontype_test extends advanced_testcase {

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected static $includecoverage = array(
        'question/type/questiontypebase.php',
        'question/type/gapfill/questiontype.php',
    );

    protected function setUp(): void {
        $this->qtype = new qtype_gapfill();
    }

    protected function tearDown(): void {
        $this->qtype = null;
    }
    /**
     * template for common example of question instance
     * @return \stdClass
     */
    protected function get_test_question_data() {
        global $USER;
        $q = new stdClass();
        $q->id = 0;
        $q->name = 'Gapfill Question';
        $q->category = 0;
        $q->contextid = 0;
        $q->parent = 0;
        $q->questiontext = 'The [cat] sat on the [mat]';
        $q->questiontextformat = FORMAT_HTML;
        $q->generalfeedback = 'General feedback.';
        $q->generalfeedbackformat = FORMAT_HTML;
        $q->defaultmark = 1;
        $q->penalty = 0.3333333;
        $q->length = 1;
        $q->stamp = make_unique_id_code();
        $q->version = make_unique_id_code();
        $q->hidden = 0;
        $q->timecreated = time();
        $q->timemodified = time();
        $q->createdby = $USER->id;
        $q->modifiedby = $USER->id;
        $q->options = new stdClass();
        test_question_maker::set_standard_combined_feedback_fields($q->options);
        $q->options->displayanswers = 0;
        $q->options->delimitchars = "[]";

        return $q;
    }


    public function test_save_question() {
        $this->resetAfterTest();
        global $DB;
        $syscontext = context_system::instance();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category([]);
        $fromform = test_question_maker::get_question_form_data('gapfill', 'catmat');
        $fromform->category = $category->id . ',' . $syscontext->id;

        $question = new stdClass();
        $question->category = $category->id;
        $question->qtype = 'gapfill';
        $question->createdby = 0;

        $this->qtype->save_question($question, $fromform);
        $this->assertEquals($DB->get_field('question', 'questiontext', ['id' => $question->id]), $question->questiontext);

        $this->qtype->save_question_options($fromform);
        $this->assertEquals($DB->get_field('question_gapfill', 'correctfeedback', ['question' => $question->id]),
         $fromform->correctfeedback['text']);

        $gaps = $this->qtype->get_gaps("[]", $question->questiontext);

        $answerfields = $this->qtype->get_answer_fields($gaps, $question);
        $this->assertIsArray($this->qtype->get_answer_fields($gaps, $question));
        $this->assertEquals('cat', $answerfields[0]['value']);
        $this->assertEquals('mat', $answerfields[1]['value']);
        $this->qtype->get_question_options($question);
    }
    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'gapfill');
    }

    public function test_can_analyse_responses() {
        $this->assertFalse($this->qtype->can_analyse_responses());
    }

    public function test_squestionid_column_name() {
        $this->assertEquals($this->qtype->questionid_column_name(), 'question');
    }

    public function test_extra_question_fields() {
        $extraquestionfields = array('question_gapfill', 'answerdisplay', 'delimitchars',
            'casesensitive', 'noduplicates', 'disableregex', 'fixedgapsize', 'optionsaftertext', 'letterhints', 'singleuse');
        $this->assertEquals($this->qtype->extra_question_fields(), $extraquestionfields);
    }
}
