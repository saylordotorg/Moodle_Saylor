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
 * This file defines the quiz random summary report class.
 *
 * @package   quiz_randomsummary
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/randomsummary/randomsummary_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/randomsummary/randomsummary_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/randomsummary/randomsummary_table.php');


/**
 * Quiz report subclass for the randomsummary report.
 *
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_randomsummary_report extends quiz_attempts_report {
    /**
     * Display the random summary form.
     * @param stdClass $quiz record from quiz table.
     * @param stdClass $cm course module.
     * @param stdClass $course course record.
     * @return prints string
     */
    public function display($quiz, $cm, $course) {
        global $DB, $OUTPUT;

        list($currentgroup, $students, $groupstudents, $allowed)
            = $this->init('randomsummary', 'quiz_randomsummary_settings_form', $quiz, $cm, $course);
        $options = new quiz_randomsummary_options('randomsummary', $quiz, $cm, $course);

        if ($fromform = $this->form->get_data()) {
            $options->process_settings_from_form($fromform);

        } else {
            $options->process_settings_from_params();
        }

        $this->form->set_data($options->get_initial_form_data());

        if ($options->attempts == self::ALL_WITH) {
            // This option is only available to users who can access all groups in
            // groups mode, so setting allowed to empty (which means all quiz attempts
            // are accessible, is not a security porblem.
            $allowed = array();
        }

        // Load the required questions.
        // First get all Random questions within this quiz.
        $idfield = $DB->sql_concat('q2.id', 'slot.slot');
        $questionsraw = $DB->get_records_sql("
            SELECT $idfield as id, q2.id as q2id, q.id as qid, slot.slot, q2.length, slot.maxmark, q2.name
              FROM {question} q
              JOIN {quiz_slots} slot ON slot.questionid = q.id
              JOIN {question} q2 on q.category = q2.category
             WHERE slot.quizid = ?
               AND q.length > 0
               AND q.qtype = 'random'
               AND q2.qtype <> 'random'
          ORDER BY slot.slot", array($quiz->id));
        $number = 1;
        $questions = array();
        foreach ($questionsraw as $question) {
            if (!isset($questions[$question->q2id])) {
                $questions[$question->q2id] = $question;
                $questions[$question->q2id]->slots = array();
            }
            $questions[$question->q2id]->slots[] = $question->slot;
            $questions[$question->q2id]->number = $number;
            $number += $questions[$question->q2id]->length;
        }

        // Prepare for downloading, if applicable.
        $courseshortname = format_string($course->shortname, true,
                array('context' => context_course::instance($course->id)));
        $table = new quiz_randomsummary_table($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudents, $students, $questions, $options->get_url());
        $filename = quiz_report_download_filename(get_string('randomsummaryfilename', 'quiz_randomsummary'),
                $courseshortname, $quiz->name);
        $table->is_downloading($options->download, $filename,
                $courseshortname . ' ' . format_string($quiz->name, true));
        if ($table->is_downloading()) {
            raise_memory_limit(MEMORY_EXTRA);
        }

        $this->course = $course; // Hack to make this available in process_actions.
        $this->process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $options->get_url());

        // Start output.
        if (!$table->is_downloading()) {
            // Only print headers if not asked to download data.
            $this->print_header_and_tabs($cm, $course, $quiz, $this->mode);
        }

        if (groups_get_activity_groupmode($cm)) {
            // Groups are being used, so output the group selector if we are not downloading.
            if (!$table->is_downloading()) {
                groups_print_activity_menu($cm, $options->get_url());
            }
        }

        // Print information on the number of existing attempts.
        if (!$table->is_downloading()) {
            // Do not print notices when downloading.
            if ($strattemptnum = quiz_num_attempt_summary($quiz, $cm, true, $currentgroup)) {
                echo '<div class="quizattemptcounts">' . $strattemptnum . '</div>';
            }
        }

        $hasquestions = quiz_has_questions($quiz->id);
        if (!$table->is_downloading()) {
            if (!$hasquestions) {
                echo quiz_no_questions_message($quiz, $cm, $this->context);
            } else if (!$students) {
                echo $OUTPUT->notification(get_string('nostudentsyet'));
            } else if ($currentgroup && !$groupstudents) {
                echo $OUTPUT->notification(get_string('nostudentsingroup'));
            }

            // Print the display options.
            $this->form->display();
        }

        $hasstudents = $students && (!$currentgroup || $groupstudents);
        if ($hasquestions && ($hasstudents || $options->attempts == self::ALL_WITH)) {
            // Construct the SQL.
            $fields = $DB->sql_concat('u.id', "'#'", 'COALESCE(quiza.attempt, 0)') .
                    ' AS uniqueid, ';

            list($fields, $from, $where, $params) = $table->base_sql($allowed);

            $table->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);

            // Test to see if there are any regraded attempts to be listed.
            $fields .= ", COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded";
            $table->set_sql($fields, $from, $where, $params);

            if (!$table->is_downloading()) {
                // Print information on the grading method.
                if ($strattempthighlight = quiz_report_highlighting_grading_method(
                        $quiz, $this->qmsubselect, $options->onlygraded)) {
                    echo '<div class="quizattemptcounts">' . $strattempthighlight . '</div>';
                }
            }

            // Define table columns.
            $columns = array();
            $headers = array();

            if (!$table->is_downloading() && $options->checkboxcolumn) {
                $columns[] = 'checkbox';
                $headers[] = null;
            }

            $this->add_user_columns($table, $columns, $headers);
            $this->add_state_column($columns, $headers);
            $this->add_time_columns($columns, $headers);

            $this->add_grade_columns($quiz, $options->usercanseegrades, $columns, $headers, false);

            foreach ($questions as $slot => $question) {
                // Ignore questions of zero length.
                $columns[] = 'qsgrade' . $slot;
                $header = get_string('qbrief', 'quiz', implode($question->slots, ', '));
                if (!$table->is_downloading()) {
                    $header .= '<br />';
                } else {
                    $header .= ' ';
                }
                $header .= '/' . $question->name;
                $headers[] = $header;
            }

            // Check to see if we need to add columns for the student responses.
            $responsecolumnconfig = get_config('quiz_randomsummary', 'showstudentresponse');
            if (!empty($responsecolumnconfig)) {
                $responsecolumns = array_filter(explode(',', $responsecolumnconfig));
                // Get the question names for these columns to display in the header.
                list($sql, $params) = $DB->get_in_or_equal($responsecolumns, SQL_PARAMS_NAMED);
                $params['quizid'] = $quiz->id;

                $responseqs = $DB->get_records_sql("
                    SELECT slot.slot, q.name
                      FROM {question} q
                      JOIN {quiz_slots} slot ON slot.questionid = q.id
                    WHERE slot.quizid = :quizid AND q.length > 0
                      AND slot.slot ".$sql." ORDER BY slot.slot", $params);

                foreach ($responseqs as $rq) {
                    $columns[] = 'qsresponse'.$rq->slot;
                    if (!empty($rq->name)) {
                        $headers[] = format_string($rq->name);
                    } else {
                        $headers[] = '';
                    }
                }
            }

            $this->set_up_table_columns($table, $columns, $headers, $this->get_base_url(), $options, false);
            $table->set_attribute('class', 'generaltable generalbox grades');

            $table->out($options->pagesize, true);
        }

        return true;
    }
}
