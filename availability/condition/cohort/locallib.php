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
 * Availability cohort - Locallib file
 *
 * @package   availability_cohort
 * @copyright 2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Check if a user is a member of given array of cohorts.
 * @param int $userid
 * @param array $cohorts The ids of the cohorts.
 *
 * @return boolean
 */
function availability_cohort_is_member($userid, $cohorts) {
    global $DB;
    if (!empty($cohorts)) {
        // Create IN statement for cohorts.
        list($in, $params) = $DB->get_in_or_equal($cohorts);
        // Add param for userid.
        $params[] = $userid;
        // Return true if "userid = " . $userid . " AND cohortid IN " . $cohorts.
        return $DB->record_exists_select('cohort_members', "cohortid $in AND userid = ?", $params);
    } else {
        return false;
    }
}

/**
 * Get all the cohorts defined in given context with all parent contexts.
 *
 * The function does not check user capability to view/manage cohorts in the given context
 * assuming that it has been already verified.
 *
 * @param \context $currentcontext The current context
 *
 * @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
 */
function availability_cohort_cohort_get_cohorts($currentcontext) {
    global $DB;

    // Add all parent context ids.
    $contextids = $currentcontext->get_parent_context_ids();
    // Add current contextid.
    $contextids[] = $currentcontext->id;
    // Make all entries to integer values.
    $contextids = array_map('intval', $contextids);

    // Get all cohorts for the currentcontext with all parent contexts.
    $cohorts = $DB->get_records_list('cohort', 'contextid', $contextids);

    return array('cohorts' => $cohorts);
}
