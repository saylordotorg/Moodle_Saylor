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
 * Grid Format.
 *
 * @package    format_grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2012+ G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

class format_grid extends core_courseformat\base {
    // Used to determine the type of view URL to generate - parameter or anchor.
    private $coursedisplay = COURSE_DISPLAY_MULTIPAGE;

    private $settings = null;

    /**
     * Creates a new instance of class
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_grid
     */
    protected function __construct($format, $courseid) {
        parent::__construct($format, $courseid);
        if ($courseid === 0) {
            global $COURSE;
            $courseid = $COURSE->id;  // Save lots of global $COURSE as we will never be the site course.
        }
        parent::__construct($format, $courseid);
    }

    /**
     * Get the course display value for the current course.
     *
     * @return int The current value (COURSE_DISPLAY_MULTIPAGE or COURSE_DISPLAY_SINGLEPAGE).
     */
    public function get_course_display(): int {
        return $this->coursedisplay;
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @param bool $invalidate Invalidate the existing known settings and get a fresh set.  Set when you know the settings have
     *                         changed.
     * @return array The settings as an array.
     */
    public function get_settings($invalidate = false) {
        if ($invalidate) {
            $this->settings = null;
        }
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
            foreach ($this->settings as $settingname => $settingvalue) {
                if (isset($settingvalue)) {
                    $settingvtype = gettype($settingvalue);
                    if ((($settingvtype == 'string') && ($settingvalue === '-')) ||
                        (($settingvtype == 'integer') && ($settingvalue === 0))) {
                        // Default value indicator is a hyphen or a number equal to 0.
                        $this->settings[$settingname] = get_config('format_grid', 'default'.$settingname);
                    }
                }
            }
        }
        return $this->settings;
    }

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    public function uses_course_index() {
        return true;
    }

    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Gets the name for the provided section.
     *
     * @param stdClass $section The section.
     * @return string The section name.
     */
    public function get_section_name($section) {
        $thesection = $this->get_section($section);
        if ((string)$thesection->name !== '') {
            return format_string($thesection->name, true,
                ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($thesection);
        }
    }


    /**
     * Returns the default section name for the topics course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of course_format::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_grid');
        } else {
            // Use course_format::get_default_section_name implementation which will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        return get_string('topicoutline');
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', ['id' => $course->id]);

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $this->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    public function supports_components() {
        return true;
    }

    /**
     * Loads all of the course sections into the navigation.
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     * @return void
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode.
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course.
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for the course.
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        $courseconfig = null;

        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseid = $this->get_courseid();
            if ($courseid == 1) { // New course.
                $defaultnumsections = $courseconfig->numsections;
            } else { // Existing course that may not have 'numsections' - see get_last_section().
                global $DB;
                $defaultnumsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
                    WHERE course = ?', array($courseid));
            }
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $defaultnumsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT
                ),
                'imagecontainerwidth' => array(
                    'default' => 0,
                    'type' => PARAM_INT
                ),
                'imagecontainerratio' => array(
                    'default' => '-',
                    'type' => PARAM_ALPHANUMEXT
                ),
                'imageresizemethod' => array(
                    'default' => 0,
                    'type' => PARAM_INT
                ),
                'singlepagesummaryimage' => array(
                    'default' => 0,
                    'type' => PARAM_INT
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            if (is_null($courseconfig)) {
                $courseconfig = get_config('moodlecourse');
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numbersections', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                )
            );

            // TODO - Use capabilities?
            $imagecontainerwidthvalues = $this->generate_default_entry(
                'imagecontainerwidth',
                0,
                \format_grid\toolbox::get_image_container_widths()
            );
            $courseformatoptionsedit['imagecontainerwidth'] = array(
                'label' => new lang_string('imagecontainerwidth', 'format_grid'),
                'help' => 'imagecontainerwidth',
                'help_component' => 'format_grid',
                'element_type' => 'select',
                'element_attributes' => array($imagecontainerwidthvalues)
            );
            $imagecontainerratiovalues = $this->generate_default_entry(
                'imagecontainerratio',
                '-',
                \format_grid\toolbox::get_image_container_ratios()
            );
            $courseformatoptionsedit['imagecontainerratio'] = array(
                'label' => new lang_string('imagecontainerratio', 'format_grid'),
                'help' => 'imagecontainerratio',
                'help_component' => 'format_grid',
                'element_type' => 'select',
                'element_attributes' => array($imagecontainerratiovalues)
            );

            $imageresizemethodvalues = $this->generate_default_entry(
                'imageresizemethod',
                0,
                array(
                    1 => new lang_string('scale', 'format_grid'), // Scale.
                    2 => new lang_string('crop', 'format_grid')   // Crop.
                )
            );
            $courseformatoptionsedit['imageresizemethod'] = array(
                'label' => new lang_string('imageresizemethod', 'format_grid'),
                'help' => 'imageresizemethod',
                'help_component' => 'format_grid',
                'element_type' => 'select',
                'element_attributes' => array($imageresizemethodvalues)
            );

            $singlepagesummaryimagevalues = $this->generate_default_entry(
                'singlepagesummaryimage',
                0,
                array(
                    1 => new lang_string('off', 'format_grid'),
                    2 => new lang_string('left', 'format_grid'),
                    3 => new lang_string('centre', 'format_grid'),
                    4 => new lang_string('right', 'format_grid')
                )
            );
            $courseformatoptionsedit['singlepagesummaryimage'] = array(
                'label' => new lang_string('singlepagesummaryimage', 'format_grid'),
                'element_type' => 'select',
                'element_attributes' => array($singlepagesummaryimagevalues),
                'help' => 'singlepagesummaryimage',
                'help_component' => 'format_grid'
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Generates the default setting value entry.
     *
     * @param string $settingname Setting name.
     * @param string/int $defaultindex Default index.
     * @param array $values Setting value array to add the default entry to.
     * @return array Updated value array with the added default entry.
     */
    private function generate_default_entry($settingname, $defaultindex, $values) {
        $defaultvalue = get_config('format_grid', 'default'.$settingname);
        $defarray = array($defaultindex => new lang_string('default', 'format_grid', $values[$defaultvalue]));

        return array_replace($defarray, $values);
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG;
        MoodleQuickForm::registerElementType(
            'sectionfilemanager',
            "$CFG->dirroot/course/format/grid/form/sectionfilemanager.php",
            'MoodleQuickForm_sectionfilemanager');

        $elements = parent::create_edit_form_elements($mform, $forsection);

        /* Increase the number of sections combo box values if the user has increased the number of sections
           using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
           reduced below the number of sections already set for the course on the site administration course
           defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
           activities / resources. */
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections + 1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }
        }

        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'Grid', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * The layout and colour defaults will come from 'course_format_options'.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data.
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update.
     * @return bool whether there were any changes to the options values.
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB; // MDL-37976.

