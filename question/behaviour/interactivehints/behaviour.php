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

/**
 * Question behaviour where the student can submit questions one at a
 * time for immediate feedback with qtype specific hints support.
 *
 * @package    qbehaviour
 * @subpackage interactivehints
 * @copyright  2013 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Question behaviour for question with hints in interactive mode.
 *
 * Behaviour variables:
 * _triesleft - a number of tries student can still do (inherited from interactive)
 * _hashint - there was hint requested in the step
 * _render_<hintname> - true if hint with hintname should be rendered when rendering question next time
 *
 * Behaviour controls:
 * submit - submit answer to grading (inherited from interactive)
 * tryagain - start another try (inherited from interactive)
 *
 * @copyright  2013 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/question/behaviour/interactive/behaviour.php');

class qbehaviour_interactivehints extends qbehaviour_interactive implements qtype_poasquestion\behaviour_with_hints {
    const IS_ARCHETYPAL = false;

    public function is_compatible_question(question_definition $question) {
        return (parent::is_compatible_question($question)) && ($question instanceof qtype_poasquestion\question_with_hints);
    }

    public function process_submit(question_attempt_pending_step $pendingstep) {
        $result = parent::process_submit($pendingstep);


        if ($pendingstep->get_state() == question_state::$todo) {//Hint was used
            $moodlehint = $this->question->get_hint(count($this->question->hints) -
                $pendingstep->get_behaviour_var('_triesleft'), $this->qa);
            $pendingstep->set_behaviour_var('_hashint',true);
            $hints = $this->adjust_hints($moodlehint->hintkeys());
            $hints = $this->expand_choosen_mi_hints($hints, $pendingstep);
            foreach ($hints as $hintkey) {
                $pendingstep->set_behaviour_var('_render_'.$hintkey, true);
            }
        }

        return $result;
    }

    /**
     * Adjust hintkey, adding number for sequential multiple instance hints.
     *
     * Passed hintkey should ends with # character to be appended with number.
     */
    public function adjust_hintkey($hintkey) {
        if (substr($hintkey, -1) == '#') {
            //$i = count($this->question->hints) + 1 - $this->qa->get_last_behaviour_var('_triesleft');//This is simplier, but not suitable for other sequential hints than moodle ones.
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
     * bunch of hint numbers for hints, that should be shown in this step.
     *
     * For this behaviour only current hint is shown for sequential hint.
     */
    public function adjust_hints($hints) {
        $result = array();
        foreach ($hints as $hintkey) {
            $result[] = $this->adjust_hintkey($hintkey);
        }
        return $result;
    }

    /**
     * Teacher can not possibly predict which instances of choosen multiple instances hints
     * will be in a student's response on each stage of interactive behaviour, so teacher could
     * only tell us a type of such hints to use - hintkey up to first '_'.
     *
     * This function will expand type of hints to all possible hintkeys of such hints.
     */
    public function expand_choosen_mi_hints($hints, $pendingstep) {

        $result = array();
        $response = $pendingstep->get_qt_data();
        $availablehints = $this->question->available_specific_hints($response);
        foreach ($hints as $hintkey) {
            if (substr($hintkey, -1) == '_') {//Choosen multiple instance hints.
                foreach($availablehints as $realhint) {
                    if (substr($realhint, 0, strlen($hintkey)) == $hintkey) {//The hint should be rendered.
                        $result[] = $realhint;
                    }
                }
            } else {
                $result[] = $hintkey;
            }
        }
        return $result;
    }
}