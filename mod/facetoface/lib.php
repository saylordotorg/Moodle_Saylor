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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/*
 * Definitions for setting notification types.
 */

// Utility definitions.
define('MDL_F2F_ICAL',   1);
define('MDL_F2F_TEXT',   2);
define('MDL_F2F_BOTH',   3);
define('MDL_F2F_INVITE', 4);
define('MDL_F2F_CANCEL', 8);

// Definitions for use in forms.
define('MDL_F2F_INVITE_BOTH', 7);     // Send a copy of both 4+1+2.
define('MDL_F2F_INVITE_TEXT', 6);     // Send just a plain email 4+2.
define('MDL_F2F_INVITE_ICAL', 5);     // Send just a combined text/ical message 4+1.
define('MDL_F2F_CANCEL_BOTH', 11);    // Send a copy of both 8+2+1.
define('MDL_F2F_CANCEL_TEXT', 10);    // Send just a plan email 8+2.
define('MDL_F2F_CANCEL_ICAL', 9);     // Send just a combined text/ical message 8+1.

// Name of the custom field where the manager's email address is stored.
define('MDL_MANAGERSEMAIL_FIELD', 'managersemail');

// Custom field related constants.
define('CUSTOMFIELD_DELIMITER', '##SEPARATOR##');
define('CUSTOMFIELD_TYPE_TEXT',        0);
define('CUSTOMFIELD_TYPE_SELECT',      1);
define('CUSTOMFIELD_TYPE_MULTISELECT', 2);

// Calendar-related constants.
define('CALENDAR_MAX_NAME_LENGTH', 15);
define('F2F_CAL_NONE',   0);
define('F2F_CAL_COURSE', 1);
define('F2F_CAL_SITE',   2);

// Signup status codes (remember to update facetoface_statuses()).
define('MDL_F2F_STATUS_USER_CANCELLED', 10);

// SESSION_CANCELLED is not yet implemented.
define('MDL_F2F_STATUS_SESSION_CANCELLED',  20);
define('MDL_F2F_STATUS_DECLINED',           30);
define('MDL_F2F_STATUS_REQUESTED',          40);
define('MDL_F2F_STATUS_APPROVED',           50);
define('MDL_F2F_STATUS_WAITLISTED',         60);
define('MDL_F2F_STATUS_BOOKED',             70);
define('MDL_F2F_STATUS_NO_SHOW',            80);
define('MDL_F2F_STATUS_PARTIALLY_ATTENDED', 90);
define('MDL_F2F_STATUS_FULLY_ATTENDED',     100);

/**
 * Returns the list of possible facetoface status.
 *
 * @param int $statuscode One of the MDL_F2F_STATUS* constants
 * @return string $string Human readable code
 */
function facetoface_statuses() {
    // This array must match the status codes above, and the values
    // must equal the end of the constant name but in lower case.

    return array(
        MDL_F2F_STATUS_USER_CANCELLED      => 'user_cancelled',
        // MDL_F2F_STATUS_SESSION_CANCELLED   => 'session_cancelled', // Not yet implemented.
        MDL_F2F_STATUS_DECLINED            => 'declined',
        MDL_F2F_STATUS_REQUESTED           => 'requested',
        MDL_F2F_STATUS_APPROVED            => 'approved',
        MDL_F2F_STATUS_WAITLISTED          => 'waitlisted',
        MDL_F2F_STATUS_BOOKED              => 'booked',
        MDL_F2F_STATUS_NO_SHOW             => 'no_show',
        MDL_F2F_STATUS_PARTIALLY_ATTENDED  => 'partially_attended',
        MDL_F2F_STATUS_FULLY_ATTENDED      => 'fully_attended',
    );
}

/**
 * Returns the human readable code for a face-to-face status
 *
 * @param int $statuscode One of the MDL_F2F_STATUS* constants
 * @return string $string Human readable code
 */
function facetoface_get_status($statuscode) {
    $statuses = facetoface_statuses();

    // Check code exists.
    if (!isset($statuses[$statuscode])) {
        throw new moodle_exception('F2F status code does not exist: ' . $statuscode);
    }

    // Get code.
    $string = $statuses[$statuscode];

    // Check to make sure the status array looks to be up-to-date.
    if (constant('MDL_F2F_STATUS_' . strtoupper($string)) != $statuscode) {
        throw new moodle_exception('F2F status code array does not appear to be up-to-date: ' . $statuscode);
    }

    return $string;
}

/**
 * Prints the cost amount along with the appropriate currency symbol.
 *
 * To set your currency symbol, set the appropriate 'locale' in
 * lang/en_utf8/langconfig.php (or the equivalent file for your
 * language).
 *
 * @param int  $amount     Numerical amount without currency symbol
 * @param bool $htmloutput Whether the output is in HTML or not
 */
function format_cost($amount, $htmloutput=true) {
    setlocale(LC_MONETARY, get_string('locale', 'langconfig'));
    $localeinfo = localeconv();

    $symbol = $localeinfo['currency_symbol'];
    if (empty($symbol)) {

        // Cannot get the locale information, default to en_US.UTF-8.
        return '$' . $amount;
    }

    // Character between the currency symbol and the amount.
    $separator = '';
    if ($localeinfo['p_sep_by_space']) {
        $separator = $htmloutput ? '&nbsp;' : ' ';
    }

    // The symbol can come before or after the amount.
    if ($localeinfo['p_cs_precedes']) {
        return $symbol . $separator . $amount;
    } else {
        return $amount . $separator . $symbol;
    }
}

/**
 * Returns the effective cost of a session depending on the presence
 * or absence of a discount code.
 *
 * @param class $sessiondata contains the discountcost and normalcost
 */
function facetoface_cost($userid, $sessionid, $sessiondata, $htmloutput=true) {
    global $CFG, $DB;

    $count = $DB->count_records_sql("SELECT COUNT(*)
                               FROM {facetoface_signups} su,
                                    {facetoface_sessions} se
                              WHERE su.sessionid = ?
                                AND su.userid = ?
                                AND su.discountcode IS NOT NULL
                                AND su.sessionid = se.id", array($sessionid, $userid));
    if ($count > 0) {
        return format_cost($sessiondata->discountcost, $htmloutput);
    } else {
        return format_cost($sessiondata->normalcost, $htmloutput);
    }
}

/**
 * Human-readable version of the duration field used to display it to
 * users
 *
 * @param  int $duration duration in hours
 * @return string
 */
function format_duration($duration) {
    $components = explode(':', $duration);

    // Default response.
    $string = '';

    // Check for bad characters.
    if (trim(preg_match('/[^0-9:\.\s]/', $duration))) {
        return $string;
    }

    if ($components and count($components) > 1) {

        // E.g. "1:30" => "1 hour and 30 minutes".
        $hours = round($components[0]);
        $minutes = round($components[1]);
    } else {

        // E.g. "1.5" => "1 hour and 30 minutes".
        $hours = floor($duration);
        $minutes = round(($duration - floor($duration)) * 60);
    }

    // Check if either minutes is out of bounds.
    if ($minutes >= 60) {
        return $string;
    }

    if (1 == $hours) {
        $string = get_string('onehour', 'facetoface');
    } else if ($hours > 1) {
        $string = get_string('xhours', 'facetoface', $hours);
    }

    // Insert separator between hours and minutes.
    if ($string != '') {
        $string .= ' ';
    }

    if (1 == $minutes) {
        $string .= get_string('oneminute', 'facetoface');
    } else if ($minutes > 0) {
        $string .= get_string('xminutes', 'facetoface', $minutes);
    }

    return $string;
}

/**
 * Converts minutes to hours
 */
function facetoface_minutes_to_hours($minutes) {
    if (!intval($minutes)) {
        return 0;
    }

    if ($minutes > 0) {
        $hours = floor($minutes / 60.0);
        $mins = $minutes - ($hours * 60.0);
        return "$hours:$mins";
    } else {
        return $minutes;
    }
}

/**
 * Converts hours to minutes
 */
function facetoface_hours_to_minutes($hours) {
    $components = explode(':', $hours);
    if ($components and count($components) > 1) {

        // E.g. "1:45" => 105 minutes.
        $hours = $components[0];
        $minutes = $components[1];
        return $hours * 60.0 + $minutes;
    } else {
        // E.g. "1.75" => 105 minutes.
        return round($hours * 60.0);
    }
}

/**
 * Turn undefined manager messages into empty strings and deal with checkboxes
 */
function facetoface_fix_settings($facetoface) {

    if (empty($facetoface->emailmanagerconfirmation)) {
        $facetoface->confirmationinstrmngr = null;
    }
    if (empty($facetoface->emailmanagerreminder)) {
        $facetoface->reminderinstrmngr = null;
    }
    if (empty($facetoface->emailmanagercancellation)) {
        $facetoface->cancellationinstrmngr = null;
    }
    if (empty($facetoface->usercalentry)) {
        $facetoface->usercalentry = 0;
    }
    if (empty($facetoface->thirdpartywaitlist)) {
        $facetoface->thirdpartywaitlist = 0;
    }
    if (empty($facetoface->approvalreqd)) {
        $facetoface->approvalreqd = 0;
    }
}

/**
 * Given an object containing all the necessary data, (defined by the
 * form in mod.html) this function will create a new instance and
 * return the id number of the new instance.
 */
function facetoface_add_instance($facetoface) {
    global $DB;

    $facetoface->timemodified = time();
    facetoface_fix_settings($facetoface);
    if ($facetoface->id = $DB->insert_record('facetoface', $facetoface)) {
        facetoface_grade_item_update($facetoface);
    }

    // Update any calendar entries.
    if ($sessions = facetoface_get_sessions($facetoface->id)) {
        foreach ($sessions as $session) {
            facetoface_update_calendar_entries($session, $facetoface);
        }
    }

    return $facetoface->id;
}

/**
 * Given an object containing all the necessary data, (defined by the
 * form in mod.html) this function will update an existing instance
 * with new data.
 */
function facetoface_update_instance($facetoface, $instanceflag = true) {
    global $DB;

    if ($instanceflag) {
        $facetoface->id = $facetoface->instance;
    }

    facetoface_fix_settings($facetoface);
    if ($return = $DB->update_record('facetoface', $facetoface)) {
        facetoface_grade_item_update($facetoface);

        // Update any calendar entries.
        if ($sessions = facetoface_get_sessions($facetoface->id)) {
            foreach ($sessions as $session) {
                facetoface_update_calendar_entries($session, $facetoface);
            }
        }
    }

    return $return;
}

/**
 * Given an ID of an instance of this module, this function will
 * permanently delete the instance and any data that depends on it.
 */
function facetoface_delete_instance($id) {
    global $CFG, $DB;

    if (!$facetoface = $DB->get_record('facetoface', array('id' => $id))) {
        return false;
    }

    $result = true;
    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records_select(
        'facetoface_signups_status',
        "signupid IN
        (
            SELECT
            id
            FROM
    {facetoface_signups}
    WHERE
    sessionid IN
    (
        SELECT
        id
        FROM
    {facetoface_sessions}
    WHERE
    facetoface = ? ))
    ", array($facetoface->id));

    $DB->delete_records_select('facetoface_signups', "sessionid IN (SELECT id FROM {facetoface_sessions} WHERE facetoface = ?)", array($facetoface->id));
    $DB->delete_records_select('facetoface_sessions_dates', "sessionid in (SELECT id FROM {facetoface_sessions} WHERE facetoface = ?)", array($facetoface->id));
    $DB->delete_records('facetoface_sessions', array('facetoface' => $facetoface->id));
    $DB->delete_records('facetoface', array('id' => $facetoface->id));
    $DB->delete_records('event', array('modulename' => 'facetoface', 'instance' => $facetoface->id)); // Course events.
    $DB->delete_records('event', array('modulename' => '0', 'eventtype' => 'facetofacesession', 'instance' => $facetoface->id)); // User events and Site events.
    facetoface_grade_item_delete($facetoface);
    $transaction->allow_commit();

    return $result;
}

/**
 * Prepare the user data to go into the database.
 */
function cleanup_session_data($session) {

    // Convert hours (expressed like "1.75" or "2" or "3.5") to minutes.
    $session->duration = facetoface_hours_to_minutes($session->duration);

    // Only numbers allowed here.
    $session->capacity = preg_replace('/[^\d]/', '', $session->capacity);
    $maxcap = 100000;
    if ($session->capacity < 1) {
        $session->capacity = 1;
    } else if ($session->capacity > $maxcap) {
        $session->capacity = $maxcap;
    }

    // Get the decimal point separator.
    setlocale(LC_MONETARY, get_string('locale', 'langconfig'));
    $localeinfo = localeconv();
    $symbol = $localeinfo['decimal_point'];
    if (empty($symbol)) {

        // Cannot get the locale information, default to en_US.UTF-8.
        $symbol = '.';
    }

    // Only numbers or decimal separators allowed here.
    $session->normalcost = round(preg_replace("/[^\d$symbol]/", '', $session->normalcost));
    $session->discountcost = round(preg_replace("/[^\d$symbol]/", '', $session->discountcost));

    return $session;
}

/**
 * Create a new entry in the facetoface_sessions table
 */
function facetoface_add_session($session, $sessiondates) {
    global $USER, $DB;

    $session->timecreated = time();
    $session = cleanup_session_data($session);

    $eventname = $DB->get_field('facetoface', 'name,id', array('id' => $session->facetoface));

    $session->id = $DB->insert_record('facetoface_sessions', $session);

    if (empty($sessiondates)) {

        // Insert a dummy date record.
        $date = new stdClass();
        $date->sessionid = $session->id;
        $date->timestart = 0;
        $date->timefinish = 0;

        $DB->insert_record('facetoface_sessions_dates', $date);
    } else {
        foreach ($sessiondates as $date) {
            $date->sessionid = $session->id;
            $DB->insert_record('facetoface_sessions_dates', $date);
        }
    }

    // Create any calendar entries.
    $session->sessiondates = $sessiondates;
    facetoface_update_calendar_entries($session);

    return $session->id;
}

/**
 * Modify an entry in the facetoface_sessions table
 */
function facetoface_update_session($session, $sessiondates) {
    global $DB;

    $session->timemodified = time();
    $session = cleanup_session_data($session);

    $transaction = $DB->start_delegated_transaction();
    $DB->update_record('facetoface_sessions', $session);
    $DB->delete_records('facetoface_sessions_dates', array('sessionid' => $session->id));

    if (empty($sessiondates)) {

        // Insert a dummy date record.
        $date = new stdClass();
        $date->sessionid = $session->id;
        $date->timestart = 0;
        $date->timefinish = 0;
        $DB->insert_record('facetoface_sessions_dates', $date);
    } else {
        foreach ($sessiondates as $date) {
            $date->sessionid = $session->id;
            $DB->insert_record('facetoface_sessions_dates', $date);
        }
    }

    // Update any calendar entries.
    $session->sessiondates = $sessiondates;
    facetoface_update_calendar_entries($session);
    $transaction->allow_commit();

    return facetoface_update_attendees($session);
}

/**
 * Update calendar entries for a given session
 *
 * @param int $session ID of session to update event for
 * @param int $facetoface ID of facetoface activity (optional)
 */
function facetoface_update_calendar_entries($session, $facetoface=null) {
    global $USER, $DB;

    if (empty($facetoface)) {
        $facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface));
    }

    // Remove from all calendars.
    facetoface_delete_user_calendar_events($session, 'booking');
    facetoface_delete_user_calendar_events($session, 'session');
    facetoface_remove_session_from_calendar($session, 0); // Session user event for session creator.
    facetoface_remove_session_from_calendar($session, $facetoface->course); // Session course event.
    facetoface_remove_session_from_calendar($session, SITEID); // Session site event.

    if (empty($facetoface->showoncalendar) && empty($facetoface->usercalentry)) {
        return true;
    }

    // Add to NEW calendartype.
    if ($facetoface->usercalentry) {

        // Get ALL enrolled/booked users.
        $users = facetoface_get_attendees($session->id);
        // If session creator is not enrolled in the course, add the session to his/her events user calendar.
        if (!in_array($USER->id, $users)) {
            facetoface_add_session_to_calendar($session, $facetoface, 'user', $USER->id, 'session');
        }

        foreach ($users as $user) {
            $eventtype = $user->statuscode == MDL_F2F_STATUS_BOOKED ? 'booking' : 'session';
            facetoface_add_session_to_calendar($session, $facetoface, 'user', $user->id, $eventtype);
        }
    }

    if ($facetoface->showoncalendar == F2F_CAL_COURSE) {
        facetoface_add_session_to_calendar($session, $facetoface, 'course', $USER->id);
    } else if ($facetoface->showoncalendar == F2F_CAL_SITE) {
        facetoface_add_session_to_calendar($session, $facetoface, 'site', $USER->id);
    }

    return true;
}

/**
 * Update attendee list status' on booking size change
 */
