<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_solo;


class attempthelper
{
    protected $cm;
    protected $context;
    protected $mod;
    protected $attempts;

    public function __construct($cm) {
        global $DB;
        $this->cm = $cm;
        $this->mod = $DB->get_record(constants::M_TABLE, ['id' => $cm->instance], '*', MUST_EXIST);
        $this->context = \context_module::instance($cm->id);
    }

    public function fetch_media_url($filearea,$attempt){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id,  constants::M_COMPONENT,$filearea,$attempt->id);
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if($filename=='.'){continue;}
            $filepath = '/';
            $mediaurl = \moodle_url::make_pluginfile_url($this->context->id, constants::M_COMPONENT,
                $filearea, $attempt->id,
                $filepath, $filename);
            return $mediaurl->__toString();

        }
        //We always take the first file and if we have none, thats not good.
        return "";
       // return "$this->context->id pp $filearea pp $attempt->id";
    }

    public function fetch_attempts($userid=false)
    {
        global $DB,$USER;

        if(!$userid){
            $userid= $USER->id;
        }
        if (!$this->attempts) {
            $this->attempts = $DB->get_records(constants::M_ATTEMPTSTABLE, [constants::M_MODNAME => $this->mod->id, 'userid'=>$userid],'timemodified DESC');
        }
        if($this->attempts){
            return $this->attempts;
        }else{
            return [];
        }
    }

    public function fetch_latest_complete_attempt($userid=false){
        global $DB, $USER;

        if(!$userid){
            $userid = $USER->id;
        }

        $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,
                array(constants::M_MODNAME => $this->mod->id,'userid'=>$userid, 'completedsteps'=>constants::STEP_SELFTRANSCRIBE),
                'id DESC');
        if($attempts){
            $attempt = array_shift($attempts);
            return $attempt;
        }else{
            return false;
        }
    }

    public function fetch_latest_attempt($userid=false){
        global $DB, $USER;

        if(!$userid){
            $userid = $USER->id;
        }

        $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,
                array(constants::M_MODNAME => $this->mod->id,'userid'=>$userid),
                'id DESC');
        if($attempts){
            $attempt = array_shift($attempts);
            return $attempt;
        }else{
            return false;
        }
    }


    //fetch a specific attempt
    public function fetch_specific_attempt($attemptid) {
        global $DB;

        $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE,
                array('id'=>$attemptid, 'solo'=>$this->mod->id,'completedsteps'=>constants::STEP_SELFTRANSCRIBE),
                'timemodified DESC');
        return $attempt;
    }


    public function fetch_attempts_for_js(){

        $attempts = $this->fetch_attempts();
        return $attempts;
    }


}//end of class