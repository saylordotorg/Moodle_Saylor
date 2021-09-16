<?php
/**
 * Displays the set-up phase.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

require_once(__DIR__ . '/../../config.php');

use \mod_wordcards\utils;
use \mod_wordcards\constants;

$cmid = required_param('id', PARAM_INT);
$termid = optional_param('termid', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();
$modulecontext = context_module::instance($cm->id);

require_login($course, true, $cm);
$mod->require_manage();

$modid = $mod->get_id();
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
$pagetitle .= ': ' . get_string('managewords', 'mod_wordcards');
$baseurl = new moodle_url('/mod/wordcards/managewords.php', ['id' => $cmid]);
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

$output = $PAGE->get_renderer('mod_wordcards');

if ($action == 'delete') {
    confirm_sesskey();
    $mod->delete_term($termid);
    // Uncomment when migrating to 3.1.
    // redirect($PAGE->url, get_string('termdeleted', 'mod_wordcards'));
    redirect($PAGE->url);

} else if ($action == 'edit') {
    // Adding those parameters ensures that we confirm that the term belongs to the right module after submission.
    $formurl->param('action', 'edit');
    $formurl->param('termid', 'termid');
    $term = $DB->get_record('wordcards_terms', ['modid' => $modid, 'id' => $termid], '*', MUST_EXIST);
}

$form = new mod_wordcards_form_term($formurl->out(false), ['termid' => $term ? $term->id : 0,'ttslanguage'=>$mod->get_mod()->ttslanguage]);

if (!$term) {
    $term = new stdClass();
    $term->id=null;
}

//prepare filemanager
$audiooptions= utils::fetch_filemanager_opts('audio');
$imageoptions= utils::fetch_filemanager_opts('image');
file_prepare_standard_filemanager($term, 'audio', $audiooptions, $modulecontext, constants::M_COMPONENT, 'audio', $term->id);
file_prepare_standard_filemanager($term, 'image', $imageoptions, $modulecontext, constants::M_COMPONENT, 'image', $term->id);
file_prepare_standard_filemanager($term, 'model_sentence_audio', $audiooptions, $modulecontext, constants::M_COMPONENT, 'model_sentence_audio', $term->id);


//set data to form
$form->set_data($term);

if ($data = $form->get_data()) {

    //if this new add and collect data->id
    $needsupdating = false;
    if (empty($data->termid)) {
        $data->modid = $modid;

        $data->id  = $DB->insert_record('wordcards_terms', $data);
    //else set id to termid
    }else{
        $data->id = $data->termid;
        $needsupdating = true;
    }


    //audio data
    if(!empty( $data->audio_filemanager)){
        $audiooptions = utils::fetch_filemanager_opts('audio');
        //$data->audio_filemanager = $audioitemid;
        $data = file_postupdate_standard_filemanager($data, 'audio', $audiooptions, $modulecontext, constants::M_COMPONENT, 'audio',
                $data->id);
        $needsupdating = true;

        //in the case a user has deleted all files, we will still have the draftid in the audio column, we want to set it to 0
        $fs = get_file_storage();
        $areafiles = $fs->get_area_files($modulecontext->id,'mod_wordcards','audio',$data->id);
        if(!$areafiles || count($areafiles)==0){
            $data->audio='';
        }elseif(count($areafiles)==1) {
            $file = array_pop($areafiles);
            if ($file->is_directory()) {
                $data->audio='';
            }
        }

    }

     //model sentence audio data
    if(!empty($data->model_sentence_audio_filemanager)){
        $audiooptions = utils::fetch_filemanager_opts('audio');
        //$data->audio_filemanager = $audioitemid;
        $data = file_postupdate_standard_filemanager($data, 'model_sentence_audio', $audiooptions, $modulecontext, constants::M_COMPONENT, 'model_sentence_audio',
                $data->id);
        $needsupdating = true;
        //in the case a user has deleted all files, we will still have the draftid in the audio column, we want to set it to 0
        $fs = get_file_storage();
        $areafiles = $fs->get_area_files($modulecontext->id,'mod_wordcards','model_sentence_audio',$data->id);

        if(!$areafiles || count($areafiles)==0){
            $data->model_sentence_audio='';
        }elseif(count($areafiles)==1) {
            $file = array_pop($areafiles);
            if ($file->is_directory()) {
                $data->model_sentence_audio='';
            }
        }

    }

    if(!empty($data->image_filemanager)){
        $imageoptions = utils::fetch_filemanager_opts('image');
        $data = file_postupdate_standard_filemanager($data, 'image', $imageoptions, $modulecontext, constants::M_COMPONENT, 'image',
                $data->id);
        $needsupdating = true;

        //in the case a user has deleted all files, we will still have the draftid in the image column, we want to set it to ''
        $fs = get_file_storage();
        $areafiles = $fs->get_area_files($modulecontext->id,'mod_wordcards','image',$data->id);
        if(!$areafiles || count($areafiles)==0){
            $data->image='';
        }elseif(count($areafiles)==1) {
            $file = array_pop($areafiles);
            if ($file->is_directory()) {
                $data->image='';
            }
        }
    }

    if ($needsupdating) {
        $DB->update_record('wordcards_terms', $data);
        //also update our passagehash update flag
        $DB->update_record('wordcards', array('id' => $modid, 'hashisold' => 1));
    }

    //finally redirect
    // Uncomment when migrating to 3.1.
    // redirect($PAGE->url, get_string('termsaved', 'mod_wordcards', $data->term));
    redirect($PAGE->url);

}

echo $output->header();
echo $output->heading($pagetitle);
echo $output->navigation($mod, 'managewords');
echo $output->box(get_string('managewordsinstructions',constants::M_COMPONENT), 'generalbox', 'intro');

// $form->display();
echo html_writer::link('#',get_string('addnewterm',constants::M_COMPONENT),
        array('class'=>'btn btn-primary mod_wordcards_item_row_addlink','data-id'=>0,'data-type'=>"add"));

$table = new mod_wordcards_table_terms('tblterms', $mod);
$table->define_baseurl($PAGE->url);
$table->out(25, false);

$props=array('contextid'=>$modulecontext->id);
$PAGE->requires->js_call_amd(constants::M_COMPONENT . '/managewordshelper', 'init', array($props));
echo $output->footer();
