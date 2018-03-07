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

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function local_affiliations_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER;

    $systemcontext = context_system::instance();
    $title = get_string('manageaffiliations', 'local_affiliations');
    // For now, requiring a session key in index.php. This means that users can only manage their affiliations through this link.
    $url = new moodle_url('/local/affiliations/index.php', array('sesskey'=>sesskey()));

    // Should be the same capability checks as editing your own profile in myprofile lib.
    if ((isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) && $iscurrentuser && has_capability('moodle/user:editownprofile', $systemcontext)) {
        $node = new core_user\output\myprofile\node('miscellaneous', 'affiliations', $title, null,
                $url);
        $tree->add_node($node);

        return true;
    }

    return false;
}

/**
 * Get the list of affiliates and info.
 *
 *
 * @return array of affiliates
 */

function local_affiliations_get_affiliates() {
    global $DB;

    // Get all the affiliates and load into an array.
    $affiliates = $DB->get_records('local_affiliations_affiliate');

    //foreach ($affiliates as $affiliate) {
    //    $data[] = array('id' => $affiliate->id,
    //                            'cohortid' => $affiliate->cohortid,
    //                            'fullname' => $affiliate->fullname,
    //                            'shortname' => $affiliate->shortname);
    //}

    return $affiliates;

}

/**
 * Check whether the supplied affiliate name and code are already in use.
 *
 * @param string $name
 * @param string $code
 *
 * @return bool
 */
function local_affiliations_check_current_affiliates($name, $code) {
    $pass = true;

    $affiliates = local_affiliations_get_affiliates();

    foreach ($affiliates as $affiliate) {
        if ($affiliate->fullname == $name) {
            $pass = false;
            break;
        }
        if ($affiliate->shortname == $code) {
            $pass = false;
            break;
        }
    }

    return $pass;
}

/**
 * Check whether the user is a member of the supplied affiliate's cohort.
 * 
 * @param int $userid
 * @param int $affiliateid
 *
 * @return bool
 */
function local_affiliations_is_member($userid, $affiliateid) {
    require_once(__DIR__ . '/../../cohort/lib.php');
    global $DB;
    $ismember = false;

    $affiliate = $DB->get_record('local_affiliations_affiliate', array('id' => $affiliateid), '*', MUST_EXIST);

    $ismember = cohort_is_member($affiliate->cohortid, $userid);

    return $ismember;
}

/**
 * Determine which affiliations the user has removed.
 * 
 * @param int $userid
 * @param array $submittedaffiliations
 *
 * @return array | null
 */
function local_affiliations_get_removed_affiliations($userid, $submittedaffiliations) {
    require_once(__DIR__ . '/../../cohort/lib.php');
    global $DB;

    $removedaffiliations = array();
    
    foreach ($submittedaffiliations as $affiliateid => $submittedaffiliation) {
        $ismember = local_affiliations_is_member($userid, $affiliateid);

        if ($ismember == true && $submittedaffiliation == false) {
            // User has removed this affiliation, add to array.
            $removedaffiliations[] = $affiliateid;
        }
    }

    return $removedaffiliations;
}

/**
 * Determine which affiliations the user has added.
 * 
 * @param int $userid
 * @param array $submittedaffiliations
 *
 * @return array | null
 */
function local_affiliations_get_added_affiliations($userid, $submittedaffiliations) {
    require_once(__DIR__ . '/../../cohort/lib.php');
    global $DB;

    $addedaffiliations = array();
    
    foreach ($submittedaffiliations as $affiliateid => $submittedaffiliation) {
        $ismember = local_affiliations_is_member($userid, $affiliateid);

        if ($ismember == false && $submittedaffiliation == true) {
            // User has removed this affiliation, add to array.
            $addedaffiliations[] = $affiliateid;
        }
    }

    return $addedaffiliations;
}

/**
 * Add a user to the cohort of the supplied affiliate.
 * 
 * @param int $userid
 * @param int $affiliateid
 *
 * @return void
 */
function local_affiliations_add_affiliation($userid, $affiliateid) {
    require_once(__DIR__ . '/../../cohort/lib.php');
    global $DB;

    $affiliate = $DB->get_record('local_affiliations_affiliate', array('id' => $affiliateid), '*', MUST_EXIST);

    
    cohort_add_member($affiliate->cohortid, $userid);
}

/**
 * Remove a user from the cohort of the supplied affiliate.
 * 
 * @param int $userid
 * @param int $affiliateid
 *
 * @return void
 */
function local_affiliations_remove_affiliation($userid, $affiliateid) {
    require_once(__DIR__ . '/../../cohort/lib.php');
    global $DB;

    $affiliate = $DB->get_record('local_affiliations_affiliate', array('id' => $affiliateid), '*', MUST_EXIST);

    
    cohort_remove_member($affiliate->cohortid, $userid);
}