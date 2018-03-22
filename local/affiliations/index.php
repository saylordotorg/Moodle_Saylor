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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/affiliations/index.php');
$PAGE->set_title(format_string(get_string('manageaffiliations', 'local_affiliations')));
$PAGE->set_heading(format_string(get_string('manageaffiliations', 'local_affiliations')));

$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$removedaffiliationsparam = optional_param('removedaffiliationsparam', null, PARAM_RAW); //Passed array of affilations.
$addedaffiliationsparam = optional_param('addedaffiliationsparam', null, PARAM_RAW); //Passed array of affilations.

global $USER;

$systemcontext = context_system::instance();
$error = NULL;

if (isloggedin() && !isguestuser($USER) && !is_mnet_remote_user($USER) && confirm_sesskey()) {
    require_capability('moodle/user:editownprofile', $systemcontext);
    $url = new moodle_url('/local/affiliations/index.php');
    $form = new local_affiliations\form\manageaffiliations($url);

    if ($confirm == md5($USER->sesskey) && data_submitted()) {
        // After confirmation modal. Do actual cohort logic here.
        $success = true;
        // Modify affiliations logic.

        if (isset($removedaffiliationsparam)) {
            parse_str($removedaffiliationsparam, $removedaffiliations);

            try {
                // Remove selected affiliations.
                foreach ($removedaffiliations as $removedaffiliationid) {
                    local_affiliations_remove_affiliation($USER->id, $removedaffiliationid);
                }
            }
            catch (ClientException $e) {
                $success = false;
                $error .= $OUTPUT->notification($e);
            }
        }
        if (isset($addedaffiliationsparam)) {
            parse_str($addedaffiliationsparam, $addedaffiliations);

            try {
                // Add selected affiliations.
                foreach ($addedaffiliations as $addedaffiliationid) {
                    // Add the user to the specified affiliate's cohort.
                    local_affiliations_add_affiliation($USER->id, $addedaffiliationid);
                }
            }
            catch (ClientException $e) {
                $success = false;
                $error .= $OUTPUT->notification($e);
            }
        }

        if ($success == true) {
            // Success. Show success message and redirect back to the profile.
            redirect('/user/profile.php?id='.$USER->id, get_string('manageaffiliationssuccess', 'local_affiliations'), 'success');
        } else {
            $error .= $OUTPUT->notification(get_string('manageaffiliationserror', 'local_affiliations'));
        }
    }

    if ($form->is_cancelled()){
        // Form has been cancelled, redirect back to the profile.
        redirect('/user/profile.php?id='.$USER->id, '');
    }

    if ($data = $form->get_data()) {
        $submittedaffiliations = $data->submittedaffiliations;

        // After form has been submitted
        $optionsyes = array('confirm'=>md5($USER->sesskey), 'sesskey'=>$USER->sesskey);
        $profileurl = new moodle_url('/user/profile.php', array('id'=>$USER->id));
        $returnurl = new moodle_url($url, array('sesskey'=>$USER->sesskey));
        $modaltext = get_config('local_affiliations', 'confirmationtext') . "<br>";

        $addedaffiliations = local_affiliations_get_added_affiliations($USER->id, $submittedaffiliations);
        $removedaffiliations = local_affiliations_get_removed_affiliations($USER->id, $submittedaffiliations);
        $affiliates = local_affiliations_get_affiliates();

        // If nothing has changed, redirect back to the page.
        if (empty($addedaffiliations) && empty($removedaffiliations)) {
            redirect($returnurl, get_string('manageaffiliationsnochange', 'local_affiliations'), 10);
        }

        if (!empty($addedaffiliations)) {
            $addedaffiliationsparam = http_build_query($addedaffiliations);

            // Add the added affiliations to the confirmation modal.
            $modaltext .= get_string('manageaffiliationsaddaffiliations', 'local_affiliations')."<br><ol>";
            foreach ($addedaffiliations as $addedaffiliationid) {
                foreach ($affiliates as $affiliate) {
                    if ($affiliate->id == $addedaffiliationid) {
                        $modaltext .= "<li>".$affiliate->fullname."</li>";
                        break;
                    }
                }
            }
            $modaltext .= "</ol><br>";

            // Add the added affiliations to the button to send back to the page.
            $optionsyes['addedaffiliationsparam'] = $addedaffiliationsparam;
        }
        if (!empty($removedaffiliations)) {
            $removedaffiliationsparam = http_build_query($removedaffiliations);

            // Add the removed affiliations to the confirmation modal.
            $modaltext .= get_string('manageaffiliationsremoveaffiliations', 'local_affiliations')."<br><ol>";
            foreach ($removedaffiliations as $removedaffiliationid) {
                foreach ($affiliates as $affiliate) {
                    if ($affiliate->id == $removedaffiliationid) {
                        $modaltext .= "<li>".$affiliate->fullname."</li>";
                        break;
                    }
                }
            }
            $modaltext .= "</ol><br>";

            // Add the removed affiliations to the button to send back to the page.
            $optionsyes['removedaffiliationsparam'] = $removedaffiliationsparam;
        }
        $confirmurl = new moodle_url($url, $optionsyes);
        $confirmbutton = new single_button($confirmurl, get_string('confirm'), 'post');

        echo $OUTPUT->header();
        echo $OUTPUT->confirm($modaltext, $confirmbutton, $returnurl);
        echo $OUTPUT->footer();
        die;
    }
    else {
        // Display the manageaffiliations form.
        echo $OUTPUT->header(get_config('local_affiliations', 'addaffiliationsdescription'));
        if (isset($error)) {
            echo $error;
        }
        else {
            $form->display();
        }
    }   

}
else {
    echo $OUTPUT->notification(get_string('manageaffiliationserror', 'local_affiliations'));
}


echo $OUTPUT->footer();
