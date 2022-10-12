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

class missedwords extends basereport {

    protected $report = "missedwords";
    protected $fields = array('rank','missed_count','passageword', 'passageindex');
    protected $headingdata = null;
    protected $qcache = array();
    protected $ucache = array();

    public function fetch_formatted_field($field, $record, $withlinks) {
        global $DB, $CFG, $OUTPUT;

        switch ($field) {

            case 'passageindex':
                $ret = $record->passageindex;
                break;


            case 'passageword':
                $ret = $record->passageword;
                break;

            case 'missed_count':
                $ret = $record->missed_count;
                break;

            case 'rank':
                $ret = $record->rank;
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
        return get_string('missedwordsheading', constants::M_COMPONENT);

    }

    public function fetch_formatted_description() {

        return get_string('missedwords_explanation', constants::M_COMPONENT);

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


        //get all the session errors for the attempts of the activity, in total or by group
        if($formdata->groupid > 0){

            list($groupswhere, $allparams) = $DB->get_in_or_equal($formdata->groupid);

            $alldatasql = "SELECT tu.*, tu.sessiontime, tu.sessionerrors as sessionerrors, tai.sessionerrors as aisessionerrors" .
                    " FROM {" . constants::M_USERTABLE . "} tu " .
                    " INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                    " INNER JOIN {groups_members} gm ON tu.userid=gm.userid " .
                    " WHERE gm.groupid $groupswhere AND tu.readaloudid=?  AND tu.dontgrade = 0 " .
                    " ORDER BY tu.userid, tu.id DESC  ";
            $allparams[]=$formdata->readaloudid;
            $alldata = $DB->get_records_sql($alldatasql, $allparams);
        }else{
            $alldatasql = "SELECT tu.*, tu.sessionerrors as sessionerrors, tai.sessionerrors as aisessionerrors" .
                " FROM {" . constants::M_USERTABLE . "} tu " .
                " INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                " WHERE tu.readaloudid=?  AND tu.dontgrade = 0 " .
                " ORDER BY tu.userid, tu.id DESC  ";
            $allparams[]=$formdata->readaloudid;
            $alldata = $DB->get_records_sql($alldatasql, $allparams);
        }

        //Get most recent attempt. Could be done in SQL I suppose ...
        $user_attempt_totals = array();
        $latestattempts = [];
        if ($alldata) {
            foreach ($alldata as $thedata) {

                //we ony take the most recent attempt
                if (array_key_exists($thedata->userid, $user_attempt_totals)) {
                    $user_attempt_totals[$thedata->userid] = $user_attempt_totals[$thedata->userid] + 1;
                    continue;
                }
                $user_attempt_totals[$thedata->userid] = 1;
                //if we do not have a human graded attempt, use aisessionerrors
                if (!$thedata->sessiontime) {
                    $thedata->sessionerrors = $thedata->aisessionerrors;
                }
                $latestattempts[] = $thedata;
            }
        }

        //loop through the attempt counting error words and storing them for use later
        $results = [];
        foreach ($latestattempts as $attempt) {
            $attempt_errors = json_decode($attempt->sessionerrors);
            foreach ($attempt_errors as $attempt_error) {
                if (array_key_exists($attempt_error->wordnumber, $results)) {
                    $results[$attempt_error->wordnumber]->missed_count++;
                    continue;
                }else{
                    $result = new \stdClass();
                    $result->missed_count = 1;
                    $result->passageindex = $attempt_error->wordnumber;
                    $result->passageword = $attempt_error->word;
                    $results[$attempt_error->wordnumber] = $result;
                }
            }
        }


        //Sort by number of missed_count, highest first
        usort($results, function($a, $b)
        {
            return ($a->missed_count < $b->missed_count) ? 1 : (($a->missed_count > $b->missed_count) ? -1 : 0);
        });

        //add a rank for the words and share rank if they are the same missed count
        $currentrank=0;
        $previousrank=0;
        $previous_missed_count=0;
        foreach($results as $result) {
            $currentrank++;
            if ($previous_missed_count != $result->missed_count){
                $result->rank = $currentrank;
                $previous_missed_count = $result->missed_count;
                $previousrank = $currentrank;
            }else{
                $result->rank=$previousrank;
            }
        }

        //Get top 10 elements (or as many as there are) and use those
        //$itemcount = min(count($results),10);
        //$this->rawdata = array_slice($results, 0,$itemcount);

        //Or let report do that
        $this->rawdata=$results;

        //return
        return true;
    }

}