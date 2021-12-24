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
 * Step definitions related to administration overrides for the iplus theme.
 *
 * @package    availability_coursecompleted
 * @copyright  2021 iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
// For that reason, we can't even rely on $CFG->admin being available here.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Site administration level steps definitions overrides for the iplus theme.
 *
 * @package    availability_coursecompleted
 * @copyright  2021 iplusacademy (www.iplusacademy.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_availability_coursecompleted extends behat_base {

    /**
     * Complete user in a course
     * @Then /^I mark course "(?P<course>[^"]*)" completed for user "(?P<user>[^"]*)"$/
     * @param string $course
     * @param string $user
     */
    public function i_mark_course_completed_for_user($course, $user) {
        $courseid = $this->get_course_id($course);
        $userid = $this->get_user_id($user);
        $ccompletion = new \completion_completion(['course' => $courseid, 'userid' => $userid]);
        $ccompletion->mark_complete(time());
        $task = new \core\task\completion_regular_task();
        ob_start();
        $task->execute();
        // Hopefully, some day MDL-33320 will be fixed and all these sleeps
        // and double cron calls in behat and unit tests will be removed.
        sleep(1);
        $task->execute();
        ob_end_clean();
    }

    /**
     * Fetch user ID from its username.
     *
     * @param string $username The username.
     * @return int The user ID.
     * @throws Exception
     */
    protected function get_user_id($username) {
        global $DB;
        if (!$userid = $DB->get_field('user', 'id', ['username' => $username])) {
            throw new Exception("A user with username '{$username}' does not exist");
        }
        return $userid;
    }
}
