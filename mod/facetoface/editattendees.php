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

define('MAX_USERS_PER_PAGE', 5000);

$s              = required_param('s', PARAM_INT); // Facetoface session ID.
$add            = optional_param('add', 0, PARAM_BOOL);
$remove         = optional_param('remove', 0, PARAM_BOOL);
$showall        = optional_param('showall', 0, PARAM_BOOL);
$searchtext     = optional_param('searchtext', '', PARAM_TEXT); // Search string.
$suppressemail  = optional_param('suppressemail', false, PARAM_BOOL); // Send email notifications.
$previoussearch = optional_param('previoussearch', 0, PARAM_BOOL);
$backtoallsessions = optional_param('backtoallsessions', 0, PARAM_INT); // Facetoface activity to go back to.

if (!$session = facetoface_get_session($s)) {
    throw new moodle_exception('error:incorrectcoursemodulesession', 'facetoface');
}
if (!$facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface))) {
    throw new moodle_exception('error:incorrectfacetofaceid', 'facetoface');
}
if (!$course = $DB->get_record('course', array('id' => $facetoface->course))) {
    throw new moodle_exception('error:coursemisconfigured', 'facetoface');
}
if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
    throw new moodle_exception('error:incorrectcoursemodule', 'facetoface');
}

// Check essential permissions.
require_course_login($course);
$context = context_course::instance($course->id);
require_capability('mod/facetoface:viewattendees', $context);

// Get some language strings.
$strsearch = get_string('search');
$strshowall = get_string('showall');
$strsearchresults = get_string('searchresults');
$strfacetofaces = get_string('modulenameplural', 'facetoface');
$strfacetoface = get_string('modulename', 'facetoface');

$errors = array();

// Get the user_selector we will need.
$potentialuserselector = new facetoface_candidate_selector('addselect', array('sessionid' => $session->id));
$existinguserselector = new facetoface_existing_selector('removeselect', array('sessionid' => $session->id));

// Process incoming user assignments.
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    require_capability('mod/facetoface:addattendees', $context);
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach ($userstoassign as $adduser) {
            if (!$adduser = clean_param($adduser->id, PARAM_INT)) {
                continue; // Invalid userid.
            }

            // Make sure that the user is enroled in the course.
            if (!has_capability('moodle/course:view', $context, $adduser)) {
                $user = $DB->get_record('user', array('id' => $adduser));
                // Make sure that the user is enroled in the course.
                if (!is_enrolled($context, $user)) {
                    $defaultroleid = null;
                    // Get default role ID for manual enrollment.
                    $conditions = array ('courseid' => $course->id, 'enrol' => 'manual');
                    $defaultroleid = $DB->get_field('enrol', 'roleid', $conditions, IGNORE_MISSING);
                    if (!enrol_try_internal_enrol($course->id, $user->id, $defaultroleid)) {
                        $errors[] = get_string('error:enrolmentfailed', 'facetoface', fullname($user));
                        $errors[] = get_string('error:addattendee', 'facetoface', fullname($user));
                        continue; // Don't sign the user up.
                    }
                }
            }

            $usernamefields = facetoface_get_all_user_name_fields(true);
            if (facetoface_get_user_submissions($facetoface->id, $adduser)) {
                $erruser = $DB->get_record('user', array('id' => $adduser), "id, {$usernamefields}");
                $errors[] = get_string('error:addalreadysignedupattendee', 'facetoface', fullname($erruser));
            } else {
                if (!facetoface_session_has_capacity($session, $context)) {
                    $errors[] = get_string('full', 'facetoface');
                    break; // No point in trying to add other people.
                }
                // Check if we are waitlisting or booking.
                if ($session->datetimeknown) {
                    $status = MDL_F2F_STATUS_BOOKED;
                } else {
                    $status = MDL_F2F_STATUS_WAITLISTED;
                }
                if (!facetoface_user_signup($session, $facetoface, $course, '', MDL_F2F_BOTH,
                $status, $adduser, !$suppressemail)) {
                    $erruser = $DB->get_record('user', array('id' => $adduser), "id, {$usernamefields}");
                    $errors[] = get_string('error:addattendee', 'facetoface', fullname($erruser));
                }
            }
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Process removing user assignments from session.
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    require_capability('mod/facetoface:removeattendees', $context);
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            if (!$removeuser = clean_param($removeuser->id, PARAM_INT)) {
                continue; // Invalid userid.
            }

            if (facetoface_user_cancel($session, $removeuser, true, $cancelerr)) {

                // Notify the user of the cancellation if the session hasn't started yet.
                $timenow = time();
                if (!$suppressemail and !facetoface_has_session_started($session, $timenow)) {
                    facetoface_send_cancellation_notice($facetoface, $session, $removeuser);
                }
            } else {
                $errors[] = $cancelerr;
                $usernamefields = facetoface_get_all_user_name_fields(true);
                $erruser = $DB->get_record('user', array('id' => $removeuser), "id, {$usernamefields}");
                $errors[] = get_string('error:removeattendee', 'facetoface', fullname($erruser));
            }
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();

        // Update attendees.
        facetoface_update_attendees($session);
    }
}

