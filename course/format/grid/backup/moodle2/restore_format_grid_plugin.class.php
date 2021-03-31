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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/grid/lib.php');

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one grid format course.
 */
class restore_format_grid_plugin extends restore_format_plugin {

    /** @var int */
    protected $originalnumsections = 0;

    /**
     * Checks if backup file was made on Moodle before 3.3 and we should respect the 'numsections'
     * and potential "orphaned" sections in the end of the course.
     *
     * @return bool Need to restore numsections.
     */
    protected function need_restore_numsections() {
        $data = $this->connectionpoint->get_data();
        return (isset($data['tags']['numsections']));
    }

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
        /* Since this method is executed before the restore we can do some pre-checks here.
           In case of merging backup into existing course find the current number of sections. */
        $target = $this->step->get_task()->get_target();
        if (($target == backup::TARGET_CURRENT_ADDING || $target == backup::TARGET_EXISTING_ADDING) &&
                $this->need_restore_numsections()) {
            global $DB;
            $maxsection = $DB->get_field_sql(
                'SELECT max(section) FROM {course_sections} WHERE course = ?',
                [$this->step->get_task()->get_courseid()]);
            $this->originalnumsections = (int)$maxsection;
        }

        $paths = array();

        // Add own format stuff.
        $elename = 'grid'; // This defines the postfix of 'process_*' below.
        /*
         * This is defines the nested tag within 'plugin_format_grid_course' to allow '/course/plugin_format_grid_course' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_grid_course' element within the 'course' element in the 'course.xml' file in the '/course'
     * folder of the zipped backup 'mbz' file.
     */
    public function process_grid($data) {
        global $DB;

        $data = (object) $data;

        /* We only process this information if the course we are restoring to
           has 'grid' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'grid') {
            return;
        }

        $data->courseid = $this->task->get_courseid();

        if (!$DB->insert_record('format_grid_summary', $data)) {
            throw new moodle_exception('invalidrecordid', 'format_grid', '',
            'Could not set summary status. Grid format database is not ready. An admin must visit the notifications section.');
        }

        if (!($course = $DB->get_record('course', array('id' => $data->courseid)))) {
            print_error('invalidcourseid', 'error');
        } // From /course/view.php.
        // No need to annotate anything here.
    }

    protected function after_execute_structure() {
    }

    /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_section_plugin_structure() {

        $paths = array();

        // Add own format stuff.
        $elename = 'gridsection'; // This defines the postfix of 'process_*' below.
        /* This is defines the nested tag within 'plugin_format_grid_section' to allow '/section/plugin_format_grid_section' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_grid_section' element within the 'section' element in the 'section.xml' file in the
     * '/sections/section_sectionid' folder of the zipped backup 'mbz' file.
     * Discovered that the files are contained in the course repository with the new section number, so we just need to alter to
     * the new value if any. * This was undertaken by performing a restore and using the url
     * 'http://localhost/moodle23/pluginfile.php/94/course/section/162/mc_fs.png' where I had an image called 'mc_fs.png' in
     * section 1 which was id 129 but now 162 as the debug code told me.  '94' is just the context id.  The url was originally
     * created in '_make_block_icon_topics' of lib.php of the format.
     * Still need courseid in the 'format_grid_icon' table as it is used in discovering what records to remove when deleting a
     * course.
     */
    public function process_gridsection($data) {
        global $DB;

        $data = (object) $data;

        /* Allow this to process even if not in the grid format so that our event observer on 'course_restored'
           can perform a clean up of restored grid image files after all the data is in place in the database
           for this to happen properly. */

        $data->courseid = $this->task->get_courseid();
        $data->sectionid = $this->task->get_sectionid();

        if (!empty($data->imagepath)) {
            $data->image = $data->imagepath;
            unset($data->imagepath);
        } else if (empty($data->image)) {
            $data->image = null;
        }

        if (!$DB->record_exists('format_grid_icon', array('courseid' => $data->courseid, 'sectionid' => $data->sectionid))) {
            if (!$DB->insert_record('format_grid_icon', $data, true)) {
                throw new moodle_exception('invalidrecordid', 'format_grid', '',
                'Could not insert icon. Grid format table format_grid_icon is not ready.'.
                '  An administrator must visit the notifications section.');
            }
        } else {
            $old = $DB->get_record('format_grid_icon', array('courseid' => $data->courseid, 'sectionid' => $data->sectionid));
            /* Always update missing icons during restore / import, noting merge into existing course currently doesn't restore
               the grid icons. */
            if (is_null($old->image)) {
                // Update the record to use this icon as we are restoring or importing and no icon exists already.
                $data->id = $old->id;
                if (!$DB->update_record('format_grid_icon', $data)) {
                    throw new moodle_exception('invalidrecordid', 'format_grid', '',
                    'Could not update icon. Grid format table format_grid_icon is not ready.'.
                    '  An administrator must visit the notifications section.');
                }
            }
        }

        // No need to annotate anything here.
    }

    /**
     * Executed after course restore is complete
     *
     * This method is only executed if course configuration was overridden
     */
    public function after_restore_course() {
        $backupinfo = $this->step->get_task()->get_info();
        if ($backupinfo->original_course_format !== 'grid') {
            // Backup from another course format.
            return;
        }

        global $DB;
        if (!$this->need_restore_numsections()) {
            /* Backup file does not contain 'numsections' so we need to set it from
               the number of sections we can determine the course has.  The 'default'
               might be wrong, so there could be an entry in the db already with this
               wrong value. */
            $courseid = $this->task->get_courseid();
            $courseformat = course_get_format($courseid);

            $maxsection = $DB->get_field_sql('SELECT max(section) FROM {course_sections} WHERE course = ?', [$courseid]);

            $courseformat->restore_numsections($maxsection);
            return;
        }

        $data = $this->connectionpoint->get_data();
        $numsections = (int)$data['tags']['numsections'];
        foreach ($backupinfo->sections as $key => $section) {
            /* For each section from the backup file check if it was restored and if was "orphaned" in the original
               course and mark it as hidden. This will leave all activities in it visible and available just as it was
               in the original course.
               Exception is when we restore with merging and the course already had a section with this section number,
               in this case we don't modify the visibility. */
            if ($this->step->get_task()->get_setting_value($key . '_included')) {
                $sectionnum = (int)$section->title;
                if ($sectionnum > $numsections && $sectionnum > $this->originalnumsections) {
                    $DB->execute("UPDATE {course_sections} SET visible = 0 WHERE course = ? AND section = ?",
                        [$this->step->get_task()->get_courseid(), $sectionnum]);
                }
            }
        }
    }
}
