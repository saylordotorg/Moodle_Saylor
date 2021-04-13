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

class attempts extends basereport {

    protected $report = "attempts";
    protected $fields = array('id', 'username', 'grade_p','timecreated', 'deletenow');
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
        $record = $this->headingdata;
        $ret = '';
        if (!$record) {
            return $ret;
        }
        return get_string('attemptsheading', constants::M_COMPONENT);

    }

    public function process_raw_data($formdata) {
        global $DB, $USER;

        //heading data
        $this->headingdata = new \stdClass();
        $emptydata = array();

        //groupsmode
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $formdata->modid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);

        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);

        //if no groups or can see all groups, simple SQL
        if($supergrouper || $groupsmode !=SEPARATEGROUPS) {
            $alldata = $DB->get_records(constants::M_ATTEMPTSTABLE, array('modid' => $formdata->modid), 'timecreated DESC');

        //if need to partition to groups, SQL for groups
        }else{
            $groups = groups_get_user_groups($course->id);
            if (!$groups || empty($groups[0])) {
                return false;
            }
            list($groupswhere, $allparams) = $DB->get_in_or_equal(array_values($groups[0]));

            $allsql ="SELECT att.* FROM {".constants::M_ATTEMPTSTABLE ."} att " .
                    "INNER JOIN {groups_members} gm ON att.userid=gm.userid " .
                    "WHERE gm.groupid $groupswhere AND att.modid = ? " .
                    "ORDER BY timecreated DESC";
            $allparams[]=$formdata->modid;
            $alldata = $DB->get_records_sql($allsql, $allparams);
        }

        if ($alldata) {
            foreach ($alldata as $thedata) {

                $this->rawdata[] = $thedata;
            }
            $this->rawdata = $alldata;
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }

}