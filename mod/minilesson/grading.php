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
 * Reports for minilesson
 *
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_minilesson\constants;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // minilesson instance ID
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$action = optional_param('action', 'grading', PARAM_TEXT); // report type
$userid = optional_param('userid', 0, PARAM_INT); // user id
$attemptid = optional_param('attemptid', 0, PARAM_INT); // attemptid
$returnurl = optional_param('returnurl', false, PARAM_URL); //returnurl
$debug  = optional_param('debug', 0, PARAM_INT);


//paging details
$paging = new stdClass();
$paging->perpage = optional_param('perpage',-1, PARAM_INT);
$paging->pageno = optional_param('pageno',0, PARAM_INT);
$paging->sort  = optional_param('sort','user', PARAM_TEXT);


if ($id) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/minilesson:evaluate', $modulecontext);

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

//set per page according to admin setting
if($paging->perpage==-1){
	$paging->perpage = $config->itemsperpage;
}

// Trigger module viewed event.
$event = \mod_minilesson\event\course_module_viewed::create(array(
   'objectid' => $moduleinstance->id,
   'context' => $modulecontext
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot(constants::M_MODNAME, $moduleinstance);
$event->trigger();



$PAGE->set_url(constants::M_URL . '/grading.php',
    array('id' => $cm->id,'format'=>$format,'action'=>$action,'userid'=>$userid,'attemptid'=>$attemptid,'returnurl'=>$returnurl));

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if($config->enablesetuptab){
    $PAGE->set_pagelayout('embedded');
}else{
    $PAGE->set_pagelayout('course');
}




$PAGE->requires->jquery();

//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$reportrenderer = $PAGE->get_renderer(constants::M_COMPONENT,'report');

//From here we actually display the page.
$mode = "grading";
$extraheader="";
switch ($action){


    //list view of attempts and grades and action links
	case 'grading':
		$report = new \mod_minilesson\report\grading();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->moduleid = $moduleinstance->id;
		$formdata->modulecontextid = $modulecontext->id;
		break;

    //list view of attempts and grades and action links for a particular user
	case 'gradingbyuser':
		$report = new \mod_minilesson\report\gradingbyuser();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->moduleid = $moduleinstance->id;
		$formdata->userid = $userid;
		$formdata->modulecontextid = $modulecontext->id;
		break;



	default:
		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
		echo "unknown action.";
        //backtotop
        echo $renderer->backtotopbutton($course->id);
		echo $renderer->footer();
		return;
}



/*
1) load the class
2) call report->process_raw_data
3) call $rows=report->fetch_formatted_records($withlinks=true(html) false(print/excel))
5) call $reportrenderer->render_section_html($sectiontitle, $report->name, $report->get_head, $rows, $report->fields);
*/

$report->process_raw_data($formdata, $moduleinstance);
$reportheading = $report->fetch_formatted_heading();

switch($format){
    case 'csv':
        $reportrows = $report->fetch_formatted_rows(false);
        $reportrenderer->render_section_csv($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
        exit;
	case 'html':
	default:
        $reportrows = $report->fetch_formatted_rows(true,$paging);
        $allrowscount = $report->fetch_all_rows_count();
	    $pagingbar = $reportrenderer->show_paging_bar($allrowscount, $paging,$PAGE->url);
        $perpage_selector = $reportrenderer->show_perpage_selector($PAGE->url,$paging);


		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
		echo $extraheader;
		echo $pagingbar;
		echo $perpage_selector;
		echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		echo $pagingbar;
		echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);
        echo $reportrenderer->show_export_buttons($cm,$formdata,$action);

        //back to course if we are not in an iframe of some sort
        if(!$config->enablesetuptab) {
            echo $renderer->backtotopbutton($course->id);
        }

        echo $renderer->footer();
}