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


namespace qtype_poasquestion;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/type/questionbase.php');
/**
 * Interface for behaviours, supporting specific hints.
 *
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
