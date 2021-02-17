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
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\tasks;

defined('MOODLE_INTERNAL') || die();

use core\task\adhoc_task;
use block_quickmail\persistents\message;

class send_message_adhoc_task extends adhoc_task {

    /*
     * This tasks kicks off the sending of a specific message to all of it's recipients
     * Note: this will in turn kick off subsequent scheduled tasks for each individual recipient delivery
     *
     * Required custom data: message_id
     */
    public function execute() {
        $data = $this->get_custom_data();

        // Attempt to fetch the message.
        if ($message = message::find_or_null($data->message_id)) {
            // Send the message.
            $message->send();
        }
    }

}