function facetoface_update_attendees($session) {
    global $USER, $DB;

    // Get facetoface.
    $facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface));

    // Get course.
    $course = $DB->get_record('course', array('id' => $facetoface->course));

    // Update user status'.
    $users = facetoface_get_attendees($session->id);

    if ($users) {

        // No/deleted session dates.
        if (empty($session->datetimeknown)) {

            // Convert any bookings to waitlists.
            foreach ($users as $user) {
                if ($user->statuscode == MDL_F2F_STATUS_BOOKED) {

                    if (!facetoface_user_signup($session, $facetoface, $course, $user->discountcode, $user->notificationtype, MDL_F2F_STATUS_WAITLISTED, $user->id)) {
                        return false;
                    }
                }
            }
        } else {

            // Session dates exist.
            // Convert earliest signed up users to booked, and make the rest waitlisted.
            $capacity = $session->capacity;

            // Count number of booked users.
            $booked = 0;
            foreach ($users as $user) {
                if ($user->statuscode == MDL_F2F_STATUS_BOOKED) {
                    $booked++;
                }
            }

            // If booked less than capacity, book some new users.
            if ($booked < $capacity) {
                foreach ($users as $user) {
                    if ($booked >= $capacity) {
                        break;
                    }

                    if ($user->statuscode == MDL_F2F_STATUS_WAITLISTED) {

                        if (!facetoface_user_signup($session, $facetoface, $course, $user->discountcode, $user->notificationtype, MDL_F2F_STATUS_BOOKED, $user->id)) {
                            return false;
                        }
                        $booked++;
                    }
                }
            }
        }
    }

    return $session->id;
}

/**
 * Return an array of all facetoface activities in the current course
 */
