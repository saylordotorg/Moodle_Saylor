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
 * JME Molecular Editor question definition class.
 *
 * @package    qtype_jme
 * @copyright  2012 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/question.php');


/**
 * Represents a jme question.
 *
 * @copyright  2012 Jean-Michel vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_jme_question extends qtype_shortanswer_question {
    // All comparisons in jme are case sensitive.
    public function compare_response_with_answer(array $response, question_answer $answer) {
        return self::compare_string_with_wildcard(
                $response['answer'], $answer->answer, false);
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW, 'jme' => PARAM_RAW, 'mol' => PARAM_RAW);
    }
}
