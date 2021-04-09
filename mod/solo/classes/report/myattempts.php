<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */

namespace mod_solo\report;

use \mod_solo\constants;
use \mod_solo\utils;

class myattempts extends basereport
{

    protected $report="myattempts";
    protected $fields = array('id','audiofile','partners','turns','stats_avturn','stats_longestturn','stats_targetwords','stats_autospellscore','stats_aiaccuracy','grade','timemodified','view');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();


    public function fetch_formatted_field($field,$record,$withlinks)
    {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'id':
                $ret = $record->id;
                break;


            case 'grade':

                $ret = $record->grade==null ? '' : $record->grade . '%';
                break;


            case 'turns':
                $ret = $record->turns;
                break;

            case 'stats_avturn':
                $ret = $record->avturn;
                break;

            case 'stats_longestturn':
                $ret = $record->longestturn;
                break;

            case 'stats_targetwords':

                // $ret = $record->targetwords . '/' . $record->totaltargetwords;
                $ret = $record->targetwords;

                break;

            case 'stats_autospellscore':
                $ret = $record->autospellscore ;
                break;

            case 'stats_aiaccuracy':
                if($record->aiaccuracy<0) {
                    $ret = '';
                }else{
                    $ret = $record->aiaccuracy;
                }

                break;

            case 'audiofile':
                if ($withlinks && !empty($record->filename)) {


                    $ret = \html_writer::tag('audio','',
                            array('controls'=>'1','src'=>$record->filename));
                    //hidden player works but less useful right now
                    /*
                    $ret = \html_writer::div('<i class="fa fa-play-circle fa-2x"></i>',
                            constants::M_HIDDEN_PLAYER_BUTTON, array('data-audiosource' => $record->filename));
                    */

                } else {
                    $ret = get_string('submitted', constants::M_COMPONENT);
                }
                break;

            case 'timemodified':
                $ret = date("Y-m-d H:i:s", $record->timemodified);
                break;

            case 'view':
                if ($withlinks && has_capability('mod/solo:view', $this->context)) {
                    $url = new \moodle_url(constants::M_URL . '/myreports.php',
                            array('format'=>'html','report' => 'singleattempt', 'id' => $this->cm->id, 'attemptid' => $record->id));
                    $btn = new \single_button($url, get_string('view'), 'post');
                    $ret = $OUTPUT->render($btn);
                }else {
                    $ret = '';
                }
                break;


            default:
                if (property_exists($record, $field)) {
                    $ret = $record->{$field};
                } else {
                    $ret = '';
                }
        }
        return $ret;
    }

    public function fetch_formatted_heading(){
        $record = $this->headingdata;
        $ret='';
        if(!$record){return $ret;}
        $user = $this->fetch_cache('user', $record->userid);
        $usersname = fullname($user);
        return get_string('myattemptsheading',constants::M_COMPONENT,$usersname );

    }

    public function process_raw_data($formdata){
        global $DB,$USER;

        //heading data
        $this->headingdata = new \stdClass();
        $this->headingdata->userid=$USER->id;

        $emptydata = array();
        $sql = 'SELECT at.id,at.grade,  at.userid, at.filename, st.turns, st.avturn, st.longestturn, st.targetwords, 
        st.totaltargetwords,st.autospellscore,st.aiaccuracy, at.timemodified ';
        $sql .= '  FROM {' . constants::M_ATTEMPTSTABLE . '} at INNER JOIN {' . constants::M_STATSTABLE .  '} st ON at.id = st.attemptid ';
        $sql .= '  INNER JOIN {' . constants::M_TABLE .  '} p ON p.id = at.solo ';
        $sql .= ' WHERE at.userid = :userid AND p.course = :courseid';
        $sql .= ' ORDER BY at.timemodified DESC';
        $alldata = $DB->get_records_sql($sql,array('userid'=>$USER->id, 'courseid'=>$this->cm->course));

        if($alldata){
            foreach($alldata as $thedata){
                //do any processing here
            }
            $this->rawdata= $alldata;
        }else{
            $this->rawdata= $emptydata;
        }
        return true;
    }

}