function facetoface_get_facetoface_menu() {
    global $CFG, $DB;

    if ($facetofaces = $DB->get_records_sql("SELECT f.id, c.shortname, f.name
                                            FROM {course} c, {facetoface} f
                                            WHERE c.id = f.course
                                            ORDER BY c.shortname, f.name")) {
        $i = 1;
        foreach ($facetofaces as $facetoface) {
            $f = $facetoface->id;
            $facetofacemenu[$f] = $facetoface->shortname . ' --- ' . format_string($facetoface->name);
            $i++;
        }

        return $facetofacemenu;

    } else {
        return '';
    }
}

/**
 * Delete entry from the facetoface_sessions table along with all
 * related details in other tables
 *
 * @param object $session Record from facetoface_sessions
 */
function facetoface_delete_session($session) {
    global $CFG, $DB;

    $facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface));

    // Cancel user signups (and notify users).
    $signedupusers = $DB->get_records_sql(
        "
            SELECT DISTINCT
                userid
            FROM
                {facetoface_signups} s
            LEFT JOIN
                {facetoface_signups_status} ss
             ON ss.signupid = s.id
            WHERE
                s.sessionid = ?
            AND ss.superceded = 0
            AND ss.statuscode >= ?
        ", array($session->id, MDL_F2F_STATUS_REQUESTED));

    if ($signedupusers and count($signedupusers) > 0) {
        foreach ($signedupusers as $user) {
            if (facetoface_user_cancel($session, $user->userid, true)) {
                facetoface_send_cancellation_notice($facetoface, $session, $user->userid);
            } else {
                return false; // Cannot rollback since we notified users already.
            }
        }
    }

    $transaction = $DB->start_delegated_transaction();

    // Remove entries from user calendars.
    $DB->delete_records_select('event', "modulename = '0' AND
                                         eventtype like 'facetoface%' AND
                                         courseid = 0 AND instance = ?",
                                         array($facetoface->id));

    // Remove entry from course calendar.
    facetoface_remove_session_from_calendar($session, $facetoface->course);

    // Remove entry from site-wide calendar.
    facetoface_remove_session_from_calendar($session, SITEID);

    // Delete session details.
    $DB->delete_records('facetoface_sessions', array('id' => $session->id));
    $DB->delete_records('facetoface_sessions_dates', array('sessionid' => $session->id));
    $DB->delete_records_select(
        'facetoface_signups_status',
        "signupid IN
        (
            SELECT
                id
            FROM
                {facetoface_signups}
            WHERE
                sessionid = {$session->id}
        )
        ");
    $DB->delete_records('facetoface_signups', array('sessionid' => $session->id));
    $transaction->allow_commit();

    return true;
}

/**
 * Substitute the placeholders in email templates for the actual data
 *
 * Expects the following parameters in the $data object:
 * - datetimeknown
 * - details
 * - discountcost
 * - duration
 * - normalcost
 * - sessiondates
 *
 * @access  public
 * @param   string  $msg            Email message
 * @param   string  $facetofacename F2F name
 * @param   int     $reminderperiod Num business days before event to send reminder
 * @param   obj     $user           The subject of the message
 * @param   obj     $data           Session data
 * @param   int     $sessionid      Session ID
 * @return  string
 */
function facetoface_email_substitutions($msg, $facetofacename, $reminderperiod, $user, $data, $sessionid) {
    global $CFG, $DB;

    if (empty($msg)) {
        return '';
    }

    if ($data->datetimeknown) {

        // Scheduled session.
        $sessiondate = userdate($data->sessiondates[0]->timestart, get_string('strftimedate'));
        $starttime = userdate($data->sessiondates[0]->timestart, get_string('strftimetime'));
        $finishtime = userdate($data->sessiondates[0]->timefinish, get_string('strftimetime'));

        $alldates = '';
        foreach ($data->sessiondates as $date) {
            if ($alldates != '') {
                $alldates .= "\n";
            }
            $alldates .= userdate($date->timestart, get_string('strftimedate')).', ';
            $alldates .= userdate($date->timestart, get_string('strftimetime')).
                ' to '.userdate($date->timefinish, get_string('strftimetime'));
        }
    } else {

        // Wait-listed session.
        $sessiondate = get_string('unknowndate', 'facetoface');
        $alldates    = get_string('unknowndate', 'facetoface');
        $starttime   = get_string('unknowntime', 'facetoface');
        $finishtime  = get_string('unknowntime', 'facetoface');
    }

    $msg = str_replace(get_string('placeholder:facetofacename', 'facetoface'), $facetofacename, $msg);
    $msg = str_replace(get_string('placeholder:firstname', 'facetoface'), $user->firstname, $msg);
    $msg = str_replace(get_string('placeholder:lastname', 'facetoface'), $user->lastname, $msg);
    $msg = str_replace(get_string('placeholder:cost', 'facetoface'), facetoface_cost($user->id, $sessionid, $data, false), $msg);
    $msg = str_replace(get_string('placeholder:alldates', 'facetoface'), $alldates, $msg);
    $msg = str_replace(get_string('placeholder:sessiondate', 'facetoface'), $sessiondate, $msg);
    $msg = str_replace(get_string('placeholder:starttime', 'facetoface'), $starttime, $msg);
    $msg = str_replace(get_string('placeholder:finishtime', 'facetoface'), $finishtime, $msg);
    $msg = str_replace(get_string('placeholder:duration', 'facetoface'), format_duration($data->duration), $msg);
    if (empty($data->details)) {
        $msg = str_replace(get_string('placeholder:details', 'facetoface'), '', $msg);
    } else {
        $msg = str_replace(get_string('placeholder:details', 'facetoface'), html_to_text(format_text($data->details)), $msg);
    }
    $msg = str_replace(get_string('placeholder:reminderperiod', 'facetoface'), $reminderperiod, $msg);

    // Replace more meta data.
    $msg = str_replace(get_string('placeholder:attendeeslink', 'facetoface'), $CFG->wwwroot . '/mod/facetoface/attendees.php?s=' . $sessionid, $msg);

    // Custom session fields (they look like "session:shortname" in the templates).
    $customfields = facetoface_get_session_customfields();
    $customdata = $DB->get_records('facetoface_session_data', array('sessionid' => $sessionid), '', 'fieldid, data');
    foreach ($customfields as $field) {
        $placeholder = "[session:{$field->shortname}]";
        $value = '';
        if (!empty($customdata[$field->id])) {
            if (CUSTOMFIELD_TYPE_MULTISELECT == $field->type) {
                $value = str_replace(CUSTOMFIELD_DELIMITER, ', ', $customdata[$field->id]->data);
            } else {
                $value = $customdata[$field->id]->data;
            }
        }

        $msg = str_replace($placeholder, $value, $msg);
    }

    return $msg;
}

/**
 * Function to be run periodically according to the moodle cron
 * Finds all facetoface notifications that have yet to be mailed out, and mails them.
 */
function facetoface_cron() {
    global $CFG, $USER, $DB;

    $signupsdata = facetoface_get_unmailed_reminders();
    if (!$signupsdata) {
        echo "\n" . get_string('noremindersneedtobesent', 'facetoface') . "\n";
        return true;
    }

    $timenow = time();
    foreach ($signupsdata as $signupdata) {
        if (facetoface_has_session_started($signupdata, $timenow)) {

            // Too late, the session already started.
            // Mark the reminder as being sent already.
            $newsubmission = new stdClass();
            $newsubmission->id = $signupdata->id;
            $newsubmission->mailedreminder = 1; // Magic number to show that it was not actually sent.
            if (!$DB->update_record('facetoface_signups', $newsubmission)) {
                echo "ERROR: could not update mailedreminder for submission ID $signupdata->id";
            }
            continue;
        }

        $earlieststarttime = $signupdata->sessiondates[0]->timestart;
        foreach ($signupdata->sessiondates as $date) {
            if ($date->timestart < $earlieststarttime) {
                $earlieststarttime = $date->timestart;
            }
        }

        $reminderperiod = $signupdata->reminderperiod;

        // Convert the period from business days (no weekends) to calendar days.
        for ($reminderday = 0; $reminderday < $reminderperiod + 1; $reminderday++) {
            $reminderdaytime = $earlieststarttime - ($reminderday * 24 * 3600);

            // Use %w instead of %u for Windows compatability.
            $reminderdaycheck = userdate($reminderdaytime, '%w');

            // Note w runs from Sun=0 to Sat=6.
            if ($reminderdaycheck == 0 || $reminderdaycheck == 6) {

                /*
                 * Saturdays and Sundays are not included in the
                 * reminder period as entered by the user, extend
                 * that period by 1
                */
                $reminderperiod++;
            }
        }

        $remindertime = $earlieststarttime - ($reminderperiod * 24 * 3600);
        if ($timenow < $remindertime) {

            // Too early to send reminder.
            continue;
        }

        if (!$user = $DB->get_record('user', array('id' => $signupdata->userid))) {
            continue;
        }

        // Hack to make sure that the timezone and languages are set properly in emails.
        // (i.e. it uses the language and timezone of the recipient of the email).
        $USER->lang = $user->lang;
        $USER->timezone = $user->timezone;
        if (!$course = $DB->get_record('course', array('id' => $signupdata->course))) {
            continue;
        }
        if (!$facetoface = $DB->get_record('facetoface', array('id' => $signupdata->facetofaceid))) {
            continue;
        }

        $postsubject = '';
        $posttext = '';
        $posttextmgrheading = '';
        if (empty($signupdata->mailedreminder)) {
            $postsubject = $facetoface->remindersubject;
            $posttext = $facetoface->remindermessage;
            $posttextmgrheading = $facetoface->reminderinstrmngr;
        }

        if (empty($posttext)) {

            // The reminder message is not set, don't send anything.
            continue;
        }

        $postsubject = facetoface_email_substitutions($postsubject, format_string($signupdata->facetofacename), $signupdata->reminderperiod,
                                                      $user, $signupdata, $signupdata->sessionid);
        $posttext = facetoface_email_substitutions($posttext, format_string($signupdata->facetofacename), $signupdata->reminderperiod,
                                                   $user, $signupdata, $signupdata->sessionid);
        $posttextmgrheading = facetoface_email_substitutions($posttextmgrheading, $signupdata->facetofacename, $signupdata->reminderperiod,
                                                             $user, $signupdata, $signupdata->sessionid);

        $posthtml = ''; // FIXME.
        if ($fromaddress = get_config(null, 'facetoface_fromaddress')) {
            $from = new stdClass();
            $from->maildisplay = true;
            $from->email = $fromaddress;
        } else {
            $from = null;
        }

        if (email_to_user($user, $from, $postsubject, $posttext, $posthtml)) {
            echo "\n" . get_string('sentreminderuser', 'facetoface') . ": $user->firstname $user->lastname $user->email";

            $newsubmission = new stdClass();
            $newsubmission->id = $signupdata->id;
            $newsubmission->mailedreminder = $timenow;
            if (!$DB->update_record('facetoface_signups', $newsubmission)) {
                echo "ERROR: could not update mailedreminder for submission ID $signupdata->id";
            }

            if (empty($posttextmgrheading)) {
                continue; // No manager message set.
            }

            $managertext = $posttextmgrheading.$posttext;
            $manager = $user;
            $manager->email = facetoface_get_manageremail($user->id);

            if (empty($manager->email)) {
                continue; // Don't know who the manager is.
            }

            // Send email to mamager.
            if (email_to_user($manager, $from, $postsubject, $managertext, $posthtml)) {
                echo "\n".get_string('sentremindermanager', 'facetoface').": $user->firstname $user->lastname $manager->email";
            } else {
                $errormsg = array();
                $errormsg['submissionid'] = $signupdata->id;
                $errormsg['userid'] = $user->id;
                $errormsg['manageremail'] = $manager->email;
                echo get_string('error:cronprefix', 'facetoface').' '.get_string('error:cannotemailmanager', 'facetoface', $errormsg)."\n";
            }
        } else {
            $errormsg = array();
            $errormsg['submissionid'] = $signupdata->id;
            $errormsg['userid'] = $user->id;
            $errormsg['useremail'] = $user->email;
            echo get_string('error:cronprefix', 'facetoface').' '.get_string('error:cannotemailuser', 'facetoface', $errormsg)."\n";
        }
    }

    print "\n";
    return true;
}

/**
 * Returns true if the session has started, that is if one of the
 * session dates is in the past.
 *
 * @param class $session record from the facetoface_sessions table
 * @param integer $timenow current time
 */
function facetoface_has_session_started($session, $timenow) {

    if (!$session->datetimeknown) {
        return false; // No date set.
    }

    foreach ($session->sessiondates as $date) {
        if ($date->timestart < $timenow) {
            return true;
        }
    }

    return false;
}

/**
 * Returns true if the session has started and has not yet finished.
 *
 * @param class $session record from the facetoface_sessions table
 * @param integer $timenow current time
 */
function facetoface_is_session_in_progress($session, $timenow) {
    if (!$session->datetimeknown) {
        return false;
    }
    foreach ($session->sessiondates as $date) {
        if ($date->timefinish > $timenow && $date->timestart < $timenow) {
            return true;
        }
    }

    return false;
}

/**
 * Get all of the dates for a given session
 */
function facetoface_get_session_dates($sessionid) {
    global $DB;

    $ret = array();
    if ($dates = $DB->get_records('facetoface_sessions_dates', array('sessionid' => $sessionid), 'timestart')) {
        $i = 0;
        foreach ($dates as $date) {
            $ret[$i++] = $date;
        }
    }

    return $ret;
}

/**
 * Get a record from the facetoface_sessions table
 *
 * @param integer $sessionid ID of the session
 */
function facetoface_get_session($sessionid) {
    global $DB;

    $session = $DB->get_record('facetoface_sessions', array('id' => $sessionid));
    if ($session) {
        $session->sessiondates = facetoface_get_session_dates($sessionid);
        $session->duration = facetoface_minutes_to_hours($session->duration);
    }

    return $session;
}

/**
 * Get all records from facetoface_sessions for a given facetoface activity and location
 *
 * @param integer $facetofaceid ID of the activity
 * @param string $location location filter (optional)
 */
function facetoface_get_sessions($facetofaceid, $location='') {
    global $CFG, $DB;

    $fromclause = "FROM {facetoface_sessions} s";
    $locationwhere = '';
    $locationparams = array();
    if (!empty($location)) {
        $fromclause = "FROM {facetoface_session_data} d
                       JOIN {facetoface_sessions} s ON s.id = d.sessionid";
        $locationwhere .= " AND d.data = ?";
        $locationparams[] = $location;
    }
    $sessions = $DB->get_records_sql("SELECT s.*
                                   $fromclause
                        LEFT OUTER JOIN (SELECT sessionid, min(timestart) AS mintimestart
                                           FROM {facetoface_sessions_dates} GROUP BY sessionid) m ON m.sessionid = s.id
                                  WHERE s.facetoface = ?
                                        $locationwhere
                               ORDER BY s.datetimeknown, m.mintimestart", array_merge(array($facetofaceid), $locationparams));

    if ($sessions) {
        foreach ($sessions as $key => $value) {
            $sessions[$key]->duration = facetoface_minutes_to_hours($sessions[$key]->duration);
            $sessions[$key]->sessiondates = facetoface_get_session_dates($value->id);
        }
    }

    return $sessions;
}

/**
 * Get a grade for the given user from the gradebook.
 *
 * @param integer $userid       ID of the user
 * @param integer $courseid     ID of the course
 * @param integer $facetofaceid ID of the Face-to-face activity
 *
 * @returns object String grade and the time that it was graded
 */
function facetoface_get_grade($userid, $courseid, $facetofaceid) {

    $ret = new stdClass();
    $ret->grade = 0;
    $ret->dategraded = 0;

    $gradinginfo = grade_get_grades($courseid, 'mod', 'facetoface', $facetofaceid, $userid);
    if (!empty($gradinginfo->items)) {
        $ret->grade = $gradinginfo->items[0]->grades[$userid]->str_grade;
        $ret->dategraded = $gradinginfo->items[0]->grades[$userid]->dategraded;
    }

    return $ret;
}

/**
 * Get list of users attending a given session
 *
 * @access public
 * @param integer Session ID
 * @return array
 */
function facetoface_get_attendees($sessionid) {
    global $CFG, $DB;

    $usernamefields = facetoface_get_all_user_name_fields(true, 'u');
    $records = $DB->get_records_sql("
        SELECT u.id, {$usernamefields},
            u.email,
            su.id AS submissionid,
            s.discountcost,
            su.discountcode,
            su.notificationtype,
            f.id AS facetofaceid,
            f.course,
            ss.grade,
            ss.statuscode,
            sign.timecreated
        FROM
            {facetoface} f
        JOIN
            {facetoface_sessions} s
         ON s.facetoface = f.id
        JOIN
            {facetoface_signups} su
         ON s.id = su.sessionid
        JOIN
            {facetoface_signups_status} ss
         ON su.id = ss.signupid
        LEFT JOIN
            (
            SELECT
                ss.signupid,
                MAX(ss.timecreated) AS timecreated
            FROM
                {facetoface_signups_status} ss
            INNER JOIN
                {facetoface_signups} s
             ON s.id = ss.signupid
            AND s.sessionid = ?
            WHERE
                ss.statuscode IN (?,?)
            GROUP BY
                ss.signupid
            ) sign
         ON su.id = sign.signupid
        JOIN
            {user} u
         ON u.id = su.userid
        WHERE
            s.id = ?
        AND ss.superceded != 1
        AND ss.statuscode >= ?
        ORDER BY
            sign.timecreated ASC,
            ss.timecreated ASC
    ", array ($sessionid, MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_WAITLISTED, $sessionid, MDL_F2F_STATUS_APPROVED));

    return $records;
}

/**
 * Get a single attendee of a session
 *
 * @access public
 * @param integer Session ID
 * @param integer User ID
 * @return false|object
 */
function facetoface_get_attendee($sessionid, $userid) {
    global $CFG, $DB;

    $record = $DB->get_record_sql("
        SELECT
            u.id,
            su.id AS submissionid,
            u.firstname,
            u.lastname,
            u.email,
            s.discountcost,
            su.discountcode,
            su.notificationtype,
            f.id AS facetofaceid,
            f.course,
            ss.grade,
            ss.statuscode
        FROM
            {facetoface} f
        JOIN
            {facetoface_sessions} s
         ON s.facetoface = f.id
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
        AND u.id = ?
    ", array($sessionid, $userid));

    if (!$record) {
        return false;
    }

    return $record;
}

/**
 * Return all user fields to include in exports
 */
function facetoface_get_userfields() {
    global $CFG;

    static $userfields = null;
    if (null == $userfields) {
        $userfields = array();

        if (function_exists('grade_export_user_fields')) {
            $fieldnames = grade_export_user_fields();
            foreach ($fieldnames as $key => $obj) {
                $userfields[$obj->shortname] = $obj->fullname;
            }
        } else {
            // Set default fields if the grade export patch is not detected (see MDL-17346).
            $fieldnames = array('firstname', 'lastname', 'email', 'city',
                                'idnumber', 'institution', 'department', 'address');
            foreach ($fieldnames as $shortname) {
                $userfields[$shortname] = get_string($shortname);
            }
            $userfields['managersemail'] = get_string('manageremail', 'facetoface');
        }
    }

    return $userfields;
}

/**
 * Download the list of users attending at least one of the sessions
 * for a given facetoface activity
 */
function facetoface_download_attendance($facetofacename, $facetofaceid, $location, $format) {
    global $CFG;

    $timenow = time();
    $timeformat = str_replace(' ', '_', get_string('strftimedate', 'langconfig'));
    $downloadfilename = clean_filename($facetofacename.'_'.userdate($timenow, $timeformat));

    $dateformat = 0;
    if ('ods' === $format) {

        // OpenDocument format (ISO/IEC 26300).
        require_once($CFG->dirroot.'/lib/odslib.class.php');
        $downloadfilename .= '.ods';
        $workbook = new MoodleODSWorkbook('-');
    } else {

        // Excel format.
        require_once($CFG->dirroot.'/lib/excellib.class.php');
        $downloadfilename .= '.xls';
        $workbook = new MoodleExcelWorkbook('-');
        $dateformat = $workbook->add_format();
        $dateformat->set_num_format('d mmm yy'); // TODO: use format specified in language pack.
    }

    $workbook->send($downloadfilename);
    $worksheet = $workbook->add_worksheet('attendance');
    facetoface_write_worksheet_header($worksheet);
    facetoface_write_activity_attendance($worksheet, 1, $facetofaceid, $location, '', '', $dateformat);
    $workbook->close();
    exit;
}

/**
 * Download the list of users attending at least one of the sessions
 * for a given facetoface activity
 */
function facetoface_download_attendees($facetofacename, $session, $attendees, $format) {
    global $CFG, $DB;

    $timenow = time();
    $timeformat = str_replace(' ', '_', get_string('strftimedate', 'langconfig'));
    $downloadfilename = clean_filename($facetofacename.'_'.userdate($timenow, $timeformat));

    $dateformat = 0;
    if ('ods' === $format) {

        // OpenDocument format (ISO/IEC 26300).
        require_once($CFG->dirroot.'/lib/odslib.class.php');
        $downloadfilename .= '.ods';
        $workbook = new MoodleODSWorkbook('-');
    } else {

        // Excel format.
        require_once($CFG->dirroot.'/lib/excellib.class.php');
        $downloadfilename .= '.xlsx';
        $workbook = new MoodleExcelWorkbook('-');
        $dateformat = $workbook->add_format();
        $dateformat->set_num_format('d mmm yy'); // TODO: use format specified in language pack.
    }

    $workbook->send($downloadfilename);
    $worksheet = $workbook->add_worksheet('attendees');

    $row = 0; // Starting worksheet row.
    $column = 0; // Starting worksheet column.
    $worksheet->write_string($row++, $column, $facetofacename, ['size' => 14, 'bold' => 1]); // Session name.
    if (empty($session->datetimeknown)) {
        $worksheet->write_string($row++, $column, get_string('status_waitlisted', 'facetoface'), ['size' => 12, 'bold' => 1]);
    } else {
        foreach ($session->sessiondates as $forsession) {
            $worksheet->write_string($row++, $column,
                    userdate($forsession->timestart, get_string('strftimedatetime')) . ' - ' .
                    userdate($forsession->timefinish, get_string('strftimedatetime')),
                    ['size' => 12, 'bold' => 1]
                );
        }
    }
    $row++;

    $fieldnames = 'firstname,lastname,' . get_config(null, 'facetoface_attendeesexportfields');
    $fieldnames = explode(',', rtrim($fieldnames, ','));

    // Export row of column headings.

    $profilefields = profile_get_custom_fields();
    foreach ($profilefields as $key => $field) {
        $field->name = format_string($field->name);
        $profilefield['profile_field_' . $field->shortname] = $field;
        unset($profilefields[$key]);
    }
    foreach ($fieldnames as $shortname) {
        if (substr( $shortname, 0, 14 ) === 'profile_field_') {
            $fieldname = $profilefield[$shortname]->name;
        } else {
            $fieldname = $shortname == 'lang' ? get_string('language') : get_string($shortname);
        }
        $worksheet->write_string($row, $column++, $fieldname, ['bold' => 1, 'border' => 1]);
    }
    // Current status.
    $worksheet->write_string($row, $column++, get_string('currentstatus', 'facetoface'), ['bold' => 1, 'border' => 1]);

    // Export row of data for each attendee.

    foreach ($attendees as $attendee) {
        $row++;
        $column = 0;

        // Load user profile fields.
        $user = $DB->get_record("user", ['id' => $attendee->id]);

        // Load custom user profile fields.
        $user->profile = (array)profile_user_record($user->id, false);

        // Prefix all custom profile field shortnames with 'profile_field_'.
        $user->profile = array_combine(
            array_map(function($key) {
                return 'profile_field_' . $key;
            }, array_keys($user->profile)), $user->profile
        );

        foreach ($fieldnames as $shortname) {
            $format = ['border' => 1, 'v_align' => 'top'];
            if (property_exists($attendee, $shortname)) {
                // Get the data from the attendees profile field.
                $data = $attendee->$shortname;
                if ($shortname == 'email') {
                    $format['underline'] = 1;
                    $format['color'] = 'blue';
                    $worksheet->write_url($row, $column++, 'mailto:' . $data, $format);
                    continue;
                }
            } else if (property_exists($user, $shortname)) {
                // Get the data from the user profile field.
                $data = $user->$shortname;
            } else if (array_key_exists($shortname, $user->profile)) {
                // Get the data from the custom user profile field.
                $data = $user->profile[$shortname];
                switch ($profilefield[$shortname]->datatype) { // Format data for some field types.
                    case 'textarea':
                        $data = html_to_text($data, 132);
                        $format['text_wrap'] = 1;
                        break;
                    case 'menu':
                        $data = empty(format_string($data));
                        break;
                    case 'checkbox':
                        // 1 = Yes, 0 = No
                        $data = empty($data) ?  "\u{2610}" : "\u{2611}";
                        $format['align'] = 'center';
                        break;
                    case 'datetime':
                        $worksheet->write_date($row, $column++, $data, $format);
                        continue 2;
                    case 'social' && $profilefield[$shortname]->param1 == 'url':
                        if (strstr($data, '://') === false) {
                            $data = 'https://' . $data;
                        }
                        $format['underline'] = 1;
                        $format['color'] = 'blue';
                        $worksheet->write_url($row, $column++, $data, $format);
                        continue 2;
                }
            } else {
                // This could happen if a custom profile field was deleted and the list of selected export fields was not updated.
                $data = '';
            }

            if (substr($data, 0, 1) != '0' && is_numeric($data)) {
                $worksheet->write_number($row, $column++, $data, $format);
            } else {
                $worksheet->write_string($row, $column++, $data, $format);
            }
        }
        $worksheet->write_string($row, $column++, get_string('status_'.facetoface_get_status($attendee->statuscode), 'facetoface'),
                ['border' => 1, 'v_align' => 'top']);
    }
    $workbook->close();
    exit;
}

/**
 * Add the appropriate column headers to the given worksheet
 *
 * @param object $worksheet  The worksheet to modify (passed by reference)
 * @returns integer The index of the next column
 */
function facetoface_write_worksheet_header(&$worksheet) {
    $pos = 0;
    $customfields = facetoface_get_session_customfields();
    foreach ($customfields as $field) {
        if (!empty($field->showinsummary)) {
            $worksheet->write_string(0, $pos++, $field->name);
        }
    }
    $worksheet->write_string(0, $pos++, get_string('date', 'facetoface'));
    $worksheet->write_string(0, $pos++, get_string('timestart', 'facetoface'));
    $worksheet->write_string(0, $pos++, get_string('timefinish', 'facetoface'));
    $worksheet->write_string(0, $pos++, get_string('duration', 'facetoface'));
    $worksheet->write_string(0, $pos++, get_string('status', 'facetoface'));

    if ($trainerroles = facetoface_get_trainer_roles()) {
        foreach ($trainerroles as $role) {
            $worksheet->write_string(0, $pos++, get_string('role').': '.$role->name);
        }
    }

    $userfields = facetoface_get_userfields();
    foreach ($userfields as $shortname => $fullname) {
        $worksheet->write_string(0, $pos++, $fullname);
    }

    $worksheet->write_string(0, $pos++, get_string('attendance', 'facetoface'));
    $worksheet->write_string(0, $pos++, get_string('datesignedup', 'facetoface'));

    return $pos;
}

/**
 * Write in the worksheet the given facetoface attendance information
 * filtered by location.
 *
 * This function includes lots of custom SQL because it's otherwise
 * way too slow.
 *
 * @param object  $worksheet    Currently open worksheet
 * @param integer $startingrow  Index of the starting row (usually 1)
 * @param integer $facetofaceid ID of the facetoface activity
 * @param string  $location     Location to filter by
 * @param string  $coursename   Name of the course (optional)
 * @param string  $activityname Name of the facetoface activity (optional)
 * @param object  $dateformat   Use to write out dates in the spreadsheet
 * @returns integer Index of the last row written
 */
function facetoface_write_activity_attendance(&$worksheet, $startingrow, $facetofaceid, $location,
                                              $coursename, $activityname, $dateformat) {
    global $CFG, $DB;

    $trainerroles = facetoface_get_trainer_roles();
    $userfields = facetoface_get_userfields();
    $customsessionfields = facetoface_get_session_customfields();
    $timenow = time();
    $i = $startingrow;

    $locationcondition = '';
    $locationparam = array();
    if (!empty($location)) {
        $locationcondition = "AND s.location = ?";
        $locationparam = array($location);
    }

    // Fast version of "facetoface_get_attendees()" for all sessions.
    $sessionsignups = array();
    $signups = $DB->get_records_sql("
        SELECT
            su.id AS submissionid,
            s.id AS sessionid,
            u.*,
            f.course AS courseid,
            ss.grade,
            sign.timecreated
        FROM
            {facetoface} f
        JOIN
            {facetoface_sessions} s
         ON s.facetoface = f.id
        JOIN
            {facetoface_signups} su
         ON s.id = su.sessionid
        JOIN
            {facetoface_signups_status} ss
         ON su.id = ss.signupid
        LEFT JOIN
            (
            SELECT
                ss.signupid,
                MAX(ss.timecreated) AS timecreated
            FROM
                {facetoface_signups_status} ss
            INNER JOIN
                {facetoface_signups} s
             ON s.id = ss.signupid
            INNER JOIN
                {facetoface_sessions} se
             ON s.sessionid = se.id
            AND se.facetoface = $facetofaceid
            WHERE
                ss.statuscode IN (?,?)
            GROUP BY
                ss.signupid
            ) sign
         ON su.id = sign.signupid
        JOIN
            {user} u
         ON u.id = su.userid
        WHERE
            f.id = ?
        AND ss.superceded != 1
        AND ss.statuscode >= ?
        ORDER BY
            s.id, u.firstname, u.lastname
    ", array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_WAITLISTED, $facetofaceid, MDL_F2F_STATUS_APPROVED));

    if ($signups) {

        // Get all grades at once.
        $userids = array();
        foreach ($signups as $signup) {
            if ($signup->id > 0) {
                $userids[] = $signup->id;
            }
        }

        foreach ($signups as $signup) {
            $userid = $signup->id;
            if ($customuserfields = facetoface_get_user_customfields($userid, $userfields)) {
                foreach ($customuserfields as $fieldname => $value) {
                    if (!isset($signup->$fieldname)) {
                        $signup->$fieldname = $value;
                    }
                }
            }

            // Set grade.
            if ($signup->grade != null) {
                $signup->grade = number_format($signup->grade, 2);
            } else {
                $signup->grade = '-';
            }

            $sessionsignups[$signup->sessionid][$signup->id] = $signup;
        }
    }

    // Fast version of "facetoface_get_sessions($facetofaceid, $location)".
    $sql = "SELECT d.id as dateid, s.id, s.datetimeknown, s.capacity,
                   s.duration, d.timestart, d.timefinish
              FROM {facetoface_sessions} s
              JOIN {facetoface_sessions_dates} d ON s.id = d.sessionid
              WHERE
                s.facetoface = ?
              AND d.sessionid = s.id
                   $locationcondition
                   ORDER BY s.datetimeknown, d.timestart";

    $sessions = $DB->get_records_sql($sql, array_merge(array($facetofaceid), $locationparam));

    $i = $i - 1; // Will be incremented BEFORE each row is written.
    foreach ($sessions as $session) {
        $status      = get_string('wait-listed', 'facetoface');

        $sessiontrainers = facetoface_get_trainers($session->id);

        if ($session->datetimeknown) {
            if ($session->timestart < $timenow) {
                $status = get_string('sessionover', 'facetoface');
            } else {
                $signupcount = 0;
                if (!empty($sessionsignups[$session->id])) {
                    $signupcount = count($sessionsignups[$session->id]);
                }

                if ($signupcount >= $session->capacity) {
                    $status = get_string('bookingfull', 'facetoface');
                } else {
                    $status = get_string('bookingopen', 'facetoface');
                }
            }
        }

        if (!empty($sessionsignups[$session->id])) {
            foreach ($sessionsignups[$session->id] as $attendee) {
                $i++;
                $j = facetoface_write_activity_attendance_helper($worksheet, $i, $session, $customsessionfields, $status, $dateformat, $session->timestart, $session->timefinish);
                if ($trainerroles) {
                    foreach (array_keys($trainerroles) as $roleid) {
                        if (!empty($sessiontrainers[$roleid])) {
                            $trainers = array();
                            foreach ($sessiontrainers[$roleid] as $trainer) {
                                $trainers[] = fullname($trainer);
                            }

                            $trainers = implode(', ', $trainers);
                        } else {
                            $trainers = '-';
                        }

                        $worksheet->write_string($i, $j++, $trainers);
                    }
                }

                foreach ($userfields as $shortname => $fullname) {
                    $value = '-';
                    if (!empty($attendee->$shortname)) {
                        $value = $attendee->$shortname;
                    }

                    if ('firstaccess' == $shortname || 'lastaccess' == $shortname ||
                        'lastlogin' == $shortname || 'currentlogin' == $shortname) {
                        $worksheet->write_date($i, $j++, (int)$value, $dateformat);
                    } else {
                        $worksheet->write_string($i, $j++, $value);
                    }
                }
                $worksheet->write_string($i, $j++, $attendee->grade);

                $worksheet->write_date($i, $j++, (int)$attendee->timecreated, $dateformat);

                if (!empty($coursename)) {
                    $worksheet->write_string($i, $j++, $coursename);
                }
                if (!empty($activityname)) {
                    $worksheet->write_string($i, $j++, $activityname);
                }
            }
        } else {
            // No one is sign-up, so let's just print the basic info.
            $i++;
            // helper
            $j = facetoface_write_activity_attendance_helper($worksheet, $i, $session, $customsessionfields, $status, $dateformat, $session->timestart, $session->timefinish);

            foreach ($userfields as $unused) {
                $worksheet->write_string($i, $j++, '-');
            }
            $worksheet->write_string($i, $j++, '-');

            if (!empty($coursename)) {
                $worksheet->write_string($i, $j++, $coursename);
            }
            if (!empty($activityname)) {
                $worksheet->write_string($i, $j++, $activityname);
            }
        }
    }

    return $i;
}

/**
 * Helper function for write_activity_attendance.
 * Could do with further tidying.
 *
 * @param object $worksheet  The worksheet to modify (passed by reference)
 * @param int $i The current row being used.
 * @param object $session
 * @return int The next Column in the sheet.
 */

function facetoface_write_activity_attendance_helper(&$worksheet, $i, $session, $customsessionfields, $status, $dateformat, $starttime, $finishtime) {
    global $DB;

    $j = 0;

    // Custom session fields.
    $customdata = $DB->get_records('facetoface_session_data', array('sessionid' => $session->id), '', 'fieldid, data');
    foreach ($customsessionfields as $field) {
        if (empty($field->showinsummary)) {
            continue; // Skip.
        }

        $data = '-';
        if (!empty($customdata[$field->id])) {
            if (CUSTOMFIELD_TYPE_MULTISELECT == $field->type) {
                $data = str_replace(CUSTOMFIELD_DELIMITER, "\n", $customdata[$field->id]->data);
            } else {
                $data = $customdata[$field->id]->data;
            }
        }
        $worksheet->write_string($i, $j++, $data);
    }

    if (empty($sessiondate)) {
        $worksheet->write_string($i, $j++, $status); // Session date.
    } else {
        if (method_exists($worksheet, 'write_date')) {
            $worksheet->write_date($i, $j++, $sessiondate, $dateformat);
        } else {
            $worksheet->write_string($i, $j++, $sessiondate);
        }
    }
    $worksheet->write_string($i, $j++, userdate($starttime));
    $worksheet->write_string($i, $j++, userdate($finishtime));
    $worksheet->write_number($i, $j++, (int)$session->duration);
    $worksheet->write_string($i, $j++, $status);

    return $j;
}

/**
 * Return an object with all values for a user's custom fields.
 *
 * This is about 15 times faster than the custom field API.
 *
 * @param array $fieldstoinclude Limit the fields returned/cached to these ones (optional)
 */
function facetoface_get_user_customfields($userid, $fieldstoinclude=false) {
    global $CFG, $DB;

    // Cache all lookup.
    static $customfields = null;
    if (null == $customfields) {
        $customfields = array();
    }

    if (!empty($customfields[$userid])) {
        return $customfields[$userid];
    }

    $ret = new stdClass();
    $sql = "SELECT uif.shortname, id.data
              FROM {user_info_field} uif
              JOIN {user_info_data} id ON id.fieldid = uif.id
              WHERE id.userid = ?";

    $customfields = $DB->get_records_sql($sql, array($userid));
    foreach ($customfields as $field) {
        $fieldname = $field->shortname;
        if (false === $fieldstoinclude or !empty($fieldstoinclude[$fieldname])) {
            $ret->$fieldname = $field->data;
        }
    }

    $customfields[$userid] = $ret;
    return $ret;
}

/**
 * Return list of marked submissions that have not been mailed out for currently enrolled students
 */
function facetoface_get_unmailed_reminders() {
    global $CFG, $DB;

    $submissions = $DB->get_records_sql("
        SELECT
            su.*,
            f.course,
            f.id as facetofaceid,
            f.name as facetofacename,
            f.reminderperiod,
            se.duration,
            se.normalcost,
            se.discountcost,
            se.details,
            se.datetimeknown
        FROM
            {facetoface_signups} su
        INNER JOIN
            {facetoface_signups_status} sus
         ON su.id = sus.signupid
        AND sus.superceded = 0
        AND sus.statuscode = ?
        JOIN
            {facetoface_sessions} se
         ON su.sessionid = se.id
        JOIN
            {facetoface} f
         ON se.facetoface = f.id
        WHERE
            su.mailedreminder = 0
        AND se.datetimeknown = 1
    ", array(MDL_F2F_STATUS_BOOKED));

    if ($submissions) {
        foreach ($submissions as $key => $value) {
            $submissions[$key]->duration = facetoface_minutes_to_hours($submissions[$key]->duration);
            $submissions[$key]->sessiondates = facetoface_get_session_dates($value->sessionid);
        }
    }

    return $submissions;
}

/**
 * Add a record to the facetoface submissions table and sends out an
 * email confirmation
 *
 * @param class $session record from the facetoface_sessions table
 * @param class $facetoface record from the facetoface table
 * @param class $course record from the course table
 * @param string $discountcode code entered by the user
 * @param integer $notificationtype type of notifications to send to user
 * @see {{MDL_F2F_INVITE}}
 * @param integer $statuscode Status code to set
 * @param integer $userid user to signup
 * @param bool $notifyuser whether or not to send an email confirmation
 * @param bool $displayerrors whether or not to return an error page on errors
 */
function facetoface_user_signup($session, $facetoface, $course, $discountcode,
                                $notificationtype, $statuscode, $userid = false,
                                $notifyuser = true) {

    global $CFG, $DB;

    // Get user ID.
    if (!$userid) {
        global $USER;
        $userid = $USER->id;
    }

    $return = false;
    $timenow = time();

    // Check to see if a signup already exists.
    if ($existingsignup = $DB->get_record('facetoface_signups', array('sessionid' => $session->id, 'userid' => $userid))) {
        $usersignup = $existingsignup;
    } else {

        // Otherwise, prepare a signup object.
        $usersignup = new stdclass;
        $usersignup->sessionid = $session->id;
        $usersignup->userid = $userid;
    }

    $usersignup->mailedreminder = 0;
    $usersignup->notificationtype = $notificationtype;

    $usersignup->discountcode = trim(strtoupper($discountcode));
    if (empty($usersignup->discountcode)) {
        $usersignup->discountcode = null;
    }

    // Update/insert the signup record.
    if (!empty($usersignup->id)) {
        $success = $DB->update_record('facetoface_signups', $usersignup);
    } else {
        $usersignup->id = $DB->insert_record('facetoface_signups', $usersignup);
        $success = (bool)$usersignup->id;
    }

    if (!$success) {
        throw new moodle_exception('error:couldnotupdatef2frecord', 'facetoface');
        return false;
    }

    // Work out which status to use.

    // If approval not required.
    if (!$facetoface->approvalreqd) {
        $newstatus = $statuscode;
    } else {

        // If approval required.
        // Get current status (if any).
        $currentstatus = $DB->get_field('facetoface_signups_status', 'statuscode', array('signupid' => $usersignup->id, 'superceded' => 0));

        // If approved, then no problem.
        if ($currentstatus == MDL_F2F_STATUS_APPROVED) {
            $newstatus = $statuscode;
        } else if ($session->datetimeknown) {

            // Otherwise, send manager request.
            $newstatus = MDL_F2F_STATUS_REQUESTED;
        } else {
            $newstatus = MDL_F2F_STATUS_WAITLISTED;
        }
    }

    // Update status.
    if (!facetoface_update_signup_status($usersignup->id, $newstatus, $userid)) {
        throw new moodle_exception('error:f2ffailedupdatestatus', 'facetoface');
        return false;
    }

    // Add to user calendar -- if facetoface usercalentry is set to true.
    if ($facetoface->usercalentry) {
        if (in_array($newstatus, array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_WAITLISTED))) {
            facetoface_add_session_to_calendar($session, $facetoface, 'user', $userid, 'booking');
        }
    }

    // Course completion.
    if (in_array($newstatus, array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_WAITLISTED))) {
        $completion = new completion_info($course);
        if ($completion->is_enabled()) {
            $ccdetails = array(
                'course' => $course->id,
                'userid' => $userid,
            );

            $cc = new completion_completion($ccdetails);
            $cc->mark_inprogress($timenow);
        }
    }

    // If session has already started, do not send a notification.
    if (facetoface_has_session_started($session, $timenow)) {
        $notifyuser = false;
    }

    // Send notification.
    if ($notifyuser) {

        // If booked/waitlisted.
        switch ($newstatus) {
            case MDL_F2F_STATUS_BOOKED:
                $error = facetoface_send_confirmation_notice($facetoface, $session, $userid, $notificationtype, false);
                break;

            case MDL_F2F_STATUS_WAITLISTED:
                $error = facetoface_send_confirmation_notice($facetoface, $session, $userid, $notificationtype, true);
                break;

            case MDL_F2F_STATUS_REQUESTED:
                $error = facetoface_send_request_notice($facetoface, $session, $userid);
                break;
        }

        if (!empty($error)) {
            throw new moodle_exception($error, 'facetoface');
            return false;
        }

        if (!$DB->update_record('facetoface_signups', $usersignup)) {
            throw new moodle_exception('error:couldnotupdatef2frecord', 'facetoface');
            return false;
        }
    }

    return true;
}

/**
 * Send booking request notice to user and their manager
 *
 * @param  object $facetoface Facetoface instance
 * @param  object $session    Session instance
 * @param  int    $userid     ID of user requesting booking
 * @return string Error string, empty on success
 */
function facetoface_send_request_notice($facetoface, $session, $userid) {
    global $DB;

    if (!$manageremail = facetoface_get_manageremail($userid)) {
        return 'error:nomanagersemailset';
    }

    $user = $DB->get_record('user', array('id' => $userid));
    if (!$user) {
        return 'error:invaliduserid';
    }

    if ($fromaddress = get_config(null, 'facetoface_fromaddress')) {
        $from = new stdClass();
        $from->maildisplay = true;
        $from->email = $fromaddress;
    } else {
        $from = null;
    }

    $postsubject = facetoface_email_substitutions(
            $facetoface->requestsubject,
            format_string($facetoface->name),
            $facetoface->reminderperiod,
            $user,
            $session,
            $session->id
    );

    $posttext = facetoface_email_substitutions(
            $facetoface->requestmessage,
            format_string($facetoface->name),
            $facetoface->reminderperiod,
            $user,
            $session,
            $session->id
    );

    $posttextmgrheading = facetoface_email_substitutions(
            $facetoface->requestinstrmngr,
            format_string($facetoface->name),
            $facetoface->reminderperiod,
            $user,
            $session,
            $session->id
    );

    // Send to user.
    if (!email_to_user($user, $from, $postsubject, $posttext)) {
        return 'error:cannotsendrequestuser';
    }

    // Send to manager.
    $user->email = $manageremail;

    if (!email_to_user($user, $from, $postsubject, $posttextmgrheading.$posttext)) {
        return 'error:cannotsendrequestmanager';
    }

    return '';
}


/**
 * Update the signup status of a particular signup
 *
 * @param integer $signupid ID of the signup to be updated
 * @param integer $statuscode Status code to be updated to
 * @param integer $createdby User ID of the user causing the status update
 * @param string $note Cancellation reason or other notes
 * @param int $grade Grade
 * @param bool $usetransaction Set to true if database transactions are to be used
 *
 * @returns integer ID of newly created signup status, or false
 *
 */
function facetoface_update_signup_status($signupid, $statuscode, $createdby, $note='', $grade=null) {
    global $DB;
    $timenow = time();

    $signupstatus = new stdclass;
    $signupstatus->signupid = $signupid;
    $signupstatus->statuscode = $statuscode;
    $signupstatus->createdby = $createdby;
    $signupstatus->timecreated = $timenow;
    $signupstatus->note = $note;
    $signupstatus->grade = $grade;
    $signupstatus->superceded = 0;
    $signupstatus->mailed = 0;

    $transaction = $DB->start_delegated_transaction();

    if ($statusid = $DB->insert_record('facetoface_signups_status', $signupstatus)) {

        // Mark any previous signup_statuses as superceded.
        $where = "signupid = ? AND ( superceded = 0 OR superceded IS NULL ) AND id != ?";
        $whereparams = array($signupid, $statusid);
        $DB->set_field_select('facetoface_signups_status', 'superceded', 1, $where, $whereparams);
        $transaction->allow_commit();

        return $statusid;
    } else {
        return false;
    }
}

/**
 * Cancel a user who signed up earlier
 *
 * @param class $session       Record from the facetoface_sessions table
 * @param integer $userid      ID of the user to remove from the session
 * @param bool $forcecancel    Forces cancellation of sessions that have already occurred
 * @param string $errorstr     Passed by reference. For setting error string in calling function
 * @param string $cancelreason Optional justification for cancelling the signup
 */
function facetoface_user_cancel($session, $userid=false, $forcecancel=false, &$errorstr=null, $cancelreason='') {
    global $USER;

    if (!$userid) {
        $userid = $USER->id;
    }

    // If $forcecancel is set, cancel session even if already occurred used by facetotoface_delete_session().
    if (!$forcecancel) {
        $timenow = time();

        // Don't allow user to cancel a session that has already occurred.
        if (facetoface_has_session_started($session, $timenow)) {
            $errorstr = get_string('error:eventoccurred', 'facetoface');
            return false;
        }
    }

    if (facetoface_user_cancel_submission($session->id, $userid, $cancelreason)) {
        // Remove entry from user's calendar.
        facetoface_remove_session_from_calendar($session, 0, $userid);
        facetoface_update_attendees($session);
        return true;
    }

    // Todo: is this necessary?
    $errorstr = get_string('error:cancelbooking', 'facetoface');

    return false;
}

/**
 * Common code for sending confirmation and cancellation notices
 *
 * @param string $postsubject Subject of the email
 * @param string $posttext Plain text contents of the email
 * @param string $posttextmgrheading Header to prepend to $posttext in manager email
 * @param string $notificationtype The type of notification to send
 * @see {{MDL_F2F_INVITE}}
 * @param class $facetoface record from the facetoface table
 * @param class $session record from the facetoface_sessions table
 * @param integer $userid ID of the recipient of the email
 * @returns string Error message (or empty string if successful)
 */
function facetoface_send_notice($postsubject, $posttext, $posttextmgrheading,
                                $notificationtype, $facetoface, $session, $userid) {
    global $CFG, $DB;

    $user = $DB->get_record('user', array('id' => $userid));
    if (!$user) {
        return 'error:invaliduserid';
    }

    if (empty($postsubject) || empty($posttext)) {
        return '';
    }

    // If no notice type is defined (TEXT or ICAL).
    if (!($notificationtype & MDL_F2F_BOTH)) {

        // If none, make sure they at least get a text email.
        $notificationtype |= MDL_F2F_TEXT;
    }

    // If we are cancelling, check if ical cancellations are disabled.
    if (($notificationtype & MDL_F2F_CANCEL) &&
        get_config(null, 'facetoface_disableicalcancel')) {
        $notificationtype |= MDL_F2F_TEXT; // Add a text notification.
        $notificationtype &= ~MDL_F2F_ICAL; // Remove the iCalendar notification.
    }

    // If we are sending an ical attachment, set file name.
    if ($notificationtype & MDL_F2F_ICAL) {
        if ($notificationtype & MDL_F2F_INVITE) {
            $attachmentfilename = 'invite.ics';
        } else if ($notificationtype & MDL_F2F_CANCEL) {
            $attachmentfilename = 'cancel.ics';
        }
    }

    // Do iCal attachement stuff.
    $icalattachments = array();
    if ($notificationtype & MDL_F2F_ICAL) {
        if (get_config(null, 'facetoface_oneemailperday')) {

            // Keep track of all sessiondates.
            $sessiondates = $session->sessiondates;

            foreach ($sessiondates as $sessiondate) {
                $session->sessiondates = array($sessiondate); // One day at a time.

                $filename = facetoface_get_ical_attachment($notificationtype, $facetoface, $session, $user);
                $subject = facetoface_email_substitutions($postsubject, format_string($facetoface->name), $facetoface->reminderperiod,
                                                          $user, $session, $session->id);
                $body = facetoface_email_substitutions($posttext, format_string($facetoface->name), $facetoface->reminderperiod,
                                                       $user, $session, $session->id);
                $htmlbody = ''; // TODO.
                $icalattachments[] = array('filename' => $filename, 'subject' => $subject,
                                           'body' => $body, 'htmlbody' => $htmlbody);
            }

            // Restore session dates.
            $session->sessiondates = $sessiondates;
        } else {
            $filename = facetoface_get_ical_attachment($notificationtype, $facetoface, $session, $user);
            $subject = facetoface_email_substitutions($postsubject, format_string($facetoface->name), $facetoface->reminderperiod,
                                                      $user, $session, $session->id);
            $body = facetoface_email_substitutions($posttext, format_string($facetoface->name), $facetoface->reminderperiod,
                                                   $user, $session, $session->id);
            $htmlbody = ''; // FIXME.
            $icalattachments[] = array('filename' => $filename, 'subject' => $subject,
                                       'body' => $body, 'htmlbody' => $htmlbody);
        }
    }

    // Fill-in the email placeholders.
    $postsubject = facetoface_email_substitutions($postsubject, format_string($facetoface->name), $facetoface->reminderperiod,
                                                  $user, $session, $session->id);
    $posttext = facetoface_email_substitutions($posttext, format_string($facetoface->name), $facetoface->reminderperiod,
                                               $user, $session, $session->id);

    $posttextmgrheading = facetoface_email_substitutions($posttextmgrheading, format_string($facetoface->name), $facetoface->reminderperiod,
                                                         $user, $session, $session->id);

    $posthtml = ''; // FIXME.
    if ($fromaddress = get_config(null, 'facetoface_fromaddress')) {
        $from = new stdClass();
        $from->maildisplay = true;
        $from->email = $fromaddress;
    } else {
        $from = null;
    }

    $usercheck = $DB->get_record('user', array('id' => $userid));

    // Send email with iCal attachment.
    if ($notificationtype & MDL_F2F_ICAL) {
        foreach ($icalattachments as $attachment) {
            if (!email_to_user($user, $from, $attachment['subject'], $attachment['body'],
                    $attachment['htmlbody'], $attachment['filename'], $attachmentfilename)) {

                return 'error:cannotsendconfirmationuser';
            }
            unlink($CFG->dataroot . '/' . $attachment['filename']);
        }
    }

    // Send plain text email.
    if ($notificationtype & MDL_F2F_TEXT) {
        if (!email_to_user($user, $from, $postsubject, $posttext, $posthtml)) {
            return 'error:cannotsendconfirmationuser';
        }
    }

    // Manager notification.
    $manageremail = facetoface_get_manageremail($userid);
    if (!empty($posttextmgrheading) and !empty($manageremail) and $session->datetimeknown) {
        $managertext = $posttextmgrheading.$posttext;
        $manager = $user;
        $manager->email = $manageremail;

        // Leave out the ical attachments in the managers notification.
        if (!email_to_user($manager, $from, $postsubject, $managertext, $posthtml)) {
            return 'error:cannotsendconfirmationmanager';
        }
    }

    // Third-party notification.
    if (!empty($facetoface->thirdparty) &&
        ($session->datetimeknown || !empty($facetoface->thirdpartywaitlist))) {

        $thirdparty = $user;
        $recipients = explode(',', $facetoface->thirdparty);
        foreach ($recipients as $recipient) {
            $thirdparty->email = trim($recipient);

            // Leave out the ical attachments in the 3rd parties notification.
            if (!email_to_user($thirdparty, $from, $postsubject, $posttext, $posthtml)) {
                return 'error:cannotsendconfirmationthirdparty';
            }
        }
    }

    return '';
}

/**
 * Send a confirmation email to the user and manager
 *
 * @param class $facetoface record from the facetoface table
 * @param class $session record from the facetoface_sessions table
 * @param integer $userid ID of the recipient of the email
 * @param integer $notificationtype Type of notifications to be sent @see {{MDL_F2F_INVITE}}
 * @param boolean $iswaitlisted If the user has been waitlisted
 * @returns string Error message (or empty string if successful)
 */
function facetoface_send_confirmation_notice($facetoface, $session, $userid, $notificationtype, $iswaitlisted) {

    $posttextmgrheading = $facetoface->confirmationinstrmngr;

    if (!$iswaitlisted) {
        $postsubject = $facetoface->confirmationsubject;
        $posttext = $facetoface->confirmationmessage;
    } else {
        $postsubject = $facetoface->waitlistedsubject;
        $posttext = $facetoface->waitlistedmessage;

        // Don't send an iCal attachement when we don't know the date!
        $notificationtype |= MDL_F2F_TEXT; // Add a text notification.
        $notificationtype &= ~MDL_F2F_ICAL; // Remove the iCalendar notification.
    }

    // Set invite bit.
    $notificationtype |= MDL_F2F_INVITE;

    return facetoface_send_notice($postsubject, $posttext, $posttextmgrheading,
                                  $notificationtype, $facetoface, $session, $userid);
}

/**
 * Send a confirmation email to the user and manager regarding the
 * cancellation
 *
 * @param class $facetoface record from the facetoface table
 * @param class $session record from the facetoface_sessions table
 * @param integer $userid ID of the recipient of the email
 * @returns string Error message (or empty string if successful)
 */
function facetoface_send_cancellation_notice($facetoface, $session, $userid) {
    global $DB;

    $postsubject = $facetoface->cancellationsubject;
    $posttext = $facetoface->cancellationmessage;
    $posttextmgrheading = $facetoface->cancellationinstrmngr;

    // Lookup what type of notification to send.
    $notificationtype = $DB->get_field('facetoface_signups', 'notificationtype',
                                  array('sessionid' => $session->id, 'userid' => $userid));

    // Set cancellation bit.
    $notificationtype |= MDL_F2F_CANCEL;

    return facetoface_send_notice($postsubject, $posttext, $posttextmgrheading,
                                  $notificationtype, $facetoface, $session, $userid);
}

/**
 * Returns true if the user has registered for a session in the given
 * facetoface activity
 *
 * @global class $USER used to get the current userid
 * @returns integer The session id that we signed up for, false otherwise
 */
function facetoface_check_signup($facetofaceid) {
    global $USER;

    if ($submissions = facetoface_get_user_submissions($facetofaceid, $USER->id)) {
        return reset($submissions)->sessionid;
    } else {
        return false;
    }
}

/**
 * Return the email address of the user's manager if it is
 * defined. Otherwise return an empty string.
 *
 * @param integer $userid User ID of the staff member
 */
function facetoface_get_manageremail($userid) {
    global $DB;
    $fieldid = $DB->get_field('user_info_field', 'id', array('shortname' => MDL_MANAGERSEMAIL_FIELD));
    if ($fieldid) {
        return $DB->get_field('user_info_data', 'data', array('userid' => $userid, 'fieldid' => $fieldid));
    } else {
        return ''; // No custom field => no manager's email.
    }
}

/**
 * Human-readable version of the format of the manager's email address
 */
function facetoface_get_manageremailformat() {
    $addressformat = get_config(null, 'facetoface_manageraddressformat');
    if (!empty($addressformat)) {
        $readableformat = get_config(null, 'facetoface_manageraddressformatreadable');
        return get_string('manageremailformat', 'facetoface', $readableformat);
    }

    return '';
}

/**
 * Returns true if the given email address follows the format
 * prescribed by the site administrator
 *
 * @param string $manageremail email address as entered by the user
 */
function facetoface_check_manageremail($manageremail) {
    $addressformat = get_config(null, 'facetoface_manageraddressformat');
    if (empty($addressformat) || strpos($manageremail, $addressformat)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Mark the fact that the user attended the facetoface session by
 * giving that user a grade of 100
 *
 * @param array $data array containing the sessionid under the 's' key
 *                    and every submission ID to mark as attended
 *                    under the 'submissionid_XXXX' keys where XXXX is
 *                     the ID of the signup
 */
function facetoface_take_attendance($data) {
    global $USER;

    $sessionid = $data->s;

    // Load session.
    if (!$session = facetoface_get_session($sessionid)) {
        // error_log('F2F: Could not load facetoface session');
        return false;
    }

    // Check facetoface has finished.
    if ($session->datetimeknown && !facetoface_has_session_started($session, time())) {
        // error_log('F2F: Can not take attendance for a session that has not yet started');
        return false;
    }

    /*
     * Record the selected attendees from the user interface - the other attendees will need their grades set
     * to zero, to indicate non attendance, but only the ticked attendees come through from the web interface.
     * Hence the need for a diff
     */
    $selectedsubmissionids = array();

    /*
     * FIXME: This is not very efficient, we should do the grade
     * query outside of the loop to get all submissions for a
     * given Face-to-face ID, then call
     * facetoface_grade_item_update with an array of grade objects.
     */
    foreach ($data as $key => $value) {
        $submissionidcheck = substr($key, 0, 13);
        if ($submissionidcheck == 'submissionid_') {
            $submissionid = substr($key, 13);
            $selectedsubmissionids[$submissionid] = $submissionid;

            // Update status.
            switch ($value) {
                case MDL_F2F_STATUS_NO_SHOW:
                    $grade = 0;
                    break;
                case MDL_F2F_STATUS_PARTIALLY_ATTENDED:
                    $grade = 50;
                    break;
                case MDL_F2F_STATUS_FULLY_ATTENDED:
                    $grade = 100;
                    break;
                default:
                    // This use has not had attendance set: jump to the next item in the foreach loop.
                    continue 2;
            }

            facetoface_update_signup_status($submissionid, $value, $USER->id, '', $grade);
            if (!facetoface_take_individual_attendance($submissionid, $grade)) {
                // error_log("F2F: could not mark '$submissionid' as " . $value);
                return false;
            }
        }
    }

    return true;
}

/**
 * Mark users' booking requests as declined or approved
 *
 * @param array $data array containing the sessionid under the 's' key
 *                    and an array of request approval/denies
 */
function facetoface_approve_requests($data) {
    global $USER, $DB;

    // Check request data.
    if (empty($data->requests) || !is_array($data->requests)) {
        // error_log('F2F: No request data supplied');
        return false;
    }

    $sessionid = $data->s;

    // Load session.
    if (!$session = facetoface_get_session($sessionid)) {
        // error_log('F2F: Could not load facetoface session');
        return false;
    }

    // Load facetoface.
    if (!$facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface))) {
        // error_log('F2F: Could not load facetoface instance');
        return false;
    }

    // Load course.
    if (!$course = $DB->get_record('course', array('id' => $facetoface->course))) {
        // error_log('F2F: Could not load course');
        return false;
    }

    // Loop through requests.
    foreach ($data->requests as $key => $value) {

        // Check key/value.
        if (!is_numeric($key) || !is_numeric($value)) {
            continue;
        }

        // Load user submission.
        if (!$attendee = facetoface_get_attendee($sessionid, $key)) {
            // error_log('F2F: User '.$key.' not an attendee of this session');
            continue;
        }

        // Update status.
        switch ($value) {

            // Decline.
            case 1:
                facetoface_update_signup_status(
                        $attendee->submissionid,
                        MDL_F2F_STATUS_DECLINED,
                        $USER->id
                );

                // Send a cancellation notice to the user.
                facetoface_send_cancellation_notice($facetoface, $session, $attendee->id);

                break;

            // Approve.
            case 2:
                facetoface_update_signup_status(
                        $attendee->submissionid,
                        MDL_F2F_STATUS_APPROVED,
                        $USER->id
                );

                if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
                    throw new moodle_exception('error:incorrectcoursemodule', 'facetoface');
                }

                $contextmodule = context_module::instance($cm->id);

                // Check if there is capacity.
                if (facetoface_session_has_capacity($session, $contextmodule)) {
                    $status = MDL_F2F_STATUS_BOOKED;
                } else {
                    if ($session->allowoverbook) {
                        $status = MDL_F2F_STATUS_WAITLISTED;
                    }
                }

                // Signup user.
                if (!facetoface_user_signup(
                        $session,
                        $facetoface,
                        $course,
                        $attendee->discountcode,
                        $attendee->notificationtype,
                        $status,
                        $attendee->id
                    )) {
                    break;
                }

                break;

            case 0:
            default:
                // Change nothing.
                break;
        }
    }

    return true;
}

/*
 * Set the grading for an individual submission, to either 0 or 100 to indicate attendance
 *
 * @param $submissionid The id of the submission in the database
 * @param $grading Grade to set
 */
function facetoface_take_individual_attendance($submissionid, $grading) {
    global $USER, $CFG, $DB;

    $timenow = time();
    $record = $DB->get_record_sql("SELECT f.*, s.userid
                                FROM {facetoface_signups} s
                                JOIN {facetoface_sessions} fs ON s.sessionid = fs.id
                                JOIN {facetoface} f ON f.id = fs.facetoface
                                JOIN {course_modules} cm ON cm.instance = f.id
                                JOIN {modules} m ON m.id = cm.module
                                WHERE s.id = ? AND m.name='facetoface'",
                            array($submissionid));

    $grade = new stdclass();
    $grade->userid = $record->userid;
    $grade->rawgrade = $grading;
    $grade->rawgrademin = 0;
    $grade->rawgrademax = 100;
    $grade->timecreated = $timenow;
    $grade->timemodified = $timenow;
    $grade->usermodified = $USER->id;

    return facetoface_grade_item_update($record, $grade);
}
/**
 * Used in many places to obtain properly-formatted session date and time info
 *
 * @param int $start a start time Unix timestamp
 * @param int $end an end time Unix timestamp
 * @param string $tz a session timezone
 * @return object Formatted date, start time, end time and timezone info
 */
function facetoface_format_session_times($start, $end, $tz) {

    $displaytimezones = get_config(null, 'facetoface_displaysessiontimezones');

    $formattedsession = new stdClass();
    if (empty($tz) or empty($displaytimezones)) {
        $targettz = core_date::get_user_timezone();
    } else {
        $targettz = core_date::get_user_timezone($tz);
    }

    $formattedsession->startdate = userdate($start, get_string('strftimedate', 'langconfig'), $targettz);
    $formattedsession->starttime = userdate($start, get_string('strftimetime', 'langconfig'), $targettz);
    $formattedsession->enddate = userdate($end, get_string('strftimedate', 'langconfig'), $targettz);
    $formattedsession->endtime = userdate($end, get_string('strftimetime', 'langconfig'), $targettz);
    if (empty($displaytimezones)) {
        $formattedsession->timezone = '';
    } else {
        $formattedsession->timezone = core_date::get_localised_timezone($targettz);
    }
    return $formattedsession;
}

/**
 * Used by course/lib.php to display a few sessions besides the
 * facetoface activity on the course page
 *
 * @param object $cm the cm_info object for the F2F instance
 * @global class $USER used to get the current userid
 * @global class $CFG used to get the path to the module
 */
function facetoface_cm_info_view(cm_info $coursemodule) {
    global $USER, $DB;
    $output = '';

    if (!($facetoface = $DB->get_record('facetoface', array('id' => $coursemodule->instance)))) {
        return null;
    }

    $coursemodule->set_name($facetoface->name);

    $contextmodule = context_module::instance($coursemodule->id);
    if (!has_capability('mod/facetoface:view', $contextmodule)) {
        return null; // Not allowed to view this activity.
    }
    // Can view attendees.
    $viewattendees = has_capability('mod/facetoface:viewattendees', $contextmodule);
    // Can see "view all sessions" link even if activity is hidden/currently unavailable.
    $iseditor = has_any_capability(array('mod/facetoface:viewattendees', 'mod/facetoface:editsessions',
        'mod/facetoface:addattendees', 'mod/facetoface:addattendees',
        'mod/facetoface:takeattendance'), $contextmodule);

    $timenow = time();

    $strviewallsessions = get_string('viewallsessions', 'facetoface');
    $sessionsurl = new moodle_url('/mod/facetoface/view.php', array('f' => $facetoface->id));
    $htmlviewallsessions = html_writer::link($sessionsurl, $strviewallsessions, array('class' => 'f2fsessionlinks f2fviewallsessions', 'title' => $strviewallsessions));

    if ($submissions = facetoface_get_user_submissions($facetoface->id, $USER->id)) {
        // User has signedup for the instance.

        foreach ($submissions as $submission) {

            if ($session = facetoface_get_session($submission->sessionid)) {
                $userisinwaitlist = facetoface_is_user_on_waitlist($session, $USER->id);
                if ($session->datetimeknown && facetoface_has_session_started($session, $timenow) && facetoface_is_session_in_progress($session, $timenow)) {
                    $status = get_string('sessioninprogress', 'facetoface');
                } else if ($session->datetimeknown && facetoface_has_session_started($session, $timenow)) {
                    $status = get_string('sessionover', 'facetoface');
                } else if ($userisinwaitlist) {
                    $status = get_string('waitliststatus', 'facetoface');
                } else {
                    $status = get_string('bookingstatus', 'facetoface');
                }

                // Add booking information.
                $session->bookedsession = $submission;

                $sessiondates = '';

                if ($session->datetimeknown) {
                    foreach ($session->sessiondates as $date) {
                        if (!empty($sessiondates)) {
                            $sessiondates .= html_writer::empty_tag('br');
                        }
                        $sessionobj = facetoface_format_session_times($date->timestart, $date->timefinish, null);
                        if ($sessionobj->startdate == $sessionobj->enddate) {
                            $sessiondatelangkey = !empty($sessionobj->timezone) ? 'sessionstartdateandtime' : 'sessionstartdateandtimewithouttimezone';
                            $sessiondates .= get_string($sessiondatelangkey, 'facetoface', $sessionobj);
                        } else {
                            $sessiondatelangkey = !empty($sessionobj->timezone) ? 'sessionstartfinishdateandtime' : 'sessionstartfinishdateandtimewithouttimezone';
                            $sessiondates .= get_string($sessiondatelangkey, 'facetoface', $sessionobj);
                        }
                    }
                } else {
                    $sessiondates = get_string('wait-listed', 'facetoface');
                }

                $span = html_writer::tag('span', get_string('options', 'facetoface').':', array('class' => 'f2fsessionnotice'));

                // Don't include the link to cancel a session if it has already occurred.
                $moreinfolink = '';
                $cancellink = '';
                if (!facetoface_has_session_started($session, $timenow)) {
                    $strmoreinfo  = get_string('moreinfo', 'facetoface');
                    $signupurl   = new moodle_url('/mod/facetoface/signup.php', array('s' => $session->id));
                    $moreinfolink = html_writer::link($signupurl, $strmoreinfo, array('class' => 'f2fsessionlinks f2fsessioninfolink', 'title' => $strmoreinfo));
                }

                // Don't include the link to view attendees if user is lacking capability.
                $attendeeslink = '';
                if ($viewattendees) {
                    $strseeattendees = get_string('seeattendees', 'facetoface');
                    $attendeesurl = new moodle_url('/mod/facetoface/attendees.php', array('s' => $session->id));
                    $attendeeslink = html_writer::link($attendeesurl, $strseeattendees, array('class' => 'f2fsessionlinks f2fviewattendees', 'title' => $strseeattendees));
                }

                $output .= html_writer::start_tag('div', array('class' => 'f2fsessiongroup'))
                    . html_writer::tag('span', $status, array('class' => 'f2fsessionnotice'))
                    . html_writer::start_tag('div', array('class' => 'f2fsession f2fsignedup'))
                    . html_writer::tag('div', $sessiondates, array('class' => 'f2fsessiontime'))
                    . html_writer::tag('div', $span . $moreinfolink . $attendeeslink . $cancellink, array('class' => 'f2foptions'))
                    . html_writer::end_tag('div')
                    . html_writer::end_tag('div');
            }
        }
        // Add "view all sessions" row to table.
        $output .= $htmlviewallsessions;

    } else if ($sessions = facetoface_get_sessions($facetoface->id)) {
        if ($facetoface->display > 0) {
            $j = 1;

            $sessionsinprogress = array();
            $futuresessions = array();

            foreach ($sessions as $session) {
                if (!facetoface_session_has_capacity($session, $contextmodule, MDL_F2F_STATUS_WAITLISTED) && !$session->allowoverbook) {
                    continue;
                }

                if ($session->datetimeknown && facetoface_has_session_started($session, $timenow) && !facetoface_is_session_in_progress($session, $timenow)) {
                    // Finished session, don't display.
                    continue;
                } else {
                    $signupurl   = new moodle_url('/mod/facetoface/signup.php', array('s' => $session->id));
                    $signuptext   = 'signup';
                    $moreinfolink = html_writer::link($signupurl, get_string($signuptext, 'facetoface'), array('class' => 'f2fsessionlinks f2fsessioninfolink'));

                    $span = html_writer::tag('span', get_string('options', 'facetoface').':', array('class' => 'f2fsessionnotice'));
                }

                $multidate = '';
                $sessiondate = '';
                if ($session->datetimeknown) {
                    if (empty($session->sessiondates)) {
                        $sessiondate = get_string('unknowndate', 'facetoface');
                    } else {
                        $sessionobj = facetoface_format_session_times($session->sessiondates[0]->timestart, $session->sessiondates[0]->timefinish, null);
                        if ($sessionobj->startdate == $sessionobj->enddate) {
                            $sessiondatelangkey = !empty($sessionobj->timezone) ? 'sessionstartdateandtime' : 'sessionstartdateandtimewithouttimezone';
                            $sessiondate = get_string($sessiondatelangkey, 'facetoface', $sessionobj);
                        } else {
                            $sessiondatelangkey = !empty($sessionobj->timezone) ? 'sessionstartfinishdateandtime' : 'sessionstartfinishdateandtimewithouttimezone';
                            $sessiondate .= get_string($sessiondatelangkey, 'facetoface', $sessionobj);
                        }
                        if (count($session->sessiondates) > 1) {
                            $multidate = html_writer::empty_tag('br') . get_string('multidate', 'facetoface');
                        }
                    }
                } else {
                    $sessiondate = get_string('wait-listed', 'facetoface');
                }

                $sessionobject = new stdClass();
                $sessionobject->date = $sessiondate;
                $sessionobject->multidate = $multidate;

                if ($session->datetimeknown && (facetoface_has_session_started($session, $timenow)) && facetoface_is_session_in_progress($session, $timenow)) {
                    $sessionsinprogress[] = $sessionobject;
                } else {
                    $sessionobject->options = $span;
                    $sessionobject->moreinfolink = $moreinfolink;
                    $futuresessions[] = $sessionobject;
                }

                $j++;
                if ($j > $facetoface->display) {
                    break;
                }
            }

            if (!empty($sessionsinprogress)) {
                $output .= html_writer::start_tag('div', array('class' => 'f2fsessiongroup'));
                $output .= html_writer::tag('span', get_string('sessioninprogress', 'facetoface'), array('class' => 'f2fsessionnotice'));

                foreach ($sessionsinprogress as $session) {
                    $output .= html_writer::start_tag('div', array('class' => 'f2fsession f2finprogress'))
                        . html_writer::tag('span', $session->date.$session->multidate, array('class' => 'f2fsessiontime'))
                        . html_writer::end_tag('div');
                }
                $output .= html_writer::end_tag('div');
            }

            if (!empty($futuresessions)) {
                $output .= html_writer::start_tag('div', array('class' => 'f2fsessiongroup'));
                $output .= html_writer::tag('span', get_string('signupforsession', 'facetoface'), array('class' => 'f2fsessionnotice'));

                foreach ($futuresessions as $session) {
                    $output .= html_writer::start_tag('div', array('class' => 'f2fsession f2ffuture'))
                        . html_writer::tag('div', $session->date.$session->multidate, array('class' => 'f2fsessiontime'))
                        . html_writer::tag('div', $session->options . $session->moreinfolink, array('class' => 'f2foptions'))
                        . html_writer::end_tag('div');
                }
                $output .= html_writer::end_tag('div');
            }

            $output .= ($iseditor || ($coursemodule->visible && $coursemodule->available)) ? $htmlviewallsessions : $strviewallsessions;

        } else {
            // Show only name if session display is set to zero.
            $content = html_writer::tag('span', $htmlviewallsessions, array('class' => 'f2fsessionnotice f2factivityname'));
            $coursemodule->set_content($content);
            return;
        }
    } else if (has_capability('mod/facetoface:viewemptyactivities', $contextmodule)) {
        $content = html_writer::tag('span', $htmlviewallsessions, array('class' => 'f2fsessionnotice f2factivityname'));
        $coursemodule->set_content($content);
        return;
    } else {
        // Nothing to display to this user.
        $coursemodule->set_content('');
        return;
    }

    $coursemodule->set_content($output);
}

/**
 * Returns the ICAL data for a facetoface meeting.
 *
 * @param integer $method The method, @see {{MDL_F2F_INVITE}}
 * @param object $facetoface A face-to-face object containing activity details
 * @param object $session A session object containing session details
 * @return string Filename of the attachment in the temp directory
 */
function facetoface_get_ical_attachment($method, $facetoface, $session, $user) {
    global $CFG, $DB;

    // First, generate all the VEVENT blocks.
    $vevents = '';
    foreach ($session->sessiondates as $date) {

        /*
         * Date that this representation of the calendar information was created -
         * we use the time the session was created
         * http://www.kanzaki.com/docs/ical/dtstamp.html
         */
        $dtstamp = facetoface_ical_generate_timestamp($session->timecreated);

        // UIDs should be globally unique.
        $urlbits = parse_url($CFG->wwwroot);
        $sql = "SELECT COUNT(*)
            FROM {facetoface_signups} su
            INNER JOIN {facetoface_signups_status} sus ON su.id = sus.signupid
            WHERE su.userid = ?
                AND su.sessionid = ?
                AND sus.superceded = 1
                AND sus.statuscode = ? ";
        $params = array($user->id, $session->id, MDL_F2F_STATUS_USER_CANCELLED);

        $uid = $dtstamp .
            '-' . substr(md5($CFG->siteidentifier . $session->id . $date->id), -8) .   // Unique identifier, salted with site identifier.
            '-' . $DB->count_records_sql($sql, $params) .                              // New UID if this is a re-signup.
            '@' . $urlbits['host'];                                                    // Hostname for this moodle installation.

        $dtstart = facetoface_ical_generate_timestamp($date->timestart);
        $dtend   = facetoface_ical_generate_timestamp($date->timefinish);

        // FIXME: currently we are not sending updates if the times of the session are changed. This is not ideal!
        $sequence = ($method & MDL_F2F_CANCEL) ? 1 : 0;

        $summary     = facetoface_ical_escape(format_string($facetoface->name));
        $description = facetoface_ical_escape(format_text($session->details), true);

        // Get the location data from custom fields if they exist.
        $customfielddata = facetoface_get_customfielddata($session->id);
        $locationstring = '';
        if (!empty($customfielddata['room'])) {
            $locationstring .= format_string($customfielddata['room']->data);
        }
        if (!empty($customfielddata['venue'])) {
            if (!empty($locationstring)) {
                $locationstring .= "\n";
            }
            $locationstring .= format_string($customfielddata['venue']->data);
        }
        if (!empty($customfielddata['location'])) {
            if (!empty($locationstring)) {
                $locationstring .= "\n";
            }
            $locationstring .= format_string($customfielddata['location']->data);
        }

        /*
         * NOTE: Newlines are meant to be encoded with the literal sequence
         * '\n'. But evolution presents a single line text field for location,
         * and shows the newlines as [0x0A] junk. So we switch it for commas
         * here. Remember commas need to be escaped too.
         */
        $location = str_replace('\n', '\, ', facetoface_ical_escape($locationstring));

        $organiseremail = get_config(null, 'facetoface_fromaddress');

        $role = 'REQ-PARTICIPANT';
        $cancelstatus = '';
        if ($method & MDL_F2F_CANCEL) {
            $role = 'NON-PARTICIPANT';
            $cancelstatus = "\nSTATUS:CANCELLED";
        }

        $icalmethod = ($method & MDL_F2F_INVITE) ? 'REQUEST' : 'CANCEL';

        // FIXME: if the user has input their name in another language, we need to set the LANGUAGE property parameter here.
        $username = fullname($user);
        $mailto   = $user->email;

        // The extra newline at the bottom is so multiple events start on their own lines. The very last one is trimmed outside the loop.
        $vevents .= <<<EOF
BEGIN:VEVENT
UID:{$uid}
DTSTAMP:{$dtstamp}
DTSTART:{$dtstart}
DTEND:{$dtend}
SEQUENCE:{$sequence}
SUMMARY:{$summary}
LOCATION:{$location}
DESCRIPTION:{$description}
CLASS:PRIVATE
TRANSP:OPAQUE{$cancelstatus}
ORGANIZER;CN={$organiseremail}:MAILTO:{$organiseremail}
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE={$role};PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE;CN={$username};LANGUAGE=en:MAILTO:{$mailto}
END:VEVENT

EOF;
    }

    $vevents = trim($vevents);

    // TODO: remove the hard-coded timezone!.
    $template = <<<EOF
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//Moodle//NONSGML Facetoface//EN
VERSION:2.0
METHOD:{$icalmethod}
BEGIN:VTIMEZONE
TZID:/softwarestudio.org/Tzfile/Pacific/Auckland
X-LIC-LOCATION:Pacific/Auckland
BEGIN:STANDARD
TZNAME:NZST
DTSTART:19700405T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=4
TZOFFSETFROM:+1300
TZOFFSETTO:+1200
END:STANDARD
BEGIN:DAYLIGHT
TZNAME:NZDT
DTSTART:19700928T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=9
TZOFFSETFROM:+1200
TZOFFSETTO:+1300
END:DAYLIGHT
END:VTIMEZONE
{$vevents}
END:VCALENDAR
EOF;

    $tempfilename = md5($template);
    $tempfilepathname = $CFG->dataroot . '/' . $tempfilename;
    file_put_contents($tempfilepathname, $template);
    return $tempfilename;
}

function facetoface_ical_generate_timestamp($timestamp) {
    return gmdate('Ymd', $timestamp) . 'T' . gmdate('His', $timestamp) . 'Z';
}

/**
 * Escapes data of the text datatype in ICAL documents.
 *
 * See RFC2445 or http://www.kanzaki.com/docs/ical/text.html or a more readable definition
 */
function facetoface_ical_escape($text, $converthtml=false) {
    if (empty($text)) {
        return '';
    }

    if ($converthtml) {
        $text = html_to_text($text);
    }

    $text = str_replace(
        array('\\',   "\n", ';',  ','),
        array('\\\\', '\n', '\;', '\,'),
        $text
    );

    // Text should be wordwrapped at 75 octets, and there should be one whitespace after the newline that does the wrapping.
    $text = wordwrap($text, 75, "\n ", true);

    return $text;
}

/**
 * Determine if a user is in the waitlist of a session.
 *
 * @param object $session A session object
 * @param int $userid The user ID
 * @return bool True if the user is on waitlist, false otherwise.
 */
function facetoface_is_user_on_waitlist($session, $userid = null) {
    global $DB, $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    $sql = "SELECT 1
            FROM {facetoface_signups} su
            JOIN {facetoface_signups_status} ss ON su.id = ss.signupid
            WHERE su.sessionid = ?
              AND ss.superceded != 1
              AND su.userid = ?
              AND ss.statuscode = ?";

    return $DB->record_exists_sql($sql, array($session->id, $userid, MDL_F2F_STATUS_WAITLISTED));
}

/**
 * Update grades by firing grade_updated event
 *
 * @param object $facetoface null means all facetoface activities
 * @param int $userid specific user only, 0 mean all (not used here)
 */
function facetoface_update_grades($facetoface=null, $userid=0) {
    global $DB;

    if ($facetoface != null) {
            facetoface_grade_item_update($facetoface);
    } else {
        $sql = "SELECT f.*, cm.idnumber as cmidnumber
                  FROM {facetoface} f
                  JOIN {course_modules} cm ON cm.instance = f.id
                  JOIN {modules} m ON m.id = cm.module
                 WHERE m.name='facetoface'";
        if ($rs = $DB->get_recordset_sql($sql)) {
            foreach ($rs as $facetoface) {
                facetoface_grade_item_update($facetoface);
            }
            $rs->close();
        }
    }

    return true;
}

/**
 * Create grade item for given Face-to-face session
 *
 * @param int facetoface  Face-to-face activity (not the session) to grade
 * @param mixed grades    grades objects or 'reset' (means reset grades in gradebook)
 * @return int 0 if ok, error code otherwise
 */
function facetoface_grade_item_update($facetoface, $grades=null) {
    global $CFG, $DB;

    if (!isset($facetoface->cmidnumber)) {

        $sql = "SELECT cm.idnumber as cmidnumber
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module
                 WHERE m.name='facetoface' AND cm.instance = ?";
        $facetoface->cmidnumber = $DB->get_field_sql($sql, array($facetoface->id));
    }

    $params = array('itemname' => format_string($facetoface->name),
                    'idnumber' => $facetoface->cmidnumber);

    $params['gradetype'] = GRADE_TYPE_VALUE;
    $params['grademin']  = 0;
    $params['gradepass'] = 100;
    $params['grademax']  = 100;

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    $retcode = grade_update('mod/facetoface', $facetoface->course, 'mod', 'facetoface',
                            $facetoface->id, 0, $grades, $params);
    return ($retcode === GRADE_UPDATE_OK);
}

/**
 * Delete grade item for given facetoface
 *
 * @param object $facetoface object
 * @return object facetoface
 */
function facetoface_grade_item_delete($facetoface) {
    $retcode = grade_update('mod/facetoface', $facetoface->course, 'mod', 'facetoface',
                            $facetoface->id, 0, null, array('deleted' => 1));
    return ($retcode === GRADE_UPDATE_OK);
}

/**
 * Return number of attendees signed up to a facetoface session
 *
 * @param integer $sessionid
 * @param integer $status MDL_F2F_STATUS_* constant (optional)
 * @return integer
 */
function facetoface_get_num_attendees($sessionid, $status=MDL_F2F_STATUS_BOOKED) {
    global $CFG, $DB;

    $sql = 'SELECT count(ss.id)
        FROM
            {facetoface_signups} su
        JOIN
            {facetoface_signups_status} ss
        ON
            su.id = ss.signupid
        WHERE
            sessionid = ?
        AND
            ss.superceded=0
        AND
        ss.statuscode >= ?';

    // For the session, pick signups that haven't been superceded, or cancelled.
    return (int) $DB->count_records_sql($sql, array($sessionid, $status));
}

/**
 * Return all of a users' submissions to a facetoface
 *
 * @param integer $facetofaceid
 * @param integer $userid
 * @param boolean $includecancellations
 * @return array submissions | false No submissions
 */
function facetoface_get_user_submissions($facetofaceid, $userid, $includecancellations=false) {
    global $CFG, $DB;

    $whereclause = "s.facetoface = ? AND su.userid = ? AND ss.superceded != 1";
    $whereparams = array($facetofaceid, $userid);

    // If not show cancelled, only show requested and up status'.
    if (!$includecancellations) {
        $whereclause .= ' AND ss.statuscode >= ? AND ss.statuscode < ?';
        $whereparams = array_merge($whereparams, array(MDL_F2F_STATUS_REQUESTED, MDL_F2F_STATUS_NO_SHOW));
    }

    // TODO fix mailedconfirmation, timegraded, timecancelled, etc.
    return $DB->get_records_sql("
        SELECT
            su.id,
            s.facetoface,
            s.id as sessionid,
            su.userid,
            0 as mailedconfirmation,
            su.mailedreminder,
            su.discountcode,
            ss.timecreated,
            ss.timecreated as timegraded,
            s.timemodified,
            0 as timecancelled,
            su.notificationtype,
            ss.statuscode
        FROM
            {facetoface_sessions} s
        JOIN
            {facetoface_signups} su
         ON su.sessionid = s.id
        JOIN
            {facetoface_signups_status} ss
         ON su.id = ss.signupid
        WHERE
            {$whereclause}
        ORDER BY
            s.timecreated
    ", $whereparams);
}

/**
 * Cancel users' submission to a facetoface session
 *
 * @param integer $sessionid   ID of the facetoface_sessions record
 * @param integer $userid      ID of the user record
 * @param string $cancelreason Short justification for cancelling the signup
 * @return boolean success
 */
function facetoface_user_cancel_submission($sessionid, $userid, $cancelreason='') {
    global $DB;

    $signup = $DB->get_record('facetoface_signups', array('sessionid' => $sessionid, 'userid' => $userid));
    if (!$signup) {
        return true; // Not signed up, nothing to do.
    }

    return facetoface_update_signup_status($signup->id, MDL_F2F_STATUS_USER_CANCELLED, $userid, $cancelreason);
}

/**
 * A list of actions in the logs that indicate view activity for participants
 */
function facetoface_get_view_actions() {
    return array('view', 'view all');
}

/**
 * A list of actions in the logs that indicate post activity for participants
 */
function facetoface_get_post_actions() {
    return array('cancel booking', 'signup');
}

/**
 * Return a small object with summary information about what a user
 * has done with a given particular instance of this module (for user
 * activity reports.)
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 */
function facetoface_user_outline($course, $user, $mod, $facetoface) {

    $result = new stdClass;
    $grade = facetoface_get_grade($user->id, $course->id, $facetoface->id);
    if ($grade->grade > 0) {
        $result = new stdClass;
        $result->info = get_string('grade') . ': ' . $grade->grade;
        $result->time = $grade->dategraded;
    } else if ($submissions = facetoface_get_user_submissions($facetoface->id, $user->id)) {
        $result->info = get_string('usersignedup', 'facetoface');
        $result->time = reset($submissions)->timecreated;
    } else {
        $result->info = get_string('usernotsignedup', 'facetoface');
    }

    return $result;
}

/**
 * Print a detailed representation of what a user has done with a
 * given particular instance of this module (for user activity
 * reports).
 */
function facetoface_user_complete($course, $user, $mod, $facetoface) {
    $grade = facetoface_get_grade($user->id, $course->id, $facetoface->id);
    if ($submissions = facetoface_get_user_submissions($facetoface->id, $user->id, true)) {
        print get_string('grade') . ': ' . $grade->grade . html_writer::empty_tag('br');
        if ($grade->dategraded > 0) {
            $timegraded = trim(userdate($grade->dategraded, get_string('strftimedatetime')));
            print '(' . format_string($timegraded) . ')' . html_writer::empty_tag('br');
        }
        echo html_writer::empty_tag('br');

        foreach ($submissions as $submission) {
            $timesignedup = trim(userdate($submission->timecreated, get_string('strftimedatetime')));
            print get_string('usersignedupon', 'facetoface', format_string($timesignedup)) . html_writer::empty_tag('br');

            if ($submission->timecancelled > 0) {
                $timecancelled = userdate($submission->timecancelled, get_string('strftimedatetime'));
                print get_string('usercancelledon', 'facetoface', format_string($timecancelled)) . html_writer::empty_tag('br');
            }
        }
    } else {
        print get_string('usernotsignedup', 'facetoface');
    }

    return true;
}

/**
 * Add a link to the session to the courses calendar.
 *
 * @param class   $session          Record from the facetoface_sessions table
 * @param class   $eventname        Name to display for this event
 * @param string  $calendartype     Which calendar to add the event to (user, course, site)
 * @param int     $userid           Optional param for user calendars
 * @param string  $eventtype        Optional param for user calendar (booking/session)
 */
function facetoface_add_session_to_calendar($session, $facetoface, $calendartype='none', $userid=0, $eventtype='session') {
    global $CFG, $DB;

    if (empty($session->datetimeknown)) {
        return true; // Date unkown, can't add to calendar.
    }

    if (empty($facetoface->showoncalendar) && empty($facetoface->usercalentry)) {
        return true; // Facetoface calendar settings prevent calendar.
    }

    $description = '';
    if (!empty($facetoface->description)) {
        $description .= html_writer::tag('p', clean_param($facetoface->description, PARAM_CLEANHTML));
    }
    $description .= facetoface_print_session($session, false, true, true);
    $linkurl = new moodle_url('/mod/facetoface/signup.php', array('s' => $session->id));
    $linktext = get_string('signupforthissession', 'facetoface');

    if ($calendartype == 'site' && $facetoface->showoncalendar == F2F_CAL_SITE) {
        $courseid = SITEID;
        $modulename = '0';
        $description .= html_writer::link($linkurl, $linktext);
    } else if ($calendartype == 'course' && $facetoface->showoncalendar == F2F_CAL_COURSE) {
        $courseid = $facetoface->course;
        $modulename = 'facetoface';
        $description .= html_writer::link($linkurl, $linktext);
    } else if ($calendartype == 'user' && $facetoface->usercalentry) {
        $courseid = 0;
        $modulename = '0';
        $urlvar = ($eventtype == 'session') ? 'attendees' : 'signup';
        $linkurl = $CFG->wwwroot . "/mod/facetoface/" . $urlvar . ".php?s=$session->id";
        $description .= get_string("calendareventdescription{$eventtype}", 'facetoface', $linkurl);
    } else {
        return true;
    }

    $shortname = $facetoface->shortname;
    if (empty($shortname)) {
        $shortname = substr($facetoface->name, 0, CALENDAR_MAX_NAME_LENGTH);
    }

    $result = true;
    foreach ($session->sessiondates as $date) {
        $newevent = new stdClass();
        $newevent->name = $shortname;
        $newevent->description = $description;
        $newevent->format = FORMAT_HTML;
        $newevent->courseid = $courseid;
        $newevent->groupid = 0;
        $newevent->userid = $userid;
        $newevent->uuid = "{$session->id}";
        $newevent->instance = $session->facetoface;
        $newevent->modulename = $modulename;
        $newevent->eventtype = "facetoface{$eventtype}";
        $newevent->type = 0; // CALENDAR_EVENT_TYPE_STANDARD: Only display on the calendar, not needed on the block_myoverview.
        $newevent->timestart = $date->timestart;
        $newevent->timeduration = $date->timefinish - $date->timestart;
        $newevent->visible = 1;
        $newevent->timemodified = time();

        if ($calendartype == 'user' && $eventtype == 'booking') {

            // Check for and Delete the 'created' calendar event to reduce multiple entries for the same event.
            $DB->delete_records_select('event', 'userid = ? AND instance = ? AND '
                . $DB->sql_compare_text('eventtype') . ' = ? AND ' . $DB->sql_compare_text('name') . ' = ?',
                array($userid, $session->facetoface, 'facetofacesession', $shortname));
        }

        $result = $result && $DB->insert_record('event', $newevent);
    }

    return $result;
}

/**
 * Remove all entries in the course calendar which relate to this session.
 *
 * @param class $session    Record from the facetoface_sessions table
 * @param integer $courseid ID of the course - 0 for user event, SITEID for global event, 2+ for course event.
 * @param string $userid    ID of the user. If not specified, will match any used ID.
 */
function facetoface_remove_session_from_calendar($session, $courseid=0, $userid=0) {
    global $DB;

    $modulename = '0';         // User events and Site events.
    if ($courseid > SITEID) {  // Course event.
        $modulename = 'facetoface';
    }
    if (empty($userid)) { // Match any UserID.
        $params = array($modulename, $session->facetoface, $courseid, $session->id);
        return $DB->delete_records_select('event', "modulename = ? AND
                                                    instance = ? AND
                                                    courseid = ? AND
                                                    uuid = ?", $params);
    } else {
        $params = array($modulename, $session->facetoface, $userid, $courseid, $session->id);
        return $DB->delete_records_select('event', "modulename = ? AND
                                                    instance = ? AND
                                                    userid = ? AND
                                                    courseid = ? AND
                                                    uuid = ?", $params);
    }
}

/**
 * Update the date/time of events in the Moodle Calendar when a
 * session's dates are changed.
 *
 * @param object $session       Record from the facetoface_sessions table
 * @param string $eventtype     Type of event to update
 */
function facetoface_update_user_calendar_events($session, $eventtype) {
    global $DB;

    $facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface));
    if (empty($facetoface->usercalentry) || $facetoface->usercalentry == 0) {
        return true;
    }

    $users = facetoface_delete_user_calendar_events($session, $eventtype);

    // Add this session to these users' calendar.
    foreach ($users as $user) {
        facetoface_add_session_to_calendar($session, $facetoface, 'user', $user->userid, $eventtype);
    }

    return true;
}

/**
 * Delete all user level calendar events for a face to face session
 *
 * @param class     $session    Record from the facetoface_sessions table
 * @param string    $eventtype  Type of the event (booking or session)
 * @return array    $users      Array of users who had the event deleted
 */
function facetoface_delete_user_calendar_events($session, $eventtype) {
    global $CFG, $DB;

    $whereclause = "modulename = '0' AND
                    eventtype = 'facetoface$eventtype' AND
                    instance = ?";

    $whereparams = array($session->facetoface);

    if ('session' == $eventtype) {
        $likestr = "%attendees.php?s={$session->id}%";
        $like = $DB->sql_like('description', '?');
        $whereclause .= " AND $like";

        $whereparams[] = $likestr;
    }

    // Users calendar.
    $users = $DB->get_records_sql("SELECT DISTINCT userid
        FROM {event}
        WHERE $whereclause", $whereparams);

    if ($users && count($users) > 0) {

        // Delete the existing events.
        $DB->delete_records_select('event', $whereclause, $whereparams);
    }

    return $users;
}

/**
 * Confirm that a user can be added to a session.
 *
 * @param class  $session Record from the facetoface_sessions table
 * @param object $context (optional) A context object (record from context table)
 * @return bool True if user can be added to session
 **/
function facetoface_session_has_capacity($session, $context=false) {
    if (empty($session)) {
        return false;
    }

    $signupcount = facetoface_get_num_attendees($session->id);
    if ($signupcount >= $session->capacity) {

        // If session is full, check if overbooking is allowed for this user.
        if (!$context || !has_capability('mod/facetoface:overbook', $context)) {
            return false;
        }
    }

    return true;
}

/**
 * Print the details of a session
 *
 * @param object $session         Record from facetoface_sessions
 * @param boolean $showcapacity   Show the capacity (true) or only the seats available (false)
 * @param boolean $calendaroutput Whether the output should be formatted for a calendar event
 * @param boolean $return         Whether to return (true) the html or print it directly (true)
 * @param boolean $hidesignup     Hide any messages relating to signing up
 */
function facetoface_print_session($session, $showcapacity, $calendaroutput=false, $return=false, $hidesignup=false) {
    global $CFG, $DB;

    $table = new html_table();
    $table->attributes['class'] = 'generaltable f2fsession';
    $table->align = array('right', 'left');

    $customfields = facetoface_get_session_customfields();
    $customdata = $DB->get_records('facetoface_session_data', array('sessionid' => $session->id), '', 'fieldid, data');
    foreach ($customfields as $field) {
        $data = '';
        if (!empty($customdata[$field->id])) {
            if (CUSTOMFIELD_TYPE_MULTISELECT == $field->type) {
                $values = explode(CUSTOMFIELD_DELIMITER, format_string($customdata[$field->id]->data));
                $data = implode(html_writer::empty_tag('br'), $values);
            } else {
                $data = format_string($customdata[$field->id]->data);
            }
        }
        $table->data[] = array(str_replace(' ', '&nbsp;', format_string($field->name)), $data);
    }

    $strdatetime = str_replace(' ', '&nbsp;', get_string('sessiondatetime', 'facetoface'));
    if ($session->datetimeknown) {
        $html = '';
        foreach ($session->sessiondates as $date) {
            if (!empty($html)) {
                $html .= html_writer::empty_tag('br');
            }
            $timestart = userdate($date->timestart, get_string('strftimedatetime'));
            $timefinish = userdate($date->timefinish, get_string('strftimedatetime'));
            $html .= "$timestart &ndash; $timefinish";
        }
        $table->data[] = array($strdatetime, $html);
    } else {
        $table->data[] = array($strdatetime, html_writer::tag('i', get_string('wait-listed', 'facetoface')));
    }

    $signupcount = facetoface_get_num_attendees($session->id);
    $placesleft = $session->capacity - $signupcount;

    if ($showcapacity) {
        if ($session->allowoverbook) {
            $table->data[] = array(get_string('capacity', 'facetoface'), $session->capacity . ' ('.strtolower(get_string('allowoverbook', 'facetoface')).')');
        } else {
            $table->data[] = array(get_string('capacity', 'facetoface'), $session->capacity);
        }
    } else if (!$calendaroutput) {
        $table->data[] = array(get_string('seatsavailable', 'facetoface'), max(0, $placesleft));
    }

    // Display requires approval notification.
    $facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface));

    if ($facetoface->approvalreqd) {
        $table->data[] = array('', get_string('sessionrequiresmanagerapproval', 'facetoface'));
    }

    // Display waitlist notification.
    if (!$hidesignup && $session->allowoverbook && $placesleft < 1) {
        $table->data[] = array('', get_string('userwillbewaitlisted', 'facetoface'));
    }

    if (!empty($session->duration)) {
        $table->data[] = array(get_string('duration', 'facetoface'), format_duration($session->duration));
    }
    if (!empty($session->normalcost)) {
        $table->data[] = array(get_string('normalcost', 'facetoface'), format_cost($session->normalcost));
    }
    if (!empty($session->discountcost)) {
        $table->data[] = array(get_string('discountcost', 'facetoface'), format_cost($session->discountcost));
    }
    if (!empty($session->details)) {
        $details = clean_text($session->details, FORMAT_HTML);
        $table->data[] = array(get_string('details', 'facetoface'), format_text($details, FORMAT_HTML, array('context' => context_system::instance())));
    }

    // Display trainers.
    $trainerroles = facetoface_get_trainer_roles();

    if ($trainerroles) {

        // Get trainers.
        $trainers = facetoface_get_trainers($session->id);
        foreach ($trainerroles as $role => $rolename) {
            $rolename = $rolename->name;

            if (empty($trainers[$role])) {
                continue;
            }

            $trainernames = array();
            foreach ($trainers[$role] as $trainer) {
                $trainerurl = new moodle_url('/user/view.php', array('id' => $trainer->id));
                $trainernames[] = html_writer::link($trainerurl, fullname($trainer));
            }

            $table->data[] = array($rolename, implode(', ', $trainernames));
        }
    }

    return html_writer::table($table, $return);
}

/**
 * Update the value of a customfield for the given session/notice.
 *
 * @param integer $fieldid    ID of a record from the facetoface_session_field table
 * @param string  $data       Value for that custom field
 * @param integer $otherid    ID of a record from the facetoface_(sessions|notice) table
 * @param string  $table      'session' or 'notice' (part of the table name)
 * @returns true if it succeeded, false otherwise
 */
function facetoface_save_customfield_value($fieldid, $data, $otherid, $table) {
    global $DB;

    $dbdata = null;
    if (is_array($data)) {
        $dbdata = trim(implode(CUSTOMFIELD_DELIMITER, $data), ';');
    } else {
        $dbdata = trim($data);
    }

    $newrecord = new stdClass();
    $newrecord->data = $dbdata;

    $fieldname = "{$table}id";
    if ($record = $DB->get_record("facetoface_{$table}_data", array('fieldid' => $fieldid, $fieldname => $otherid))) {
        if (empty($dbdata)) {

            // Clear out the existing value.
            return $DB->delete_records("facetoface_{$table}_data", array('id' => $record->id));
        }

        $newrecord->id = $record->id;
        return $DB->update_record("facetoface_{$table}_data", $newrecord);
    } else {
        if (empty($dbdata)) {
            return true; // No need to store empty values.
        }

        $newrecord->fieldid = $fieldid;
        $newrecord->$fieldname = $otherid;

        return $DB->insert_record("facetoface_{$table}_data", $newrecord);
    }
}

/**
 * Return the value of a customfield for the given session/notice.
 *
 * @param object  $field    A record from the facetoface_session_field table
 * @param integer $otherid  ID of a record from the facetoface_(sessions|notice) table
 * @param string  $table    'session' or 'notice' (part of the table name)
 * @returns string The data contained in this custom field (empty string if it doesn't exist)
 */
function facetoface_get_customfield_value($field, $otherid, $table) {
    global $DB;

    if ($record = $DB->get_record("facetoface_{$table}_data", array('fieldid' => $field->id, "{$table}id" => $otherid))) {
        if (!empty($record->data)) {
            if (CUSTOMFIELD_TYPE_MULTISELECT == $field->type) {
                return explode(CUSTOMFIELD_DELIMITER, $record->data);
            }
            return $record->data;
        }
    }

    return '';
}

/**
 * Return the values stored for all custom fields in the given session.
 *
 * @param integer $sessionid  ID of facetoface_sessions record
 * @returns array Indexed by field shortnames
 */
function facetoface_get_customfielddata($sessionid) {
    global $CFG, $DB;

    $sql = "SELECT f.shortname, d.data
              FROM {facetoface_session_field} f
              JOIN {facetoface_session_data} d ON f.id = d.fieldid
              WHERE d.sessionid = ?";

    $records = $DB->get_records_sql($sql, array($sessionid));

    return $records;
}

/**
 * Return a cached copy of all records in facetoface_session_field
 */
function facetoface_get_session_customfields() {
    global $DB;

    static $customfields = null;
    if (null == $customfields) {
        if (!$customfields = $DB->get_records('facetoface_session_field')) {
            $customfields = array();
        }
    }
    return $customfields;
}

/**
 * Display the list of custom fields in the site-wide settings page
 */
function facetoface_list_of_customfields() {
    global $CFG, $USER, $DB, $OUTPUT;

    if ($fields = $DB->get_records('facetoface_session_field', array(), 'name', 'id, name')) {
        $table = new html_table();
        $table->attributes['class'] = 'halfwidthtable';
        foreach ($fields as $field) {
            $fieldname = format_string($field->name);
            $editurl = new moodle_url('/mod/facetoface/customfield.php', array('id' => $field->id));
            $editlink = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
            $deleteurl = new moodle_url('/mod/facetoface/customfield.php', array('id' => $field->id, 'd' => '1', 'sesskey' => $USER->sesskey));
            $deletelink = $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete', get_string('delete')));
            $table->data[] = array($fieldname, $editlink, $deletelink);
        }
        return html_writer::table($table, true);
    }

    return get_string('nocustomfields', 'facetoface');
}

function facetoface_update_trainers($sessionid, $form) {
    global $DB;

    // If we recieved bad data.
    if (!is_array($form)) {
        return false;
    }

    // Load current trainers.
    $oldtrainers = facetoface_get_trainers($sessionid);

    $transaction = $DB->start_delegated_transaction();

    // Loop through form data and add any new trainers.
    foreach ($form as $roleid => $trainers) {

        // Loop through trainers in this role.
        foreach ($trainers as $trainer) {

            if (!$trainer) {
                continue;
            }

            // If the trainer doesn't exist already, create it.
            if (!isset($oldtrainers[$roleid][$trainer])) {

                $newtrainer = new stdClass();
                $newtrainer->userid = $trainer;
                $newtrainer->roleid = $roleid;
                $newtrainer->sessionid = $sessionid;

                if (!$DB->insert_record('facetoface_session_roles', $newtrainer)) {
                    throw new moodle_exception('error:couldnotaddtrainer', 'facetoface');
                    $transaction->force_transaction_rollback();

                    return false;
                }
            } else {
                unset($oldtrainers[$roleid][$trainer]);
            }
        }
    }

    // Loop through what is left of old trainers, and remove (as they have been deselected).
    if ($oldtrainers) {
        foreach ($oldtrainers as $roleid => $trainers) {

            // If no trainers left.
            if (empty($trainers)) {
                continue;
            }

            // Delete any remaining trainers.
            foreach ($trainers as $trainer) {
                if (!$DB->delete_records('facetoface_session_roles', array('sessionid' => $sessionid, 'roleid' => $roleid, 'userid' => $trainer->id))) {
                    throw new moodle_exception('error:couldnotdeletetrainer', 'facetoface');
                    $transaction->force_transaction_rollback();
                    return false;
                }
            }
        }
    }

    $transaction->allow_commit();

    return true;
}


/**
 * Return array of trainer roles configured for face-to-face
 *
 * @return array
 */
function facetoface_get_trainer_roles() {
    global $CFG, $DB;

    // Check that roles have been selected.
    if (empty($CFG->facetoface_session_roles)) {
        return false;
    }

    // Parse roles.
    $cleanroles = clean_param($CFG->facetoface_session_roles, PARAM_SEQUENCE);
    $roles = explode(',', $cleanroles);
    list($rolesql, $params) = $DB->get_in_or_equal($roles);

    // Load role names.
    $rolenames = $DB->get_records_sql("
        SELECT
            r.id,
            r.name
        FROM
            {role} r
        WHERE
            r.id {$rolesql}
        AND r.id <> 0
    ", $params);

    // Return roles and names.
    if (!$rolenames) {
        return array();
    }

    return $rolenames;
}


/**
 * Get all trainers associated with a session, optionally
 * restricted to a certain roleid
 *
 * If a roleid is not specified, will return a multi-dimensional
 * array keyed by roleids, with an array of the chosen roles
 * for each role
 *
 * @param  integer $sessionid
 * @param  integer $roleid (optional)
 * @return array
 */
function facetoface_get_trainers($sessionid, $roleid = null) {
    global $CFG, $DB;

    $usernamefields = facetoface_get_all_user_name_fields(true, 'u');
    $sql = "
        SELECT
            u.id,
            r.roleid,
            {$usernamefields}
        FROM
            {facetoface_session_roles} r
        LEFT JOIN
            {user} u
         ON u.id = r.userid
        WHERE
            r.sessionid = ?
        ";
    $params = array($sessionid);

    if ($roleid) {
        $sql .= "AND r.roleid = ?";
        $params[] = $roleid;
    }

    $rs = $DB->get_recordset_sql($sql , $params);
    $return = array();
    foreach ($rs as $record) {

        // Create new array for this role.
        if (!isset($return[$record->roleid])) {
            $return[$record->roleid] = array();
        }
        $return[$record->roleid][$record->id] = $record;
    }
    $rs->close();

    // If we are only after one roleid.
    if ($roleid) {
        if (empty($return[$roleid])) {
            return false;
        }
        return $return[$roleid];
    }

    // If we are after all roles.
    if (empty($return)) {
        return false;
    }

    return $return;
}

/**
 * Determines whether an activity requires the user to have a manager (either for
 * manager approval or to send notices to the manager)
 *
 * @param  object $facetoface A database fieldset object for the facetoface activity
 * @return boolean whether a person needs a manager to sign up for that activity
 */
function facetoface_manager_needed($facetoface) {
    return $facetoface->approvalreqd
        || $facetoface->confirmationinstrmngr
        || $facetoface->reminderinstrmngr
        || $facetoface->cancellationinstrmngr;
}

/**
 * Display the list of site notices in the site-wide settings page
 */
function facetoface_list_of_sitenotices() {
    global $CFG, $USER, $DB, $OUTPUT;

    if ($notices = $DB->get_records('facetoface_notice', array(), 'name', 'id, name')) {
        $table = new html_table();
        $table->data = array();
        $table->size = array('100%');
        foreach ($notices as $notice) {
            $noticename = format_string($notice->name);
            $editurl = new moodle_url('/mod/facetoface/sitenotice.php', array('id' => $notice->id));
            $editlink = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
            $deleteurl = new moodle_url('/mod/facetoface/sitenotice.php', array('id' => $notice->id, 'd' => '1', 'sesskey' => $USER->sesskey));
            $deletelink = $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete', get_string('delete')));
            $table->data[] = array($noticename, $editlink, $deletelink);
        }
        return html_writer::table($table, true);
    }

    return get_string('nositenotices', 'facetoface');
}

/**
 * Add formslib fields for all custom fields defined site-wide.
 * (used by the session add/edit page and the site notices)
 */
function facetoface_add_customfields_to_form(&$mform, $customfields, $alloptional=false) {
    foreach ($customfields as $field) {
        $fieldname = "custom_$field->shortname";

        $options = array();
        if (!$field->required) {
            $options[''] = get_string('none');
        }
        foreach (explode(CUSTOMFIELD_DELIMITER, $field->possiblevalues) as $value) {
            $v = trim($value);
            if (!empty($v)) {
                $options[$v] = format_string($v);
            }
        }

        switch ($field->type) {
            case CUSTOMFIELD_TYPE_TEXT:
                $mform->addElement('text', $fieldname, format_string($field->name));
                break;
            case CUSTOMFIELD_TYPE_SELECT:
                $mform->addElement('select', $fieldname, format_string($field->name), $options);
                break;
            case CUSTOMFIELD_TYPE_MULTISELECT:
                $select = &$mform->addElement('select', $fieldname, format_string($field->name), $options);
                $select->setMultiple(true);
                break;
            default:
                // error_log("facetoface: invalid field type for custom field ID $field->id");
                continue 2;
        }

        $mform->setType($fieldname, PARAM_TEXT);
        $mform->setDefault($fieldname, $field->defaultvalue);
        if ($field->required and !$alloptional) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }
    }
}

/**
 * Get session cancellations
 *
 * @access  public
 * @param   integer $sessionid
 * @return  array
 */
function facetoface_get_cancellations($sessionid) {
    global $CFG, $DB;

    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $usernamefields = facetoface_get_all_user_name_fields(true, 'u');
    $instatus = array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_WAITLISTED, MDL_F2F_STATUS_REQUESTED);
    list($insql, $inparams) = $DB->get_in_or_equal($instatus);

    // Nasty SQL follows:
    // Load currently cancelled users, include most recent booked/waitlisted time also.
    $sql = "
            SELECT
                u.id,
                {$usernamefields},
                su.id AS signupid,
                MAX(ss.timecreated) AS timesignedup,
                c.timecreated AS timecancelled,
                " . $DB->sql_compare_text('c.note', 250) . " AS cancelreason
            FROM
                {facetoface_signups} su
            JOIN
                {user} u
             ON u.id = su.userid
            JOIN
                {facetoface_signups_status} c
             ON su.id = c.signupid
            AND c.statuscode = ?
            AND c.superceded = 0
            LEFT JOIN
                {facetoface_signups_status} ss
             ON su.id = ss.signupid
             AND ss.statuscode $insql
            AND ss.superceded = 1
            WHERE
                su.sessionid = ?
            GROUP BY
                u.id, su.id,
                {$usernamefields},
                c.timecreated,
                " . $DB->sql_compare_text('c.note', 250) . "
            ORDER BY
                {$fullname},
                c.timecreated
    ";
    $params = array_merge(array(MDL_F2F_STATUS_USER_CANCELLED), $inparams);
    $params[] = $sessionid;
    return $DB->get_records_sql($sql, $params);
}


/**
 * Get session unapproved requests
 *
 * @access  public
 * @param   integer $sessionid
 * @return  array
 */
function facetoface_get_requests($sessionid) {
    global $CFG, $DB;

    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $usernamefields = facetoface_get_all_user_name_fields(true, 'u');

    $params = array($sessionid, MDL_F2F_STATUS_REQUESTED);

    $sql = "SELECT u.id, su.id AS signupid, {$usernamefields},
                   ss.timecreated AS timerequested
              FROM {facetoface_signups} su
              JOIN {facetoface_signups_status} ss ON su.id=ss.signupid
              JOIN {user} u ON u.id = su.userid
             WHERE su.sessionid = ? AND ss.superceded != 1 AND ss.statuscode = ?
          ORDER BY $fullname, ss.timecreated";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Get session declined requests
 *
 * @access  public
 * @param   integer $sessionid
 * @return  array
 */
function facetoface_get_declines($sessionid) {
    global $CFG, $DB;

    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $usernamefields = facetoface_get_all_user_name_fields(true, 'u');

    $params = array($sessionid, MDL_F2F_STATUS_DECLINED);

    $sql = "SELECT u.id, su.id AS signupid, {$usernamefields},
                   ss.timecreated AS timerequested
              FROM {facetoface_signups} su
              JOIN {facetoface_signups_status} ss ON su.id=ss.signupid
              JOIN {user} u ON u.id = su.userid
             WHERE su.sessionid = ? AND ss.superceded != 1 AND ss.statuscode = ?
          ORDER BY $fullname, ss.timecreated";
    return $DB->get_records_sql($sql, $params);
}


/**
 * Returns all other caps used in module
 * @return array
 */
function facetoface_get_extra_capabilities() {
    return array('moodle/site:viewfullnames');
}


/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function facetoface_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        default:
            return null;
    }
}

/**
 * A centralised location for the all name fields. Returns an array / sql string snippet.
 *
 * @param bool $returnsql True for an sql select field snippet.
 * @param string $tableprefix table query prefix to use in front of each field.
 * @return array|string All name fields.
 */
function facetoface_get_all_user_name_fields($returnsql = false, $tableprefix = null) {
    global $CFG;

    $ret = \core_user\fields::get_name_fields();
    if (!empty($tableprefix)) {
        $ret = substr_replace($ret, $tableprefix . '.', 0, 0);
    }
    if ($returnsql) {
        $ret = join(',', $ret);
    }
    return $ret;
}

/*
 * facetoface assignment candidates
 */
class facetoface_candidate_selector extends user_selector_base {
    protected $sessionid;

    public function __construct($name, $options) {
        $this->sessionid = $options['sessionid'];
        parent::__construct($name, $options);
    }

    /*
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;

        // All non-signed up system user.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(u.id)';
        $sql = "
                  FROM {user} u
                 WHERE $wherecondition
                   AND u.id NOT IN
                       (
                       SELECT u2.id
                         FROM {facetoface_signups} s
                         JOIN {facetoface_signups_status} ss ON s.id = ss.signupid
                         JOIN {user} u2 ON u2.id = s.userid
                        WHERE s.sessionid = :sessid
                          AND ss.statuscode >= :statuswaitlisted
                          AND ss.superceded = 0
                       )
               ";
        $order = " ORDER BY u.lastname ASC, u.firstname ASC";
        $params = array_merge($params,
            array(
                'sessid' => $this->sessionid,
                'statuswaitlisted' => MDL_F2F_STATUS_WAITLISTED
            ));

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > 100) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        $groupname = get_string('potentialusers', 'role', count($availableusers));

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['sessionid'] = $this->sessionid;
        $options['file'] = 'mod/facetoface/lib.php';
        return $options;
    }
}

/**
 * Facetoface assignment candidates
 */
class facetoface_existing_selector extends user_selector_base {
    protected $sessionid;

    public function __construct($name, $options) {
        $this->sessionid = $options['sessionid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $whereparams) = $this->search_sql($search, 'u');

        $fields  = 'SELECT ' . $this->required_fields_sql('u');
        $fields .= ', su.id AS submissionid, s.discountcost, su.discountcode, su.notificationtype, f.id AS facetofaceid,
            f.course, ss.grade, ss.statuscode, sign.timecreated';
        $countfields = 'SELECT COUNT(1)';
        $sql = "
            FROM
                {facetoface} f
            JOIN
                {facetoface_sessions} s
             ON s.facetoface = f.id
            JOIN
                {facetoface_signups} su
             ON s.id = su.sessionid
            JOIN
                {facetoface_signups_status} ss
             ON su.id = ss.signupid
            LEFT JOIN
                (
                SELECT
                    ss.signupid,
                    MAX(ss.timecreated) AS timecreated
                FROM
                    {facetoface_signups_status} ss
                INNER JOIN
                    {facetoface_signups} s
                 ON s.id = ss.signupid
                AND s.sessionid = :sessid1
                WHERE
                    ss.statuscode IN (:statusbooked, :statuswaitlisted)
                GROUP BY
                    ss.signupid
                ) sign
             ON su.id = sign.signupid
            JOIN
                {user} u
             ON u.id = su.userid
            WHERE
                $wherecondition
            AND s.id = :sessid2
            AND ss.superceded != 1
            AND ss.statuscode >= :statusapproved
        ";
        $order = " ORDER BY sign.timecreated ASC, ss.timecreated ASC";
        $params = array ('sessid1' => $this->sessionid, 'statusbooked' => MDL_F2F_STATUS_BOOKED, 'statuswaitlisted' => MDL_F2F_STATUS_WAITLISTED);
        $params = array_merge($params, $whereparams);
        $params['sessid2'] = $this->sessionid;
        $params['statusapproved'] = MDL_F2F_STATUS_APPROVED;
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > 100) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);
        if (empty($availableusers)) {
            return array();
        }

        $groupname = get_string('existingusers', 'role', count($availableusers));
        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['sessionid'] = $this->sessionid;
        $options['file'] = 'mod/facetoface/lib.php';
        return $options;
    }
}
