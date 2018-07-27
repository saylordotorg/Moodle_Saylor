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
 * Question behaviour for the old adaptive mode.
 *
 * @package    qbehaviour_adaptive_adapted_for_coderunner
 * @copyright  2016 Richard Lobb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/behaviour/adaptive/renderer.php');


class qbehaviour_adaptive_adapted_for_coderunner_renderer extends qbehaviour_adaptive_renderer
{
    public function controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        if (!empty($question->precheck)) {
            $buttons = $this->precheck_button($qa, $options) . "\n" . $this->submit_button($qa, $options);
        } else {
            $buttons = $this->submit_button($qa, $options);
        }
        return $buttons;
    }

    /**
     * Construct the HTML for the optional 'precheck' button, which triggers
     * a partial submit in which no penalties are imposed but only the
     * 'Use as example' test cases are run.
     * This code is identical to the 'submit_button' code in
     * qbehaviour_renderer::submit_button except for the id and name of the
     * button.
     */
    protected function precheck_button(question_attempt $qa, question_display_options $options) {
        if (!$qa->get_state()->is_active()) {
            return '';  // This happens when we are on the Quiz review page, after the attempt is submitted.
        }
        $attributes = array(
            'type' => 'submit',
            'id' => $qa->get_behaviour_field_name('precheck'),
            'name' => $qa->get_behaviour_field_name('precheck'),
            'value' => get_string('precheck', 'qbehaviour_adaptive_adapted_for_coderunner'),
            'class' => 'submit btn',
        );
        if ($options->readonly) {
            $attributes['disabled'] = 'disabled';
        }
        $output = html_writer::empty_tag('input', $attributes);
        if (!$options->readonly) {
            $this->page->requires->js_init_call('M.core_question_engine.init_submit_button',
                    array($attributes['id'], $qa->get_slot()));
        }
        return $output;
    }

    // Override superclass method to suppress feedback on prechecks.
    public function feedback(question_attempt $qa, question_display_options $options) {
        if ($qa->get_last_behaviour_var('_precheck', 0)) {
            return '';
        } else {
            return parent::feedback($qa, $options);
        }
    }
}
