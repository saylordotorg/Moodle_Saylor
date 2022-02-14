<?php
/**
 * External.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */


use mod_wordcards\utils;
use mod_wordcards\constants;

/**
 * External class.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */
class mod_wordcards_external extends external_api {

    public static function check_by_phonetic_parameters(){
        return new external_function_parameters(
                 array('spoken' => new external_value(PARAM_TEXT, 'The spoken phrase'),
                       'correct' => new external_value(PARAM_TEXT, 'The correct phrase'),
                       'language' => new external_value(PARAM_TEXT, 'The language eg en-US')
                 )
        );

    }
    public static function check_by_phonetic($spoken, $correct, $language){
        $language = substr($language,0,2);
        $spokenphonetic = utils::convert_to_phonetic($spoken,$language);
        $correctphonetic = utils::convert_to_phonetic($correct,$language);
        $similar_percent = 0;
        $similar_chars = similar_text($correctphonetic,$spokenphonetic,$similar_percent);
        return round($similar_percent,0);

    }

    public static function check_by_phonetic_returns(){
        return new external_value(PARAM_INT,'how close is spoken to correct, 0 - 100');
    }

    public static function mark_as_seen_parameters() {
        return new external_function_parameters([
            'termid' => new external_value(PARAM_INT)
        ]);
    }

    public static function mark_as_seen($termid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::mark_as_seen_parameters(), compact('termid'));
        extract($params);

        $term = $DB->get_record('wordcards_terms', ['id' => $termid], '*', MUST_EXIST);
        $mod = mod_wordcards_module::get_by_modid($term->modid);
        self::validate_context($mod->get_context());

        // We do not log the completion for teachers.
        if ($mod->can_manage()) {
            return true;
        }

        // Require view and make sure the user did not previously mark as seen.
        $params = ['userid' => $USER->id, 'termid' => $termid];
        $mod->require_view();
        if ($DB->record_exists('wordcards_seen', $params)) {
            return true;
        }

        $record = (object) $params;
        $record->timecreated = time();
        $DB->insert_record('wordcards_seen', $record);

