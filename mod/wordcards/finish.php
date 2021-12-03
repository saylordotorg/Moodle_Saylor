<?php
/**
 * Page to record the 'end' state.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

require_once(__DIR__ . '/../../config.php');

use mod_wordcards\utils;
use mod_wordcards\constants;

$cmid = required_param('id', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_RAW);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();
$currentstate = mod_wordcards_module::STATE_END;

require_login($course, true, $cm);
require_sesskey();
$mod->require_view();
$mod->resume_progress($currentstate);

utils::update_finalgrade($mod->get_id());

$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
$pagetitle .= ': ' . get_string('activitycompleted', 'mod_wordcards');

$PAGE->set_url('/mod/wordcards/finish.php', ['id' => $cmid, 'sesskey'=>$sesskey]);
$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);

//Get admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

$output = $PAGE->get_renderer('mod_wordcards');

echo $output->header();
echo $output->heading($pagetitle);


$navdisabled=false;
echo $output->navigation($mod, $currentstate,$navdisabled);

$renderer = $PAGE->get_renderer('mod_wordcards');
echo $renderer->finish_page($mod);

echo $output->footer();
