<?php

namespace mod_minilesson\report;

/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */


use \mod_minilesson\constants;
use \mod_minilesson\utils;

class courseattempts extends basereport {

    protected $report = "attempts";
    protected $fields = array('studentid', 'username','studentname','activityname','itemcount','correctcount', 'grade_p','timecreated', 'lessonkey');
    protected $headingdata = null;
    protected $qcache = array();
    protected $ucache = array();

    public function fetch_formatted_field($field, $record, $withlinks) {
        global $DB, $CFG, $OUTPUT;
        $user = $this->fetch_cache('user', $record->userid);
        switch ($field) {
            case 'studentid':
                $ret = $user->id;
                break;

            case 'username':
                $ret = $user->username;
                break;

            case 'studentname':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = fullname($user);
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                        array('action' => 'gradingbyuser', 'n' => $record->moduleid, 'userid' => $user->id));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'activityname':
                $ret = $record->activityname;
                break;


            case 'grade_p':
                $ret = $record->sessionscore;
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/reports.php',
                            array('report' => 'attemptresults', 'n' => $record->moduleid, 'attemptid' => $record->id));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            case 'deletenow':
                if ($withlinks) {
                    $url = new \moodle_url(constants::M_URL . '/manageattempts.php',
                            array('action' => 'delete', 'n' => $record->moduleid, 'attemptid' => $record->id,
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
        $record = $this->headingdata;
        $ret = '';
        if (!$record) {
            return $ret;
        }
        return get_string('courseattemptsheading', constants::M_COMPONENT);

    }

    public function process_raw_data($formdata) {
        global $DB, $USER;

        //heading data
        $this->headingdata = new \stdClass();
        $emptydata = array();

        //groupsmode
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $formdata->moduleid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);

        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);


        if($formdata->groupid > 0){

            list($groupswhere, $allparams) = $DB->get_in_or_equal($formdata->groupid);

            $allsql ="SELECT att.*, act.name as activityname,  FROM {".constants::M_ATTEMPTSTABLE ."} att " .
                    "INNER JOIN {groups_members} gm ON att.userid=gm.userid " .
                    " INNER JOIN {" . constants::M_TABLE . "} act ON act.id=att.moduleid " .
                    "WHERE gm.groupid $groupswhere AND att.courseid = ? AND att.status = " . constants::M_STATE_COMPLETE .
                    " ORDER BY timecreated DESC";
            $allparams[]=$formdata->courseid;
            $alldata = $DB->get_records_sql($allsql, $allparams);

        }else{

            $allsql ="SELECT att.*,act.id as activityid,act.name as activityname FROM {".constants::M_ATTEMPTSTABLE ."} att " .
                " INNER JOIN {" . constants::M_TABLE . "} act ON act.id=att.moduleid " .
                "WHERE  att.courseid = ? AND att.status = " . constants::M_STATE_COMPLETE .
                " ORDER BY timecreated DESC";
            $allparams[]=$formdata->courseid;
            $alldata = $DB->get_records_sql($allsql, $allparams);

        }

        $quizdatas=[];


        if ($alldata) {
            foreach ($alldata as $thedata) {

                if(!array_key_exists($thedata->activityid,$quizdatas)){
                    $cm = get_coursemodule_from_instance(constants::M_TABLE, $thedata->activityid, $formdata->courseid, false, MUST_EXIST);
                    $comp_test =  new \mod_minilesson\comprehensiontest($cm);
                    $forcetitles=false;
                    $quizdatas[$thedata->activityid] = $comp_test->fetch_test_data_for_js($forcetitles);
                }
                $quizdata = $quizdatas[$thedata->activityid];

                    $steps = json_decode($thedata->sessiondata)->steps;

                    //in some cases its not an array.. urgh
                    if(!is_array($steps)){
                        $steps =(array) $steps;
                    }

                    $results = array_filter($steps, function($step){return $step->hasgrade;});
                    $thedata->itemcount = 0;
                    $thedata->correctcount = 0;
                    foreach($results as $result){
                        $result->type=$quizdata[$result->index]->type;
                        if($result->type!==constants::TYPE_PAGE){
                            $thedata->itemcount+=$result->totalitems ;
                            $thedata->correctcount+=$result->correctitems;
                        }
                    }

                    //$this->rawdata = $results;




                $this->rawdata[] = $thedata;
            }
            $this->rawdata = $alldata;
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }

}