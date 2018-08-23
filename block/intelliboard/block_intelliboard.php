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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    block_intelliboard
 * @copyright  2017 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    https://intelliboard.net/
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Displays recent intelliboard
 */
class block_intelliboard extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_intelliboard');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function applicable_formats() {
        return array(
                'admin' => false,
                'site-index' => false,
                'course-view' => true,
                'mod' => true,
                'my' => false
        );
    }

    public function specialization() {
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_intelliboard');
        } else {
            $this->title = $this->config->title;
        }
    }
    public function hide_header() {
        return true;
    }
    public function get_content() {
        global $USER, $PAGE, $CFG, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->config)) {
            $this->config = new stdClass();
        }
        // Create empty content.
        $this->content = new stdClass();
        $this->content->text = '';

        $cmid = isset($this->page->cm->id) ? $this->page->cm->id : 0;
        $courseid = isset($this->page->course->id) ? $this->page->course->id : 0;

        if ($cmid) {
            $context = context_module::instance($cmid);
            $canupdate = has_capability('moodle/course:manageactivities', $context);
        } elseif ($courseid) {
            $context = context_course::instance($courseid);
            $canupdate = has_capability('moodle/course:update', $context);
        } else {
            return $this->content;
        }
        $this->content->text = get_string('s44', 'block_intelliboard');
        if ($canupdate) {
            if (!isset($this->config->enableadmin) or !$this->config->enableadmin or empty($this->config->adminlist)) {
                return $this->content;
            }
            $navigation = $this->config->adminlist;
        } else {
            if (!isset($this->config->enablelearner) or !$this->config->enablelearner or empty($this->config->learnerlist)) {
                return $this->content;
            }
            $navigation = $this->config->learnerlist;
        }

        $this->page->requires->jquery();
        $this->page->requires->js(new moodle_url('https://www.gstatic.com/charts/loader.js'));
        $this->page->requires->js('/blocks/intelliboard/script.js');
        $this->page->requires->css('/blocks/intelliboard/style.css');

        $menu = "";
        $widgets = "";
        if (in_array(0, $navigation) and (($courseid and !$cmid) or ($cmid and $canupdate))) {
            $label = ($cmid) ? get_string('s6', 'block_intelliboard') : get_string('s5', 'block_intelliboard');
            $menu .= html_writer::tag('a', $label, array('href' => 'intelliboard_learners_progress'));
            $widgets .= html_writer::start_tag('li', array('id'=>'intelliboard_learners_progress'));
            $widgets .= html_writer::tag('h4', $label);
            $widgets .= html_writer::tag('div', '', array('class' => 'intelliboard-ajax-block intelliboard_learners_progress'));
            $widgets .= html_writer::end_tag('li');
        }
        if (in_array(1, $navigation) and $canupdate and !$cmid) {
            $menu .= html_writer::tag('a', get_string('s6', 'block_intelliboard'), array('href' => 'intelliboard_activities_progress'));
            $widgets .= html_writer::start_tag('li', array('id'=>'intelliboard_activities_progress'));
            $widgets .= html_writer::tag('h4', get_string('s6', 'block_intelliboard'));
            $widgets .= html_writer::tag('div', '', array('class' => 'intelliboard-ajax-block intelliboard_activities_progress'));
            $widgets .= html_writer::end_tag('li');
        }
        if (in_array(2, $navigation)) {
            $menu .= html_writer::tag('a', get_string('s7', 'block_intelliboard'), array('href' => 'intelliboard_learners_performance'));
            $widgets .= html_writer::start_tag('li', array('id'=>'intelliboard_learners_performance'));
            $widgets .= html_writer::tag('h4', get_string('s7', 'block_intelliboard'));
            $widgets .= html_writer::tag('div', '', array('class' => 'intelliboard-ajax-block intelliboard_learners_performance'));
            $widgets .= html_writer::end_tag('li');
        }
        if (in_array(3, $navigation)) {
            $label = ($cmid) ? get_string('s81', 'block_intelliboard') : get_string('s8', 'block_intelliboard');
            $menu .= html_writer::tag('a', $label, array('href' => 'intelliboard_course_summary'));
            $widgets .= html_writer::start_tag('li', array('id'=>'intelliboard_course_summary'));
            $widgets .= html_writer::tag('h4', $label);
            $widgets .= html_writer::tag('div', '', array('class' => 'intelliboard-ajax-block intelliboard_course_summary'));
            $widgets .= html_writer::end_tag('li');
        }
        if (in_array(4, $navigation) and $canupdate and !$cmid) {
            $menu .= html_writer::tag('a', get_string('s9', 'block_intelliboard'), array('href' => 'intelliboard_live_stream'));
            $widgets .= html_writer::start_tag('li', array('id'=>'intelliboard_live_stream'));
            $widgets .= html_writer::tag('h4', get_string('s9', 'block_intelliboard'));
            $widgets .= html_writer::tag('div', '', array('class' => 'intelliboard-ajax-block intelliboard_live_stream'));
            $widgets .= html_writer::end_tag('li');
        }

        $menu .= html_writer::start_tag('div', array('class' => 'external-links'));
        if (isloggedin() and !$canupdate) {
            $url = new moodle_url('/local/intelliboard/student/index.php');
            $icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/user')->out(false),'id'=>'intelliboard-navcontent'));
            $menu .= html_writer::link($url, $icon .' '. get_string('s3', 'block_intelliboard'), array('target' => '_blank', 'class' => 'external-link'));
        }
        if (isloggedin() and $canupdate) {
            $url = new moodle_url('/local/intelliboard/instructor/index.php');
            $icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/outcomes')->out(false),'id'=>'intelliboard-navcontent'));
            $menu .= html_writer::link($url, $icon .' '. get_string('s2', 'block_intelliboard'), array('target' => '_blank', 'class' => 'external-link'));
        }
        if (is_siteadmin() and $canupdate) {
            $url = new moodle_url('/local/intelliboard/index.php');
            $icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/scales')->out(false),'id'=>'intelliboard-navcontent'));
            $menu .= html_writer::link($url, $icon .' '. get_string('s1', 'block_intelliboard'), array('target' => '_blank', 'class' => 'external-link'));

            $icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/publish')->out(false),'id'=>'intelliboard-navcontent'));
            $menu .= html_writer::link('https://intelliboard.net', $icon .' '. get_string('s4', 'block_intelliboard'), array('target' => '_blank', 'class' => 'external-link'));
        }

        $menu .= html_writer::end_tag('div');

        $content = html_writer::start_tag('div', array('class' => 'intelliboard-block'));
        $content .= html_writer::start_tag('div', array('class' => 'intelliboard-block-nav'));
        $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('a/view_list_active')->out(false),'id'=>'intelliboard-navcontent'));
        $content .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('e/fullscreen')->out(false), 'id' => 'intelliboard-resize'));
        $content .= html_writer::end_tag('div');

        $content .= html_writer::start_tag('div', array('class' => 'intelliboard-navcontent'));
        $content .= html_writer::tag('h4', get_string('pluginname', 'block_intelliboard'));
        $content .= $menu;
        $content .= html_writer::end_tag('div');

        $content .= html_writer::tag('ul', $widgets, array('class' => 'intelliboard-widgets'));

        $content .= html_writer::end_tag('div');

        $params = array(
            'url' => $CFG->wwwroot . "/blocks/intelliboard/ajax.php?courseid=" . $courseid . "&cmid=" . $cmid,
            'lang' => array(
                's17' => get_string('s17', 'block_intelliboard'),
                's29' => get_string('s29', 'block_intelliboard'),
                's35' => get_string('s35', 'block_intelliboard'),
                's36' => get_string('s36', 'block_intelliboard'),
                's37' => get_string('s37', 'block_intelliboard'),
                's38' => get_string('s38', 'block_intelliboard'),
                's39' => get_string('s39', 'block_intelliboard'),
                's40' => get_string('s40', 'block_intelliboard'),
                's41' => get_string('s41', 'block_intelliboard'),
                's42' => get_string('s42', 'block_intelliboard')
            )
        );
        $this->page->requires->js_init_call('intelliboard_block_init', array($params), false);

        $this->content->text = $content;

        return $this->content;
    }
}