        $currentsettings = $this->get_settings();
        $data = (array) $data;
        if ($oldcourse !== null) {
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        /* If previous format does not have the field 'numsections' and $data['numsections'] is not set,
                           we fill it with the maximum section number from the DB. */
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default.
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        $changes = $this->update_format_options($data);

        if ($changes && array_key_exists('numsections', $data)) {
            // If the numsections was decreased, try to completely delete the orphaned sections (unless they are not empty).
            $numsections = (int)$data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }

        $newsettings = $this->get_settings(true); // Ensure we get the new values.

        if (($currentsettings['imagecontainerwidth'] != $newsettings['imagecontainerwidth']) ||
            ($currentsettings['imagecontainerratio'] != $newsettings['imagecontainerratio'])) {
            $performimagecontainersize = true;
        } else {
            $performimagecontainersize = false;
        }

        if (($currentsettings['imageresizemethod'] != $newsettings['imageresizemethod'])) {
            $performimageresizemethod = true;
        } else {
            $performimageresizemethod = false;
        }

        if (($performimagecontainersize) || ($performimageresizemethod)) {
            \format_grid\toolbox::update_displayed_images($this->courseid);
        }

        return $changes;
    }

    public function section_format_options($foreditform = false) {
        static $sectionformatoptions = false;
        if ($sectionformatoptions === false) {
            $sectionformatoptions = array(
                'sectionimage_filemanager' => array(
                    'default' => '',
                    'type' => PARAM_RAW
                ),
                'sectionimagealttext' => array(
                    'default' => '',
                    'type' => PARAM_TEXT
                ),
                'sectionbreak' => array(
                    'default' => 1, // No.
                    'type' => PARAM_INT
                ),
                'sectionbreakheading' => array(
                    'default' => '',
                    'type' => PARAM_RAW
                )
            );
        }
        if ($foreditform && !isset($sectionformatoptions['sectionimage_filemanager']['label'])) {
            $sectionformatoptionsedit = array(
                'sectionimage_filemanager' => array(
                    'label' => new lang_string('sectionimage', 'format_grid'),
                    'help' => 'sectionimage',
                    'help_component' => 'format_grid',
                    'element_type' => 'sectionfilemanager',
                    'element_attributes' => array(
                        array(
                            'course' => $this->course,
                            'sectionid' => optional_param('id', 0, PARAM_INT)
                        )
                    )
                ),
                'sectionimagealttext' => array(
                    'label' => new lang_string('sectionimagealttext', 'format_grid'),
                    'help' => 'sectionimagealttext',
                    'help_component' => 'format_grid',
                    'element_type' => 'text'
                ),
                'sectionbreak' => array(
                    'label' => new lang_string('sectionbreak', 'format_grid'),
                    'help' => 'sectionbreak',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'),
                            2 => new lang_string('yes')
                        )
                    ),
                ),
                'sectionbreakheading' => array(
                    'label' => new lang_string('sectionbreakheading', 'format_grid'),
                    'help' => 'sectionbreakheading',
                    'help_component' => 'format_grid',
                    'element_type' => 'textarea'
                )
            );
            $sectionformatoptions = array_merge_recursive($sectionformatoptions, $sectionformatoptionsedit);
        }

        return $sectionformatoptions;
    }

    /**
     * Deletes a section
     *
     * Do not call this function directly, instead call {@link course_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty if set to false section will not be deleted if it has modules in it.
     * @return bool whether section was deleted
     */
    public function delete_section($section, $forcedeleteifnotempty = false) {
        if (!$this->uses_sections()) {
            // Not possible to delete section if sections are not used.
            return false;
        }
        if (!is_object($section)) {
            global $DB;
            $section = $DB->get_record('course_sections', array('course' => $this->get_courseid(), 'section' => $section),
                'id,section,sequence,summary');
        }
        if (!$section || !$section->section) {
            // Not possible to delete 0-section.
            return false;
        }

        if (!$forcedeleteifnotempty && (!empty($section->sequence) || !empty($section->summary))) {
            return false;
        }
        if (parent::delete_section($section, $forcedeleteifnotempty)) {
            \format_grid\toolbox::delete_image($section->id, $this->get_courseid());
            return true;
        }
        return false;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_grid');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_grid', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0, not allow in orphaned sections.
        return !$section->section || ($section->visible && $section->section <= $this->get_course()->numsections);
    }

    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'topics' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_grid');

        if (!($section instanceof section_info)) {
            $modinfo = course_modinfo::instance($this->courseid);
            $section = $modinfo->get_section_info($section->section);
        }
        $elementclass = $this->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($this, $section);

        $rv['section_availability'] = $renderer->render($availability);
        return $rv;
    }

    /**
     * Restores the numsections if was not in the backup.
     * @param int $numsections The number of sections.
     */
    public function restore_numsections($numsections) {
        $data = array('numsections' => $numsections);
        $this->update_course_format_options($data);
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    // Grid specific methods...
    /**
     * Class instance update images callback.
     */
    public static function update_displayed_images_callback() {
        \format_grid\toolbox::update_displayed_images_callback();
    }
}

// Transposed from block_html_pluginfile.
/**
 * Form for editing HTML block instances.
 *
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   format_grid
 * @param stdClass $course course object
 * @param stdClass $birecordorcm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function format_grid_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_COURSE) {
        send_file_not_found();
    }

    // Check if user has capability to access course.
    require_course_login($course);

    if ($filearea !== 'displayedsectionimage') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = $args[2];
    $sectionid = $args[0];

    $file = $fs->get_file($context->id, 'format_grid', 'displayedsectionimage', $sectionid, '/', $filename);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // NOTE:
    // It would be nice to have file revisions here, for now rely on standard file lifetime,
    // do not lower it because the files are displayed very often.  But... Grid format is using
    // displayedsectionimage in the URL as a means to overcome this.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_grid_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'grid'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
