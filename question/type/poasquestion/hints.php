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


defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Question which could return some specific hints and want to use *withhint behaviours should implement this
 *
 * @copyright  2011 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface question_with_qtype_specific_hints {

    /**
     * Returns an array of all available specific hint types, that can be used in both student- and teacher-planned behaviours.
     *
     * The values are hint type indentifiers (hintkeys), unique for the qtype.
     * For multiple instance sequential hints hintkeys should end with '#' character.
     * For multiple instance choosen hints a separate key for each instance should be returned. That may depend on $response.
     */
    public function available_specific_hints($response = null);

    /**
     * Returns an array of available specific hint types for use in student-choosed behaviours.
     *
     * Used from student-choosen behaviours like 
     */
    public function hints_available_for_student($response = null);

    /**
     * Hint object factory.
     *
     * Returns a hint object for given type, for multiple instance choosen hints response may be needed to generate correct object.
     */
    public function hint_object($hintkey, $response = null);
}

interface behaviour_with_hints {
    /**
     * Adjust hintkey, adding current number for sequential multiple instance hints.
     *
     * Passed hintkey should ends with # character to be appended with number.
     */
    public function adjust_hintkey($hintkey);

    /**
     * Adjust hints array, replacing every hintkey that ends with # with a whole 
     * bunch of hint numbers for hints, that should be shown in this step.
     */
    public function adjust_hints($hints);
}
//TODO - MOVED IN NAMESPACE, DELETE IN 2.9
/**
 * Base class for question-type specific hints
 *
 * @copyright  2011 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_specific_hint {

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
    abstract public function render_hint($renderer, question_attempt $qa, question_display_options $options, $response = null);
}
//TODO - MOVED IN NAMESPACE, DELETE IN 2.9
/**
 * Class for compatibility with Moodle teacher-defined text and other hints
 *
 * @copyright  2013 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poasquestion_hintmoodle extends qtype_specific_hint {

    /** @var int A number of hint in question, getted from hintkey*/
    protected $number;

    public function hint_type() {
        return qtype_specific_hint::SEQENTIAL_MULTIPLE_INSTANCE_HINT;
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

    public function render_hint($renderer, question_attempt $qa, question_display_options $options, $response = null) {
        $hint = $this->question->hints[$this->number];
        $hint->adjust_display_options($options);// For the hints like question_hint_with_parts.
        return $this->question->format_hint($hint, $qa);
    }
}
//TODO - MOVED IN NAMESPACE, DELETE IN 2.9
/**
 * A special class for compatibility with interactivehints behaviour
 *
 * @copyright  2013 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poasquestion_moodlehint_adapter extends question_hint_with_parts {
    /** @var boolean option to show the number of sub-parts of the question that were right. */
    public $shownumcorrect;

    /** @var boolean option to clear the parts of the question that were wrong on retry. */
    public $clearwrong;

    /** @var specific hint options (a number of specific hint keys, separated by line break character). */
    public $options;


    /**
     * Constructor.
     * @param int the hint id from the database.
     * @param string $hint The hint text
     * @param int the corresponding text FORMAT_... type.
     * @param bool $shownumcorrect whether the number of right parts should be shown.
     * @param bool $clearwrong whether the wrong parts should be reset.
     * @param string options a number of specific hint keys, separated by line break character.
     */
    public function __construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong, $options) {
        parent::__construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong);
        $this->options = $options;
    }

    /**
     * Create a basic hint from a row loaded from the question_hints table in the database.
     * @param object $row with $row->hint, ->shownumcorrect, ->clearwrong and ->options set.
     * @return qtype_poasquestion_moodlehint_adapter
     */
    public static function load_from_record($row) {
        return new qtype_poasquestion_moodlehint_adapter($row->id, $row->hint, $row->hintformat,
                $row->shownumcorrect, $row->clearwrong, $row->options);
    }

    /**
     * Returns an array of moodle hint keys for this hint.
     */
    public function hintkeys() {

        $hintkeys = explode("\n", $this->options);
        // $hintkeys[] = 'hintmoodle#';// Moodle hint always active. ???TODO - check if this is necessary.
        return $hintkeys;
    }
}