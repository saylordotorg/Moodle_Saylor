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
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
$returnurl = new moodle_url('/auth/mcae/convert.php');

admin_externalpage_setup('cohorttoolmcae');

require_capability('moodle/site:config', $context, $USER->id);

$action = optional_param('action', 'list', PARAM_ALPHA);
$clist = (isset($_POST['clist'])) ? $_POST['clist'] : false;

switch ($action) {
    case 'list':
        $cohorts = $DB->get_records('cohort', array('contextid' => $context->id), 'name ASC');
        $cohortslist = array();

        foreach ($cohorts as $cohort) {
            $cid = $cohort->id;
            $cname = format_string($cohort->name);
            $cohortslist[$cid]['name'] = $cname;
            $cohortslist[$cid]['component'] = $cohort->component;
            $cohortslist[$cid]['count'] = $DB->count_records('cohort_members', array('cohortid' => $cid));
        }

        $row = array();
        $cell = array();
        $rownum = 0;

        foreach ($cohortslist as $key => $val) {
            $color = ($val['component'] == 'auth_mcae') ? '#f4c430 !important' : '#e9967a !important';
            $viewurl = new moodle_url('/auth/mcae/view.php', array('cid' => $key));

            $row[$rownum] = new html_table_row();
            $cell[1] = new html_table_cell();
            $cell[2] = new html_table_cell();
            $cell[3] = new html_table_cell();
            $cell[4] = new html_table_cell();

            $cell[1]->text = '<input type="checkbox" name="clist[]" value="'.$key.'"> '.$val['name'];
            $cell[2]->text = $val['component'];
            $cell[3]->text = $val['count'];
            $cell[4]->text = '<a href="'.$viewurl.'">'.get_string('auth_userlink', 'auth_mcae').'</a>';

            $cell[1]->style = 'font-weight: bold; background-color: '. $color .';';
            $cell[2]->style = 'font-style: italic; background-color: '. $color .';';
            $cell[3]->style = 'background-color: '. $color .';';
            $cell[4]->style = 'background-color: '. $color .';';

            $row[$rownum]->cells = $cell;
            $rownum++;
        }

        $table = new html_table();
        $table->head = array(
            get_string('auth_cohortname', 'auth_mcae'),
            get_string('auth_component', 'auth_mcae'),
            get_string('auth_count', 'auth_mcae'),
            get_string('auth_link', 'auth_mcae')
        );
        $table->width = '60%';
        $table->data = $row;

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('auth_cohorttoolmcae', 'auth_mcae'));

        echo get_string('auth_cohortoper_help', 'auth_mcae');
        echo "<form action=\"{$returnurl}\" method=\"POST\">";

        echo html_writer::table($table);

        echo '<select name="action"><option value="do">Convert to auth_mcae</option>'.
             '<option value="restore">Convert to manual</option><option value="delete">'.
             'Delete cohorts</option></select>';
        echo '<input type="submit" name="submit" value="Submit">';
        echo '</form>';
    break;
    case 'do':
        if ($clist) {
            list($usql, $params) = $DB->get_in_or_equal($clist);
            $DB->set_field_select('cohort', 'component', 'auth_mcae', 'id ' . $usql, $params);
        };
        redirect($returnurl);
    break;
    case 'restore':
        if ($clist) {
            list($usql, $params) = $DB->get_in_or_equal($clist);
            $DB->set_field_select('cohort', 'component', '', 'id ' . $usql, $params);
        };
        redirect($returnurl);
    break;
    case 'delete':
        if ($clist) {
            set_time_limit(0);

            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('auth_cohorttoolmcae', 'auth_mcae'));

            $progress = new progress_bar('delcohort');
            $progress->create();
            $delcount = count($clist);
            $delcurrent = 1;

            foreach ($clist as $cid) {
                $cohort = $DB->get_record('cohort', array('contextid' => $context->id, 'id' => $cid));
                cohort_delete_cohort($cohort);
                $progress->update($delcurrent, $delcount, "{$delcurrent} / {$delcount}");
                $delcurrent++;
            };
        };
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die();
    break;
}

echo $OUTPUT->footer();
