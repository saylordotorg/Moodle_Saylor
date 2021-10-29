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
 * Adaptive question behaviour for multi-part questions.
 *
 * @package   qbehaviour_adaptivemultipart
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/behaviour/adaptive/behaviour.php');


/**
 * Adaptive question behaviour for multi-part questions.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface question_automatically_gradable_with_multiple_parts
        extends question_automatically_gradable {

    /**
     * Grade those parts of the question that can be graded, and return the grades and penalties.
     * @param array $response the current response being processed. Response variable name => value.
     * @param array $lastgradedresponses array part name => $response array from the last
     *      time this part registered a try. If a particular part has not yet registered a
     *      try, then there will not be an entry in the array for it.
     * @param bool $finalsubmit set to true when the student click submit all and finish,
     *      since the question is ending, we make a final attempt to award the student as much
     *      credit as possible for what they did.
     * @return array part name => qbehaviour_adaptivemultipart_part_result. There should
     *      only be entries in this array for those parts of the question where this
     *      sumbission counts as a new try at that part.
     */
    public function grade_parts_that_can_be_graded(array $response, array $lastgradedresponses, $finalsubmit);

    /**
     * Get a list of all the parts of the question, and the weight they have within
     * the question.
     * @return array part identifier => weight. The sum of all the weights should be 1.
     */
    public function get_parts_and_weights();

    /**
     * Are two responses the same insofar as a certain part is concerned. This is
     * used so we do not penalise the same mistake twice.
     *
     * @param string $part a part indentifier. Whether the two responses are the same
     *      for the given part.
     * @param array $prevresponse the responses previously recorded for this question,
     *      as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     * @return bool whether the two sets of responses are the same for the given
     *      part.
     */
    public function is_same_response_for_part($part, array $prevresponse, array $newresponse);

    /**
     * @param array $response the current response being processed. Response variable name => value.
     * @return bool true if any part of the response is invalid.
     */
    public function is_any_part_invalid(array $response);
}


/**
 * Holds the result of grading a try at one part of an adaptive question.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_adaptivemultipart_part_result {

    /** @var string the name of the part this relates to. */
    public $partname;

    /** @var float the fraction for this response, before any penaly is applied. */
    public $rawfraction;

    /** @var float the additional penalty that this try incurs. */
    public $penalty;

    /** @var bool if any errors occurred during processing. */
    public $errors;

    public function __construct($partname, $rawfraction, $penalty, $errors = false) {
        $this->partname    = $partname;
        $this->rawfraction = $rawfraction;
        $this->penalty     = $penalty;
        $this->errors      = $errors;
    }
}


