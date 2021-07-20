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
 * Provides {@see backup_subcourse_activity_task} class.
 *
 * @package     mod_subcourse
 * @category    backup
 * @copyright   2013 David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/subcourse/backup/moodle2/backup_subcourse_stepslib.php');

/**
 * Provides settings and steps to perform a complete backup of the activity.
 *
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_subcourse_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_subcourse_activity_structure_step('subcourse_structure', 'subcourse.xml'));
    }

    /**
     * Code the transformations to perform in the activity in order to get transportable (encoded) links
     *
     * @param string $content User text content
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of subcourses.
        $search = "/(".$base."\/mod\/subcourse\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SUBCOURSEINDEX*$2@$', $content);

        // Link to subcourse by moduleid.
        $search = "/(".$base."\/mod\/subcourse\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SUBCOURSEVIEWBYID*$2@$', $content);

        return $content;
    }
}
