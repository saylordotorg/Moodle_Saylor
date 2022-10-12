<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */

namespace mod_readaloud\report;

use \mod_readaloud\constants;
use \mod_readaloud\utils;
use \mod_readaloud\diff;

class courseattempts extends basereport {

    protected $report = "courseattempts";
    protected $fields = array('studentid', 'username','studentname','activityname','activitywords',
        'errorcount','oralreadingscore_p','readingtime', 'wpm','timecreated', 'passagekey');
    protected $headingdata = null;
    protected $qcache = array();
    protected $ucache = array();

    public function fetch_formatted_field($field, $record, $withlinks) {
        global $DB, $CFG, $OUTPUT;
        $user = $this->fetch_cache('user', $record->userid);
        $activitywords=[];
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
                        array('action' => 'gradingbyuser', 'n' => $record->activityid, 'userid' => $user->id));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'errorcount':
                $ret = $record->errorcount;
                break;

            case 'oralreadingscore_p':
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                        array('action' => 'gradenow', 'n' => $record->activityid, 'attemptid' => $record->id));
                    $ret = \html_writer::link($link, $ret = $record->accuracy);
                } else {
                    $ret = $record->accuracy;
                }

                break;

            case 'wpm':
                $ret = $record->wpm;
                break;

            case 'readingtime':
                $ret = $record->sessiontime;
                break;

            case 'activitywords':
                if(array_key_exists($record->activityid,$activitywords)){
                    $ret = $activitywords[$record->activityid];
                }else{
                    $wordcount = count(diff::fetchWordArray($record->passage));
                    $activitywords[$record->activityid]=$wordcount;
                    $ret = $wordcount;
                }
                break;

            case 'activityname':
                $ret = $record->activityname;
                break;


            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            case 'passagekey':
                $ret = $record->passagekey;
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
        //$ec = $this->fetch_cache(constants::M_TABLE,$record->englishcentralid);
        return get_string('courseattemptsheading', constants::M_COMPONENT);

    }

    public function fetch_formatted_description() {

        return get_string('courseattempts_explanation', constants::M_COMPONENT);

    }

    public function process_raw_data($formdata) {
        global $DB, $USER;

        //heading data
        $this->headingdata = new \stdClass();
        $emptydata = array();

        //Groups stuff
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $formdata->readaloudid));
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);

        //if we need to show  groups
        //if we need to show just one groups
        if($formdata->groupid > 0){

            list($groupswhere, $allparams) = $DB->get_in_or_equal($formdata->groupid);

            $alldatasql = "SELECT tu.*,tai.wpm as aiwpm, tai.accuracy as aiaccuracy, tai.errorcount as aierrorcount, " .
                " tu.wpm as wpm, tu.accuracy as accuracy, tu.errorcount as errorcount, " .
                " tu.sessiontime as sessiontime,tai.sessiontime as aisessiontime at.name as activityname,at.id as activityid, at.passage, at.passagekey " .
                    " FROM {" . constants::M_USERTABLE . "} tu " .
                    " INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                    " INNER JOIN {groups_members} gm ON tu.userid=gm.userid " .
                    " INNER JOIN {" . constants::M_TABLE . "} at ON at.id=tu.readaloudid " .
                    " WHERE gm.groupid $groupswhere AND tu.courseid=?  AND tu.dontgrade = 0 ";
            $allparams[]=$formdata->courseid;
            $alldata = $DB->get_records_sql($alldatasql, $allparams);
        }else{
            $alldatasql = "SELECT tu.*,tai.wpm as aiwpm, tai.accuracy as aiaccuracy, tai.errorcount as aierrorcount, " .
                " tu.wpm as wpm, tu.accuracy as accuracy, tu.errorcount as errorcount, " .
                " tu.sessiontime as sessiontime, tai.sessiontime as aisessiontime, at.name as activityname,at.id as activityid, at.passage, at.passagekey " .
                " FROM {" . constants::M_USERTABLE . "} tu " .
                " INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                " INNER JOIN {" . constants::M_TABLE . "} at ON at.id=tu.readaloudid " .
                " WHERE tu.courseid=?  AND tu.dontgrade = 0 ";
            $allparams[]=$formdata->courseid;
            $alldata = $DB->get_records_sql($alldatasql, $allparams);
        }


        if ($alldata) {
            //sessiontime is our indicator that a human grade has been saved.
            foreach ($alldata as $result) {
                if (!$result->sessiontime || $moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY) {
                    $result->wpm = $result->aiwpm;
                    $result->accuracy = $result->aiaccuracy;
                    $result->errorcount = $result->aierrorcount;
                    $result->sessiontime = $result->aisessiontime;
                }
            }
        }


        if ($alldata) {
            $this->rawdata = $alldata;
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }

}