<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */

namespace mod_minilesson\report;

use \mod_minilesson\constants;
use \mod_minilesson\utils;

class attemptresults extends basereport
{

    protected $report="attemptresults";
    protected $fields = array('qnumber','type','title','result','grade_p');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();



    public function fetch_formatted_heading(){
        $record = $this->headingdata;
        if(!$record){return '';}
        $user = $this->fetch_cache('user',$record->userid);
        $a = new \stdClass();
        $a->username = fullname($user);
        $a->date = date("Y-m-d H:i:s", $record->timecreated);
        $a->attemptid = $record->id;
        $a->sessionscore = $record->sessionscore;
        return get_string('attemptresultsheading',constants::M_COMPONENT,$a);

    }

    public function fetch_formatted_field($field, $record, $withlinks)
    {
        global $DB, $CFG, $OUTPUT;


        switch ($field) {
            case 'qnumber':
                $ret = $record->index;
                break;

            case 'type':
                $ret = $record->type;
                break;

            case 'title':
                $ret = $record->title;
                break;

            case 'result':
                $ret = $record->correctitems . '/' . $record->totalitems;
                break;

            //grade could hold either human or ai data
            case 'grade_p':
                //if not human or ai graded
                $ret = $record->grade;
                break;


            default:
                if (property_exists($record, $field)) {
                    $ret = $record->{$field};
                } else {
                    $ret = '';
                }
        }
        return $ret;

    } //end of function


    public function process_raw_data($formdata)
    {
        global $DB, $USER;

        $this->rawdata = [];

        //get the comp test quiz data
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $formdata->moduleid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
        $comp_test =  new \mod_minilesson\comprehensiontest($cm);
        $forcetitles=true;
        $quizdata = $comp_test->fetch_test_data_for_js($forcetitles);
        $emptydata = array();


        //groupsmode
        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);


        //if no groups, or can see all groups then the SQL is simple
        if($supergrouper || $groupsmode !=SEPARATEGROUPS) {

            //we just need the  individual recoen
            $record =$DB->get_record(constants::M_ATTEMPTSTABLE,
                    array('id'=>$formdata->attemptid,'moduleid'=>$formdata->moduleid));

        //if need to partition to groups, SQL for groups
        }else{
            $groups = groups_get_user_groups($course->id);
            if (!$groups || empty($groups[0])) {
                return false;
            }
            list($groupswhere, $allparams) = $DB->get_in_or_equal(array_values($groups[0]));

            $allsql ="SELECT tu.* FROM {".constants::M_ATTEMPTSTABLE ."} tu " .
                    " INNER JOIN {groups_members} gm ON tu.userid=gm.userid " .
                    " WHERE gm.groupid $groupswhere AND tu.moduleid = ? AND tu.id= ?" .
                    " ORDER BY tu.id DESC";
            $allparams[]=$formdata->moduleid;
            $allparams[]=$formdata->attemptid;
            $records  = $DB->get_records_sql($allsql, $allparams);
            if($records){
                $record = array_shift($records);
            }else{
                $record =false;
            }
        }





        if ($record) {
                //heading data
                $this->headingdata= $record;

                $steps = json_decode($record->sessiondata)->steps;
                $results = array_filter($steps, function($step){return $step->hasgrade;});
                foreach($results as $result){
                    $result->title=$quizdata[$result->index]->title;
                    $result->type=get_string($quizdata[$result->index]->type,constants::M_COMPONENT);
                    $result->index++;
                }
                $this->rawdata = $results;

        } else {
            //heading data
            $this->headingdata= false;
            $this->rawdata = $emptydata;
        }
        return true;
    }//end of function

}