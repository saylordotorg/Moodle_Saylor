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
 * Grid Information
 *
 * @package    format_grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

/**
 * Provides the information to backup grid course format
 */
class backup_format_grid_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '/course/format', 'grid');

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('showsummary'));

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Set source to populate the data.
        $pluginwrapper->set_source_table('format_grid_summary', array('courseid' => backup::VAR_PARENTID));

        // Temporarily remove the generated images so that they are not in the backup.
        $this->delete_displayed_images();

        // Don't need to annotate ids nor files.
        return $plugin;
    }

    private function delete_displayed_images() {
        global $CFG, $DB;

        /* We only process this information if the course we are backing up is in the
          'grid' format (target format can change depending of restore options).
          Note: This appears to be a bit silly as this code is executed even if the
          course is not in the 'grid' format.
         */
        $courseid = $this->task->get_courseid();
        $format = $DB->get_field('course', 'format', array('id' => $courseid));
        if ($format != 'grid') {
            return;
        }

        require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.
        require_once($CFG->dirroot . '/course/format/grid/lib.php'); // For format_grid.

        $courseformat = course_get_format($courseid);
        \format_grid\toolbox::delete_displayed_images($courseformat);
    }

    /**
     * Returns the format information to attach to section element
     */
    protected function define_section_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'grid');

        // Create one standard named plugin element (the visible container).
        // The sectionid and courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('image', 'alttext'));

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Set source to populate the data.
        $pluginwrapper->set_source_table('format_grid_icon', array('sectionid' => backup::VAR_SECTIONID));

        // Don't need to annotate ids nor files.
        return $plugin;
    }

}
