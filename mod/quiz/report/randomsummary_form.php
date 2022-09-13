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
 * This file defines the setting form for the quiz random summary report.
 *
 * @package   quiz_randomsummary
 * @copyright 2015 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');


/**
 * Quiz random summary report settings form.
 *
 * @copyright 2015 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_randomsummary_settings_form extends mod_quiz_attempts_report_form {

    /**
     * Allows the randomsummary report to add extra fields to the attempts area of the form.
     * @param MoodleQuickForm $mform
     */
    protected function other_attempt_fields(MoodleQuickForm $mform) {
    }

    /**
     * Allows the randomsummary report to add extra fields to the preferences area of the form,
     * @param MoodleQuickForm $mform
     */
    protected function other_preference_fields(MoodleQuickForm $mform) {
    }
}
