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

class downloadaudio extends basereport
{

    protected $report="downloadaudio";
    protected $fields = array('id','idnumber', 'username','file');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();


    public function fetch_formatted_field($field,$record,$withlinks)
    {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'id':
                $ret = $record->id;
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/reports.php',
                            array('format'=>'html','report' => 'singleattempt', 'id' => $this->cm->id, 'attemptid' => $record->id));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'idnumber':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = $user->idnumber;
                break;

            case 'username':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = fullname($user);
                break;

            case 'file':
                $ret = $record->filename;
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
        return $record->activityname .'-'.get_string('downloadaudioheading',constants::M_COMPONENT);
    }

    public function process_raw_data($formdata){
        global $DB;

        //heading data
        $this->headingdata = new \stdClass();
        $this->headingdata->activityname = $formdata->activityname;

        $emptydata = array();
        $sql = 'SELECT at.id,at.filename, at.userid';
        $sql .= '  FROM {' . constants::M_ATTEMPTSTABLE . '} at' ;
        $sql .= ' WHERE at.solo = :soloid';
        $sql .= ' ORDER BY at.timemodified DESC';
        $alldata = $DB->get_records_sql($sql,array('soloid'=>$formdata->soloid));



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