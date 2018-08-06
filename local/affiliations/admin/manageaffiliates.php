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

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../../../cohort/lib.php');
require_once(__DIR__ . '/../lib.php');

$path = optional_param('path', '', PARAM_PATH);
$exclude = optional_param('exclude', '', PARAM_NOTAGS);
$includewarnings = optional_param('includewarnings', true, PARAM_BOOL);
$pageparams = array();
if ($path) {
    $pageparams['path'] = $path;
}
if ($exclude) {
    $pageparams['exclude'] = $exclude;
}
$pageparams['includewarnings'] = $includewarnings;

$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$addaffiliatesname = optional_param('addaffiliatesname', null, PARAM_RAW);
$addaffiliatescode = optional_param('addaffiliatescode', null, PARAM_RAW);
$deleteaffiliatesparam = optional_param('deleteaffiliatesstring', null, PARAM_RAW);

admin_externalpage_setup('local_affiliations_manageaffiliates', '', $pageparams);

global $DB, $USER;

$url = new moodle_url('/local/affiliations/admin/manageaffiliates.php');
$form = new local_affiliations\form\manageaffiliates($url);

if ($confirm == md5($USER->sesskey) &&  data_submitted()) {
    // After confirmation modal. Do actual cohort logic here.
    $success = false;
    $successmessage = '';
    // Modify affiliations logic.

	// Handle affiliate creation.
	if (!$addaffiliatesname == null && !$addaffiliatescode == null) {
		// Better check if there is already an affiliate with this name/code.
		$name = trim($addaffiliatesname);
		$code = trim(strtoupper($addaffiliatescode));

		if (local_affiliations_check_current_affiliates($name, $code) == true) {
			// Name and code are not currently in use by this plugin, add the affiliate.
			// Create the cohort
			$newaffiliatecohort = new stdClass;
			$newaffiliatecohort->name = "AFF-$code";
			$newaffiliatecohort->description = $name;
			$newaffiliatecohort->contextid = 1;

			$id = cohort_add_cohort($newaffiliatecohort);

			if ($id > 0) {
				// Success creating the cohort. Save the id.
				$newaffiliatecohort->id = $id;
			}
			else {
				// Error creating the cohort.
				debugging(get_string('addaffiliateserror', 'local_affiliations')."$id");
				redirect($url, get_string('addaffiliateserror', 'local_affiliations')."$id", 10);
			}

			// Cohort successfully created, add record.
			$record = new stdClass();
			$record->fullname         = $newaffiliatecohort->description;
			$record->cohortid         = $newaffiliatecohort->id;
			$record->shortname 		  = $newaffiliatecohort->name;
			if (!$DB->insert_record('local_affiliations_affiliate', $record, false)) {
				// There was an error creating the affiliate record.
				debugging(get_string('addaffiliateserror', 'local_affiliations'));
				redirect($url, get_string('addaffiliateserror', 'local_affiliations'), 10);
			}
			else {
				$successmessage .= "Successfully added affiliate: $record->fullname ($record->shortname)<br>";
			}

		}
		else {
			// Name or code already in use. Error.
			debugging(get_string('manageaffiliatesalreadyused', 'local_affiliations'));
			redirect($url, get_string('manageaffiliatesalreadyused', 'local_affiliations'), 10);
		}
	}
	if (!$deleteaffiliatesparam == null) {
		// Now delete the affiliates marked for deletion.
		// First, convert the query string param back to an array.
		parse_str($deleteaffiliatesparam, $output);

		foreach ($output as $deleteaffiliate) { 
			$id = $deleteaffiliate;
			// Get the affiliate record.
			$affiliate = $DB->get_record('local_affiliations_affiliate', array('id' => $id), '*', MUST_EXIST);

			$cohort = $DB->get_record('cohort', array('id' => $affiliate->cohortid), '*', MUST_EXIST);

			// Delete the cohort.
			cohort_delete_cohort($cohort);
			// Delete the affiliate record.
			if ($DB->delete_records('local_affiliations_affiliate', array('id' => $id))) {
				$successmessage .= "Successfully removed affiliate: $affiliate->fullname ($affiliate->shortname)<br>";
			}
			else {
				debugging("Could not delete affiliate $affiliate->fullname with id $id from the local_affiliations_affiliate table.");
			}
		}
	}

	redirect($url, $successmessage, 10);


    // if ($success == true) {
    //     // Success. Show success message.
    //     echo $OUTPUT->header(get_string('manageaffiliations', 'local_affiliations'));
    //     echo $OUTPUT->notification(get_string('manageaffiliationssuccess', 'local_affiliations'), 'notifysuccess');
    //     echo $OUTPUT->footer();
    //     exit;
    // } else {
    //     $error = $OUTPUT->notification(get_string('manageaffiliationserror', 'local_affiliations'));
    // }
}
if ($data = $form->get_data()) {
    // After form has been submitted
    $optionsyes = array('confirm'=>md5($USER->sesskey), 'sesskey'=>$USER->sesskey);

    $change = false;

	// Build confirmation modal text.
	$modaltext = '';
	// Deleting affiliates.
	if (isset($data->deleteaffiliates)) {
		$deleteaffiliates = array();

		$change = true;
		$modaltext .= get_string('currentaffiliatesdeleteconfirmation', 'local_affiliations')."<br><ol>";
		$affiliates = local_affiliations_get_affiliates();

		// Show list of affiliates to delete and create an array of affiliate ids to delete.
		foreach ($data->deleteaffiliates as $deleteaffiliateid => $deleteaffiliatebool) {
			// Add the affiliate id to the list of affiliates to delete.
			$deleteaffiliates[] = $deleteaffiliateid;
			foreach ($affiliates as $affiliate) {
				if ($deleteaffiliateid == $affiliate->id) {
					$modaltext .= "<li>".$affiliate->fullname."(".$affiliate->shortname.")</li>";
					break;
				}
			}
		}
		$modaltext .= "</ol><br>";
		// Add the affiliates to delete to the confirmation button.
		$optionsyes['deleteaffiliatesstring'] = http_build_query($deleteaffiliates);
	}
	// Adding affiliate.
	if (!$data->addaffiliatesname == null && !$data->addaffiliatescode == null) {
		$change = true;
		$modaltext .= get_string('addaffiliatesconfirmation', 'local_affiliations')."<br>";
		$modaltext .= $data->addaffiliatesname."<br>";
		$modaltext .= $data->addaffiliatescode."<br>";

		// Add the affiliate info to the confirmation button.
		$optionsyes['addaffiliatesname'] = $data->addaffiliatesname;
		$optionsyes['addaffiliatescode'] = $data->addaffiliatescode;
	}

	if ($change == true) {
	    $confirmurl = new moodle_url($url, $optionsyes);
	    $confirmbutton = new single_button($confirmurl, get_string('confirm'), 'post');
	    $returnurl = $url;

		echo $OUTPUT->header(get_config('local_affiliations', 'manageaffiliates'));
	    echo $OUTPUT->confirm($modaltext, $confirmbutton, $returnurl);
	    echo $OUTPUT->footer();
	    die;
	}
	else {
		// No changes detected in the form... reload the page.
		redirect($url, get_string('manageaffiliatesnochange', 'local_affiliations'), 10);
	}

    //TODO: Error if only one of the two add affiliates fields is null.
}
else {
    // Display the manageaffiliations form.
    echo $OUTPUT->header(get_config('local_affiliations', 'manageaffiliates'));
    if (isset($error)) {
        echo $error;
    }
    else {
        $form->display();
    }

    echo $OUTPUT->footer();
}   
