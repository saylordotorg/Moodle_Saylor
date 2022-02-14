<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_solo;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/completionlib.php');

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
        $this->course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
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

    public function submit_step($step, $data){
        global $USER, $DB;

        $ret = new \stdClass();

        if ($data ) {
            require_sesskey();

            $newattempt = $data;
            $newattempt->solo = $this->mod->id;
            $newattempt->userid = $USER->id;
            $newattempt->modifiedby=$USER->id;
            $newattempt->timemodified=time();

            //are we in edit or new mode
            if ($data->attemptid) {
                $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE, array('id'=>$data->attemptid,constants::M_MODNAME => $this->mod->id), '*', MUST_EXIST);
                if(!$attempt){
                    $ret->message = 'could not find attempt of id:' . $data->attemptid;
                    $ret->success = false;
                    return $ret;
                }
                //This would force a step, if we needed to
                $lateststep = $attempt->completedsteps;
                $edit = true;
            } else {
                $lateststep = constants::STEP_NONE;
                $edit = false;
            }

            //first insert a new attempt if we need to
            //that will give us a attemptid, we need that for saving files
            if($edit) {
                $newattempt->id = $data->attemptid;
            }else{
                $newattempt->timecreated=time();
                $newattempt->createdby=$USER->id;

                //try to insert it
                if (!$newattempt->id = $DB->insert_record(constants::M_ATTEMPTSTABLE,$newattempt)){
                    $ret->message = "Could not insert solo attempt!";
                    $ret->success = false;
                    return $ret;
                }
            }

            //type specific settings
            switch($data->activitytype) {
                case constants::STEP_USERSELECTIONS:
                    $newattempt->topictargetwords = $this->mod->targetwords;
                    break;

                case constants::STEP_AUDIORECORDING:
                    $rerecording = $attempt && $newattempt->filename
                        && $attempt->filename != $newattempt->filename;

                    //if rerecording we want to clear old AI data out
                    //as well as self transcript and force us back to self transcript
                    if($rerecording) {
                        utils::clear_ai_data($this->mod->id, $newattempt->id);
                        utils::remove_stats($newattempt);
                        $newattempt->selftranscript="";
                        $newattempt->completedsteps = $step;
                    }
                    //if rerecording, or we are in "new" mode (first recording) we register our AWS task
                    if($rerecording || !$edit){
                        utils::register_aws_task($this->mod->id, $newattempt->id, $this->context->id);
                    }


                    break;
                case constants::STEP_SELFTRANSCRIBE:
                    //if the user has altered their self transcript, we ought to recalc all the stats and ai data
                    $st_altered = $attempt && $newattempt->selftranscript
                        && $attempt->selftranscript != $newattempt->selftranscript;
                    if($st_altered) {
                        $stats = utils::calculate_stats($newattempt->selftranscript, $attempt, $this->mod->ttslanguage);
                        if ($stats) {
                            $stats = utils::fetch_sentence_stats($newattempt->selftranscript,$stats, $this->mod->ttslanguage);
                            $stats = utils::fetch_word_stats($newattempt->selftranscript,$this->mod->ttslanguage,$stats);
                            $stats = utils::calc_grammarspell_stats($newattempt->selftranscript,$this->mod->region,
                                $this->mod->ttslanguage,$stats);

                            utils::save_stats($stats, $attempt);
                        }
                        //recalculate AI data, if the selftranscription is altered AND we have a jsontranscript
                        if($attempt->jsontranscript){
                            $passage = $newattempt->selftranscript;
                            $aitranscript = new \mod_solo\aitranscript($attempt->id, $this->context->id,$passage,$attempt->transcript, $attempt->jsontranscript);
                            $aitranscript->recalculate($passage,$attempt->transcript, $attempt->jsontranscript);
                        }
                    }
                    break;
                default:
            }

            //Set the last completed stage
            if($lateststep < $step){
                $newattempt->completedsteps = $step;
            }

            //now update the db
            if (!$DB->update_record(constants::M_ATTEMPTSTABLE,$newattempt)){
                $ret->message = "Could not update solo attempt!";
                $ret->success = false;
                return $ret;
            }

            //if we just finished the last step then lets indicate this activity complete in the Moodle sense.
            if($step==constants::STEP_FINAL){
                //notify completion handler that we are finished
                $completion=new \completion_info($this->course);
                if($completion->is_enabled($this->cm) && $this->mod->completionallsteps) {
                    $completion->update_state($this->cm,COMPLETION_COMPLETE);
                }
            }

            //go back to top page
            $ret->message = "Updated solo attempt!";
            $ret->success = true;
            return $ret;
        }

        //should not really get here , but lets return anyway
        return $ret;

    }


}//end of class