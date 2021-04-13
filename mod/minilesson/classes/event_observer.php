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
 * Event observer for minilesson plugin
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_minilesson;

defined('MOODLE_INTERNAL') || die();

use \mod_minilesson\constants;


/**
 * Event observer for mod_minilesson
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_observer{

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
       global $DB;
		//constants::M_TABLE should be deleted elsewhere
        $ret = $DB->delete_records(constants::M_ATTEMPTSTABLE,array('courseid'=>$event->objectid));
        $ret = $DB->delete_records_select(constants::M_QTABLE, "minilesson IN (SELECT id from {" . constants::M_TABLE . "} WHERE course = :course)",
                array('course'=>$event->objectid));
		return $ret;
	}
}
