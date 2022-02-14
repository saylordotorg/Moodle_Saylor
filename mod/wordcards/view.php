<?php
/**
 * Displays the definitions.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use \mod_wordcards\constants;

$cmid = required_param('id', PARAM_INT);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();
$currentstate = mod_wordcards_module::STATE_TERMS;

require_login($course, true, $cm);
$mod->require_view();
$mod->resume_progress($currentstate);
$moduleinstance = $mod->get_mod();
//trigger module viewed event
$mod->register_module_viewed();

//$pagetitle = get_string('tabdefinitions', 'mod_wordcards');
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());

$PAGE->set_url('/mod/wordcards/view.php', ['id' => $cmid]);
$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);
$PAGE->force_settings_menu(true);
$modulecontext = $mod->get_context();
//Get an admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}


$renderer = $PAGE->get_renderer('mod_wordcards');

echo $renderer->header();
echo $renderer->heading($pagetitle, 3, 'main');

//show open close dates and module intro
$hasopenclosedates = $moduleinstance->viewend > 0 || $moduleinstance->viewstart>0;
if (!empty($mod->get_mod()->intro)) {
    $moduleintro = format_module_intro('wordcards', $mod->get_mod(), $cm->id);
    if($hasopenclosedates) {
        $moduleintro .= $renderer->show_open_close_dates($moduleinstance);
    }
    echo $renderer->box($moduleintro, 'generalbox', 'intro');
}else{
    if($hasopenclosedates) {
        echo $renderer->box($renderer->show_open_close_dates($moduleinstance), 'generalbox');
    }
}

//enforce open close dates
if($hasopenclosedates){
    $current_time=time();
    $closed = false;
    if ( $current_time>$moduleinstance->viewend){
        echo get_string('activityisclosed',constants::M_COMPONENT);
        $closed = true;
    }elseif($current_time<$moduleinstance->viewstart){
        echo get_string('activityisnotopenyet',constants::M_COMPONENT);
        $closed = true;
    }
    //if we are not a teacher and the activity is closed/not-open leave at this point
    if(!has_capability('mod/wordcards:preview',$modulecontext) && $closed){
        echo $renderer->footer();
        exit;
    }
}


echo $renderer->navigation($mod, $currentstate);


echo $renderer->definitions_page($mod);

echo $renderer->footer();
