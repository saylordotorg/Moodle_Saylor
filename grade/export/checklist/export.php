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
 * Run the export
 *
 * @package   gradeexport_checklist
 * @copyright 2010 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Note - to adjust the user columns included in the report, edit 'columns.php'.

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/mod/checklist/lib.php'); // For CHECKLIST_* definitions.
require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');

$courseid = required_param('id', PARAM_INT);
$district = optional_param('choosedistrict', false, PARAM_TEXT);
$checklistid = required_param('choosechecklist', PARAM_INT);
$group = optional_param('group', 0, PARAM_INT);
$exportoptional = optional_param('exportoptional', false, PARAM_BOOL);

$percentcol = optional_param('percentcol', false, PARAM_BOOL);
$percentrow = optional_param('percentrow', false, PARAM_BOOL);
$percentheadings = optional_param('percentheadings', false, PARAM_BOOL);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course->id);
$context = context_course::instance($course->id);

require_capability('gradeexport/checklist:view', $context);
$viewall = has_capability('gradeexport/checklist:viewall', $context);
$viewdistrict = has_capability('gradeexport/checklist:viewdistrict', $context);
if (!$viewall && (!$viewdistrict || !$district)) {
    print_error('nopermission', 'gradeexport_checklist');
}

if (!$viewall) {
    $sql = "SELECT ud.data AS district FROM {user_info_data} ud, {user_info_field} uf ";
    $sql .= "WHERE ud.fieldid = uf.id AND uf.shortname = 'district' AND ud.userid = ?";
    $mydistrict = $DB->get_record_sql($sql, [$USER->id]);
    if ($district != $mydistrict->district) {
        print_error('wrongdistrict', 'gradeexport_checklist');
    }
}

if ($group) {
    if ($course->groupmode != VISIBLEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        if (!groups_is_member($group)) {
            print_error('wronggroup', 'gradeexport_checklist');
        }
    }
} else {
    if ($course->groupmode != VISIBLEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        $group = groups_get_all_groups($course->id, $USER->id);
        if ($group) {
            $group = array_keys($group);
        }
    }
}

if (!$checklist = $DB->get_record('checklist', array('id' => $checklistid))) {
    print_error('checklistnotfound', 'gradeexport_checklist');
}

$studentmarking = ($checklist->teacheredit != CHECKLIST_MARKING_TEACHER);
$teachermarking = ($checklist->teacheredit != CHECKLIST_MARKING_STUDENT);

$strchecklistreport = get_string('checklistreport', 'gradeexport_checklist');

$users = get_users_by_capability($context, 'mod/checklist:updateown', 'u.*', 'u.firstname, u.lastname', '', '', $group, false);

if ($district && $district !== 'ALL' && $users) {
    list($usql, $uparam) = $DB->get_in_or_equal(array_keys($users));

    $sql = "
       SELECT u.*
         FROM ({user} u
         JOIN {user_info_data} ud ON u.id = ud.userid)
         JOIN {user_info_field} uf ON ud.fieldid = uf.id
        WHERE u.id $usql AND uf.shortname = 'district' AND ud.data = ?";
    $users = $DB->get_records_sql($sql, array_merge($uparam, [$district]));
}
if (!$users) {
    print_error('nousers', 'gradeexport_checklist');
}

require_once($CFG->dirroot.'/grade/export/checklist/columns.php');
if (!$percentcol) {
    unset($checklistexportusercolumns['_percent']);
}

// Useful for debugging.
if (defined('BEHAT_SITE_RUNNING')) {
    class FakeMoodleExcelWorkbook {
        public function __construct($ignore) {
        }

        public function send($ignore) {
        }

        public function write_string($row, $col, $data) {
            echo "($row, $col) = $data<br/>";
        }

        public function write_number($row, $col, $data) {
            echo "($row, $col) = $data<br/>";
        }

        public function add_worksheet($ignore) {
            return new FakeMoodleExcelWorkbook($ignore);
        }

        public function close() {
        }
    }
}

// Only write the data if it exists.
function safe_write_string($myxls, $row, $col, $user, $extra, $element) {
    if (isset($user[$element])) {
        $myxls->write_string($row, $col, $user[$element]);
    } else if (isset($extra[$element])) {
        $myxls->write_string($row, $col, $extra[$element]->data);
    }
}

