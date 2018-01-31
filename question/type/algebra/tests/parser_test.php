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
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/algebra/tests/helper.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');


/**
 * Unit tests for the algebra question parser.
 *
 * @copyright  2017 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_algebra_parser_test extends advanced_testcase {
    /**
     * Test base elements of the parser
     */
    public function test_parser_vars_functions() {
        $p = new qtype_algebra_parser;

        $expr = $p->parse('sin(2x) + cos(3y)');
        $this->assertEquals(array('x', 'y'), $expr->get_variables());
        $this->assertEquals(array('sin', 'cos'),  $expr->get_functions());
        $this->assertEquals('\sin \left( 2  x_{} \right) + \cos \left( 3  y_{} \right)', $expr->tex());
    }

    /**
     * Test how various multiplications are displayed using TeX
     */
    public function test_parser_multiply_display() {
        $p = new qtype_algebra_parser;

        $expr = $p->parse('sin(2x) + cos(3y)');
        $this->assertEquals('\sin \left( 2  x_{} \right) + \cos \left( 3  y_{} \right)', $expr->tex());
        $expr = $p->parse('sin(4 x) + cos(5 y)');
        $this->assertEquals('\sin \left( 4  x_{} \right) + \cos \left( 5  y_{} \right)', $expr->tex());
        $expr = $p->parse('sin(6*x) + cos(7*y)');
        $this->assertEquals('\sin \left( 6  x_{} \right) + \cos \left( 7  y_{} \right)', $expr->tex());
        $expr = $p->parse('3x y');
        $this->assertEquals('3  x_{} y_{}', $expr->tex());
        $expr = $p->parse('x*y*3');
        $this->assertEquals('x_{} y_{} \times 3 ', $expr->tex());
    }
}
