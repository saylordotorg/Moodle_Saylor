<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ExpectationException;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Dialog form steps.
 *
 * @package    local_openlms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_openlms extends behat_base {
    /**
     * Submits modal form dialog.
     *
     * @When /^I press dialog form button "(?P<element_string>(?:[^"]|\\")*)"$/
     * @param string $element Element we look for
     */
    public function i_press_dialog_form_button($element) {
        if (!$this->running_javascript()) {
            throw new Behat\Mink\Exception\ExpectationException('Can\'t take focus off from "' . $element . '" in non-js mode', $this->getSession());
        }

        $node = $this->get_node_in_container('button', $element, 'css_element', '.local_openlms-dialog_form');
        $this->ensure_node_is_visible($node);

        $node->click();
    }

    /**
     * Looks into definition of a term in a list and makes sure text is not there.
     *
     * @Given I run the :taskname task
     *
     * @param string $task
     */
    public function execute_scheduled_task(string $taskname) {
        global $CFG;

        $task = \core\task\manager::get_scheduled_task($taskname);

        if (!$task) {
            throw new DriverException('The "' . $taskname . '" scheduled task does not exist');
        }
        $taskname = get_class($task);

        $ch = new curl();
        $options = [
            'FOLLOWLOCATION' => true,
            'RETURNTRANSFER' => true,
            'SSL_VERIFYPEER' => false,
            'SSL_VERIFYHOST' => 0,
            'HEADER' => 0,
        ];

        $content = $ch->get("$CFG->wwwroot/local/openlms/tests/behat/task_runner.php",
            ['behat_task' => $taskname], $options);

        if (strpos($content, "Scheduled task '$taskname' completed") === false) {
            throw new ExpectationException("Scheduled task '$taskname' did not complete successfully, content : " . $content, $this->getSession());
        }
    }
}
