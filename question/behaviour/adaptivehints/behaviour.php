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
 * Question behaviour for question with hints in adaptive mode.
 *
 * Behaviour variables:
 * _try - number of submissions (inherited from adaptive)
 * _rawfraction - fraction for the step without penalties (inherited from adaptive)
 * _hashint - there was hint requested in the step
 * _resp_hintbtns, _nonresp_hintbtns - variables are set if buttons for response-based and non-response based hints should be rendered in the step
 * _render_<hintname> - true if hint with hintname should be rendered when rendering question next time
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

require_once($CFG->dirroot . '/question/behaviour/adaptive/behaviour.php');

class qbehaviour_adaptivehints extends qbehaviour_adaptive implements qtype_poasquestion\behaviour_with_hints {
    const IS_ARCHETYPAL = false;


    public function is_compatible_question(question_definition $question) {
        return ($question instanceof question_automatically_gradable) && ($question instanceof qtype_poasquestion\question_with_hints);
    }

    public function get_expected_data() {
        $expected = parent::get_expected_data();

        $step = $this->qa->get_last_step();
        if ($this->qa->get_state()->is_active()) {// Returning an array of hint buttons.
            foreach ($this->question->hints_available_for_student($step->get_qt_data()) as $hintkey) {
                $hintkey = $this->adjust_hintkey($hintkey);
                $expected[$hintkey.'btn'] = PARAM_BOOL;
            }
        }
        return $expected;
    }

    public function adjust_display_options(question_display_options $options) {
        parent::adjust_display_options($options);// There seems to nothing to be done until question_display_options will be passed to specific_feedback function of question renderer.
    }

    /**
     * Adjust hintkey, adding number for sequential multiple instance hints.
     *
     * Passed hintkey should ends with # character to be appended with number.
     */
    public function adjust_hintkey($hintkey) {
        if (substr($hintkey, -1) == '#') {
            $i = 0;
            while ($this->qa->get_last_behaviour_var('_render_' . $hintkey . $i) !== null) {
                $i++;
            }
            $hintkey = $hintkey . $i;
        }
        return $hintkey;
    }

    /**
     * Adjust hints array, replacing every hintkey that ends with # with a whole 
     * bunch of numbers up to max used in this attempt.
     */
    public function adjust_hints($hints) {
        $result = array();
        foreach ($hints as $hintkey) {
            if (substr($hintkey, -1) == '#') {
                $adjustedkey = $this->adjust_hintkey($hintkey);
                $maxnumber = substr($adjustedkey, strpos($adjustedkey, '#') + 1);
                for ($i = 0; $i <= $maxnumber; $i++) {
                    $key = $hintkey . $i;
                    $result[] = $key;
                }
            } else {
                $result[] = $hintkey;
            }
        }
        return $result;
    }

    // Summarise functions.
    public function summarise_action(question_attempt_step $step) {
        // Summarise hint action.
        foreach ($this->question->hints_available_for_student($step->get_qt_data()) as $hintkey) {
            $hintkey = $this->adjust_hintkey($hintkey);
            if ($step->has_behaviour_var($hintkey.'btn')) {
                return $this->summarise_hint($step, $hintkey);
            }
        }

        return parent::summarise_action($step);
    }

    public function summarise_hint(question_attempt_step $step, $hintkey) {
        $response = $step->get_qt_data();
        $hintobj = $this->question->hint_object($hintkey, $step->get_qt_data());
        $hintdescription = $hintobj->hint_description();
        $a = new stdClass();
        $a->hint = $hintdescription;
        $a->response = $this->question->summarise_response($response);
        $a->penalty = $hintobj->penalty_for_specific_hint($response);
        return get_string('hintused', 'qbehaviour_adaptivehints', $a);
    }

    // We should init first step to show non-response based hint buttons.
    public function init_first_step(question_attempt_step $step, $variant) {
        parent::init_first_step($step, $variant);
        $step->set_behaviour_var('_nonresp_hintbtns', true);
    }

    // Process functions.
    public function process_action(question_attempt_pending_step $pendingstep) {

        $result = null;
        // Process hint button press.
        foreach ($this->question->hints_available_for_student($pendingstep->get_qt_data()) as $hintkey) {
            $hintkey = $this->adjust_hintkey($hintkey);
            if ($pendingstep->has_behaviour_var($hintkey.'btn')) {
                $result = $this->process_hint($pendingstep, $hintkey);
            }
        }

        // Proces all actions.
        if ($result === null) {
            $result = parent::process_action($pendingstep);
        }

        // Compute variables to show question it should render it's hint buttons.
        if (!$this->qa->get_state()->is_finished()) {
            $pendingstep->set_behaviour_var('_nonresp_hintbtns', true);
            $response = $pendingstep->get_qt_data();
            if ($this->question->is_complete_response($response)) {
                $pendingstep->set_behaviour_var('_resp_hintbtns', true);
            }
        }

        return $result;
    }

