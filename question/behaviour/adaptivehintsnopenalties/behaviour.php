<?php
// This file is part of POAS question and related behaviours - https://code.google.com/p/oasychev-moodle-plugins/
//
// POAS question is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// POAS question is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


defined('MOODLE_INTERNAL') || die();


/**
 * Question behaviour for question with hints in adaptive mode (no penalties).
 *
 * Behaviour variables:
 * _try - number of submissions (inherited from adaptive)
 * _rawfraction - fraction for the step without penalties (inherited from adaptive)
 * _hashint - there was hint requested in the step
 * _<hintname>count - count of hint named <hintname>
 * _penalty - penalty added in this state (used for rendering and summarising mainly)
 * _totalpenalties - sum of all penalties already done
 *
 * Behaviour controls:
 * submit - submit answer to grading (inherited from adaptive)
 * <hintname>btn - buttons to get hint <hintname>
 *
 * @copyright  2011 Oleg Sychev Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/question/behaviour/adaptivehints/behaviour.php');

class qbehaviour_adaptivehintsnopenalties extends qbehaviour_adaptivehints {
    const IS_ARCHETYPAL = false;

    public function summarise_hint(question_attempt_step $step, $hintkey) {
        $response = $step->get_qt_data();
        $hintkey = $this->adjust_hintkey($hintkey);
        $hintobj = $this->question->hint_object($hintkey, $step->get_qt_data());
        $hintdescription = $hintobj->hint_description();
        $a = new stdClass();
        $a->hint = $hintdescription;
        $a->response = $this->question->summarise_response($response);
        return get_string('hintused', 'qbehaviour_adaptivehintsnopenalties', $a);
    }

    // Overloading this to have easy 'no penalties' adaptive version.
    protected function adjusted_fraction($fraction, $penalty) {
        return $fraction;
    }

}