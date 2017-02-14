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
 * Renderer for outputting parts of a question belonging to the legacy
 * adaptive behaviour with hinting.
 *
 * @copyright  2011 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/question/behaviour/adaptive/renderer.php');

class qbehaviour_adaptivehints_renderer extends qbehaviour_adaptive_renderer {

     public function button_cost($str, $penalty, $options) {
        return '  '.get_string($str, 'qbehaviour_adaptivehints', format_float($penalty, $options->markdp));
     }

    public function controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        $output = parent::controls($qa, $options);// Submit button.
        $penalty = $question->penalty;
        if ($penalty != 0) {
            $output .= $this->button_cost('withpossiblepenalty', $penalty, $options);
        }
        $output .= html_writer::empty_tag('br');

        // Render buttons that should be rendered by behaviour.
         foreach ($question->hints_available_for_student() as $hintkey) {

            $behaviour = $qa->get_behaviour();
            $hintkey = $behaviour->adjust_hintkey($hintkey);
            $hintobj = $question->hint_object($hintkey);

            if (!$hintobj->button_rendered_by_question()) {// Button(s) isn't rendered by the question, so behaviour must render it.

                // Check whether button should be rendered at all.
                $laststep = $qa->get_last_step();
                if ($hintobj->hint_response_based()) {
                    $showhintbtn = $laststep->has_behaviour_var('_resp_hintbtns');
                } else {
                    $showhintbtn = $laststep->has_behaviour_var('_nonresp_hintbtns');
                }
                // Hide hint button if such hint buttons should not shown at all or hint unavailable or hint already rendered.
                if (!$showhintbtn || !$hintobj->hint_available() || ($laststep->has_behaviour_var('_render_'.$hintkey) && $hintobj->hint_type() !== qtype_poasquestion\hint::SEQENTIAL_MULTIPLE_INSTANCE_HINT)) {
                    // Should not pass $response to hint_available, since response could be changed in adaptive.
                    continue;
                }

                $output .= $this->render_hint_button($qa, $options, $hintobj);
                $output .= html_writer::empty_tag('br');

            }
        }

        return $output;
    }

    /**
     * Renders hint button. Could be used by behaviour or question renderer to avoid code duplication while rendering it.
     * @param hintobj object an object of a child of qtype_specific_hint class
     */
    public function render_hint_button(question_attempt $qa, question_display_options $options, $hintobj) {
        $question = $qa->get_question();
        $hintkey = $hintobj->hint_key();

        // Render button.
        $attributes = array(
            'type' => 'submit',
            'id' => $qa->get_behaviour_field_name($hintkey.'btn'),
            'name' => $qa->get_behaviour_field_name($hintkey.'btn'),
            'value' => get_string('hintbtn', 'qbehaviour_adaptivehints', $hintobj->hint_description()),
            'class' => 'submit btn',
        );
        if ($options->readonly) {
            $attributes['disabled'] = 'disabled';
        }
        $output = html_writer::empty_tag('input', $attributes);

        // Cost message.
        if ($hintobj->penalty_response_based()) {// If penalty is response-based.
            // Try to get last response.
            $response = $qa->get_last_qt_data();
            if (empty($response)) {
                $response = null;
            }
            $penalty = $hintobj->penalty_for_specific_hint($response);
            if ($penalty != 0) {
                $output .= $this->button_cost('withpenaltyapprox', $penalty, $options);// Note that reported penalty is approximation since user could change response in adaptive.
            }
        } else {
            $penalty = $hintobj->penalty_for_specific_hint(null);
            if ($penalty != 0) {
                $output .= $this->button_cost('withpenalty', $penalty, $options);
            }
        }

        if (!$options->readonly) {
            $this->page->requires->js_init_call('M.core_question_engine.init_submit_button',
                array($attributes['id'], $qa->get_slot()));
         }

         return $output;
    }

    // Overload penalty_info to show actual penalty.
    protected function penalty_info(question_attempt $qa, $mark,
            question_display_options $options) {
        if (!$qa->get_question()->penalty && !$qa->get_last_behaviour_var('_hashint', false)) {// No penalty for the attempts and no hinting done.
            return '';
        }
        $output = '';

        // Print details of grade adjustment due to penalties.
        if ($mark->raw != $mark->cur) {
            $output .= ' ' . get_string('gradingdetailsadjustment', 'qbehaviour_adaptive', $mark);
        }

        // Print information about any new penalty, only relevant if the answer can be improved.
        if ($qa->get_behaviour()->is_state_improvable($qa->get_state())) {
            $output .= ' ' . get_string('gradingdetailspenalty', 'qbehaviour_adaptive',
                    format_float($qa->get_last_step()->get_behaviour_var('_penalty'), $options->markdp));
        }

        return $output;
    }
}
