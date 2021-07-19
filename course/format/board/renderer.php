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

require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * format_board_renderer
 *
 */
class format_board_renderer extends format_topics_renderer {

    /**
     * start_section_list
     *
     */
    protected function start_section_list($i = 0) {
        global $course;
        if ($course->sectionlayout == 0) {
            $layout = '';
        } elseif ($course->sectionlayout == 1) {
            $layout = ' blocks';
        } elseif ($course->sectionlayout == 2) {
            $layout = ' lines';
        }
        $width = isset($course->{'widthcol'.$i}) ? (int)$course->{'widthcol'.$i} : 100;
        return html_writer::start_tag('ul', array('class' => 'board width'.$width.$layout, 'id' => 'col-'.$i));
    }

    /**
     * section_header
     *
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        global $PAGE;
        $o = '';
        $currenttext = '';
        $sectionstyle = '';
        if ($section->section != 0) {
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }
        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region', 'aria-label'=> get_section_name($course, $section)));
        $o .= html_writer::tag('span', $this->section_title($section, $course), array('class' => 'hidden sectionname'));
        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));
        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        if ($course->showdefaultsectionname) {
            $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
        }
        $o.= html_writer::start_tag('div', array('class' => 'summary'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');
        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section, has_capability('moodle/course:viewhiddensections', $context));
        return $o;
    }

    /**
     * print_multiple_section_page
     *
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $course->coursedisplay = 0;
        $context = context_course::instance($course->id);
        $completioninfo = new completion_info($course);
        $cont = 1;
        $currentcol = 1;
        $courseconfig = get_config('moodlecourse');
        $max = $courseconfig->maxsections;
        for ($i = 1; $i <= $max; $i++) {
            $numtopicscol[$i] = $course->{'numsectionscol'.$i};
        }
        if (isset($course->color)) {
            $course->color = str_replace('#', '', $course->color);
            $course->color = substr($course->color, 0, 6);
            if (is_numeric('0x'.$course->color)) {
                $course->color = '#'.$course->color;
                $css = '
                .course-content ul.board li.section.main.current h3.sectionname { color: '.$course->color.'; }
                .course-content ul.board.blocks li.section.main h3.sectionname { color: '.$course->color.'; }
                .course-content ul.board.blocks li.section.main.current { border-color: '.$course->color.'; }
                .course-content ul.board.blocks li.section.main.current h3.sectionname { background: '.$course->color.'; color: #fff; }
                ';
                echo html_writer::tag('style', $css);
            }
        }
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        echo $this->course_activity_clipboard($course, 0);
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                /* 0-section is displayed a little different then the others */
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->start_section_list();
                    echo $this->section_header($thissection, $course, false, 0, $section);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                    echo $this->end_section_list();
                }
                continue;
            }
            if ($section == 1) {
                echo $this->start_section_list(1);
            }
            if ($section > $course->numsections) {
                continue;
            }
            $showsection = $thissection->uservisible || ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo));
            if (!$showsection) {
                if ($PAGE->user_is_editing()) {
                    if (!$course->hiddensections && $thissection->available) {
                        echo $this->section_header($thissection, $course, false, 0, $section);
                        if ($PAGE->user_is_editing()) {
                            echo $this->section_hidden($section, $course->id);
                            continue;
                        }
                    }
                }
            } else {
                echo $this->section_header($thissection, $course, false, 0, $section);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
                echo $this->section_footer();
            }
            if ($cont == @$numtopicscol[$currentcol] && @$numtopicscol[$currentcol] != 0) {
                $cont = 0;
                $currentcol++;
                echo $this->end_section_list();
                echo $this->start_section_list($currentcol);
            }
            $cont++;
        }
        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();
            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()
                )
            );
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));
            if ($course->numsections > 0) {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()
                    )
                );
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }
            echo html_writer::end_tag('div');
        }
        echo $this->end_section_list();
    }

}
