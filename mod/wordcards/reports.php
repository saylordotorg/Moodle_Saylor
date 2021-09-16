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
 * Reports for Wordcards
 *
 *
 * @package    mod_wordcards
 * @copyright  2020 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use \mod_wordcards\constants;
use \mod_wordcards\utils;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // wordcards instance ID
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$showreport = optional_param('report', 'menu', PARAM_TEXT); // report type
$userid = optional_param('userid', 0, PARAM_INT); // user id
$attemptid = optional_param('attemptid', 0, PARAM_INT); // attempt id

//paging details
$paging = new stdClass();
$paging->perpage = optional_param('perpage', -1, PARAM_INT);
$paging->pageno = optional_param('pageno', 0, PARAM_INT);
$paging->sort = optional_param('sort', 'iddsc', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_url(constants::M_URL . '/reports.php',
        array('id' => $cm->id, 'report' => $showreport, 'format' => $format,
                'userid' => $userid, 'attemptid' => $attemptid));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/wordcards:viewreports', $modulecontext);

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

//set per page according to admin setting
if ($paging->perpage == -1) {
    $paging->perpage = 20;//$config->itemsperpage;
}

// Trigger module viewed event.
$mod = mod_wordcards_module::get_by_cmid($cm->id);
$mod->register_module_viewed();

/// Set up the page header
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
$pagetitle .= ': ' . get_string('reports', constants::M_COMPONENT);
$PAGE->set_title($pagetitle);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}
$PAGE->requires->jquery();


//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$reportrenderer = $PAGE->get_renderer(constants::M_COMPONENT, 'report');

//From here we actually display the page.
//this is core renderer stuff
$mode = "reports";
$extraheader = "";
$wordcardsmodule = mod_wordcards_module::get_by_modid($moduleinstance->id);
switch ($showreport) {

    //not a true report, separate implementation in renderer
    case 'menu':
        echo $renderer->header();
        echo $renderer->heading($pagetitle);
        echo $renderer->navigation($wordcardsmodule , 'reports');
        echo get_string('reportsmenutop', constants::M_COMPONENT);
        echo $reportrenderer->render_reportmenu($moduleinstance, $cm);
        // Finish the page
        echo $renderer->footer();
        return;

    case 'basic':
        $report = new \mod_wordcards\local\report\basic();
        //formdata should only have simple values, not objects
        //later it gets turned into urls for the export buttons
        $formdata = new stdClass();
        break;

    case 'attempts':
        $report = new \mod_wordcards\local\report\attempts();
        $formdata = new stdClass();

        $formdata->modid = $moduleinstance->id;
        $formdata->modulecontextid = $modulecontext->id;
        $formdata->groupmenu = true;
        break;

    case 'userattempts':
        if (!groups_user_groups_visible($course, $userid, $cm)) {
            throw new moodle_exception('nopermissiontoshow');
        }
        $report = new \mod_wordcards\local\report\userattempts();
        $formdata = new stdClass();
        $formdata->modid = $moduleinstance->id;
        $formdata->userid = $userid;
        $formdata->modulecontextid = $modulecontext->id;
        break;


    case 'grades':
        $report = new \mod_wordcards\local\report\grades();
        $formdata = new stdClass();
        $formdata->modid = $moduleinstance->id;
        $formdata->modulecontextid = $modulecontext->id;
        $formdata->groupmenu = true;
        break;

    default:
        echo $renderer->header();
        $pagetitle =get_string('reports', constants::M_COMPONENT);
        echo $renderer->heading($pagetitle);
        echo $renderer->navigation($wordcardsmodule , 'reports');
        echo "unknown report type.";
        echo $renderer->footer();
        return;
}

/*
1) load the class
2) call report->process_raw_data
3) call $rows=report->fetch_formatted_records($withlinks=true(html) false(print/excel))
5) call $reportrenderer->render_section_html($sectiontitle, $report->name, $report->get_head, $rows, $report->fields);
*/

$groupmenu = '';
if(isset($formdata->groupmenu)){
    // fetch groupmode/menu/id for this activity
    if ($groupmode = groups_get_activity_groupmode($cm)) {
        $groupmenu = groups_print_activity_menu($cm, $PAGE->url, true);
        $groupmenu .= ' ';
        $formdata->groupid = groups_get_activity_group($cm);
    }else{
        $formdata->groupid  = 0;
    }
}else{
    $formdata->groupid  = 0;
}

$report->process_raw_data($formdata);
$reportheading = $report->fetch_formatted_heading();

switch ($format) {
    case 'csv':
        $reportrows = $report->fetch_formatted_rows(false);
        $reportrenderer->render_section_csv($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows,
                $report->fetch_fields());
        exit;
    default:

        $reportrows = $report->fetch_formatted_rows(true, $paging);
        $allrowscount = $report->fetch_all_rows_count();
        $pagingbar = $reportrenderer->show_paging_bar($allrowscount, $paging, $PAGE->url);
        echo $renderer->header();
        $pagetitle =get_string('reports', constants::M_COMPONENT);
        echo $renderer->heading($pagetitle);
        echo $renderer->navigation($wordcardsmodule, 'reports');
        echo $extraheader;
        echo $groupmenu;
        echo $pagingbar;
        echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows,
                $report->fetch_fields());
        echo $pagingbar;
        echo $reportrenderer->show_reports_footer($moduleinstance, $cm, $formdata, $showreport);
        echo $renderer->footer();
}