// Calculate file name.
$downloadfilename = clean_filename("{$course->shortname} {$checklist->name} $strchecklistreport.xls");
// Creating a workbook.
if (defined('BEHAT_SITE_RUNNING')) {
    $workbook = new FakeMoodleExcelWorkbook("-");
} else {
    $workbook = new MoodleExcelWorkbook("-");
}
// Sending HTTP headers.
$workbook->send($downloadfilename);
// Adding the worksheet.
$wsname = str_replace(array('\\', '/', '?', '*', '[', ']', ' ', ':', '\''), '', $checklist->name);
$wsname = substr($wsname, 0, 31);
$myxls = $workbook->add_worksheet($wsname);

// Print names of all the fields.
$col = 0;
$row = 0;
foreach ($checklistexportusercolumns as $field => $headerstr) {
    $myxls->write_string($row, $col++, $headerstr);
}

if ($percentheadings) {
    if ($exportoptional) {
        $itemoptional = ''; // All items.
    } else {
        $itemoptional = ' AND itemoptional <> '.CHECKLIST_OPTIONAL_YES.' '; // All except optional items.
    }
} else {
    if ($exportoptional) {
        $itemoptional = ' AND itemoptional < '.CHECKLIST_OPTIONAL_HEADING; // All except headings.
    } else {
        $itemoptional = ' AND itemoptional = '.CHECKLIST_OPTIONAL_NO; // Only required items.
    }
}

$items = $DB->get_records_select('checklist_item',
                                 "checklist = ? AND userid = 0 $itemoptional AND hidden = 0",
                                 array($checklist->id), 'position');
if ($items) {
    $parentitem = 0;
    foreach ($items as $item) {
        if ($item->itemoptional == CHECKLIST_OPTIONAL_HEADING) {
            $parentitem = $item->id;
            $items[$item->id]->subitems = array();
        } else if ($parentitem && $item->itemoptional == CHECKLIST_OPTIONAL_NO) {
            $items[$parentitem]->subitems[] = $item->id;
        }
        $myxls->write_string($row, $col++, strip_tags($item->displaytext));
    }

    $countitems = array();
    if ($percentrow && !empty($users)) {
        list($isql, $iparam) = $DB->get_in_or_equal(array_keys($items));
        list($usql, $uparam) = $DB->get_in_or_equal(array_keys($users));
        $sql = "SELECT item, COUNT(*) AS countitems
                  FROM {checklist_check}
                 WHERE item $isql
                   AND userid $usql";
        if (!$teachermarking) {
            $sql .= ' AND usertimestamp > 0 ';
        } else {
            $sql .= ' AND teachermark = '.CHECKLIST_TEACHERMARK_YES.' ';
        }
        $sql .= ' GROUP BY item';
        $countitems = $DB->get_records_sql($sql, array_merge($iparam, $uparam));
    }

    if (!empty($countitems)) {
        $row++;
        $columncount = count($checklistexportusercolumns);
        for ($col = 0; $col < $columncount; $col++) {
            $myxls->write_string($row, $col, '');
        }
        $countusers = count($users);
        foreach ($items as $item) {
            if ($item->itemoptional == CHECKLIST_OPTIONAL_HEADING) {
                $percent = '';
            } else if (empty($countitems[$item->id]->countitems)) {
                $percent = '0%';
            } else {
                $percent = round(100 * $countitems[$item->id]->countitems / $countusers, 0).'%';
            }
            $myxls->write_string($row, $col++, $percent);
        }
    }
}

