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
 * Renderer for outputting parts of a question belonging to the
 * adaptive behaviour for multi-part questions.
 *
 * @package    qbehaviour_adaptivemultipart
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/behaviour/adaptive/renderer.php');


/**
 * Renderer for outputting parts of a question belonging to the
 * adaptive behaviour for multi-part questions.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_adaptivemultipart_renderer extends qbehaviour_renderer {

    public function controls(question_attempt $qa, question_display_options $options) {
        return $this->submit_button($qa, $options);
    }

    /**
     * Display the scoring information about an adaptive attempt.
     *
     * This class is mostly a copy-and-paste of some code that has been proposed to
     * go into core Moodle. See http://tracker.moodle.org/browse/MDL-34066. For now
     * it is copied here, so that STACK will work with the standard release of Moodle 2.3.
     *
     * @param qbehaviour_adaptivemultipart_mark_details contains all the score details we need.
     * @param question_display_options $options display options.
     */
    public function render_adaptive_marks(qbehaviour_adaptivemultipart_mark_details $details, question_display_options $options) {
        if ($details->state == question_state::$todo || $options->marks < question_display_options::MARK_AND_MAX) {
            // No grades yet.
            return '';
        }

        // Display the grading details from the last graded state.
        $mark = $details->get_formatted_marks($options->markdp);
        $class = $details->state->get_feedback_class();

        $gradingdetails = get_string('gradingdetails', 'qbehaviour_adaptive', $mark)
                . $this->penalty_info($details, $options);

        return html_writer::tag('div', $gradingdetails,
                        array('class' => 'gradingdetails'));
    }

    /**
     * Display the information about the penalty calculations.
     *
     * This class is mostly a copy-and-paste of some code that has been proposed to
     * go into core Moodle. See http://tracker.moodle.org/browse/MDL-34066. For now
     * it is copied here, so that STACK will work with the standard release of Moodle 2.3.
     *
     * @param qbehaviour_adaptivemultipart_mark_details contains all the score details we need.
     * @param question_display_options $options display options.
     */
    protected function penalty_info(qbehaviour_adaptivemultipart_mark_details $details, question_display_options $options) {

        if ($details->currentpenalty == 0 && $details->totalpenalty == 0) {
            return '';
        }

        $output = '';
        $mark = $details->get_formatted_marks($options->markdp);

        // Print details of grade adjustment due to penalties.
        if ($details->rawmark != $details->actualmark) {
            $output .= ' ' . get_string('gradingdetailsadjustment', 'qbehaviour_adaptive', $mark);
        }

        // Print information about any new penalty, only relevant if the answer can be improved.
        if ($details->improvable) {
            $output .= ' ' . get_string('gradingdetailspenalty', 'qbehaviour_adaptive',
                    format_float($details->currentpenalty, $options->markdp));

            // Print information about total penalties so far, if larger than current penalty.
            if ($details->totalpenalty > $details->currentpenalty) {
                $output .= ' ' . get_string('gradingdetailspenaltytotal', 'qbehaviour_adaptive',
                        format_float($details->totalpenalty, $options->markdp));
            }
        }

        return $output;
    }
}
