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
 * Custom behat steps
 *
 * @package   gradeexport_checklist
 * @copyright 2022 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom behat steps
 */
class behat_gradeexport_checklist extends behat_base {

    /**
     * Convert page names to URLS for steps like 'When I am on the "[identifier]" "[page type]" page.
     * @param string $type
     * @param string $identifier
     * @return moodle_url
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        global $DB;
        $courseid = $DB->get_field('course', 'id', ['shortname' => $identifier], MUST_EXIST);
        switch (strtolower($type)) {
            case 'export':
                return new moodle_url('/grade/export/checklist/index.php', ['id' => $courseid]);
            default:
                throw new Exception('Unrecognised checklist grade export page type "'.$type.'"');
        }
    }
}