        return true;
    }

    public static function mark_as_seen_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function report_successful_association_parameters() {
        return new external_function_parameters([
            'termid' => new external_value(PARAM_INT)
        ]);
    }

    public static function report_successful_association($termid) {
        global $DB;

        $params = self::validate_parameters(self::report_successful_association_parameters(), compact('termid'));
        extract($params);

        $term = $DB->get_record('wordcards_terms', ['id' => $termid], '*', MUST_EXIST);
        $mod = mod_wordcards_module::get_by_modid($term->modid);
        self::validate_context($mod->get_context());

        // We do not log associations for teachers.
        if ($mod->can_manage()) {
            return true;
        }

        // We need read access.
        $mod->require_view();
        $mod->record_successful_association($term);

        return true;
    }

    public static function report_successful_association_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function report_failed_association_parameters() {
        return new external_function_parameters([
            'term1id' => new external_value(PARAM_INT),
            'term2id' => new external_value(PARAM_INT),
        ]);
    }

    public static function report_failed_association($term1id, $term2id) {
        global $DB;

        $params = self::validate_parameters(self::report_failed_association_parameters(), compact('term1id', 'term2id'));
        extract($params);

        $term = $DB->get_record('wordcards_terms', ['id' => $term1id], '*', MUST_EXIST);
        $mod = mod_wordcards_module::get_by_modid($term->modid);
        self::validate_context($mod->get_context());

        // We do not log associations for teachers.
        if ($mod->can_manage()) {
            return true;
        }

        // We need read access in at least one of the terms. The rest will be validated elsewhere.
        $mod->require_view();
        $mod->record_failed_association($term, $term2id);

        return true;
    }

    public static function report_failed_association_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function report_step_grade_parameters() {
        return new external_function_parameters([
                'modid' => new external_value(PARAM_INT),
                'correct' => new external_value(PARAM_INT),
        ]);
    }

    public static function report_step_grade($modid,$correct){
        $ret= utils::update_stepgrade($modid, $correct);
        return $ret;
    }
    public static function report_step_grade_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function submit_newterm_parameters() {
        return new external_function_parameters([
            'modid' => new external_value(PARAM_INT),
            'term' => new external_value(PARAM_RAW),
            'definition' => new external_value(PARAM_RAW),
            'translations' => new external_value(PARAM_RAW),
            'sourcedef' => new external_value(PARAM_RAW),
            'modelsentence' => new external_value(PARAM_RAW),
        ]);
    }

    public static function submit_newterm($modid,$term, $definition,$translations,$sourcedef,$modelsentence){
        $ret= utils::save_newterm($modid,$term, $definition,$translations,$sourcedef,$modelsentence);
        if($ret){
            return true;
        }else{
            return false;
        }
    }
    public static function submit_newterm_returns() {
        return new external_value(PARAM_BOOL);
    }


    public static function submit_mform_parameters() {
        return new external_function_parameters(
                array(
                        'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                        'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array')
                )
        );
    }

    public static function submit_mform($contextid,$jsonformdata) {
        global $CFG, $DB, $USER;


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_mform_parameters(),
                ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        //Init return object
        $ret = new \stdClass();
        $ret->termid=0;
        $ret->error=true;
        $ret->message="";


        list($ignored, $course) = get_context_info_array($context->id);
        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        //get filechooser and html editor options
        $audiooptions= utils::fetch_filemanager_opts('audio');
        $imageoptions= utils::fetch_filemanager_opts('image');;

        // get the objects we need
        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);


        //we need to pretend this was posted and these help
        $method='post';
        $target='';
        $attributes=null;
        $editable=true;

        //get the mform for our term
        $mform = new \mod_wordcards_form_term(null,
                ['termid' => $data['termid'] ? $data['termid'] : 0,'ttslanguage'=>$moduleinstance->ttslanguage],
                        $method, $target,$attributes,$editable,$data
                );
       // $mform = new \mod_wordcards_form_term(null, $data);

        $validateddata = $mform->get_data();
        if ($validateddata) {

            //currently data is an array, but it should be an object
            $data = (object)$data;

            //if this new add and collect data->id
            $needsupdating = false;
            if (empty($data->termid)) {
                $data->modid = $moduleinstance->id;
                $data->id  = $DB->insert_record('wordcards_terms', $data);

            //else set id to termid
            }else{
                $data->id = $data->termid;
                $needsupdating = true;
            }
            if($data->id){
                $ret->error=false;
            }


            //audio data
            if(!empty( $data->audio_filemanager)){
                $data = file_postupdate_standard_filemanager($data, 'audio', $audiooptions, $context, constants::M_COMPONENT, 'audio',
                        $data->id);
                $needsupdating = true;

                //in the case a user has deleted all files, we will still have the draftid in the audio column, we want to set it to 0
                $fs = get_file_storage();
                $areafiles = $fs->get_area_files($context->id,'mod_wordcards','audio',$data->id);
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
                //$data->audio_filemanager = $audioitemid;
                $data = file_postupdate_standard_filemanager($data, 'model_sentence_audio', $audiooptions, $context, constants::M_COMPONENT, 'model_sentence_audio',
                        $data->id);
                $needsupdating = true;
                //in the case a user has deleted all files, we will still have the draftid in the audio column, we want to set it to 0
                $fs = get_file_storage();
                $areafiles = $fs->get_area_files($context->id,'mod_wordcards','model_sentence_audio',$data->id);

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
                $data = file_postupdate_standard_filemanager($data, 'image', $imageoptions, $context, constants::M_COMPONENT, 'image',
                        $data->id);
                $needsupdating = true;

                //in the case a user has deleted all files, we will still have the draftid in the image column, we want to set it to ''
                $fs = get_file_storage();
                $areafiles = $fs->get_area_files($context->id,'mod_wordcards','image',$data->id);
                if(!$areafiles || count($areafiles)==0){
                    $data->image='';
                }elseif(count($areafiles)==1) {
                    $file = array_pop($areafiles);
                    if ($file->is_directory()) {
                        $data->image='';
                    }
                }
            }


            //lets update the passage hash here before we save the item in db
            if ($needsupdating) {
                if($DB->update_record('wordcards_terms', $data)) {
                    //also update our passagehash update flag
                    $DB->update_record('wordcards', array('id' => $moduleinstance->id, 'hashisold' => 1));
                    $ret->error=false;
                }
            }

            if($ret->error==true){
                //$ret->message = $ret->message;
            }else{
                $theitem=$data;
                $ret->itemid=$theitem->id;
                $ret->error=false;
            }
        }
        return json_encode($ret);
    }

    public static function submit_mform_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }

}
