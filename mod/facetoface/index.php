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
 * Copyright (C) 2007-2011 Catalyst IT (http://www.catalyst.net.nz)
 * Copyright (C) 2011-2013 Totara LMS (http://www.totaralms.com)
 * Copyright (C) 2014 onwards Catalyst IT (http://www.catalyst-eu.net)
 *
 * @package    mod
 * @subpackage facetoface
 * @copyright  2014 onwards Catalyst IT <http://www.catalyst-eu.net>
 * @author     Stacey Walker <stacey@catalyst-eu.net>
 * @author     Alastair Munro <alastair.munro@totaralms.com>
 * @author     Aaron Barnes <aaron.barnes@totaralms.com>
 * @author     Francois Marier <francois@catalyst.net.nz>
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    throw new moodle_exception('error:coursemisconfigured', 'facetoface');
}

require_course_login($course);
$context = context_course::instance($course->id);
require_capability('mod/facetoface:view', $context);

// Logging and events trigger.
$params = array(
    'context'  => $context,
    'objectid' => $course->id
);
$event = \mod_facetoface\event\course_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strfacetofaces = get_string('modulenameplural', 'facetoface');
$strfacetoface = get_string('modulename', 'facetoface');
$strfacetofacename = get_string('facetofacename', 'facetoface');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strcourse = get_string('course');
$strname = get_string('name');

$pagetitle = format_string($strfacetofaces);

$PAGE->set_url('/mod/facetoface/index.php', array('id' => $id));

$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

if (!$facetofaces = get_all_instances_in_course('facetoface', $course)) {
    notice(get_string('nofacetofaces', 'facetoface'), "../../course/view.php?id=$course->id");
    die;
}

$timenow = time();

$table = new html_table();

if ($course->format == 'weeks' && has_capability('mod/facetoface:viewattendees', $context)) {
    $table->head  = array ($strweek, $strfacetofacename, get_string('sign-ups', 'facetoface'));
    $table->align = array ('center', 'left', 'center');
} else if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strfacetofacename);
    $table->align = array ('center', 'left', 'center', 'center');
} else if ($course->format == 'topics' && has_capability('mod/facetoface:viewattendees', $context)) {
    $table->head  = array ($strcourse, $strfacetofacename, get_string('sign-ups', 'facetoface'));
    $table->align = array ('center', 'left', 'center');
} else if ($course->format == 'topics') {
    $table->head  = array ($strcourse, $strfacetofacename);
    $table->align = array ('center', 'left', 'center', 'center');
} else {
    $table->head  = array ($strfacetofacename);
    $table->align = array ('left', 'left');
}

$currentsection = '';

foreach ($facetofaces as $facetoface) {

    $submitted = get_string('no');

    if (!$facetoface->visible) {
        // Show dimmed if the mod is hidden.
        $link = html_writer::link("view.php?f=$facetoface->id", format_string($facetoface->name), array('class' => 'dimmed'));
    } else {
        // Show normal if the mod is visible.
        $link = html_writer::link("view.php?f=$facetoface->id", format_string($facetoface->name));
    }

    $printsection = '';
    if ($facetoface->section !== $currentsection) {
        if ($facetoface->section) {
            $printsection = $facetoface->section;
        }
        $currentsection = $facetoface->section;
    }

    $totalsignupcount = 0;
    if ($sessions = facetoface_get_sessions($facetoface->id)) {
        foreach ($sessions as $session) {
            if (!facetoface_has_session_started($session, $timenow)) {
                $signupcount = facetoface_get_num_attendees($session->id);
                $totalsignupcount += $signupcount;
            }
        }
    }
    $url = new moodle_url('/course/view.php', array('id' => $course->id));
    $courselink = html_writer::link($url, $course->shortname, array('title' => $course->shortname));
    if ($course->format == 'weeks' or $course->format == 'topics') {
        if (has_capability('mod/facetoface:viewattendees', $context)) {
            $table->data[] = array ($courselink, $link, $totalsignupcount);
        } else {
            $table->data[] = array ($courselink, $link);
        }
    } else {
        $table->data[] = array ($link, $submitted);
    }
}

echo html_writer::empty_tag('br');

echo html_writer::table($table);
echo $OUTPUT->footer($course);
