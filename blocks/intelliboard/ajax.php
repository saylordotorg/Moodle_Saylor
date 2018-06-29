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

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot .'/local/intelliboard/locallib.php');

require_login();

$action = required_param('action', PARAM_ALPHANUMEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);

$teacher_roles = get_config('local_intelliboard', 'filter10');
$learner_roles = get_config('local_intelliboard', 'filter11');

$grade_avg = intelliboard_grade_sql(true);
$grade_single = intelliboard_grade_sql();

$data = array();
$html = '';

if ($cmid) {
    $context = context_module::instance($cmid);
    $canupdate = has_capability('moodle/course:manageactivities', $context);
} elseif ($courseid) {
    $context = context_course::instance($courseid);
    $canupdate = has_capability('moodle/course:update', $context);
}

if ($action == 'intelliboard_learners_progress') {
    $access = true;
    $params = array(
        'u1' => $USER->id,
        'u2' => $USER->id,
        'cmid' => $cmid,
        'courseid' => $courseid
    );

    list($sql, $params) = intelliboard_filter_in_sql($learner_roles, "ra.roleid", $params);

    if ($cmid and !$canupdate) {
        $access = false;
    } elseif ($cmid and $canupdate) {
        $data = $DB->get_record_sql("SELECT
                    COUNT(DISTINCT ra.userid) as enrolled,
                    COUNT(DISTINCT cc.userid) as completed
                FROM {role_assignments} ra
                    LEFT JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50
                    LEFT JOIN {course_modules} cm ON cm.course = ctx.instanceid AND cm.visible = 1
                    LEFT JOIN {course_modules_completion} cc ON cc.coursemoduleid = cm.id AND cc.completionstate IN(1,2) AND cc.userid = ra.userid
                WHERE ctx.instanceid = :courseid AND cm.id = :cmid $sql
                GROUP BY ctx.instanceid", $params);
    } elseif ($courseid) {
        if ($canupdate) {
            $data = $DB->get_record_sql("SELECT
                    COUNT(DISTINCT ra.userid) as enrolled,
                    COUNT(DISTINCT cc.userid) as completed
                FROM {role_assignments} ra
                    LEFT JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50
                    LEFT JOIN {course_completions} cc ON cc.course = ctx.instanceid AND cc.timecompleted > 0 AND cc.userid = ra.userid
                WHERE ctx.instanceid = :courseid $sql
                GROUP BY ctx.instanceid", $params);
        } else {
            $data = $DB->get_record_sql("SELECT
                    COUNT(DISTINCT cm.id) as enrolled,
                    COUNT(DISTINCT cmc.id) as completed,
                    MAX(cc.timecompleted) AS timecompleted
                FROM {course_modules} cm
                    LEFT JOIN {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id AND cmc.userid = :u1 AND cmc.completionstate IN(1,2)
                    LEFT JOIN {course_completions} cc ON cc.course = cm.course AND cc.userid = :u2
                WHERE cm.course = :courseid AND cm.visible = 1 AND cm.completion > 0
                GROUP BY cm.course", $params);
        }
    }
    if ($access) {
        $html = html_writer::start_tag('div', array('class' => 'summary-chart-wrap'));
        $html .= html_writer::start_tag('span', array('class' => 'summary-chart-label'));
        $html .= intval(($data->completed / $data->enrolled ) * 100) . '%';
        $html .= html_writer::tag('i', get_string('s10', 'block_intelliboard'));
        $html .= html_writer::end_tag('span');
        $html .= html_writer::tag('div', '', array('id' => 'widget_progress', 'class' => 'summary-chart'));
        $html .= html_writer::end_tag('div');

        $lang1 = ($canupdate) ? get_string('s11', 'block_intelliboard') : get_string('s14', 'block_intelliboard');
        $lang2 = ($canupdate) ? get_string('s12', 'block_intelliboard') : get_string('s15', 'block_intelliboard');
        $lang3 = ($canupdate) ? get_string('s13', 'block_intelliboard') : get_string('s16', 'block_intelliboard');

        if (!$cmid) {
            $html .= html_writer::start_tag('p');
            $html .= html_writer::tag('span', $lang1);
            $html .= html_writer::tag('strong', $data->enrolled);
            $html .= html_writer::end_tag('p');
        }
        $html .= html_writer::start_tag('p');
        $html .= html_writer::tag('span', $lang2);
        $html .= html_writer::tag('strong', $data->completed);
        $html .= html_writer::end_tag('p');
        $html .= html_writer::start_tag('p');
        $html .= html_writer::tag('span', $lang3);
        $html .= html_writer::tag('strong', ($data->enrolled - $data->completed));
        $html .= html_writer::end_tag('p');
    }
} elseif ($action == 'intelliboard_course_summary') {
    $params = array(
        'u1' => $USER->id,
        'u2' => $USER->id,
        'c1' => $courseid,
        'c2' => $courseid,
        'c3' => $courseid
    );
    list($sql, $params) = intelliboard_filter_in_sql($learner_roles, "ra.roleid", $params);


    if ($cmid) {
        $params['cmid1'] = $cmid;
        $params['cmid2'] = $cmid;

        if ($canupdate) {
            $data = $DB->get_record_sql("SELECT
                cm.id,
                (SELECT $grade_avg FROM {grade_items} gi, {grade_grades} g WHERE g.itemid = gi.id AND g.finalgrade IS NOT NULL AND gi.itemtype = 'mod' AND gi.iteminstance = cm.instance AND gi.itemmodule = m.name) AS grade,
                l.visits,
                l.timespend
                FROM {modules} m, {course_modules} cm
                    LEFT JOIN (SELECT param, SUM(visits) AS visits, SUM(timespend) AS timespend FROM {local_intelliboard_tracking} WHERE param = :cmid2 AND page = 'module' AND userid IN (SELECT DISTINCT ra.userid FROM {role_assignments} ra, {context} ctx WHERE ctx.id = ra.contextid AND ctx.instanceid = :c3 AND ctx.contextlevel = 50 $sql) GROUP BY param) l ON l.param = cm.id
                WHERE cm.id = :cmid1 AND cm.module = m.id", $params);
        } else {
            $data = $DB->get_record_sql("SELECT
                cm.id,
                (SELECT $grade_single FROM {grade_items} gi, {grade_grades} g WHERE g.itemid = gi.id AND g.finalgrade IS NOT NULL AND gi.itemtype = 'mod' AND gi.iteminstance = cm.instance AND gi.itemmodule = m.name AND g.userid = :u1) AS grade,
                l.visits,
                l.timespend
                FROM {modules} m, {course_modules} cm
                    LEFT JOIN (SELECT param, SUM(visits) AS visits, SUM(timespend) AS timespend FROM {local_intelliboard_tracking} WHERE param = :cmid2 AND page = 'module' AND userid = :u2 GROUP BY param) l ON l.param = cm.id
                WHERE cm.id = :cmid1 AND cm.module = m.id", $params);
        }
    } else {
        if ($canupdate) {
            $data = $DB->get_record_sql("SELECT
                    SUM(l.visits) AS visits,
                    SUM(l.timespend) AS timespend,
                    $grade_avg as grade
                FROM {grade_items} gi
                    LEFT JOIN {grade_grades} g ON g.itemid = gi.id AND g.finalgrade IS NOT NULL
                    LEFT JOIN (SELECT courseid, SUM(visits) AS visits, SUM(timespend) AS timespend FROM {local_intelliboard_tracking} WHERE courseid = :c2 AND userid IN (SELECT DISTINCT ra.userid FROM {role_assignments} ra, {context} ctx WHERE ctx.id = ra.contextid AND ctx.instanceid = :c3 AND ctx.contextlevel = 50 $sql) GROUP BY courseid) l ON l.courseid = gi.courseid
                WHERE gi.itemtype = 'course' AND gi.courseid = :c1
                GROUP BY gi.courseid", $params);
        } else {
            $data = $DB->get_record_sql("SELECT
                    l.visits,
                    l.timespend,
                    $grade_single as grade
                FROM {grade_items} gi
                    LEFT JOIN {grade_grades} g ON g.userid = :u1 AND g.itemid = gi.id AND g.finalgrade IS NOT NULL
                    LEFT JOIN (SELECT courseid, SUM(visits) AS visits, SUM(timespend) AS timespend FROM {local_intelliboard_tracking} WHERE courseid = :c2 AND userid = :u2 GROUP BY courseid) l ON l.courseid = gi.courseid
                WHERE gi.itemtype = 'course' AND gi.courseid = :c1", $params);
        }
    }


    $lang1 = ($canupdate) ? get_string('s17', 'block_intelliboard') : get_string('s18', 'block_intelliboard');
    if ($cmid) {
        $lang2 = ($canupdate) ? get_string('s19', 'block_intelliboard') : get_string('s23', 'block_intelliboard');
        $lang3 = ($canupdate) ? get_string('s20', 'block_intelliboard') : get_string('s24', 'block_intelliboard');
    } else {
        $lang2 = ($canupdate) ? get_string('s21', 'block_intelliboard') : get_string('s25', 'block_intelliboard');
        $lang3 = ($canupdate) ? get_string('s22', 'block_intelliboard') : get_string('s26', 'block_intelliboard');
    }

    $html = html_writer::start_tag('div', array('class' => 'summary-chart-wrap'));
    $html .= html_writer::start_tag('span', array('class' => 'summary-chart-label'));
    $html .= intval($data->grade) . '%';
    $html .= html_writer::tag('i', $lang1);
    $html .= html_writer::end_tag('span');
    $html .= html_writer::tag('div', '', array('id' => 'widget_summary', 'class' => 'summary-chart'));
    $html .= html_writer::end_tag('div');

    $html .= html_writer::start_tag('p');
    $html .= html_writer::tag('span', $lang2);
    $html .= html_writer::tag('strong', seconds_to_time($data->timespend));
    $html .= html_writer::end_tag('p');
    $html .= html_writer::start_tag('p');
    $html .= html_writer::tag('span', $lang3);
    $html .= html_writer::tag('strong', $data->visits);
    $html .= html_writer::end_tag('p');
} elseif ($action == 'intelliboard_activities_progress' and $canupdate and !$cmid) {
    $sql_columns = "";
    $modules = $DB->get_records_sql("SELECT m.id, m.name FROM {modules} m WHERE m.visible = 1");
    foreach($modules as $module){
        $sql_columns .= " WHEN m.name='{$module->name}' THEN (SELECT name FROM {".$module->name."} WHERE id = cm.instance)";
    }
    $sql_columns =  ($sql_columns) ? ", CASE $sql_columns ELSE 'none' END AS activity" : "'' AS activity";

    $params = array(
        'c1'=>$courseid,
        'c2'=>$courseid,
        'c3'=>$courseid,
        'c4'=>$courseid,
        'c5'=>$courseid
    );
    list($sql1, $params) = intelliboard_filter_in_sql($learner_roles, "ra.roleid", $params);

    $stats = $DB->get_record_sql("SELECT COUNT(DISTINCT ra.userid) AS learners FROM {role_assignments} ra, {context} ctx WHERE ctx.id = ra.contextid AND ctx.instanceid = :c4 AND ctx.contextlevel = 50 $sql1", $params);

    $data = $DB->get_records_sql("SELECT cm.id,
        m.name as module,
        cmc.completed,
        g.grade,
        l.visits,
        l.timespend
        $sql_columns FROM
        {course_modules} cm
            LEFT JOIN {modules} m ON m.id = cm.module
            LEFT JOIN (SELECT gi.iteminstance, gi.itemmodule, $grade_avg AS grade FROM {grade_items} gi, {grade_grades} g WHERE gi.itemtype = 'mod' AND g.itemid = gi.id AND g.finalgrade IS NOT NULL AND gi.courseid = :c1 GROUP BY gi.iteminstance, gi.itemmodule) as g ON g.iteminstance = cm.instance AND g.itemmodule = m.name
            LEFT JOIN (SELECT coursemoduleid, COUNT(id) AS completed FROM {course_modules_completion} WHERE completionstate IN(1,2) GROUP BY coursemoduleid) cmc ON cmc.coursemoduleid = cm.id
            LEFT JOIN (SELECT param, SUM(visits) AS visits, SUM(timespend) AS timespend FROM {local_intelliboard_tracking} WHERE page='module' AND courseid = :c2 AND userid IN (SELECT DISTINCT ra.userid FROM {role_assignments} ra, {context} ctx WHERE ctx.id = ra.contextid AND ctx.instanceid = :c4 AND ctx.contextlevel = 50 $sql1) GROUP BY param) l ON l.param=cm.id
            WHERE cm.visible = 1 AND cm.course = :c3", $params);


    $max_time = 0;
    foreach ($data as $item) {
        if ($max_time<$item->timespend) {
            $max_time = $item->timespend;
        }
    }
    $html = html_writer::start_tag('div', array('class' => 'flat-list'));
    foreach ($data as $item) {
        $completed = ($item->completed and $stats->learners) ? intval(($item->completed / $stats->learners) * 100) : 0;
        $timespend = ($max_time and $item->timespend) ? intval(($item->timespend / $max_time) * 100) : 0;


        $html .= html_writer::start_tag('div', array('class' => 'flat-list-item'));
        $html .= html_writer::tag('h5', $item->activity);

        $html .= html_writer::start_tag('div', array('class' => 'item-sum clearfix'));
        $html .= html_writer::tag('span', get_string('s27', 'block_intelliboard'));
        $html .= html_writer::tag('strong', intval($item->completed));
        $html .= html_writer::tag('div', '', array('class' => 'clearfix clear'));
        $html .= html_writer::tag('div', '', array('class' => 'item-line item-grade', 'title'=> get_string('s27', 'block_intelliboard') . ':' . intval($item->completed), 'style'=>'width:' . $completed.'%'));
        $html .= html_writer::end_tag('div');

        $html .= html_writer::start_tag('div', array('class' => 'item-sum clearfix'));
        $html .= html_writer::tag('span', get_string('s17', 'block_intelliboard'));
        $html .= html_writer::tag('strong', intval($item->grade));
        $html .= html_writer::tag('div', '', array('class' => 'clearfix clear'));
        $html .= html_writer::tag('div', '', array('class' => 'item-line item-completed', 'title'=> get_string('s17', 'block_intelliboard') . ':' . intval($item->grade).'%', 'style'=>'width:' . intval($item->grade).'%'));
        $html .= html_writer::end_tag('div');



        $html .= html_writer::start_tag('div', array('class' => 'item-sum clearfix'));
        $html .= html_writer::tag('span', get_string('s28', 'block_intelliboard'));
        $html .= html_writer::tag('strong', seconds_to_time($item->timespend));
        $html .= html_writer::tag('div', '', array('class' => 'clearfix clear'));
        $html .= html_writer::tag('div', '', array('class' => 'item-line item-time', 'title'=> get_string('s28', 'block_intelliboard') . ':' . seconds_to_time($item->timespend), 'style'=>'width:' . $timespend.'%'));
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');
    }
    $html .= html_writer::end_tag('div');
} elseif ($action == 'intelliboard_learners_performance') {
    $params = array(
        'c1'=>$courseid,
        'c2'=>$courseid,
        'c3'=>$courseid,
        'c4'=>$courseid,
        'c5'=>$courseid,
        'u1' => $USER->id,
        'u2' => $USER->id,
        'u3' => $USER->id,
        'u4' => $USER->id,
        'u5' => $USER->id,
    );
    if ($canupdate) {
        $sql1 = "";
        $sql2 = "";
        $sql3 = "";
        $sql4 = "";
        $sql5 = "";
    } else {
        $sql1 = " AND g.userid = :u1";
        $sql2 = " AND g.userid = :u2";
        $sql3 = " AND g.userid = :u3";
        $sql4 = " AND g.userid = :u4";
        $sql5 = " AND g.userid = :u5";
    }
    if ($cmid) {
        $cm = $DB->get_record_sql("SELECT cm.instance, m.name FROM {course_modules} cm, {modules} m WHERE m.id = cm.module AND cm.id = :id",
            array("id" => $cmid));

        $params['m1'] = $cm->name;
        $params['m2'] = $cm->name;
        $params['m3'] = $cm->name;
        $params['m4'] = $cm->name;
        $params['m5'] = $cm->name;

        $params['i1'] = $cm->instance;
        $params['i2'] = $cm->instance;
        $params['i3'] = $cm->instance;
        $params['i4'] = $cm->instance;
        $params['i5'] = $cm->instance;

        $sql1 .= " AND gi.itemmodule = :m1 AND gi.iteminstance =:i1";
        $sql2 .= " AND gi.itemmodule = :m2 AND gi.iteminstance =:i2";
        $sql3 .= " AND gi.itemmodule = :m3 AND gi.iteminstance =:i3";
        $sql4 .= " AND gi.itemmodule = :m4 AND gi.iteminstance =:i4";
        $sql5 .= " AND gi.itemmodule = :m5 AND gi.iteminstance =:i5";
    }

    $data = $DB->get_record_sql("SELECT
        (SELECT COUNT(g.finalgrade) FROM {grade_items} gi, {grade_grades} g WHERE
        g.itemid = gi.id AND gi.itemtype = 'mod' AND g.finalgrade IS NOT NULL
        AND gi.courseid = :c1 AND $grade_single < 60  $sql1) AS grade_f,

        (SELECT COUNT(g.finalgrade) FROM {grade_items} gi, {grade_grades} g WHERE
        g.itemid = gi.id AND gi.itemtype = 'mod' AND g.finalgrade IS NOT NULL
        AND gi.courseid = :c2 AND $grade_single > 59 and $grade_single < 70  $sql2) AS grade_d,

        (SELECT COUNT(g.finalgrade) FROM {grade_items} gi, {grade_grades} g WHERE
        g.itemid = gi.id AND gi.itemtype = 'mod' AND g.finalgrade IS NOT NULL
        AND gi.courseid = :c3 AND $grade_single > 69 and $grade_single < 80  $sql3) AS grade_c,

        (SELECT COUNT(g.finalgrade) FROM {grade_items} gi, {grade_grades} g WHERE
        g.itemid = gi.id AND gi.itemtype = 'mod' AND g.finalgrade IS NOT NULL
        AND gi.courseid = :c4 AND $grade_single > 79 and $grade_single < 90  $sql4) AS grade_b,

        (SELECT COUNT(g.finalgrade) FROM {grade_items} gi, {grade_grades} g WHERE
        g.itemid = gi.id AND gi.itemtype = 'mod' AND g.finalgrade IS NOT NULL
        AND gi.courseid = :c5 AND $grade_single > 89  $sql5) AS grade_a", $params);

    $html = html_writer::tag('div', '', array('id' => 'widget_grade', 'class' => 'summary-chart'));

} elseif ($action == 'intelliboard_live_stream' and $canupdate and !$cmid) {
    $time = optional_param('timepoint', 0, PARAM_INT);
    $timepoint = ($time) ? $time : strtotime('-1 hour');
    $activities = array();

    $params = array('courseid'=>$courseid, 'timepoint'=> $timepoint);

    $activities['grades'] = $DB->get_records_sql("SELECT
        g.id,
        g.userid,
        g.timemodified AS timepoint,
        gi.itemname,
        gi.itemtype,
        gi.itemmodule,
        u.firstname,
        u.lastname,
        $grade_single AS grade
     FROM
        {grade_items} gi,
        {grade_grades} g,
        {user} u
     WHERE g.userid = u.id AND g.itemid = gi.id AND g.finalgrade IS NOT NULL AND gi.courseid = :courseid AND g.timemodified > :timepoint
     ORDER BY g.timemodified DESC", $params, 0, 10);

    $activities['enrols'] = $DB->get_records_sql("SELECT
        ra.id,
        ra.userid,
        ra.timemodified AS timepoint,
        r.shortname,
        u.firstname,
        u.lastname
        FROM {role_assignments} ra, {user} u, {context} ctx, {role} r
     WHERE r.id = ra.roleid AND u.id = ra.userid AND ctx.id = ra.contextid AND ctx.contextlevel = 50 AND ctx.instanceid = :courseid AND ra.timemodified > :timepoint
     ORDER BY ra.timemodified DESC", $params, 0, 10);

    $sql_columns = "";
    $modules = $DB->get_records_sql("SELECT m.id, m.name FROM {modules} m WHERE m.visible = 1");
    foreach($modules as $module){
        $sql_columns .= " WHEN m.name='{$module->name}' THEN (SELECT name FROM {".$module->name."} WHERE id = cm.instance)";
    }
    $sql_columns =  ($sql_columns) ? ", CASE $sql_columns ELSE 'none' END AS activity" : "'' AS activity";

    $activities['visits'] = $DB->get_records_sql("SELECT
        l.id,
        l.userid,
        l.page,
        l.param,
        l.useragent,
        l.lastaccess AS timepoint,
        m.name,
        u.firstname,
        u.lastname
        $sql_columns
        FROM {local_intelliboard_tracking} l
            LEFT JOIN {course_modules} cm ON cm.id = l.param
            LEFT JOIN {modules} m ON m.id = cm.module
            LEFT JOIN {user} u ON u.id = l.userid
     WHERE l.courseid = :courseid AND l.lastaccess > :timepoint
     ORDER BY l.lastaccess DESC", $params, 0, 10);

    $activities['completions'] = $DB->get_records_sql("SELECT
        c.id,
        c.userid,
        c.timecompleted AS timepoint,
        u.firstname,
        u.lastname
        FROM {course_completions} c
            LEFT JOIN {user} u ON u.id = c.userid
     WHERE c.course = :courseid AND c.timecompleted > :timepoint
     ORDER BY c.timecompleted DESC", $params, 0, 10);

    $activities['modules'] = $DB->get_records_sql("SELECT
        cmc.id,
        cmc.userid,
        cmc.timemodified AS timepoint,
        cmc.coursemoduleid,
        m.name,
        u.firstname,
        u.lastname
        $sql_columns
        FROM {course_modules} cm
            LEFT JOIN {modules} m ON m.id = cm.module
            LEFT JOIN {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id
            LEFT JOIN {user} u ON u.id = cmc.userid
     WHERE cm.course = :courseid AND cmc.completionstate IN(1,2) AND cmc.timemodified > :timepoint
     ORDER BY cmc.timemodified DESC", $params, 0, 10);

    $users = array();
    foreach ($activities as $key => $values) {
        foreach ($values as $value) {
            $value->key = $key;
            if (!isset($users[$value->timepoint])) {
                $users[$value->timepoint] = $value;
            }else{
                for ($i=1;$i<100;$i++) {
                    $timepoint = $value->timepoint + $i;
                    if (!isset($users[$timepoint])) {
                        $users[$timepoint] = $value;
                        break;
                    }
                }
            }
        }
    }
    krsort($users);

    if (!$time) {
        $html .= html_writer::start_tag('div', array('class' => 'intelliboard-block-activities'));
    }
    foreach ($users as $item) {
        $date = html_writer::tag('span', date('m/d/Y h:i:s', $item->timepoint));
        $params = new stdClass();
        $params->user = html_writer::link(new moodle_url("/user/view.php", array('id' => $item->userid)), fullname($item), array('target' => '_blank'));

        if ($item->key == 'grades') {
            $params->grade = html_writer::tag('strong', $item->grade);
            if ($item->itemtype == 'mod') {
                $itemname = html_writer::tag('ins', $item->itemname);
                $params->name =  "$itemname $item->itemmodule";
            } else {
                $params->name = html_writer::tag('strong', get_string('course'));
            }
            $text = get_string('s30', 'block_intelliboard', $params);
        } elseif ($item->key == 'enrols') {
            $params->name = html_writer::tag('strong', $item->shortname);
            $text = get_string('s32', 'block_intelliboard', $params);
        } elseif ($item->key == 'visits') {
            if ($item->page == 'module') {
                $params->name = html_writer::link(new moodle_url("/mod/$item->name/view.php", array('id' => $item->param)), $item->activity, array('target' => '_blank'));
            } else {
                $params->name = html_writer::tag('strong', get_string('course'));
            }
            $text = get_string('s32', 'block_intelliboard', $params);
        } elseif ($item->key == 'completions') {
            $params->name = html_writer::tag('strong', get_string('course'));
            $text = get_string('s33', 'block_intelliboard', $params);
        } elseif ($item->key == 'modules') {
            $params->name = html_writer::link(new moodle_url("/mod/$item->name/view.php", array('id' => $item->coursemoduleid)), $item->activity, array('target' => '_blank'));
            $text = get_string('s33', 'block_intelliboard', $params);
        }
        $html .= html_writer::tag('div', "$date $text", array('class' => 'intelliboard-block-activity'));
    }
    if (!$time) {
        $html .= html_writer::end_tag('div');
    }
    $data = time();
}

$response = array(
    'data' => $data,
    'html' => $html
);
die(json_encode($response));
