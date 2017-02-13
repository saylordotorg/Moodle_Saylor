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
global $CFG;
require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * A special class for compatibility with interactivehints behaviour
 *
 * @copyright  2013 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle_hint_adapter extends \question_hint_with_parts {
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
        return new moodle_hint_adapter($row->id, $row->hint, $row->hintformat,
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