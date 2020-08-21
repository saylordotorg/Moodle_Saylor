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
 * Contains the helper class for the select missing words question type tests.
 *
 * @package    qtype_gapfill
 * @copyright  2013 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * utilities used by the other test classes
 *
 * @package    qtype_gapfill
 * @copyright  2018 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_test_helper extends question_test_helper {

    /**
     *  must be implemented or class made abstract
     *
     * @return string
     */
    public function get_test_questions() {
        return array('catmat');
    }

    /**
     * Slight improvements over original make_question class
     *
     * @param string $questiontext
     * @param array $poptions
     * @return qtype_gapfill
     */
    public static function make_question($questiontext = "The [cat] sat on the [mat]",
        $poptions=['noduplicates' => 0,
        'disableregex' => 0, 'delimitchars' => '[]' ]) {
        $type = 'gapfill';
        question_bank::load_question_definition_classes($type);
        $question = new qtype_gapfill_question();
        $question->questiontext = $questiontext;
        test_question_maker::initialise_a_question($question);

        $question->qtype = question_bank::get_qtype('gapfill');
        $answerwords = $question->qtype->get_gaps($poptions['delimitchars'], $questiontext);

        $question->places = array();

        $answers = array();
        $id = 1;
        foreach ($answerwords as $key => $answer) {
            $id++;
            $answers[$key] = (object) array(
                        'question' => '163',
                        'answer' => $answer,
                        'fraction' => '1',
                        'feedback' => 'Feedback text',
                        'feedbackformat' => '1',
                        'id' => $id,
            );
        }
        $options = (object) array(
                    'id' => '117',
                    'question' => '163',
                    'layout' => '0',
                    'answerdisplay' => 'dragdrop',
                    'delimitchars' => '[]',
                    'casesensitive' => false,
                    'noduplicates' => '1',
                    'disableregex' => $poptions['disableregex'],
                    'fixedgapsize' => '0',
                    'optionaftertext' => '',
                    'letterhints' => '1',
                    'correctfeedback' => 'Correct Feedback',
                    'correctfeedbackformat' => '0',
                    'partiallycorrectfeedback' => 'Partially Correct Feedback',
                    'partiallycorrectfeedbackformat' => '0',
                    'incorrectfeedback' => 'Incorrect Feedback',
                    'incorrectfeedbackformat' => '0',
                    'optionsaftertext' => false,
                    'singleuse' => false,
                    'answers' => $answers,
        );

        $questiondata = (object) array(
                    'id' => '2',
                    'idnumber' => '1',
                    'category' => '2',
                    'contextid' => '1',
                    'parent' => '0',
                    'name' => 'Generic Gapfill Question',
                    'questiontext' => $questiontext,
                    'questiontextformat' => '1',
                    'generalfeedback' => '',
                    'generalfeedbackformat' => '1',
                    'qtype' => 'gapfill',
                    'length' => '1',
                    'stamp' => 'tjh238.vledev.open.ac.uk+100708154547+JrHygi',
                    'version' => 'tjh238.vledev.open.ac.uk+100708154548+a3zh8v',
                    'hidden' => '0',
                    'timecreated' => '1278603947',
                    'timemodified' => '1278603947',
                    'createdby' => '3',
                    'modifiedby' => '3',
                    'defaultmark' => '1.0000000',
                    'penalty' => '0.3333333',
                    'maxmark' => '1.00000',
                    'options' => $options
        );

        $question = $question->qtype->make_question($questiondata);
        $question->gapstofill = count($answerwords);
        $question->disableregex = $poptions['disableregex'];
        $question->noduplicates = $poptions['noduplicates'];
        return $question;
    }


    /**
     * Gets the question data for a shortanswer question with just the correct
     * answer 'frog', and no other answer matching.
     * @return stdClass
     */
    public function get_gapfill_question_data_catmat() {
        $qdata = new stdClass();
        test_question_maker::initialise_question_data($qdata);

        $qdata->qtype = 'gapfill';
        $qdata->name = 'catmat';
        $qdata->questiontext = 'The [cat] sat on the [mat]';
        $qdata->generalfeedback = 'someanswer';

        $qdata->options = new stdClass();
        $qdata->options->casesensitive = false;

        return $qdata;
    }

}