    public function process_hint(question_attempt_pending_step $pendingstep, $hintkey) {
        $status = $this->process_save($pendingstep);
        $response = $pendingstep->get_qt_data();
        $hintobj = $this->question->hint_object($hintkey, $response);
        if (!$hintobj->hint_available($response)) {// Couldn't compute hint for such response.
            return question_attempt::DISCARD;
        }

        // Process data from last graded state (e.g. submit).
        $prevstep = $this->get_graded_step();
        if (!is_null($prevstep)) {// TODO - deal with situation where hint requested for response that is correct already.
            if ($prevstep->get_state() == question_state::$complete) {
                $pendingstep->set_state(question_state::$complete);
            } else {
                $pendingstep->set_state(question_state::$todo);
            }
            $pendingstep->set_behaviour_var('_rawfraction', $prevstep->get_behaviour_var('_rawfraction'));
        } else {// Hint requested before submitting anything.
            $pendingstep->set_fraction(0);
            $pendingstep->set_behaviour_var('_rawfraction', 0);
            $pendingstep->set_state(question_state::$todo);
        }

        // Set hint variables.
        $pendingstep->set_behaviour_var('_hashint',true);
        $prevtotal = $this->qa->get_last_behaviour_var('_totalpenalties', 0);
        $penalty = $hintobj->penalty_for_specific_hint($response);
        $pendingstep->set_behaviour_var('_penalty', $penalty);
        $newtotal = $prevtotal + $penalty;
        $pendingstep->set_behaviour_var('_totalpenalties', $newtotal);
        $pendingstep->set_behaviour_var('_render_'.$hintkey, true);
        // Copy previous _render_hintxxx variables if previous state is hint state and response is same.
        $prevhintstep = $this->qa->get_last_step();
        if ($prevhintstep->has_behaviour_var('_hashint') && $this->is_same_response($pendingstep)) {
            $prevhints = $this->adjust_hints($this->question->hints_available_for_student($pendingstep->get_qt_data()));
            foreach ($prevhints as $prevhintkey) {
                if ($prevhintstep->has_behaviour_var('_render_'.$prevhintkey)) {
                    $pendingstep->set_behaviour_var('_render_'.$prevhintkey, true);
                }
            }
        }


        $prevbest = $pendingstep->get_fraction();
        if (is_null($prevbest)) {
            $prevbest = 0;
        }
        // The fraction = rawfraction - totalpenalties (already collected).
        $pendingstep->set_fraction(max($prevbest, $this->adjusted_fraction($pendingstep->get_behaviour_var('_rawfraction'),  $newtotal)));

        $pendingstep->set_new_response_summary($this->question->summarise_response($response));

        return question_attempt::KEEP;
    }

    // Overload process_submit to recalculate fraction and add _totalpenalties.
    public function process_submit(question_attempt_pending_step $pendingstep) {

        // Must find out prevbest before parent function get in it's fraction.
        $prevbest = $pendingstep->get_fraction();
        if (is_null($prevbest)) {
            $prevbest = 0;
        }

        $status = parent::process_submit($pendingstep);

        $response = $pendingstep->get_qt_data();
        if ($this->question->is_gradable_response($response) && $status == question_attempt::KEEP) {// State was graded.
            $prevtotal = $this->qa->get_last_behaviour_var('_totalpenalties', 0);
            // The fraction = rawfraction - totalpenalties (already collected).
            $pendingstep->set_fraction(max($prevbest, $this->adjusted_fraction($pendingstep->get_behaviour_var('_rawfraction'), $prevtotal)));
            $pendingstep->set_behaviour_var('_totalpenalties', $prevtotal + $this->question->penalty);// For submit penalty is added after fraction is calculated.
            $pendingstep->set_behaviour_var('_penalty', $this->question->penalty);
        }
        return $status;
    }

    // Overload process_finish to recalculate fraction and add _totalpenalties.
    public function process_finish(question_attempt_pending_step $pendingstep) {

        // Must find out prevbest before parent function get in it's fraction.
        $prevbest = $this->qa->get_fraction();
        if (is_null($prevbest)) {
            $prevbest = 0;
        }

        $status = parent::process_finish($pendingstep);

        if ($pendingstep->get_state() != question_state::$gaveup) {// State was graded.
            $laststep = $this->qa->get_last_step();
            $total = $this->qa->get_last_behaviour_var('_totalpenalties', 0);
            if (!$laststep->has_behaviour_var('_try')) {// Submitting ( not previous grading) resulted in finishing, so need to apply penalty.
                $total += $this->question->penalty;
                $pendingstep->set_behaviour_var('_penalty', $this->question->penalty);
            }
            $pendingstep->set_behaviour_var('_totalpenalties', $total);
            // Must substract by one submission penalty less , to account for one lawful submission.
            $pendingstep->set_fraction(max($prevbest, $this->adjusted_fraction($pendingstep->get_behaviour_var('_rawfraction'), $total - $this->question->penalty)));
        }
        return question_attempt::KEEP;
    }

    // Overloading this to have easy 'no penalties' adaptive version.
    protected function adjusted_fraction($fraction, $penalty) {
        return $fraction - $penalty;
    }

    // Overload get_graded_step since hinting changes grade too, we need to use last one with grade.
    public function get_graded_step() {
        // Variable _totalpenalties is set only when grading, i.e. on hinting, finishing and submitting.
        $step = $this->qa->get_last_step_with_behaviour_var('_totalpenalties');
        if ($step->has_behaviour_var('_totalpenalties')) {
            return $step;
        } else {
            return null;
        }
    }
}