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
 * Grid format.
 *
 * @package   format_grid
 * @copyright &copy; 2022-onwards G J Barnard.
 * @author    G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot."/lib/form/filemanager.php");

class MoodleQuickForm_sectionfilemanager extends MoodleQuickForm_filemanager implements templatable {

    private static $options = array(
        'maxfiles' => 1,
        'subdirs' => 0,
        'accepted_types' => array('gif', 'jpe', 'jpeg', 'jpg', 'png', 'webp'),
        'return_types' => FILE_INTERNAL
    );

    /**
     * Constructor
     *
     * @param string $elementName (optional) name of the filemanager
     * @param string $elementLabel (optional) filemanager label
     * @param array $attributes (optional) Either a typical HTML attribute string
     *              or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes, self::$options);
    }

    /**
     * Check that all files have the allowed type.
     *
     * @param int $value Draft item id with the uploaded files.
     * @return string|null Validation error message or null.
     */
    public function validateSubmitValue($value) {
        $failure = parent::validateSubmitValue($value);
        if (!$failure) {
            $course = $this->getAttribute('course');
            $coursecontext = context_course::instance($course->id);
            $sectionid = $this->getAttribute('sectionid');

            // Only allow this code to be executed once at the same time for the given section id (the id is unique).
            $lock = true;
            if (!defined('BEHAT_SITE_RUNNING')) {
                $lockfactory = \core\lock\lock_config::get_lock_factory('format_grid');
                $lock = $lockfactory->get_lock('sectionid'.$sectionid, 5);
            }
            if ($lock) {
                $fs = get_file_storage();
                $indata = new stdClass();
                $indata->sectionimage_filemanager = $value;
                // The file manager deals with the files table when the image is deleted.
                $outdata = file_postupdate_standard_filemanager($indata, 'sectionimage', self::$options, $coursecontext, 'format_grid', 'sectionimage', $sectionid);
                global $DB;
                if ($outdata->sectionimage == '1') {
                    // We have file(s).
                    $format = course_get_format($course);
                    $files = $fs->get_area_files($coursecontext->id, 'format_grid', 'sectionimage', $sectionid);
                    foreach ($files as $file) {
                        if (!$file->is_directory()) {
                            $filename = $file->get_filename();
                            $contenthash = $file->get_contenthash();
                            $sectionimage = $DB->get_record_select(
                                'format_grid_image',
                                'courseid = ? AND sectionid = ? AND '.$DB->sql_compare_text('image') . ' = ?',
                                array($course->id, $sectionid, $filename)
                            );
                            if ($sectionimage) {
                                if (($contenthash !== $sectionimage->contenthash) || ($filename !== $sectionimage->image)) {
                                    // Change of image.
                                    $conditionsarray = array('courseid' => $course->id, 'sectionid' => $sectionid, 'image' => $filename);
                                    $DB->set_field('format_grid_image', 'contenthash', $contenthash, $conditionsarray);
                                } // Else image not changed.
                            } else {
                                // New image.
                                $sectionimage = new \stdClass();
                                $sectionimage->sectionid = $sectionid;
                                $sectionimage->courseid = $course->id;
                                $sectionimage->image = $filename;
                                $sectionimage->contenthash = $contenthash;
                                $sectionimage->displayedimagestate = 0; // Not generated.
                                $sectionimage->id = $DB->insert_record('format_grid_image', $sectionimage, true);
                            }
                            $toolbox = \format_grid\toolbox::get_instance();
                            $toolbox->setup_displayed_image($sectionimage, $file, $course->id, $sectionid, $format);
                        }
                    }
                    // Note: Not done the case whereby 'a' file is removed - needed?
                } else {
                    // No files - possible deletion of existing image.
                    $DB->delete_records('format_grid_image', array('courseid' => $course->id, 'sectionid' => $sectionid));
                    // Remove existing displayed image.
                    $existingfiles = $fs->get_area_files($coursecontext->id, 'format_grid', 'displayedsectionimage', $sectionid);
                    foreach ($existingfiles as $existingfile) {
                        if (!$existingfile->is_directory()) {
                            $existingfile->delete();
                        }
                    }
                }
                if (!defined('BEHAT_SITE_RUNNING')) {
                    $lock->release();
                }
            } else {
                throw new \moodle_exception('cannotgetimagelock', 'format_grid', '',
                    get_string('cannotgetmanagesectionimagelock', 'format_grid')
                );
            }
        }

        return $failure;
    }

    /**
     * Returns HTML for sectionfilemanager form element.
     *
     * @return string
     */
    public function toHtml() {
        $this->init();
        return parent::toHtml();
    }

    /**
     * Prepare the file area.
     */
    private function init() {
        $course = $this->getAttribute('course');
        $sectionid = $this->getAttribute('sectionid');

        $coursecontext = context_course::instance($course->id);
        $fmd = file_prepare_standard_filemanager(
            $course, 'sectionimage', self::$options, $coursecontext, 'format_grid', 'sectionimage', $sectionid);
        $this->setValue($fmd->sectionimage_filemanager);
    }    
}
