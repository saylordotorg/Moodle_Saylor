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
 * Base class for question-type specific hints
 *
 * @copyright  2011 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_poasquestion;
defined('MOODLE_INTERNAL') || die();

abstract class hint {

    /**
     *  Single instance hint allows exactly one hint for each question state.
     *  Example is next character or next lexem hint in preg question type.
     */
    const SINGLE_INSTANCE_HINT = 1;
    /**
     *  Choosen multiple instance hint allows several hint buttons, from which the user
     *  (either teacher or student, depending on behaviour) could choose one they want.
     *  Example is hint, that would show how you should place misplaced lexem in correct writing question type.
     *
     *  In the hint options for interactive mode (save_hint_options function) available choosen multiple instance
     *  hint keys should end on '_' character, behaviour will add all instances returned by available_specific_hints
     *  starting from the prefixes from save_hint_options.
     */
    const CHOOSEN_MULTIPLE_INSTANCE_HINT = 2;
    /**
     *  Sequential multuple instance hint allows several hints, that could be used only in sequence.
     *  Current moodle text hints are example of this ones since there are no way to allow students to choose between them.
     *
     *  Hintkeys for sequential multiple instance hints should be returned ending with '#' character. Behaviour will expand
     *  them appending number of instance.
     */
    const SEQENTIAL_MULTIPLE_INSTANCE_HINT = 3;

    /** @var object Question object, created this hint*/
    protected $question;

    /** @var string Hint key for this hint, useful for choosen miltiple instance hints*/
    protected $hintkey;

    /**
     * Returns one of hint type constants (single instance etc).
     */
    abstract public function hint_type();

    /**
     * Constructs hint object, remember question to use.
     */
    public function __construct($question, $hintkey) {
        $this->question = $question;
        $this->hintkey = $hintkey;
    }

    /**
     * Returns hint key, passed to the constructor.
     */
    public function hint_key() {
        return $this->hintkey;
    }

    /**
     * Returns hint description to show on the hint button etc.
     */
    abstract public function hint_description();

    /**
     * Is hint based on response or not?
     *
     * @return boolean true if response is used to calculate hint (and, possibly, penalty)
     */
    abstract public function hint_response_based();

    /**
     * Returns whether question and response allows for the hint to be done.
     */
    abstract public function hint_available($response = null);

    /**
     * Returns whether response is used to calculate penalty (cost) for the hint.
     */
    public function penalty_response_based() {
        return false;// Most hint have fixed penalty (cost).
    }

    /**
     * Returns penalty (cost) for using specific hint of given hint type (possibly for given response).
     *
     * Even if response is used to calculate penalty, hint object should still return an approximation
     * to show to the student if $response is null.
     */
    abstract public function penalty_for_specific_hint($response = null);

    /**
     * Question may decide to render buttons for some hints to place them in more appropriate place
     * near a controls or in specific feedback.
     *
     * Questions should render hint buttons when _nonresp_hintbtns and/or _resp_hintbtns behaviour
     * variable is set, depending on whether hint is response based.
     */
    public function button_rendered_by_question() {
        // By default, hint button should be rendered by behaviour.
        return false;
    }

    /**
     * Renders hint information for given response using question renderer.
     *
     * Response may be omitted for non-response based hints.
     * @param renderer question renderer which could be used to render things
     */
    abstract public function render_hint($renderer, \question_attempt $qa, \question_display_options $options, $response = null);
}