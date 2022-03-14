<?php
/**
 * Page to display free mode activity.
 *
 * @package mod_wordcards
 * @author  David Watson - evolutioncode.uk
 */

require_once(__DIR__ . '/../../config.php');

use \mod_wordcards\constants;
use mod_wordcards\utils;

$cmid = required_param('id', PARAM_INT);
//the step that the user is requesting

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();
require_login($course, true, $cm);

if (!$mod->can_free_mode()) {
    redirect(
        new moodle_url('/mod/wordcards/view.php', ['id' => $cm->id]),
        get_string('freemodenotavailable', constants::M_COMPONENT),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}
$practicetypeoptions =  utils::get_practicetype_options(\mod_wordcards_module::WORDPOOL_LEARN);
$practicetype = optional_param('practicetype', mod_wordcards_module::PRACTICETYPE_NONE, PARAM_INT);
if ($practicetype && !isset($practicetypeoptions[$practicetype])) {
    throw new invalid_parameter_exception('Invalid activity type');
}

$wordpool = optional_param('wordpool', mod_wordcards_module::WORDPOOL_LEARN, PARAM_INT);
$wordpools = mod_wordcards_module::get_wordpools();
if (!in_array($wordpool, $wordpools)) {
    throw new invalid_parameter_exception('Invalid wordpool');
}

//if there are no learn terms and its set to wordpoodl learn, lets set it to review
$learnterms = $mod->get_terms();
if(empty($learnterms)){
    $wordpool=mod_wordcards_module::WORDPOOL_REVIEW;
}else{
    //mark all terms as seen, if its the first view
    if(!$mod->has_seen_all_terms()) {
        $mod->mark_terms_as_seen();
    }
}


$PAGE->set_url('/mod/wordcards/freemode.php', ['id' => $cmid, 'practicetype' => $practicetype, 'wordpool' => $wordpool]);
$mod->require_view();

//is teacher?
$isteacher = ($mod->can_manage() || $mod->can_viewreports());

// If it looks like we have had some vocab updates, request an update of the lang speech model
if($mod->get_mod()->hashisold) {
    $mod->set_region_passagehash();
}
$renderer = $PAGE->get_renderer('mod_wordcards');
$templateable = new \mod_wordcards\output\freemode($cm, $course, $practicetype, $wordpool);
$templatedata = $templateable->export_for_template($renderer);
$PAGE->navbar->add($templatedata->pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true));
$PAGE->set_title($templatedata->pagetitle);
$PAGE->force_settings_menu(true);

$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

//load glide
//$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/glidejs@2.1.0/dist/css/glide.core.min.css'));
$PAGE->requires->css(new moodle_url('/mod/wordcards/freemode.css'));
$PAGE->requires->js_call_amd(constants::M_COMPONENT . "/mywords", 'init', []);
$PAGE->requires->js_call_amd(constants::M_COMPONENT . "/freemode", 'init', []);

echo $renderer->header();
//if admin we show a heading in free mode (otherwise they would not be able to add definitions)
if($isteacher && $mod->get_mod()->journeymode==constants::MODE_FREE) {
    //this is a bit hacky, TO DO make a new state "FREEMODE"
    $currentstate = mod_wordcards_module::STATE_TERMS;
    echo $renderer->navigation($mod, $currentstate);
}
echo $renderer->render_from_template('mod_wordcards/freemode', $templatedata);
echo $renderer->footer();
