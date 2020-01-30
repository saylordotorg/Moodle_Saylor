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
 * Completion overview report.
 *
 * @package   report_completionoverview
 * @copyright 2017 Jim Harris <jim.harris@twoscope.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Get our initial params.
$courseid = optional_param('id', 0, PARAM_INT);

// Paging params for paging bars.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$page2 = optional_param('page2', 0, PARAM_INT); // Which page to show for our second progress bar.
$page3 = optional_param('page3', 0, PARAM_INT); // Which page to show for our third progress bar.
$perpage = optional_param('perpage', 10, PARAM_INT); // How many per page.
$baseurl = new moodle_url('index.php', array('id' => $courseid));

admin_externalpage_setup('reportcompletionoverview', '', null, '', array('pagelayout' => 'report'));
require_login();

// Set contesxt and check capability.
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('report/coursecompletionoverview:view', $context);

// Get our data.
// Courses not tracking completion.
$compcoursenamesql = "SELECT DISTINCT fullname AS fname, id
                                         FROM {course}
                                         WHERE enablecompletion <> ?
                                         AND category > ?
                                         ORDER BY fname";
$compduplicate = $DB->get_records_sql($compcoursenamesql, array('1', '0'));
$nocompletioncount = count($compduplicate);
$compcoursename = $DB->get_records_sql($compcoursenamesql, array('1', '0'), $page * $perpage, $perpage);
// Get completion totals for courses which are tracking.
$compdatasql = "SELECT DISTINCT cr.id AS courseid, cr.fullname AS coursename,
                COUNT(DISTINCT ra.id ) AS enrols,
                COUNT(DISTINCT cc.timecompleted) AS completed
		        FROM {course} cr
		        JOIN {context} ct ON ( ct.instanceid = cr.id )
		        LEFT JOIN {role_assignments} ra ON ( ra.contextid = ct.id ) and ra.roleid = 5
		        LEFT JOIN {course_completions} cc ON cc.course = cr.id
		        WHERE ct.contextlevel = ? AND cr.id <> ? AND cr.enablecompletion = ?
               GROUP BY  cr.fullname, cr.id
               ORDER BY coursename";
$compdatacount = $DB->get_records_sql($compdatasql, array('50', '1', '1'));
$completioncount = count($compdatacount);
$compdata = $DB->get_records_sql($compdatasql, array('50', '1', '1'), $page2 * $perpage, $perpage);

// Get course with completion data in array for our select dropdown.
$table = 'course';
$conditions = array('enablecompletion' => '1');
$sort = 'fullname';
$fields = 'id, fullname';
$result = $DB->get_records_menu($table, $conditions, $sort, $fields);

echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class' => 'span9 well'));
echo html_writer::start_tag('p');
echo ($nocompletioncount.' '.get_string('countnocompletions', 'report_completionoverview'));
echo html_writer::end_tag('p');
// This is the table which displays the courses without completion.
$table = new html_table();
$table->width = '*';
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');
foreach ($compcoursename as $row) {
    $a = array();
    $a[] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$row->id.'">'.$row->fname.'</a>';
    $table->data[] = $a;
}
echo html_writer::table($table);
// Paging bar for the no completion table.
echo $OUTPUT->paging_bar($nocompletioncount, $page, $perpage, $baseurl);

echo html_writer::end_tag('div'); // End span 9 well.
echo html_writer::start_tag('div', array('class' => 'span9 well'));
echo html_writer::start_tag('p');
echo (get_string('completionsoverview', 'report_completionoverview') .' - ');
echo ($completioncount.' '.get_string('countcompletions', 'report_completionoverview'));
echo html_writer::end_tag('p');

// This is the table which shows our list of courses tracking completion.
$table = new html_table();
$table->width = '*';
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');
$table->head = array(get_string('tablehead1', 'report_completionoverview'),
get_string('tablehead2', 'report_completionoverview'), get_string('tablehead3', 'report_completionoverview'),
get_string('tablehead4', 'report_completionoverview'));

