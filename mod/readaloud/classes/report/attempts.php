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

class attempts extends basereport {

    protected $report = "attempts";
    protected $fields = array('id', 'username', 'audiofile', 'wpm', 'accuracy_p', 'grade_p' ,'timecreated', 'deletenow');
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

            case 'audiofile':
                if ($withlinks) {
                    /*
                    $ret = html_writer::tag('audio','',
                            array('controls'=>'','src'=>$record->audiourl));
                        */
                    $ret = \html_writer::div('<i class="fa fa-play-circle"></i>',
                            constants::M_HIDDEN_PLAYER_BUTTON, array('data-audiosource' => $record->audiourl));

                } else {
                    $ret = get_string('submitted', constants::M_COMPONENT);
                }
                break;

            case 'wpm':
                $ret = $record->wpm;
                break;

            case 'accuracy_p':
                $ret = $record->accuracy;
                break;

            case 'grade_p':
                $ret = $record->sessionscore;
                break;

            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            case 'deletenow':
                if ($withlinks) {
                    $url = new \moodle_url(constants::M_URL . '/manageattempts.php',
                            array('action' => 'delete', 'n' => $record->readaloudid, 'attemptid' => $record->id,
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
        //$ec = $this->fetch_cache(constants::M_TABLE,$record->englishcentralid);
        return get_string('attemptsheading', constants::M_COMPONENT);

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

            $alldatasql = "SELECT tu.* FROM {" . constants::M_USERTABLE . "} tu " .
                    " INNER JOIN {groups_members} gm ON tu.userid=gm.userid " .
                    " WHERE gm.groupid $groupswhere AND tu.readaloudid=?  AND tu.dontgrade = 0 ";
            $allparams[]=$formdata->readaloudid;
            $alldata = $DB->get_records_sql($alldatasql, $allparams);
        }else{
            $alldata = $DB->get_records(constants::M_USERTABLE, array('readaloudid' => $formdata->readaloudid));
        }

        if ($alldata) {
            foreach ($alldata as $thedata) {
                $thedata->audiourl =
                        \mod_readaloud\utils::make_audio_URL($thedata->filename, $formdata->modulecontextid, constants::M_COMPONENT,
                                constants::M_FILEAREA_SUBMISSIONS, $thedata->id);
                $this->rawdata[] = $thedata;
            }
            $this->rawdata = $alldata;
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }

}