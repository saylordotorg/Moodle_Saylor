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

$pagetitle = get_string('activitycompleted', 'mod_wordcards');

$PAGE->set_url('/mod/wordcards/finish.php', ['id' => $cmid]);
$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);

$output = $PAGE->get_renderer('mod_wordcards');

echo $output->header();
echo $output->heading($pagetitle);

echo $output->navigation($mod, $currentstate);

$renderer = $PAGE->get_renderer('mod_wordcards');
echo $renderer->finish_page($mod);

echo $output->footer();
