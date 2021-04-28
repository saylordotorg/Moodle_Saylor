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
 * Availability cohort - Frontend
 *
 * @package     availability_cohort
 * @copyright   2018 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 *              based on code of availability_group 2014 The Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_cohort;

defined('MOODLE_INTERNAL') || die();

/**
 * Availability cohort - Frontend class
 *
 * @package     availability_cohort
 * @copyright   2018 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 *              based on code of availability_group 2014 The Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /** @var array Array of all cohorts */
    protected $allcohorts;

    /**
     * Get javascript strings.
     * @return array
     */
    protected function get_javascript_strings() {
        return array('anycohort');
    }

    /**
     * Function to initialize the params for the javascript array.
     *
     * @param \stdClass          $course
     * @param \cm_info|null      $cm
     * @param \section_info|null $section
     *
     * @return array
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        // Get course context.
        $context = \context_course::instance($course->id);
        // Get all cohorts.
        $allcohorts = $this->get_all_current_context_cohorts($context);

        // Change to JS array format and return.
        $jsarray = array();
        foreach ($allcohorts as $rec) {

            $jsarray[] = (object)array('id' => $rec->id, 'name' =>
                    format_string($rec->name, true));
        }

        return array($jsarray);
    }

    /**
     * Gets all available cohorts.
     *
     * @param \context $context The current context
     * @return array Array of all the cohort objects
     */
    protected function get_all_current_context_cohorts($context) {
        global $CFG;
        require_once($CFG->dirroot.'/cohort/lib.php');

        return cohort_get_available_cohorts($context, 0, 0, 0);
    }

    /**
     * Function to decide if the button to select the restriction will be presented.
     *
     * @param \stdClass          $course
     * @param \cm_info|null      $cm
     * @param \section_info|null $section
     *
     * @return bool
     */
    protected function allow_add($course, \cm_info $cm = null,
            \section_info $section = null) {

        // Only show this option if there are some cohorts.
        return count($this->get_all_current_context_cohorts(\context_course::instance($course->id))) > 0;
    }
}