/**
 * Adaptive question behaviour for multi-part questions.
 *
 * This allows each part of the question to be graded as soon as the
 * corresponding inputs have been completed, and so counts the tries, and
 * does the penalty calculations for each part separately.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_adaptivemultipart extends qbehaviour_adaptive {
    const IS_ARCHETYPAL = false;

    protected $applypenalties;

    public function __construct(question_attempt $qa, $preferredbehaviour) {
        parent::__construct($qa, $preferredbehaviour);
        $this->applypenalties = 'adaptivenopenalty' !== $preferredbehaviour;
    }

    public function init_first_step(question_attempt_step $step, $variant) {
        $step->set_behaviour_var('_applypenalties', (int) $this->applypenalties);
        parent::init_first_step($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        if ($step->has_behaviour_var('_applypenalties')) {
            $this->applypenalties = (bool) $step->get_behaviour_var('_applypenalties');
        }
        parent::apply_attempt_state($step);
    }

    public function is_compatible_question(question_definition $question) {
        return $question instanceof question_automatically_gradable_with_multiple_parts;
    }

    public function adjust_display_options(question_display_options $options) {
        // Save some bits so we can put them back later.
        $save = clone($options);

        // Do the default thing.
        parent::adjust_display_options($options);

        // Restore original feedback options.
        $options->feedback        = $save->feedback;
        $options->correctness     = $save->correctness;
        $options->numpartscorrect = $save->numpartscorrect;
    }

    /**
     * Update some display options to take into account the state of a given part.
     * Since the display options are updated in-place, you probably want to call
     * this on a clone of the real display options.
     *
     * (Note, this method is not used by STACK. If you want to see an example of
     * its use, have a look at qtype_formulas.
     *
     * @param string $index the part index.
     * @param question_display_options some display options. Will be updated in place.
     */
    public function adjust_display_options_for_part($index, question_display_options $options) {
        $step = $this->qa->get_last_step_with_behaviour_var('_tries_' . $index);

        if (!$this->qa->get_state()->is_finished() && !$step->has_behaviour_var('_tries_' . $index)) {
            // There never was a try at this part and question is not finished
            // hide feedback.
            $options->feedback = question_display_options::HIDDEN;
            $options->numpartscorrect = question_display_options::HIDDEN;
            $options->correctness = question_display_options::HIDDEN;
        }
    }

    protected function is_same_response(question_attempt_step $pendingstep) {
        if (!parent::is_same_response($pendingstep)) {
            return false;
        }
        // If the question part of the response is exactly the same, then
        // we need to check if the previous action was a save, and this action
        // is a submit.
        if ($pendingstep->has_behaviour_var('submit') &&
                !$this->qa->get_last_step()->has_behaviour_var('submit')) {
            return false;
        }
        return true;
    }

    public function process_action(question_attempt_pending_step $pendingstep) {
        if ($pendingstep->has_behaviour_var('comment')) {
            return $this->process_comment($pendingstep);
        } else if ($pendingstep->has_behaviour_var('finish')) {
            return $this->process_finish($pendingstep);
        } else if ($pendingstep->has_behaviour_var('submit')) {
            return $this->process_submit($pendingstep);
        } else {
            return $this->process_save($pendingstep);
        }
    }

    protected function adjusted_fraction($fraction, $prevtries) {
        return $fraction - $this->question->penalty * $prevtries;
    }

    protected function process_parts_that_can_be_graded(question_attempt_pending_step $pendingstep, $finalsubmit) {

        // Get the response we are processing.
        if ($finalsubmit) {
            $laststep = $this->qa->get_last_step();
            $response = $laststep->get_qt_data();
        } else {
            $response = $pendingstep->get_qt_data();
        }

        // Get last graded response for each part.
        $lastgradedresponses = array();
        $currenttries = array();
        $currentpenalties = array();
        $currentfractions = array();
        $currentrawfractions = array();
        $prevseenresponse = array();

        $steps = $this->qa->get_reverse_step_iterator();
        if ($finalsubmit) {
            $steps->next();
        }

        foreach ($steps as $step) {
            foreach ($step->get_behaviour_data() as $name => $value) {
                if (!preg_match('~_tries_(.*)$~', $name, $matches)) {
                    continue;
                }

                $oldresponse = $step->get_qt_data();

                $partname = $matches[1];
                if (array_key_exists($partname, $currenttries)) {
                    // We already have a most recent try for this part, but now
                    // have an older response that was a try for this part, and
                    // we want to know if the current response is the same as this.
                    if ($this->question->is_same_response_for_part($partname, $oldresponse, $response)) {
                        $prevseenresponse[$partname] = true;
                    }

                    continue;
                }

                $lastgradedresponses[$partname] = $oldresponse;
                $currenttries[$partname] = $value;
                $currentpenalties[$partname] = $step->get_behaviour_var('_penalty_' . $partname);
                $currentfractions[$partname] = $step->get_behaviour_var('_fraction_' . $partname);
                $currentrawfractions[$partname] = $step->get_behaviour_var('_rawfraction_' . $partname);
            }
        }

        $partscores = $this->question->grade_parts_that_can_be_graded($response, $lastgradedresponses, $finalsubmit);

        foreach ($partscores as $partname => $partscore) {
            if ($partscore->errors) {
                $pendingstep->set_behaviour_var('_errors_' . $partname, 1);
                continue;
            }

            if (!array_key_exists($partname, $currentpenalties)) {
                $currenttries[$partname]     = 0;
                $currentpenalties[$partname] = 0;
                $currentfractions[$partname] = 0;
            }

            if (!empty($prevseenresponse[$partname])) {
                $partscore->penalty = 0;
            }

            $pendingstep->set_behaviour_var('_tries_' . $partname, $currenttries[$partname] + 1);
            if ($this->applypenalties) {
                $pendingstep->set_behaviour_var('_curpenalty_' . $partname, $partscore->penalty);
                $pendingstep->set_behaviour_var('_penalty_' . $partname,
                        min($currentpenalties[$partname] + $partscore->penalty, 1)); // Cap cumulative penalty at 1.

            } else {
                $pendingstep->set_behaviour_var('_penalty_' . $partname, 0);
            }
            $pendingstep->set_behaviour_var('_rawfraction_' . $partname, $partscore->rawfraction);
            $currentrawfractions[$partname] = $partscore->rawfraction;
            $currentfractions[$partname] = max($partscore->rawfraction - $currentpenalties[$partname],
                                                    $currentfractions[$partname]); // Current fraction never decreases.
            $pendingstep->set_behaviour_var('_fraction_' . $partname, $currentfractions[$partname]);
        }

        if (empty($currentfractions)) {
            $totalfraction = null;
            $overallstate = question_state::$gaveup;
        } else {
            $totalweight = 0;
            $totalfraction = 0;
            foreach ($this->question->get_parts_and_weights() as $index => $weight) {
                $totalweight += $weight;
                if (array_key_exists($index, $currentfractions)) {
                    $totalfraction += $weight * $currentfractions[$index];
                }
            }
            if ($totalweight > 0) {
                $totalfraction = $totalfraction / $totalweight;
            }

            $allright = true;
            $allwrong = true;
            foreach ($this->question->get_parts_and_weights() as $index => $weight) {
                if (array_key_exists($index, $currentrawfractions)) {
                    $partstate = question_state::graded_state_for_fraction($currentrawfractions[$index]);
                    if ($partstate != question_state::$gradedright) {
                        $allright = false;
                    }
                    if ($partstate != question_state::$gradedwrong) {
                        $allwrong = false;
                    }
                } else {
                    $allright = false;
                }
            }
            if ($allright) {
                $overallstate = question_state::$gradedright;
            } else if ($allwrong) {
                $overallstate = question_state::$gradedwrong;
            } else {
                $overallstate = question_state::$gradedpartial;
            }
        }

        return array($totalfraction, $overallstate);
    }

    public function process_submit(question_attempt_pending_step $pendingstep) {
        $status = $this->process_save($pendingstep);
        if ($status == question_attempt::DISCARD) {
            return question_attempt::DISCARD;
        }

        list($totalfraction, $overallstate) = $this->process_parts_that_can_be_graded($pendingstep, false);
        $pendingstep->set_fraction($totalfraction);

        $prevstep = $this->qa->get_last_step();
        if ($this->question->is_any_part_invalid($pendingstep->get_qt_data())) {
            $pendingstep->set_state(question_state::$invalid);
        } else if ($prevstep->get_state() == question_state::$complete) {
            $pendingstep->set_state(question_state::$complete);
        } else if ($overallstate == question_state::$gradedright) {
            $pendingstep->set_state(question_state::$complete);
        } else {
            $pendingstep->set_state(question_state::$todo);
        }
        $pendingstep->set_new_response_summary($this->question->summarise_response($pendingstep->get_qt_data()));

        return question_attempt::KEEP;
    }

    public function process_finish(question_attempt_pending_step $pendingstep) {
        if ($this->qa->get_state()->is_finished()) {
            return question_attempt::DISCARD;
        }

        list($totalfraction, $overallstate) = $this->process_parts_that_can_be_graded($pendingstep, true);

        $pendingstep->set_fraction($totalfraction);
        $pendingstep->set_state($overallstate);
        $pendingstep->set_new_response_summary($this->question->summarise_response(
                $this->qa->get_last_step()->get_qt_data()));
        return question_attempt::KEEP;
    }

    /**
     * Get the current mark details for a particular part.
     * @param string $index the part index.
     * @return qbehaviour_adaptivemultipart_mark_details the marks information.
     */
    public function get_part_mark_details($index) {
        $step = $this->qa->get_last_step_with_behaviour_var('_tries_' . $index);

        if (!$step->has_behaviour_var('_tries_' . $index)) {
            return new qbehaviour_adaptivemultipart_mark_details(question_state::$todo);
        }

        $weights = $this->question->get_parts_and_weights();
        $weight = 0;
        // Purely formative PRTs will not occur in the weights array.
        if (array_key_exists($index, $weights)) {
            $weight = $weights[$index];
        }

        $state = question_state::graded_state_for_fraction(
                        $step->get_behaviour_var('_rawfraction_' . $index));

        $details = new qbehaviour_adaptivemultipart_mark_details($state);

        $details->maxmark    = $weight * $this->qa->get_max_mark();
        $details->actualmark = $step->get_behaviour_var('_fraction_' . $index) * $details->maxmark;
        $details->rawmark    = $step->get_behaviour_var('_rawfraction_' . $index) * $details->maxmark;

        $details->currentpenalty = $step->get_behaviour_var('_curpenalty_' . $index) * $details->maxmark;
        $details->totalpenalty   = $step->get_behaviour_var('_penalty_' . $index) * $details->maxmark;

        $details->improvable = !$state->is_correct();

        return $details;
    }

    /**
     * Get the step where a particular part was last graded.
     * @param string $index the part index.
     * @return question_attempt_step the relevant step, or null if there is not one.
     */
    public function get_last_graded_response_step_for_part($index) {
        $stepsiterator = $this->qa->get_reverse_step_iterator();
        foreach ($stepsiterator as $step) {
            if ($step->has_behaviour_var('_tries_' . $index) || $step->has_behaviour_var('_errors_' . $index)) {
                break;
            }
        }

        if (!$step->has_behaviour_var('_tries_' . $index) && !$step->has_behaviour_var('_errors_' . $index)) {
            // This part has never been graded.
            return null;
        } else if ($step->get_qt_data()) {
            // This step has the data that was actually graded.
            return $step;
        } else {
            // This can happen when "Submit all and finish" is processed. The grading
            // is actually done the step after the results are submitted.
            $stepsiterator->next();
            return $stepsiterator->current();
        }
    }
}


