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
 * Unit tests for the short answer question definition class.
 *
 * @package    qtype_algebra
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/algebra/tests/helper.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');


/**
 * Unit tests for exceptions of the algebra question parser.
 *
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_algebra_parser_exception_test extends advanced_testcase {
    /**
     * No close bracket.
     */
    public function test_parser_mismatched_brackets() {
        $this->expectException('parser_exception');
        $this->expectExceptionMessage('Mismatched brackets: Open bracket without a close bracket found');
        $p = new qtype_algebra_parser;
        $expr = $p->parse('sin(2x) + cos(');
    }

    /**
     * Wrong number of arguments for function.
     */
    public function test_parser_wrong_arguments_number() {
        $this->expectException('parser_exception');
        $this->expectExceptionMessage("Syntax Error: Operator '^' requires two arguments");
        $p = new qtype_algebra_parser;
        $expr = $p->parse('x^');
    }

    /**
     * Plus or minus in an invalid location.
     */
    public function test_parser_invalid_minus() {
        $this->expectException('parser_exception');
        $this->expectExceptionMessage('Found a + or - in an invalid location');
        $p = new qtype_algebra_parser;
        $expr = $p->parse('(-)');
    }

    /**
     * Operator missing one argument.
     */
    public function test_parser_wrong_arguments_number2() {
        $this->expectException('parser_exception');
        $this->expectExceptionMessage("Syntax Error: Operator '-' requires two arguments");
        $p = new qtype_algebra_parser;
        $expr = $p->parse('x-');
    }
}
