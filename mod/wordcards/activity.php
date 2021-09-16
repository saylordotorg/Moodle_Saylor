<?php
/**
 * Displays the global scatter.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

require_once(__DIR__ . '/../../config.php');

use \mod_wordcards\constants;

$cmid = required_param('id', PARAM_INT);
//the step that the user is requesting
$nextstep = optional_param('nextstep',mod_wordcards_module::STATE_STEP1, PARAM_TEXT);
//the most recent step they came from
$oldstep = optional_param('oldstep',mod_wordcards_module::STATE_TERMS, PARAM_TEXT);

//request a reattempt
$reattempt = optional_param('reattempt',0, PARAM_INT);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();

$PAGE->set_url('/mod/wordcards/activity.php', ['id' => $cmid,'oldstep'=>$oldstep, 'nextstep'=>$nextstep]);
require_login($course, true, $cm);
$mod->require_view();

//create a new attempt and set it to STATE_TERMS (which should be bumped up to STATE_STEP1 shortly after)
if($mod->can_attempt() && $reattempt){
    $mod->create_reattempt();
}


//we use the suggested step if they are finished or a teacher
//otherwsie we use their currentstate (the step they are up to)
//$currentstate = the latest step they have acccess to
//$currentstep = the step we have agreed to display
list($currentstate) = $mod->get_state();
if($currentstate==mod_wordcards_module::STATE_END){
    //we have endded, but we can go wherever we need to
    $currentstep=$nextstep;
}else{
    if($currentstate != $nextstep){
        $mod->resume_progress($currentstate);
        list($currentstep) = $mod->get_state();
    }else{
        $currentstep = $nextstep;
    }
}

//redirect to finished if this state end
if($currentstep==mod_wordcards_module::STATE_END) {
    redirect(new moodle_url('/mod/wordcards/finish.php', ['id' => $cm->id, 'sesskey'=>sesskey()]));
}

//redirect to finished if this state end
if($currentstep==mod_wordcards_module::STATE_TERMS) {
    redirect(new moodle_url('/mod/wordcards/view.php', ['id' => $cm->id]));
}

//get our practicetype an wordpool
$practicetype = $mod->get_practicetype($currentstep);
$wordpool = $mod->get_wordpool($currentstep);

//if its  review type and we have no review words, we just use a learn pool,
//we used to skip such tabs, but grading would get messed up
if($wordpool==mod_wordcards_module::WORDPOOL_REVIEW) {
    $reviewpoolempty = !$mod->are_there_words_to_review();//$mod->get_review_terms(mod_wordcards_module::STATE_STEP2) ? false : true;
    if($reviewpoolempty){
        $wordpool=mod_wordcards_module::WORDPOOL_LEARN;
    };
}

//depending on wordpool set page title
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
if($wordpool==mod_wordcards_module::WORDPOOL_REVIEW) {
    $pagetitle .= ': ' . get_string('reviewactivity', 'mod_wordcards');
}else{
    $pagetitle .= ': ' .  get_string('learnactivity', 'mod_wordcards');
}


//iif it looks like we have had some vocab updates, request an update of the lang speech model
if($mod->get_mod()->hashisold) {
    $mod->set_region_passagehash();
}


$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);

$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

//load glide
$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/glidejs@2.1.0/dist/css/glide.core.min.css'));
//load google font never works ... why?
//$PAGE->requires->css(new moodle_url('https//fonts.googleapis.com/css2',array('family'=>'Orbitron','display'=>'swap')));

$renderer = $PAGE->get_renderer('mod_wordcards');

echo $renderer->header();
$heading = $renderer->heading($pagetitle, 3, 'main');
$displaytext = \html_writer::div($heading, constants::M_CLASS . '_center');
echo $displaytext;

if (!empty($mod->get_mod()->intro)) {
    echo $renderer->box(format_module_intro('wordcards', $mod->get_mod(), $cm->id), 'generalbox', 'intro');
}

echo $renderer->navigation($mod, $currentstep);
switch ($practicetype){

    case mod_wordcards_module::PRACTICETYPE_MATCHSELECT:
    case mod_wordcards_module::PRACTICETYPE_MATCHTYPE:
    case mod_wordcards_module::PRACTICETYPE_DICTATION:
    case mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE:
    case mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV:
    case mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV:
    case mod_wordcards_module::PRACTICETYPE_DICTATION_REV:
    case mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE_REV:
        echo $renderer->a4e_page($mod, $practicetype, $wordpool, $currentstep);
        break;

    case mod_wordcards_module::PRACTICETYPE_SPEECHCARDS:
    case mod_wordcards_module::PRACTICETYPE_SPEECHCARDS_REV:
        echo $renderer->speechcards_page($mod, $wordpool,$currentstep);
        break;
    //no longer using this
    case mod_wordcards_module::PRACTICETYPE_SCATTER:
    case mod_wordcards_module::PRACTICETYPE_SCATTER_REV:
    default:
        echo $renderer->scatter_page($mod, $wordpool,$currentstep);
}

echo $renderer->footer();