/**
 * This class encapsulates all the information about the current state-of-play
 * scoring-wise. It is used to communicate between the beahviour and the renderer.
 *
 * This class is mostly a copy-and-paste of some code that has been proposed to
 * go into core Moodle. See http://tracker.moodle.org/browse/MDL-34066. For now
 * it is copied here, so that STACK will work with the standard release of Moodle 2.3.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_adaptivemultipart_mark_details {
    /** @var question_state the current state of the question. */
    public $state;

    /** @var float the maximum mark for this question. */
    public $maxmark;

    /** @var float the current mark for this question. */
    public $actualmark;

    /** @var float the raw mark for this question before penalties were applied. */
    public $rawmark;

    /** @var float the the amount of additional penalty this attempt attracted. */
    public $currentpenalty;

    /** @var float the total that will apply to future attempts. */
    public $totalpenalty;

    /** @var bool whether it is possible for this mark to be improved in future. */
    public $improvable;

    /**
     * Constructor.
     * @param question_state $state
     */
    public function __construct($state, $maxmark = null, $actualmark = null, $rawmark = null,
            $currentpenalty = null, $totalpenalty = null, $improvable = null) {
        $this->state          = $state;
        $this->maxmark        = $maxmark;
        $this->actualmark     = $actualmark;
        $this->rawmark        = $rawmark;
        $this->currentpenalty = $currentpenalty;
        $this->totalpenalty   = $totalpenalty;
        $this->improvable     = $improvable;
    }

    /**
     * Get the marks, formatted to a certain number of decimal places, in the
     * form required by calls like get_string('gradingdetails', 'qbehaviour_adaptive', $a).
     * @param int $markdp the number of decimal places required.
     * @return array ready to substitute into language strings.
     */
    public function get_formatted_marks($markdp) {
        return array(
            'max' => format_float($this->maxmark,    $markdp),
            'cur' => format_float($this->actualmark, $markdp),
            'raw' => format_float($this->rawmark,    $markdp),
        );
    }
}
