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

class machinegrading extends basereport {

    protected $report = "machinegrading";
    protected $fields = array('id', 'username', 'audiofile', 'totalattempts', 'rawwpm', 'rawaccuracy_p', 'rawgrade_p', 'review',
            'regrade', 'adjustedwpm', 'adjustedaccuracy_p', 'adjustedgrade_p', 'timecreated');
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
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                            array('action' => 'gradingbyuser', 'n' => $record->readaloudid, 'userid' => $record->userid));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'totalattempts':
                $ret = $record->totalattempts;
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                            array('action' => 'machinegradingbyuser', 'n' => $record->readaloudid, 'userid' => $record->userid));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'audiofile':
                if ($withlinks) {
                    /*
                    $ret = html_writer::tag('audio','',
                            array('controls'=>'','src'=>$record->audiourl));
                        */
                    $ret = \html_writer::div('<i class="fa fa-play-circle"></i>', constants::M_HIDDEN_PLAYER_BUTTON,
                            array('data-audiosource' => $record->audiourl));

                } else {
                    $ret = get_string('submitted', constants::M_COMPONENT);
                }
                break;

            case 'rawwpm':
                $ret = $record->wpm;
                break;

            case 'rawaccuracy_p':
                $ret = $record->accuracy;
                break;

            case 'rawgrade_p':
                $ret = $record->sessionscore;
                break;

            case 'adjustedwpm':
                $ret = $record->adjustwpm;
                break;

            case 'adjustedaccuracy_p':
                $ret = $record->adjustaccuracy;
                break;

            case 'adjustedgrade_p':
                $ret = $record->adjustsessionscore;
                break;

            //we took this button away to make some room
            case 'gradenow':
                if ($withlinks) {
                    $url = new \moodle_url(constants::M_URL . '/grading.php',
                            array('action' => 'gradenow', 'n' => $record->readaloudid, 'attemptid' => $record->attemptid));
                    $btn = new \single_button($url, get_string('gradenow', constants::M_COMPONENT), 'post');
                    $ret = $OUTPUT->render($btn);
                } else {
                    $ret = get_string('cannotgradenow', constants::M_COMPONENT);
                }
                break;

            //case 'regrade':
            //this will be useful once we add hints to passage markup, and when you change machine algorythms and processing
            //case "review":
            case 'regrade':

                //FOR  REGRADE ... when fixing bogeys (replace review link with this one)
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                            array('action' => 'regradenow', 'n' => $record->readaloudid, 'attemptid' => $record->attemptid));
                    $ret = \html_writer::link($link, get_string('regrade', constants::M_COMPONENT));
                } else {
                    $ret = get_string('cannotgradenow', constants::M_COMPONENT);
                }
                break;

            case 'review':

                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                            array('action' => 'machinereview', 'n' => $record->readaloudid, 'attemptid' => $record->attemptid));
                    $ret = \html_writer::link($link, get_string('review', constants::M_COMPONENT));
                } else {
                    $ret = get_string('cannotgradenow', constants::M_COMPONENT);
                }
                break;

            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            //do we need this..? hid it for now
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

    } //end of function

    public function fetch_formatted_heading() {
        $record = $this->headingdata;
        $ret = '';
        if (!$record) {
            return $ret;
        }
        return get_string('machinegradingheading', constants::M_COMPONENT);

    }//end of function

    public function process_raw_data($formdata) {
        global $DB;

        //heading data
        $this->headingdata = new \stdClass();
        $this->rawdata = [];

        $emptydata = array();
        $maxfield = 'id';
        $user_attempt_totals = array();
        $sql = "SELECT tai.id,tu.userid, tai.wpm, tai.accuracy,tu.timecreated,tai.attemptid, tai.sessionerrors," .
                " tai.sessionscore,tai.sessiontime,tai.sessionendword, tu.filename, tai.readaloudid,  u.firstnamephonetic," .
                "u.lastnamephonetic,u.middlename,u.alternatename,u.firstname,u.lastname  FROM {" . constants::M_AITABLE .
                "} tai INNER JOIN  {" . constants::M_USERTABLE . "}" .
                " tu ON tu.id =tai.attemptid AND tu.readaloudid=tai.readaloudid INNER JOIN {user} u ON tu.userid=u.id WHERE tu.readaloudid=?" .
                " ORDER BY u.lastnamephonetic,u.firstnamephonetic,u.lastname,u.firstname,u.middlename,u.alternatename, tai.id DESC";
        $alldata = $DB->get_records_sql($sql, array($formdata->readaloudid));

        if ($alldata) {
            foreach ($alldata as $thedata) {

                //we ony take the max (attempt, accuracy, wpm ..)
                if (array_key_exists($thedata->userid, $user_attempt_totals)) {
                    $user_attempt_totals[$thedata->userid] = $user_attempt_totals[$thedata->userid] + 1;
                } else {
                    $user_attempt_totals[$thedata->userid] = 1;
                }
                if (array_key_exists($thedata->userid, $this->rawdata)) {
                    if ($this->rawdata[$thedata->userid]->{$maxfield} >= $thedata->{$maxfield}) {
                        continue;
                    }
                }

                //make the audio url for the selected attempt data
                $thedata->audiourl =
                        \mod_readaloud\utils::make_audio_URL($thedata->filename, $formdata->modulecontextid, constants::M_COMPONENT,
                                constants::M_FILEAREA_SUBMISSIONS, $thedata->id);

                //fetch and poke in the adjusted scores here, though we could also do it from
                //fetch formatted field
                //we need to calc the no. of errors and adjust
                $errorcount = \mod_readaloud\utils::count_sessionerrors($thedata->sessionerrors);
                $newerrorcount = $errorcount - $formdata->accadjust;
                if ($newerrorcount < 0) {
                    $newerrorcount = 0;
                }
                //recalculate scores with new errorcount
                $adjusted_scores = \mod_readaloud\utils::processscores($thedata->sessiontime,
                        $thedata->sessionendword,
                        $newerrorcount,
                        $formdata->moduleinstance);
                $thedata->adjustwpm = $adjusted_scores->wpmscore;
                $thedata->adjustaccuracy = $adjusted_scores->accuracyscore;
                $thedata->adjustsessionscore = $adjusted_scores->sessionscore;
                $this->rawdata[$thedata->userid] = $thedata;
            }
            foreach ($this->rawdata as $thedata) {
                $thedata->totalattempts = $user_attempt_totals[$thedata->userid];
            }

        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }//end of function
}//end of class