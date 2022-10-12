<?php

namespace mod_readaloud\report;

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Report Classes.
 *
 * @package    mod_readaloud
 * @copyright  readaloud
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;

/**
 * Classes for Reports
 *
 *    The important functions are:
 *  process_raw_data : turns log data for one thing (e.g question attempt) into one row
 * fetch_formatted_fields: uses data prepared in process_raw_data to make each field in fields full of formatted data
 * The allusers report is the simplest example
 *
 * @package    mod_readaloud
 * @copyright  readaloud
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class basereport {

    protected $report = "";
    protected $head = array();
    protected $rawdata = null;
    protected $fields = array();
    protected $dbcache = array();

    abstract function process_raw_data($formdata);

    abstract function fetch_formatted_heading();

    public function fetch_formatted_description() {

        return '';

    }

    public function fetch_fields() {
        return $this->fields;
    }

    public function fetch_head() {
        $head = array();
        foreach ($this->fields as $field) {
            $head[] = get_string($field, constants::M_COMPONENT);
        }
        return $head;
    }

    public function fetch_name() {
        return $this->report;
    }

    public function fetch_all_rows_count() {
        return $this->rawdata ? count($this->rawdata) : 0;
    }

    public function truncate($string, $maxlength) {
        if (strlen($string) > $maxlength) {
            $string = substr($string, 0, $maxlength - 2) . '..';
        }
        return $string;
    }

    public function fetch_cache($table, $rowid) {
        global $DB;
        if (!array_key_exists($table, $this->dbcache)) {
            $this->dbcache[$table] = array();
        }
        if (!array_key_exists($rowid, $this->dbcache[$table])) {
            $this->dbcache[$table][$rowid] = $DB->get_record($table, array('id' => $rowid));
        }
        return $this->dbcache[$table][$rowid];
    }

    public function fetch_formatted_time($seconds) {

        //return empty string if the timestamps are not both present.
        if (!$seconds) {
            return '';
        }
        $time = time();
        return $this->fetch_time_difference($time, $time + $seconds);
    }

    public function fetch_time_difference($starttimestamp, $endtimestamp) {

        //return empty string if the timestamps are not both present.
        if (!$starttimestamp || !$endtimestamp) {
            return '';
        }

        $s = $date = new \DateTime();
        $s->setTimestamp($starttimestamp);

        $e = $date = new \DateTime();
        $e->setTimestamp($endtimestamp);

        $diff = $e->diff($s);
        $ret = $diff->format("%H:%I:%S");
        return $ret;
    }

    public function fetch_formatted_rows($withlinks = true, $paging = false) {
        $records = $this->rawdata;
        $fields = $this->fields;
        $returndata = array();
        if ($paging) {
            $startrecord = ($paging->perpage * $paging->pageno) + 1;
            $endrecord = $startrecord + $paging->perpage - 1;
        }
        $reccount = 0;
        foreach ($records as $record) {
            $reccount++;
            if ($paging && ($reccount < $startrecord || $reccount > $endrecord)) {
                continue;
            }

            $data = new \stdClass();
            foreach ($fields as $field) {
                $data->{$field} = $this->fetch_formatted_field($field, $record, $withlinks);
            }//end of for each field
            $returndata[] = $data;
        }//end of for each record
        return $returndata;
    }

    public function fetch_formatted_field($field, $record, $withlinks) {
        global $DB;
        switch ($field) {
            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;
            case 'userid':
                $u = $this->fetch_cache('user', $record->userid);
                $ret = fullname($u);
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

}
