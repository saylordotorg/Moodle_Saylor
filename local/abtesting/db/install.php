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

function xmldb_local_abtesting_install() {
	global $CFG, $DB;   
    require_once($CFG->dirroot.'/local/abtesting/lib.php');

    // Create the profile field if it does not already exist
	$field = new abtesting_field();

    if (!abtesting_field_exists()) {
    	$field->id = abtesting_create_field($field);
    }
    else {
    	// Field already exists, get the id.
    	$record = $DB->get_record('user_info_field', array('shortname'=>$field->shortname), 'id');
    	$field->id = $record->id;

    }

    // Field exists; get users.
    $users = $DB->get_records('user');

    foreach ($users as $user) {
    	$testinggroup = abtesting_get_testinggroup($user->id);

	    $data = new stdClass();
	    $data->userid = $user->id;
	    $data->fieldid = $field->id;
	    $data->data = abtesting_get_testinggroup($user->id);
	    $data->dataformat = 0;

	    // Check if the record exists before creating
	    if (!abtesting_data_exists($data->userid, $data->fieldid)) {
	    	// Create the record
	    	$dataid = abtesting_create_data($data);
	    }
    }

    return true;
}
