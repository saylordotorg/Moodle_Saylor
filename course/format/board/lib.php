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
 * format_board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @copyright  2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * format_board
 *
 */
class format_board extends format_topics {

    /**
     * course_format_options
     *
     */
    public function course_format_options($foreditform = false) {
        global $PAGE;
        static $courseformatoptions = false;
        $courseconfig = get_config('moodlecourse');
        $max = $courseconfig->maxsections;
        if (!isset($max) || !is_numeric($max)) {
            $max = 52;
        }
        if ($courseformatoptions === false) {
            $courseformatoptions['numsections'] = array(
                'default' => $courseconfig->numsections,
                'type' => PARAM_INT,
            );
            $courseformatoptions['showdefaultsectionname'] = array(
                'default' => get_config('format_board', 'showdefaultsectionname'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['sectionlayout'] = array(
                'default' => get_config('format_board', 'sectionlayout'),
                'type' => PARAM_INT,
            );
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptions['widthcol'.$i] = array(
                    'default' => get_config('format_board', 'widthcol'.$i),
                    'type' => PARAM_INT,
                );
                $courseformatoptions['numsectionscol'.$i] = array(
                    'default' => get_config('format_board', 'numsectionscol'.$i),
                    'type' => PARAM_INT,
                );
            }
            $courseformatoptions['color'] = array(
                'default' => get_config('format_board', 'color'),
                'type' => PARAM_TEXT,
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit['numsections'] = array(
                'label' => new lang_string('numberweeks'),
                'element_type' => 'select',
                'element_attributes' => array($sectionmenu),
            );
            $courseformatoptionsedit['showdefaultsectionname'] = array(
                'label' => get_string('showdefaultsectionname', 'format_board'),
                'help' => 'showdefaultsectionname',
                'help_component' => 'format_board',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_board'),
                        0 => get_string('no', 'format_board'),
                    ),
                ),
            );
            $sectionmenu[0] = get_string('unlimited', 'format_board');
            $courseformatoptionsedit['sectionlayout'] = array(
                'label' => get_string('sectionlayout', 'format_board'),
                'help' => 'sectionlayout',
                'help_component' => 'format_board',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('none', 'format_board'),
                        1 => get_string('blocks', 'format_board'),
                    ),
                ),
            );
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptionsedit['widthcol'.$i] = array(
                    'label' => get_string('widthcol', 'format_board').' '.$i,
                    'help' => 'widthcol',
                    'help_component' => 'format_board',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            100 => '100%',
                            75 => '75%',
                            66 => '66%',
                            50 => '50%',
                            33 => '33%',
                            25 => '25%',
                        ),
                    ),
                );
                $courseformatoptionsedit['numsectionscol'.$i] = array(
                    'label' => get_string('numsectionscol', 'format_board').' '.$i,
                    'help' => 'numsectionscol',
                    'help_component' => 'format_board',
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                );
            }
            $courseformatoptionsedit['color'] = array(
                'label' => get_string('color', 'format_board'),
                'help' => 'color',
                'help_component' => 'format_board',
                'element_type' => 'text',
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * update_course_format_options
     *
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;
        $data = (array)$data;
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse))
                        $data[$key] = $oldcourse[$key];
                    else if ($key === 'numsections') {
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection)
                            $data['numsections'] = $maxsection;
                    }
                }
            }
        }
        $changed = $this->update_format_options($data);
        if ($changed && array_key_exists('numsections', $data)) {
            $numsections = (int)$data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                        WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--)
                if (!$this->delete_section($sectionnum, false))
                    break;
        }
        return $changed;
    }

  /**
     * get_view_url
     *
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

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
                $usercoursedisplay = 0;
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

}
