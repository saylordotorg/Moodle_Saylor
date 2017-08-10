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
 * Contains functions called by core.
 *
 * @package    block_sayloroverview
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The timeline view.
 */
define('BLOCK_SAYLOROVERVIEW_TIMELINE_VIEW', 'timeline');

/**
 * The courses view.
 */
define('BLOCK_SAYLOROVERVIEW_COURSES_VIEW', 'courses');

/**
 * Returns the name of the user preferences as well as the details this plugin uses.
 *
 * @return array
 */
function block_sayloroverview_user_preferences() {
    $preferences = array();
    $preferences['block_sayloroverview_last_tab'] = array(
        'type' => PARAM_ALPHA,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_SAYLOROVERVIEW_TIMELINE_VIEW,
        'choices' => array(BLOCK_SAYLOROVERVIEW_TIMELINE_VIEW, BLOCK_SAYLOROVERVIEW_COURSES_VIEW)
    );

    return $preferences;
}

/**
 *
 *
 *
 */
function block_sayloroverview_get_accredible_cert($course) {
	global $CFG, $DB, $USER;
	require_once($CFG->dirroot . '/mod/accredible/locallib.php');

	// Check if accredible api key is set, return if not
    if(!isset($CFG->accredible_api_key)) {
        return null;
    }

	if($accredible_records = $DB->get_records('accredible', array('course'=> $course->id))) {

		$credentials = array();
		// For each accredible record (if there are multiple possible certificates per course) get a user's credentials
		foreach ($accredible_records as $record) {
			if (!isset($record->achievementid)) {
				$groupid = $record->groupid;
			}
			else {
				$groupid = $record->achievementid;
			}

			$returnedcredentials = accredible_get_credentials($groupid, $USER->email);

			foreach ($returnedcredentials as $credential) {
				// Add credentials to $credentials array
				$credentials[$credential->id] = $credential;
			}
		}

		// 
		if (count($credentials) > 0) {
			// Only return the latest issued credential
			foreach ($credentials as $credential) {
				if (!isset($newestcredential)){
					$newestcredential = $credential;
					continue;
				}

				$credentialissueddate = new DateTime($credential->issued_on);
				$newestcredentialissueddate = new DateTime($newestcredential->issued_on);

				if ($credentialissueddate->format('U') >= $newestcredentialissueddate->format('U')) {
					$newestcredential = $credential;
				}
			}
			return $newestcredential;
		}
		else {
			// We didn't find any credentials
			return null;
		}

	}
	else {
		return null;
	}

}

/**
* Returns reordered list of courses by last time accessed
*/
function block_sayloroverview_sort_courses_by_last_access($courses){
	global $DB, $USER;

	foreach ($courses as $id=>$course) {

		$lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('userid' => $USER->id, 'courseid' => $course->id));

		$courses[$id]->lastaccess = $lastaccess;
	}
	
	usort($courses, "block_sayloroverview_compare_last_access");

	return $courses;
}

function block_sayloroverview_compare_last_access($a, $b){
	return ($a->lastaccess > $b->lastaccess) ? -1 : 1;
}


