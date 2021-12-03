<?php

namespace mod_wordcards\local\report;

/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */


use \mod_wordcards\constants;
use \mod_wordcards\utils;

class grades extends basereport {

    protected $report = "grades";
    protected $fields = array('id', 'username','attempts','grade1_p','grade2_p','grade3_p','grade4_p','grade5_p','grade_p','timecreated', 'deletenow');
    protected $headingdata = null;
    protected $qcache = array();
    protected $ucache = array();

    public function fetch_formatted_field($field, $record, $withlinks) {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'id':
                $ret = $record->id;
                break;

            case 'username':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = fullname($user);
                break;

            case 'attempts':
                if ($withlinks) {
                    $url = new \moodle_url(constants::M_URL . '/reports.php',
                            array('report' => 'userattempts', 'n' => $record->modid, 'userid'=>$record->userid));
                    $ret = "<a href='" . $url->out() . "'>". $record->attempts . "</a>" ;
                } else {
                    $ret =  $record->attempts;
                }

                break;

            case 'grade1_p':
                $ret = $record->grade1;
                break;

            case 'grade2_p':
                $ret = $record->grade2;
                break;

            case 'grade3_p':
                $ret = $record->grade3;
                break;

            case 'grade4_p':
                $ret = $record->grade4;
                break;

            case 'grade5_p':
                $ret = $record->grade5;
                break;

            case 'grade_p':
                $ret = $record->totalgrade;
                break;

            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            case 'deletenow':
                if ($withlinks) {
                    $url = new \moodle_url(constants::M_URL . '/manageattempts.php',
                            array('action' => 'delete', 'n' => $record->modid, 'attemptid' => $record->id,
                                    'source' => $this->report));
                    $btn = new \single_button($url, get_string('delete'), 'post');
                    $btn->add_confirm_action(get_string('deleteattemptconfirm', constants::M_COMPONENT));
                    $ret = $OUTPUT->render($btn);
                } else {
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

    public function fetch_formatted_heading() {
        if($this->headingdata->gradeoptions == constants::M_GRADEHIGHEST) {
            return get_string('gradesheadinghighest', constants::M_COMPONENT);
        }else{
            return get_string('gradesheadinglatest', constants::M_COMPONENT);
        }
    }

    public function process_raw_data($formdata) {
        global $DB,$USER;

        //heading data
        $this->headingdata = new \stdClass();
        $this->headingdata->gradeoptions = $formdata->gradeoptions;
        $emptydata = array();

        //groupsmode
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $formdata->modid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);

        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);

        //if grading on latest attempt fetch that, if grading on highest score fetch that
        switch($formdata->gradeoptions){
            case constants::M_GRADEHIGHEST:
                $sortfield = "totalgrade";
                break;
            case constants::M_GRADELATEST:
            default:
                $sortfield = "timecreated";
                break;
        }


        //if need to partition to groups, SQL for groups
        if($formdata->groupid > 0){

            list($groupswhere, $allparams) = $DB->get_in_or_equal($formdata->groupid);

            $allsql ="SELECT att.* FROM {".constants::M_ATTEMPTSTABLE ."} att " .
              "INNER JOIN {groups_members} gm ON att.userid=gm.userid " .
              "WHERE gm.groupid $groupswhere AND att.modid = ? " .
              "ORDER BY $sortfield DESC";
            $allparams[]=$formdata->modid;
            $alldata = $DB->get_records_sql($allsql, $allparams);
        }else{
            //if no groups, or can see all groups then the SQL is simple
            $alldata = $DB->get_records(constants::M_ATTEMPTSTABLE, array('modid' => $formdata->modid), "$sortfield DESC");

        }

        //loop through data an attemptcount and add latestattempt/highestattempt
        $userattemptcount=[];
        if ($alldata) {
            foreach ($alldata as $thedata) {
                if(array_key_exists($thedata->userid,$userattemptcount)){
                    $userattemptcount[$thedata->userid]++;
                    continue;
                }else{
                    $userattemptcount[$thedata->userid]=1;
                    $this->rawdata[] = $thedata;
                }
            }
        } else {
            $this->rawdata = $emptydata;
        }

        //add attempt count
        foreach ($this->rawdata as $thedata) {
            $thedata->attempts=$userattemptcount[$thedata->userid];
        }

        return true;
    }

}