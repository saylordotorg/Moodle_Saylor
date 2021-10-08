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

namespace theme_saylor\output\core_course;

use core_course;

require_once($CFG->dirroot . "/theme/saylor/classes/output/course_renderer.php");

defined('MOODLE_INTERNAL') || die;

class activity_navigation extends \core_course\output\activity_navigation {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output Renderer base.
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        global $DB, $PAGE, $USER;
        $data = new \stdClass();
        if ($this->prevlink) {
            $data->prevlink = $this->prevlink->export_for_template($output);
        }

        if ($this->nextlink) {
            $data->nextlink = $this->nextlink->export_for_template($output);
        }

        if ($this->activitylist) {
            $data->activitylist = $this->activitylist->export_for_template($output);
        }

        // Add completion button.
        $cm = $PAGE->cm;//new cm_info($this, null, $PAGE->cm, null);
        $cmcompletion = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
        $activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);

        $activityinfo = new \core_course\output\activity_information($cm, $cmcompletion, $activitydates);

        $data = (object) array_merge_recursive((array) $data, (array) $activityinfo->export_for_template($output));

        return $data;
    }
}