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

class incompleteattempts extends basereport {

    protected $report = "incompleteattempts";
    protected $fields = array('id', 'username', 'itemscomplete','timecreated', 'deletenow');
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

            case 'itemscomplete':
                $ret = $record->itemscomplete;
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
        return get_string('incompleteattemptsheading', constants::M_COMPONENT);

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

            $allsql ="SELECT att.* FROM {".constants::M_ATTEMPTSTABLE ."} att " .
                    "INNER JOIN {groups_members} gm ON att.userid=gm.userid " .
                    "WHERE gm.groupid $groupswhere AND att.moduleid = ? AND att.status = " . constants::M_STATE_INCOMPLETE .
                    " ORDER BY timecreated DESC";
            $allparams[]=$formdata->moduleid;
            $alldata = $DB->get_records_sql($allsql, $allparams);

        }else{



            $alldata = $DB->get_records(constants::M_ATTEMPTSTABLE,
                array('moduleid' => $formdata->moduleid, 'status' => constants::M_STATE_INCOMPLETE), 'timecreated DESC');

        }

        if ($alldata) {
            foreach ($alldata as $thedata) {
                $thedata->itemscomplete =0;
                if(utils::is_json($thedata->sessiondata)) {
                    $sessiondata = json_decode($thedata->sessiondata);
                    if(isset($sessiondata->steps)) {
                        $stepsdata = $sessiondata->steps;
                        if ($stepsdata && is_array($stepsdata)) {
                            $thedata->itemscomplete = count($stepsdata);
                        }
                    }
                }
                $this->rawdata[] = $thedata;
            }
            $this->rawdata = $alldata;
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }

}