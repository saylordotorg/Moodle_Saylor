<?php
// This file is part of Poasquestion question type - https://code.google.com/p/oasychev-moodle-plugins/
//
// Poasquestion question type is free software: you can redistribute it and/or modify
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
 * This file contains hint definitions, that is used by different poas questions.
 *
 * Note: interfaces and classes there are intentionally left without qtype_poasquestion prefix as
 *  they are intended for more general Moodle use after hinting behaviours would be complete.
 *
 * @package    qtype_poasquestion
 * @subpackage hints
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_poasquestion;
defined('MOODLE_INTERNAL') || die();

/**
 * Class for compatibility with Moodle teacher-defined text and other hints
 *
 * @copyright  2013 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hintmoodle extends hint {

    /** @var int A number of hint in question, getted from hintkey*/
    protected $number;

    public function hint_type() {
        return hint::SEQENTIAL_MULTIPLE_INSTANCE_HINT;
    }

    /**
     * Constructs hint object, remember question to use.
     */
    public function __construct($question, $hintkey) {
        $this->question = $question;
        $this->hintkey = $hintkey;
        // Hintkey is like <hintname>#<number>
        $this->number = substr($hintkey, strpos($hintkey, '#') + 1);
    }

    public function hint_description() {
        $number = '';
        if (is_numeric($this->number)) {
            $number = get_string ('No', 'qtype_poasquestion', $this->number + 1);
        }
        return get_string('teachertext', 'qtype_poasquestion', $number);
    }

    public function hint_response_based() {
        return false;// Teacher-defined text hint has nothing to do with student's response.
    }

    public function hint_available($response = null) {
        return is_numeric($this->number) && $this->number < count($this->question->hints);
    }

    public function penalty_for_specific_hint($response = null) {
        return $this->question->penalty;
    }

    public function render_hint($renderer, \question_attempt $qa, \question_display_options $options, $response = null) {
        $hint = $this->question->hints[$this->number];
        $hint->adjust_display_options($options);// For the hints like question_hint_with_parts.
        return $this->question->format_hint($hint, $qa);
    }
}