// Go through each of the users.
$row++;
$itemoptional = str_replace('itemoptional', 'i.itemoptional', $itemoptional);
foreach ($users as $user) {
    $sql = "
       SELECT uf.shortname, ud.data
         FROM {user_info_data} ud
         JOIN {user_info_field} uf ON uf.id = ud.fieldid
        WHERE ud.userid = ?
    ";
    $extra = $DB->get_records_sql($sql, array($user->id));
    $groups = groups_get_all_groups($course->id, $user->id, 0, 'g.id, g.name');
    if ($groups) {
        $groups = array_values($groups);
        $first = reset($groups);
        $groupsstr = $first->name;
        while ($next = next($groups)) {
            $groupsstr .= ', '.$next->name;
        }
    } else {
        $groupsstr = '';
    }
    $col = 0;

    $sql = "
       SELECT i.id, i.itemoptional, c.usertimestamp, c.teachermark
         FROM {checklist_item} i
         LEFT JOIN (
             SELECT ch.item, ch.usertimestamp, ch.teachermark
               FROM {checklist_check} ch
              WHERE ch.userid = ?
         ) c ON c.item = i.id
       WHERE i.checklist = ? AND userid = 0 $itemoptional AND i.hidden = 0
       ORDER BY i.position
    ";
    $checks = $DB->get_records_sql($sql, array($user->id, $checklist->id));

    $userarray = (array)$user;
    foreach ($checklistexportusercolumns as $field => $header) {
        if ($field === '_groups') {
            $myxls->write_string($row, $col++, $groupsstr);

        } else if ($field === '_enroldate') {
            $sql = 'SELECT ue.id, ue.timestart FROM {user_enrolments} ue, {enrol} e ';
            $sql .= "WHERE e.id = ue.enrolid AND e.courseid = ? AND ue.userid = ? AND e.enrol <> 'guest' ";
            $sql .= 'ORDER BY ue.timestart ASC ';
            $enrolement = $DB->get_records_sql($sql, array($course->id, $user->id), 0, 1);
            $datestr = '';
            if (!empty($enrolement)) {
                $enrolement = reset($enrolement);
                $datestr = userdate($enrolement->timestart, get_string('strftimedate'));
            }
            $myxls->write_string($row, $col++, $datestr);

        } else if ($field === '_startdate') {
            $firstview = null;
            $manager = get_log_manager();
            $readers = $manager->get_readers('\core\log\sql_reader');
            /** @var \core\log\sql_reader $reader */
            $reader = reset($readers);
            if ($reader) {
                $select = "userid = ? AND courseid = ? AND target = 'course' AND action = 'viewed'";
                $params = array($user->id, $course->id);
                $events = $reader->get_events_select($select, $params, 'timecreated ASC', 0, 1);
                /** @var \core\event\base $event */
                $event = reset($events);
                if ($event) {
                    $firstview = $event->timecreated;
                }
            }
            $datestr = '';
            if ($firstview) {
                $datestr = userdate($firstview, get_string('strftimedate'));
            }
            $myxls->write_string($row, $col++, $datestr);

        } else if ($field === '_percent') {
            $checked = 0;
            $total = 0;
            foreach ($checks as $check) {
                if ($check->itemoptional != CHECKLIST_OPTIONAL_NO) {
                    continue; // Only count 'required' items.
                }
                $total++;
                if (!$teachermarking) {
                    if ($check->usertimestamp > 0) {
                        $checked++;
                    }
                } else { // Teacher / both => use teacher mark for percentage.
                    if ($check->teachermark == CHECKLIST_TEACHERMARK_YES) {
                        $checked++;
                    }
                }
            }
            if ($checked == 0) {
                $percent = '';
            } else {
                $percent = round(100 * $checked / $total, 0).'%';
            }
            $myxls->write_string($row, $col++, $percent);

        } else {
            safe_write_string($myxls, $row, $col++, $userarray, $extra, $field);
        }
    }

    foreach ($checks as $check) {
        $out = '';
        if ($check->itemoptional == CHECKLIST_OPTIONAL_HEADING) {
            $checked = 0;
            $item = $items[$check->id];
            foreach ($item->subitems as $subitem) {
                if (!$teachermarking) {
                    if ($checks[$subitem]->usertimestamp > 0) {
                        $checked++;
                    }
                } else {
                    if ($checks[$subitem]->teachermark == CHECKLIST_TEACHERMARK_YES) {
                        $checked++;
                    }
                }
            }
            if ($count = count($item->subitems)) {
                $out .= round(100 * $checked / $count, 0).'%';
            }
        } else {
            if ($teachermarking) {
                if ($check->teachermark == CHECKLIST_TEACHERMARK_NO) {
                    $out .= 'N';
                } else if ($check->teachermark == CHECKLIST_TEACHERMARK_YES) {
                    $out .= 'Y';
                }
            }
            if ($studentmarking && $check->usertimestamp > 0) {
                $out .= '1';
            }
        }
        $myxls->write_string($row, $col, $out);
        $col++;
    }
    $row++;
}

$workbook->close();
exit;
