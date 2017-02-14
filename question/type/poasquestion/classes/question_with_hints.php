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
 * Question which could return some specific hints and want to use *hints behaviours should implement this.
 *
 * @copyright  2011 Sychev Oleg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface question_with_hints {

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
