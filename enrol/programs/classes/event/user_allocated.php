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

namespace enrol_programs\event;

/**
 * User allocated event.
 *
 * @package    enrol_programs
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class user_allocated extends \core\event\base {
    /**
     * Helper for event creation.
     *
     * @param \stdClass $allocation
     * @param \stdClass $program
     *
     * @return user_allocated|static
     */
    public static function create_from_allocation(\stdClass $allocation, \stdClass $program) {
        $context = \context::instance_by_id($program->contextid);
        $data = array(
            'context' => $context,
            'objectid' => $allocation->id,
            'relateduserid' => $allocation->userid,
            'other' => ['programid' => $program->id]
        );
        /** @var static $event */
        $event = self::create($data);
        $event->add_record_snapshot('enrol_programs_allocations', $allocation);
        $event->add_record_snapshot('enrol_programs_programs', $program);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->relateduserid' was allocated to program with id '$this->objectid'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_user_allocated', 'enrol_program');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/enrol/programs/management/user_allocation.php', ['id' => $this->objectid]);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol_programs_allocations';
    }
}