// Main page.
$pagetitle = format_string($facetoface->name);

$PAGE->set_cm($cm);
$PAGE->set_url('/mod/facetoface/editattendees.php', array('s' => $s, 'backtoallsessions' => $backtoallsessions));

$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('addremoveattendees', 'facetoface'));

// Create user_selector form.
$out = html_writer::start_tag('form', array('id' => 'assignform', 'method' => 'post', 'action' => $PAGE->url));
$out .= html_writer::start_tag('div');
$out .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => "previoussearch", 'value' => $previoussearch));
$out .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => "backtoallsessions", 'value' => $backtoallsessions));
$out .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => "sesskey", 'value' => sesskey()));

$table = new html_table();
$table->attributes['class'] = "generaltable generalbox boxaligncenter";
$cells = array();
$content = html_writer::start_tag('p') . html_writer::tag('label', get_string('attendees', 'facetoface'),
        array('for' => 'removeselect')) . html_writer::end_tag('p');
$content .= $existinguserselector->display(true);
$cell = new html_table_cell($content);
$cell->attributes['id'] = 'existingcell';
$cells[] = $cell;
$content = html_writer::tag('div', html_writer::empty_tag('input',
    array('type' => 'submit', 'id' => 'add', 'name' => 'add', 'title' => get_string('add'),
        'value' => $OUTPUT->larrow().' '.get_string('add'))), array('id' => 'addcontrols'));
$content .= html_writer::tag('div', html_writer::empty_tag('input',
    array('type' => 'submit', 'id' => 'remove', 'name' => 'remove', 'title' => get_string('remove'),
        'value' => $OUTPUT->rarrow().' '.get_string('remove'))), array('id' => 'removecontrols'));
$cell = new html_table_cell($content);
$cell->attributes['id'] = 'buttonscell';
$cells[] = $cell;
$content = html_writer::start_tag('p') . html_writer::tag('label',
        get_string('potentialattendees', 'facetoface'), array('for' => 'addselect')) . html_writer::end_tag('p');
$content .= $potentialuserselector->display(true);
$cell = new html_table_cell($content);
$cell->attributes['id'] = 'potentialcell';
$cells[] = $cell;
$table->data[] = new html_table_row($cells);
$content = html_writer::checkbox('suppressemail', 1, $suppressemail, get_string('suppressemail', 'facetoface'),
    array('id' => 'suppressemail'));
$content .= $OUTPUT->help_icon('suppressemail', 'facetoface');
$cell = new html_table_cell($content);
$cell->attributes['id'] = 'backcell';
$cell->attributes['colspan'] = '3';
$table->data[] = new html_table_row(array($cell));

$out .= html_writer::table($table);

// Get all signed up non-attendees.
$nonattendees = 0;
$usernamefields = facetoface_get_all_user_name_fields(true, 'u');
$nonattendeesrs = $DB->get_recordset_sql(
     "SELECT
            u.id,
            {$usernamefields},
            u.email,
            ss.statuscode
        FROM
            {facetoface_sessions} s
        JOIN
            {facetoface_signups} su
         ON s.id = su.sessionid
        JOIN
            {facetoface_signups_status} ss
         ON su.id = ss.signupid
        JOIN
            {user} u
         ON u.id = su.userid
        WHERE
            s.id = ?
        AND ss.superceded != 1
        AND ss.statuscode = ?
        ORDER BY
            u.lastname, u.firstname", array($session->id, MDL_F2F_STATUS_REQUESTED)
);

$table = new html_table();
$table->head = array(get_string('name'), get_string('email'), get_string('status'));
foreach ($nonattendeesrs as $user) {
    $data = array();
    $data[] = new html_table_cell(fullname($user));
    $data[] = new html_table_cell($user->email);
    $data[] = new html_table_cell(get_string('status_' . facetoface_get_status($user->statuscode), 'facetoface'));
    $row = new html_table_row($data);
    $table->data[] = $row;
    $nonattendees++;
}

$nonattendeesrs->close();
if ($nonattendees) {
    $out .= html_writer::empty_tag('br');
    $out .= $OUTPUT->heading(get_string('unapprovedrequests', 'facetoface') . ' (' . $nonattendees . ')');
    $out .= html_writer::table($table);
}

$out .= html_writer::end_tag('div') . html_writer::end_tag('form');
echo $out;

if (!empty($errors)) {
    $msg = html_writer::start_tag('p');
    foreach ($errors as $e) {
        $msg .= $e . html_writer::empty_tag('br');
    }
    $msg .= html_writer::end_tag('p');
    echo $OUTPUT->box_start('center');
    echo $OUTPUT->notification($msg);
    echo $OUTPUT->box_end();
}

// Bottom of the page links.
echo html_writer::start_tag('p');
$url = new moodle_url('/mod/facetoface/attendees.php', array('s' => $session->id, 'backtoallsessions' => $backtoallsessions));
echo html_writer::link($url, get_string('goback', 'facetoface'));
echo html_writer::end_tag('p');
echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);