foreach ($compdata as $row) {
    $notcompleted = $row->enrols - $row->completed;
    $a = array();
    $a[] = '<a target="_new" href="'.$CFG->wwwroot.'/course/view.php?id='.$row->courseid.'">'.$row->coursename.'</a>';
    $a[] = $row->enrols;
    $a[] = $row->completed;
    $a[] = $notcompleted;
    $table->data[] = $a;
}
echo html_writer::table($table);
echo $OUTPUT->paging_bar($completioncount, $page2, $perpage, $baseurl, $pagevar = 'page2');

echo html_writer::end_tag('div');

// Put a form on the page to show completion details for selected course.
echo html_writer::start_tag('form', array('action' => 'index.php', 'method' => 'post'));
echo html_writer::start_tag('div');
$table = new html_table();
$table->width = '*';
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');

$coursemenu = html_writer::label('Select course', 'menureport', false, array('class' => 'accesshide'));
$coursemenu .= html_writer::select($result, 'id', $result);

$table->data[] = array(get_string('selecttext', 'report_completionoverview'), $coursemenu,
                       html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('view'))));

echo html_writer::table($table);
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

// Only show datatable if a course is selected from the dropdown.
if (!empty($result) && $courseid != 0) {
    $compdatacoursesql = "SELECT u.id AS userid, c.id as courseid,
                          c.fullname AS fullname,
                          u.firstname AS firstname,
                          u.lastname AS lastname,
                          u.email,
                          cc.timecompleted,
                          cc.timestarted,
                          gg.finalgrade
                          FROM {user} u
                          INNER JOIN {role_assignments} ra ON ra.userid = u.id
                          INNER JOIN {context} ct ON ct.id = ra.contextid
                          INNER JOIN {course} c ON c.id = ct.instanceid
                          AND c.enablecompletion = '1' AND c.id = ".$courseid."
                          INNER JOIN {role} r ON r.id = ra.roleid and r.id = 5
                          LEFT OUTER JOIN {course_completions} cc ON (cc.course = c.id) AND cc.userid = u.id
                          LEFT JOIN
                          (SELECT u.id AS userid,c.id as courseid, g.finalgrade AS finalgrade
                          FROM {user} u
                          JOIN {grade_grades} g ON g.userid = u.id
                          JOIN {grade_items} gi ON g.itemid =  gi.id
                          JOIN {course} c ON c.id = gi.courseid where gi.itemtype = 'course') gg
                          ON gg.userid = u.id and gg.courseid = c.id
                          ORDER BY u.lastname";
    $compdatacoursecount = $DB->get_records_sql($compdatacoursesql);
    $compstudentcount = count($compdatacoursecount);
    $compdatacourse = $DB->get_records_sql($compdatacoursesql, array(), $page3 * $perpage, $perpage);

    $table = new html_table();
    $table->width = '*';
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left');
    $table->head  = array(get_string('coursetablehead1', 'report_completionoverview'),
                          get_string('coursetablehead5', 'report_completionoverview'),
                          get_string('coursetablehead2', 'report_completionoverview'),
                          get_string('coursetablehead4', 'report_completionoverview'),
                          get_string('coursetablehead3', 'report_completionoverview'));

    foreach ($compdatacourse as $row) {
        $a = array();
        $a[] = $row->firstname .= ' '.$row->lastname;
        $a[] = $row->email;
        $a[] = ($row->timestarted ? date('jS F Y H:i:s', ($row->timestarted)) : 'Not started');
        $a[] = ($row->timecompleted ? date('jS F Y H:i:s', ($row->timecompleted)) : 'Not completed');
        $a[] = ($row->finalgrade ? $row->finalgrade : 'No grade');
        $table->data[] = $a;
    }

    // It's possible completion tracking is enabled but there are no enrolled students.
    // Our data set will be empty, so catch this and inform user.
    if (!empty($compdatacourse)) {
        echo $OUTPUT->heading($row->fullname.' - <a target="_new" href="'.$CFG->wwwroot.'/course/view.php?id='.$row->courseid.'">'
        .get_string("courselink", "report_completionoverview").'</a>');
    } else {
        echo $OUTPUT->heading($result[$courseid].' - <a target="_new" href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'
        .get_string("courselink", "report_completionoverview").'</a>');
        echo (get_string('noenrolments', 'report_completionoverview'). '<br /><br />');
    }
    // Here we've modified code from report/completion written by Aaron Barnes <aaronb@catalyst.net.nz>.
    $courseobject = $DB->get_record('course', array('id' => $courseid));
    $modinfo = get_fast_modinfo($courseobject);
    $completion = new completion_info($courseobject);
    $criteria = array();
    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $criterion) {
        $criteria[] = $criterion;
    }
    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion) {
        $criteria[] = $criterion;
    }
    foreach ($completion->get_criteria() as $criterion) {
        if (!in_array($criterion->criteriatype, array(
            COMPLETION_CRITERIA_TYPE_COURSE, COMPLETION_CRITERIA_TYPE_ACTIVITY))) {
            $criteria[] = $criterion;
        }
    }

    // Get the completion criteria names.
    $critname = array();
    foreach ($criteria as $criterion) {
            // Get criteria details.
            $critname[] = $criterion->get_title_detailed();
    }

    // Get user data.
    $progress = array();
    $progress = $completion->get_progress_all();
    // A small function to sort the progress array by lastname.
    uasort($progress, function($a, $b) {
        return $a->lastname > $b->lastname;
    });

    // Put shared pager at the top of our table.
    echo $OUTPUT->paging_bar($compstudentcount, $page3, $perpage, $baseurl, $pagevar = 'page3');
    echo('<table class="table table-bordered generaltable flexible boxaligncenter
        completionreport"><tr><th style="vertical-align:bottom" class="header">Modules</th>');
    $modulecount = '0';
    foreach ($critname as $crit) {
        echo('<th class="colheader criterianame">
        <div class="rotated-text-container"><span class="rotated-text">'.$crit.'</span></div></th>');
        $modulecount++;
    }
    if ($modulecount == 0) {
        echo(get_string('nomodules', 'report_completionoverview'). '<br /><br />');
    }

    print '<th scope="col" class="colheader criterianame">';
    print '<div class="rotated-text-container">
           <span class="rotated-text">'.get_string('coursecomplete', 'completion').'</span></div>';
    print '</th></tr>';
    echo('<tr><th>Student</th>');

    // Print criteria icons.
    foreach ($criteria as $criterion) {
        $iconlink = '';
        $iconalt = ''; // Required.
        $iconattributes = array('class' => 'icon');
        switch ($criterion->criteriatype) {

            case COMPLETION_CRITERIA_TYPE_ACTIVITY:

                // Display icon.
                $iconlink = $CFG->wwwroot.'/mod/'.$criterion->module.'/view.php?id='.$criterion->moduleinstance;
                $iconattributes['title'] = $modinfo->cms[$criterion->moduleinstance]->get_formatted_name();
                $iconalt = get_string('modulename', $criterion->module);
                break;

            case COMPLETION_CRITERIA_TYPE_COURSE:
                // Load course.
                $crs = $DB->get_record('course', array('id' => $criterion->courseinstance));

                // Display icon.
                $iconlink = $CFG->wwwroot.'/course/view.php?id='.$criterion->courseinstance;
                $iconattributes['title'] = format_string(
                $crs->fullname, true, array('context' => context_course::instance($crs->id, MUST_EXIST)));
                $iconalt = format_string($crs->shortname, true, array(
                'context' => context_course::instance($crs->id)));
                break;

            case COMPLETION_CRITERIA_TYPE_ROLE:
                // Load role.
                $role = $DB->get_record('role', array('id' => $criterion->role));

                // Display icon.
                $iconalt = $role->name;
                break;
        }

        // Create icon alt if not supplied.
        if (!$iconalt) {
            $iconalt = $criterion->get_title();
        }

        // Print icon and cell.
        print '<th class="criteriaicon">';
            print ($iconlink ? '<a href="'.$iconlink.'" title="'.$iconattributes['title'].'">' : '');
            print $OUTPUT->render($criterion->get_icon($iconalt, $iconattributes));
            print ($iconlink ? '</a>' : '');
            print '</th>';
    }

    // Overall course completion status.
    print '<th class="criteriaicon">';
    print $OUTPUT->pix_icon('i/course', get_string('coursecomplete', 'completion'));
    print '</th>';
    print '</tr>';
    // Loop through users progress.
    foreach ($progress as $user) {
        // Only show users who are in our data table - this is for paging.
        foreach ($compdatacourse as $row) {
            if ($user->id == $row->userid) {
                // User name.
                echo('<tr><td>'.fullname($user).'</td>');
                foreach ($criteria as $criterion) {
                    $criteria_completion = $completion->get_user_completion($user->id, $criterion);
                    $is_complete = $criteria_completion->is_complete();

                    // Handle activity completion differently.
                    if ($criterion->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {

                        // Load activity.
                        $activity = $modinfo->cms[$criterion->moduleinstance];

                            // Get progress information and state.
                        if (array_key_exists($activity->id, $user->progress)) {
                            $state = $user->progress[$activity->id]->completionstate;
                        } else if ($is_complete) {
                            $state = COMPLETION_COMPLETE;
                        } else {
                            $state = COMPLETION_INCOMPLETE;
                        }
                        if ($is_complete) {
                            $date = userdate($criteria_completion->timecompleted,
                            get_string('strftimedatetimeshort', 'langconfig'));
                        } else {
                            $date = '';
                        }

                        // Work out how it corresponds to an icon.
                        switch($state) {
                            case COMPLETION_INCOMPLETE    : $completiontype = 'n';
                            break;
                            case COMPLETION_COMPLETE      : $completiontype = 'y';
                            break;
                            case COMPLETION_COMPLETE_PASS : $completiontype = 'pass';
                            break;
                            case COMPLETION_COMPLETE_FAIL : $completiontype = 'fail';
                            break;
                        }

                        $auto = $activity->completion == COMPLETION_TRACKING_AUTOMATIC;
                        $completionicon = 'completion-'.($auto ? 'auto' : 'manual').'-'.$completiontype;
                        $describe = get_string('completion-'.$completiontype, 'completion');
                        $a = new StdClass();
                        $a->state     = $describe;
                        $a->date      = $date;
                        $a->user      = fullname($user);
                        $a->activity  = $activity->get_formatted_name();
                        $fulldescribe = get_string('progress-title', 'completion', $a);

                        print '<td class="completion-progresscell">';
                        print $OUTPUT->pix_icon('i/' . $completionicon, $fulldescribe);
                        print '</td>';
                        continue;
                    }
                }
                // Load course completion.
                $params = array(
                    'userid'    => $user->id,
                    'course'    => $courseid
                );

                $ccompletion = new completion_completion($params);
                $completiontype = $ccompletion->is_complete() ? 'y' : 'n';
                $describe = get_string('completion-'.$completiontype, 'completion');

                $a = new StdClass;
                if ($ccompletion->is_complete()) {
                    $a->date = userdate($ccompletion->timecompleted, get_string('strftimedatetimeshort', 'langconfig'));
                } else {
                    $a->date = '';
                }
                $a->state    = $describe;
                $a->user     = fullname($user);
                $a->activity = strip_tags(get_string('coursecomplete', 'completion'));
                $fulldescribe = get_string('progress-title', 'completion', $a);

                print '<td class="completion-progresscell">';
                print $OUTPUT->pix_icon('i/completion-auto-' . $completiontype, $fulldescribe);
                print '</td>';
            }
        }
    }

    echo ('</table>');
    // Our last table showing final grades.
    echo html_writer::table($table);
    echo $OUTPUT->paging_bar($compstudentcount, $page3, $perpage, $baseurl, $pagevar = 'page3');
} // End if course dropdown select.
echo $OUTPUT->footer();