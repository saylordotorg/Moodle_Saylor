<?php
// This file is part of Stack - https://stack.maths.ed.ac.uk
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/fixtures/test_base.php');
require_once(__DIR__ . '/../stack/cas/parsingrules/202_decimal_places_validation.filter.php');

/**
 * Unit tests for {@link 202_decimal_places_validation}.
 * @group qtype_stack
 * @group qtype_stack_ast_filters
 */
class stack_parser_rule_202_test extends qtype_stack_testcase {

    public function filter(string $input, int $min = -1, int $max = -1): array {
        $ast = maxima_parser_utils::parse($input);
        $filter = new stack_ast_filter_202_decimal_places_validation();
        $filter->set_filter_parameters([
            'min' => $min,
            'max' => $max
        ]);
        $errs = array();
        $note = array();
        $security = new stack_cas_security();

        $ast = $filter->filter($ast, $errs, $note, $security);

        $hasinvalid = false;
        $findinvalid = function($node) use(&$hasinvalid) {
            if (isset($node->position['invalid']) && $node->position['invalid'] === true) {
                $hasinvalid = true;
                return false;
            }
            return true;
        };
        $ast->callbackRecurse($findinvalid, false);

        $r = [
            'output' => $ast->toString(['nosemicolon' => true]),
            'notes' => $note,
            'errors' => $errs,
            'valid' => !$hasinvalid
        ];
        return $r;
    }

    public function test_normal() {
        $test = '-0.001';
        $result = $this->filter($test, 1);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, 2, 2);
        $this->assertFalse($result['valid']);
        $result = $this->filter($test, 4);
        $this->assertFalse($result['valid']);
        $result = $this->filter($test, 1, 2);
        $this->assertFalse($result['valid']);

        $test = '+(0.233332)*10^3';
        $result = $this->filter($test, 1, 6);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, 6, 7);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, -1, 5);
        $this->assertFalse($result['valid']);

        $test = '0.000';
        $result = $this->filter($test, 3);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, 2);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, 1, 4);
        $this->assertTrue($result['valid']);
        $result = $this->filter($test, 2, 2);
        $this->assertFalse($result['valid']);

        $test = '120/4';
        $result = $this->filter($test, 1, 2);
        $this->assertFalse($result['valid']);
        $result = $this->filter($test, 1, 3);
        $this->assertFalse($result['valid']);

        $test = 'x';
        $result = $this->filter($test);
        $this->assertFalse($result['valid']);
    }
}
