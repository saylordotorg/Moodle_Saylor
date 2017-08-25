<?php
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
 * local_abtesting
 *
 *
 * @package    local
 * @subpackage abtesting
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once($CFG->dirroot.'/user/profile/lib.php');

class abtesting_field {

	public $id;
	public $shortname = 'abtestinggroup';
	public $name = 'ABTestingGroup';
	public $datatype = 'text';
	public $description = '<p>The AB testing group ID for the user.</p>';
	public $descriptionformat = 1;
	public $categoryid = 0;
	public $sortorder = 1;
	public $required = 0;
	public $locked = 0;
	public $visible = PROFILE_VISIBLE_NONE;
	public $forceunique = 0;
	public $signup = 0;
	public $defaultdata = '';
	public $defaultdataformat = 0;
	public $param1 = 3;
	public $param2 = 3;
	public $param3 = 0;
	public $param4 = '';
	public $param5 = '';

}

function abtesting_create_field($field) {
	global $DB;

	$id = $DB->insert_record('user_info_field', $field);

	return $id;
}

function abtesting_create_data($data) {
	global $DB;

	$id = $DB->insert_record('user_info_data', $data);

	return $id;
}

function abtesting_field_exists() {
	global $DB;
	// Checks if the abtesting_field exits in the user_info_field table of the database.
	// Compares the shortname of the field.

	$field = new abtesting_field();

	if($DB->record_exists('user_info_field', array('shortname'=>$field->shortname))) {
		return true;
	}

	return false;
}

function abtesting_data_exists($userid, $fieldid) {
	global $DB;

	if($DB->record_exists('user_info_data', array('userid'=>$userid, 'fieldid'=>$fieldid))) {
		return true;
	}

	return false;
}

/*
 * get_user_timecreated
 *
 * Get the timecreated field for a specified user
 *
 * @param userid
 * @return unix timestamp
 */

function abtesting_get_user_timecreated($userid) {
	global $DB;

	$user = $DB->get_record('user', 'id', $userid);

	return $user->timecreated;
}

 /*
 * get_testinggroup
 *
 * Return the testing group for a specific user
 *
 * @param userid
 * @return int the three digit testinggroup
 */
function abtesting_get_testinggroup($userid) {
	global $DB;

	// Get the user's timecreated timestamp
	$user = $DB->get_record('user', array('id'=>$userid));
	$timecreated = $user->timecreated;

	// If the userid is 1, it's the guest user (timecreated is 0).
	// Set the testinggroup to 000
	if ($user->id == 1) {
		$testinggroup = 000;
	}
	// If the userid is 2, this is the base admin (timecreated is 0).
	// Set the testinggroup to 000
	else if ($user->id == 2) {
		$testinggroup = 000;
	}
	// If the timecreated is 0 for some reason, force the testinggroup to 000
	else if ($user->timecreated == 0) {
		$testinggroup = 000;
	}
	// Otherwise, this is a normal user with a timecreated field.
	// Set to the last three digits of the timestamp.
	else {
		$testinggroup = substr($user->timecreated, -3);
	}

	return $testinggroup;
}

/*
 * User creation handler. Assigns abtestinggroup to new users upon creation.
 *
 * @param core/event $event core user created event
 */
function abtesting_user_created_handler($event) {
	global $DB;

    // Get the user id
    $userid = $event->relateduserid;

    if (abtesting_field_exists()){
    	// Field already exists, get the id.
    	$field = new abtesting_field();
    	$record = $DB->get_record('user_info_field', array('shortname'=>$field->shortname), 'id');
    	$field->id = $record->id;       	
    }
    else {
    	// The profile field hasn't been set yet (has the initial task been run?)
    	// Throw an error so the task will be retired
    	throw new ddl_field_missing_exception($field->shortname, 'user_info_field');
    	
    }
    // Get the testinggroup for this user
    $testinggroup = abtesting_get_testinggroup($userid);

    $data = new stdClass();
    $data->userid = $userid;
    $data->fieldid = $field->id;
    $data->data = $testinggroup;
    $data->dataformat = 0;

    // Check if the record exists before creating
    if (!abtesting_data_exists($data->userid, $data->fieldid)) {
    	// Create the record
    	$dataid = abtesting_create_data($data);
    }

}