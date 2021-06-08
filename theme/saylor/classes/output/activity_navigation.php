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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/theme/saylor/classes/output/course_renderer.php");

class activity_navigation extends \core_course\output\activity_navigation {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output Renderer base.
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        global $DB, $PAGE;
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

        // Add completion toggle.
        $course = $PAGE->course;
        $completioninfo = new \completion_info($course);
        $cm = $PAGE->cm;
        $backto = $PAGE->url->out(false) . "#togglecompletionwrapper";

        $courserenderer = $PAGE->get_renderer('theme_saylor', 'core_course\core_course');

        $toggle = $courserenderer->course_section_cm_completion($course, $completioninfo, $cm, array(), $backto);
        $data->togglecompletion = $toggle;
 
        return $data;
    }
}