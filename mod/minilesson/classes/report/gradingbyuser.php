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

class gradingbyuser extends basereport
{

    protected $report="gradingbyuser";
    protected $fields = array('id','grade_p','timecreated','deletenow');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();



    public function fetch_formatted_heading(){
        $record = $this->headingdata;
        $ret='';
        if(!$record){return $ret;}
        $user = $this->fetch_cache('user',$record->userid);
        return get_string('gradingbyuserheading',constants::M_COMPONENT,fullname($user));

    }

    public function fetch_formatted_field($field, $record, $withlinks)
    {
        global $DB, $CFG, $OUTPUT;


        switch ($field) {
            case 'id':
                $ret = $record->id;
                break;


            //grade could hold either human or ai data
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
                        array('action' => 'delete', 'n' => $record->moduleid, 'attemptid' => $record->id, 'source' => $this->report));
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

    } //end of function


    public function process_raw_data($formdata)
    {
        global $DB, $USER;

        //heading data
        $this->headingdata = new \stdClass();
        $this->rawdata = [];

        //heading data
        $this->headingdata->userid = $formdata->userid;

        $emptydata = array();

        //groupsmode
        $moduleinstance = $DB->get_record(constants::M_TABLE,array('id'=>$formdata->moduleid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);

        $groupsmode = groups_get_activity_groupmode($cm,$course);
        $context = empty($cm) ? \context_course::instance($course->id) : \context_module::instance($cm->id);
        $supergrouper = has_capability('moodle/site:accessallgroups', $context, $USER->id);


        //if no groups, or can see all groups then the SQL is simple
        if($supergrouper || $groupsmode !=SEPARATEGROUPS) {

            //if we are not machine grading the SQL is simpler
            $the_sql = "SELECT tu.* FROM {" . constants::M_ATTEMPTSTABLE . "} tu " .
                    " WHERE tu.moduleid=? AND tu.status=" . constants::M_STATE_COMPLETE .
                    " AND tu.userid=? " .
                    " ORDER BY tu.id DESC";

            $alldata =$DB->get_records_sql($the_sql, array($formdata->moduleid, $formdata->userid));
            //if need to partition to groups, SQL for groups
        }else{
            $groups = groups_get_user_groups($course->id);
            if (!$groups || empty($groups[0])) {
                return false;
            }
            list($groupswhere, $allparams) = $DB->get_in_or_equal(array_values($groups[0]));

            $allsql ="SELECT tu.* FROM {".constants::M_ATTEMPTSTABLE ."} tu " .
                    " INNER JOIN {groups_members} gm ON tu.userid=gm.userid " .
                    " WHERE gm.groupid $groupswhere AND tu.moduleid = ? AND tu.status=" . constants::M_STATE_COMPLETE .
                    " ORDER BY tu.id DESC";
            $allparams[]=$formdata->moduleid;
            $alldata = $DB->get_records_sql($allsql, $allparams);
        }


        if ($alldata) {
            foreach ($alldata as $thedata) {
                $this->rawdata[] = $thedata;
            }

        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }//end of function

}