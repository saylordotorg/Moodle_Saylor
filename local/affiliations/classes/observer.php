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
 * Affiliations
 *
 * This plugin is designed to work with Moodle 3.2+ and allows students to select 
 * which entities they would like to be affiliated with. The student will be placed
 * into the corresponding cohort.
 *
 * @package    local
 * @subpackage affiliations
 * @copyright  2018 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

class local_affiliations_observer {

    /**
     * Action when an affiliate is created in settings.
     *
     * @param \local_affiliations\event\affiliate_created $event
     * @return bool true if all ok 
     */
    public static function affiliate_created(\local_affiliations\event\affiliate_created $event) {
	}

    /**
     * Action when an affiliate is deleted in settings.
     *
     * @param \local_affiliations\event\affiliate_deleted $event
     * @return bool true if all ok 
     */
    public static function affiliate_deleted(\local_affiliations\event\affiliate_deleted $event) {
	}

    /**
     * Action when a student adds an affiliate from their list.
     *
     * @param \local_affiliations\event\affiliate_added $event
     * @return bool true if all ok 
     */
    public static function affiliate_added(\local_affiliations\event\affiliate_added $event) {
	}

    /**
     * Action when a student removes an affiliate from their list.
     *
     * @param \local_affiliations\event\affiliate_removed $event
     * @return bool true if all ok 
     */
    public static function affiliate_removed(\local_affiliations\event\affiliate_removed $event) {
	}
}
