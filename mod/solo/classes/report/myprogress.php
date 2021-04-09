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

class myprogress extends basereport
{

    protected $report="myprogress";
    protected $fields = array('soloname','stats_words','stats_turns','stats_avturn','stats_longestturn','stats_autospellscore','stats_aiaccuracy');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();

    public function fetch_chart_data($allfields=[]){

        if(empty($allfields)){
            $allfields=$this->fields;
        }


            //if we have data, yay
            if ($this->rawdata) {

                //init our data set
                $chartdata = new \stdClass();
                $chartdata->labels =[];
                $chartdata->series =[];

                //get some working data
                $rawdata = new \stdClass();
                foreach($allfields as $field){
                    $rawdata->{$field}=[];
                }


                //loop through each attempt
                foreach ($this->rawdata as $data) {
                    foreach ($allfields as $field) {
                        switch ($field) {
                            case 'soloname':
                                $chartdata->labels[] = $data->soloname;
                                break;
                            default:
                                $rawdata->{$field}[] = round($data->{$field},1);
                        }

                    }
                }
                //add rawdata to chartdata
                //get some working data
                foreach($allfields as $field){
                    switch ($field) {
                        case 'soloname':
                            break;
                        default:
                            $chartdata->series[] = new \core\chart_series(get_string($field, constants::M_COMPONENT),$rawdata->{$field});
                    }
                }
                return $chartdata;

            }else{
                return false;
            }
    }


    public function fetch_formatted_field($field,$record,$withlinks)
    {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'soloname':
                $ret = $record->soloname;
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
        return get_string('myprogressheading',constants::M_COMPONENT,$usersname );

    }

    public function process_raw_data($formdata){
        global $DB, $USER;

        //heading data
        $this->headingdata = new \stdClass();
        $this->headingdata->userid=$USER->id;

        $emptydata = array();
        $sql = 'SELECT p.id, p.name soloname, MAX(st.words) stats_words, MAX(st.turns) stats_turns, MAX(st.avturn) stats_avturn, MAX(st.longestturn) stats_longestturn,   MAX(st.autospellscore)stats_autospellscore ,MAX(st.aiaccuracy) stats_aiaccuracy';
        $sql .= '  FROM {' . constants::M_ATTEMPTSTABLE . '} at INNER JOIN {' . constants::M_STATSTABLE .  '} st ON at.id = st.attemptid ';
        $sql .= '  INNER JOIN {' . constants::M_TABLE .  '} p ON p.id = at.solo ';
        $sql .= ' WHERE p.course = :courseid AND at.userid= :userid';
        $sql .= ' GROUP BY p.id, p.name';
        $alldata = $DB->get_records_sql($sql,array('courseid'=>$this->cm->course, 'userid'=>$USER->id));

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