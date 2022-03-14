<?php
/**
 * Displays the set-up phase.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - ishinekk.co.jp
 */

use \mod_wordcards\constants;
use \mod_wordcards\utils;

require_once(__DIR__ . '/../../config.php');

$cmid = required_param('id', PARAM_INT);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();

require_login($course, true, $cm);
$mod->require_manage();

$modid = $mod->get_id();
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
$pagetitle .= ': ' . get_string('word_wizard', 'mod_wordcards');
$baseurl = new moodle_url('/mod/wordcards/wordwizard.php', ['id' => $cmid]);
$formurl = new moodle_url($baseurl);
$term = null;

$PAGE->set_url($baseurl);
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

$renderer = $PAGE->get_renderer('mod_wordcards');

echo $renderer->header();
echo $renderer->heading($pagetitle);
echo $renderer->navigation($mod, 'word_wizard');
echo $renderer->box(get_string('wizardinstructions',constants::M_COMPONENT), 'generalbox wordcards_wizardintro', 'intro');
echo $renderer->word_wizard($mod,$cm);


echo $renderer->footer();
