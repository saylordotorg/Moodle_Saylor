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
 * Question type class for the jme question type.
 *
 * @package    qtype_jme
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/jme/question.php');
require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');

define('QTYPE_JME_APPLET_WIDTH', 360);
define('QTYPE_JME_APPLET_HEIGHT', 315);

/**
 * The jme question type.
 */
class qtype_jme extends qtype_shortanswer {
    public function extra_question_fields() {
        return array('qtype_jme_options', 'jmeoptions', 'width', 'height');
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        // For JME questions comparisons are always case sensitive.
        $questiondata->options->usecase = true;
        parent::initialise_question_instance($question, $questiondata);
    }

    /**
     * Provide export functionality for xml format
     * As answers field in question_jme contains answers ids we can't use typebase method.
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return string the data to append to the output buffer or false if error
     */
    public function export_to_xml( $question, qformat_xml $format, $extra=null ) {
        $extraquestionfields = $this->extra_question_fields();
        array_shift($extraquestionfields);
        $expout='';
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <{$field}>{$exportedvalue}</{$field}>\n";
        }
        // Write out all the answers.
        $expout .= $format->write_answers($question->options->answers);
        return $expout;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        global $CFG;

        if (!array_key_exists('@', $data)) {
            return false;
        }
        if (!array_key_exists('type', $data['@'])) {
            return false;
        }
        if ($data['@']['type'] == 'jme') {

            // Get common parts.
            $question = $format->import_headers($data);

            // Header parts particular to jme.
            $question->qtype = 'jme';
            $question->jmeoptions = $format->getpath($data, array('#', 'jmeoptions', 0, '#'), $CFG->qtype_jme_options);
            $question->width = $format->getpath($data, array('#', 'width', 0, '#'), QTYPE_JME_APPLET_WIDTH);
            $question->height = $format->getpath($data, array('#', 'height', 0, '#'), QTYPE_JME_APPLET_HEIGHT);

            // Run through the answers.
            $answers = $data['#']['answer'];
            $anscount = 0;
            foreach ($answers as $answer) {
                $ans = $format->import_answer( $answer );
                $question->answer[$anscount] = $ans->answer['text'];
                $question->fraction[$anscount] = $ans->fraction;
                $question->feedback[$anscount] = $ans->feedback;
                ++$anscount;
            }

            $format->import_hints($question, $data);

            return $question;
        }
        return false;
    }
}
