<?php
/**
 * Displays the definitions.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$cmid = required_param('id', PARAM_INT);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();
$currentstate = mod_wordcards_module::STATE_TERMS;

require_login($course, true, $cm);
$mod->require_view();
$mod->resume_progress($currentstate);

//trigger module viewed event
$mod->register_module_viewed();

$pagetitle = get_string('tabdefinitions', 'mod_wordcards');

$PAGE->set_url('/mod/wordcards/view.php', ['id' => $cmid]);
$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);
$PAGE->force_settings_menu(true);
//load google font never works
//$googlefont = new moodle_url('https//fonts.googleapis.com/css2',array('family'=>'Orbitron','display'=>'swap'));
//$PAGE->requires->css($googlefont);


$renderer = $PAGE->get_renderer('mod_wordcards');

echo $renderer->header();
echo $renderer->heading($pagetitle);

if (!empty($mod->get_mod()->intro)) {
    echo $renderer->box(format_module_intro('wordcards', $mod->get_mod(), $cm->id), 'generalbox', 'intro');
}

echo $renderer->navigation($mod, $currentstate);

echo $renderer->definitions_page($mod);

echo $renderer->footer();
