<?php
// This file is part of the Accredible Certificate module for Moodle - http://moodle.org/
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
 * Add event handlers for the quiz
 *
 * @package    mod
 * @subpackage accredible
 * @category   event
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$observers = array(
    // Listen for finished quizes.
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'includefile' => '/mod/accredible/locallib.php',
        'callback' => 'accredible_quiz_submission_handler',
        'internal' => false
    ),
    // Course completed only runs with a cron job.
    // There's no other way to ensure course completion without the Moodle course completion cron job running.
     array(
        'eventname'   => '\core\event\course_completed',
        'includefile' => '/mod/accredible/locallib.php',
        'callback'    => 'accredible_course_completed_handler',
        'internal' => false
    )
);
