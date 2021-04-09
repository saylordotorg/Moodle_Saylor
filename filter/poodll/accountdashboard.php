<?php

use filter_poodll\constants;

require_once("../../config.php");
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('accountdashboard');

//detect if its a CSV or not
$exportcsv = optional_param('csv', 0, PARAM_INT);


//if we are exporting html, do that
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('accountdashboard', 'filter_poodll'), 3);

$params=[];
$result = \filter_poodll\poodlltools::call_cloudpoodll('local_cpapi_fetch_user_report',$params);
if(!$result || !isset($result->returnMessage) || !($usagedata=json_decode($result->returnMessage))){
    echo get_string('failedfetchsubreport',constants::M_COMP);
    echo $OUTPUT->footer();
    return;
}

$renderer = $PAGE->get_renderer('filter_poodll');
$renderer->display_usage_report($usagedata);


echo $OUTPUT->footer();