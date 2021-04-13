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

class userattempts extends basereport {

    protected $report = "userattempts";
    protected $fields = array('id', 'username','grade1_p','grade2_p','grade3_p','grade4_p','grade5_p', 'grade_p','timecreated', 'deletenow');
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
        $record = $this->headingdata;
        $ret = '';
        if (!$record) {
            return $ret;
        }
        return get_string('userattemptsheading', constants::M_COMPONENT);
    }

    public function process_raw_data($formdata) {
        global $DB;

        //heading data
        $this->headingdata = new \stdClass();
        $emptydata = array();

        //we would usually check for group access here, but its already been checked in reports.php
        $alldata = $DB->get_records(constants::M_ATTEMPTSTABLE, array('modid' => $formdata->modid, 'userid'=>$formdata->userid),'timecreated DESC');

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