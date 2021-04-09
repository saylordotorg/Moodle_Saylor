<?php
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

namespace filter_poodll\task;

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');

/**
 *
 * This is an adhoc task for copying back a file from Amazon S3
 *
 * @package   filter_poodll
 * @since      Moodle 3.1
 * @copyright  2016 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_s3_move extends \core\task\adhoc_task {

    const LOG_PLACEHOLDER_REPLACE_FAIL = 1;
    const LOG_FETCH_FILE_FAIL = 2;
    const LOG_NOT_ON_S3 = 3;
    const LOG_NOT_CONVERTED = 4;
    const LOG_PLACEHOLDER_NOT_FOUND = 5;

    //cd needs filename, filerecord and mediatype and savedatetime and convext

    public function execute() {
        //NB: seems any exceptions not thrown HERE, kill subsequent tasks
        //so wrap some function calls in try catch to prevent that happening

        $trace = new \text_progress_trace();

        //get passed in data we need to perform conversion
        $cd = $this->get_custom_data();
        $awsremote = new \filter_poodll\awsremote();

        try {
            $ret = $awsremote->fetch_s3_converted_file($cd->mediatype, $cd->infilename, $cd->outfilename, $cd->filename);

        } catch (\Exception $e) {
            //we ought to give up here. We used to try again. But if we get unrecoverable error here we will download
            //multiple times until task expiry. This causes S3 blowout. So changing to giveup=true 20200603
            $giveup = true;
            $message = 'could not fetch:' . $cd->filename . ':' . $e->getMessage();
            $this->handle_s3_error(self::LOG_FETCH_FILE_FAIL, $message, $cd, $giveup, $trace);
            return;
        }

        if ($ret === false) {
            //this indicates no "in" or "out" file, so we should just snuff this task and not repeat it
            //so we silently return
            $giveup = true;
            $message = 'the files: ' . $cd->infilename . ' | ' . $cd->outfilename . ' were not found anywhere on S3. giving up';
            $this->handle_s3_error(self::LOG_NOT_ON_S3, $message, $cd, $giveup, $trace);
            return;
        } else if ($ret === true) {
            //this indicates we had an "in" file, but no "out" file yet. try again
            $giveup = false;
            $message = 'the file ' . $cd->infilename . ' has not yet been converted.';
            $this->handle_s3_error(self::LOG_NOT_CONVERTED, $message, $cd, $giveup, $trace);
            return;
        } else {
            //this indicates the file was found and saved and the path returned
            $tempfilepath = $ret;
        }

        //fetch any file records, that currently hold the placeholder file
        //usually just one, but occasionally there will be two (1 in draft, and 1 in perm)
        $placeholder_file_recs = \filter_poodll\poodlltools::fetch_placeholder_file_record($cd->mediatype, $cd->filename);
        //do the replace, if it succeeds yay. If it fails ... try again. The user may just not have saved yet
        if (!$placeholder_file_recs) {
            $giveup = false;
            $message = 'could not find placeholder file:' . $cd->filename;
            $this->handle_s3_error(self::LOG_PLACEHOLDER_NOT_FOUND, $message, $cd, $giveup, $trace);
            return;
        }

        try {
            foreach ($placeholder_file_recs as $file_rec) {
                \filter_poodll\poodlltools::replace_placeholderfile_in_moodle($cd->filerecord, $file_rec, $tempfilepath);
                //log what we just did
                $cd->filerecord = $file_rec;
                \filter_poodll\event\adhoc_move_completed::create_from_task($cd)->trigger();
            }
        } catch (\Exception $e) {
            $giveup = true;
            $message = 'could not get replace placeholder with converted::' . $cd->filename . ':' . $e->getMessage();
            $this->handle_s3_error(self::LOG_PLACEHOLDER_REPLACE_FAIL, $message, $cd, $giveup, $trace);
            return;
        }

    }

    private function handle_s3_error($errorcode, $errorstring, $cd, $giveup, $trace) {
        //data for logging
        $contextid = $cd->filerecord->contextid;
        $userid = $cd->filerecord->userid;

        //we do not retry indefinitely
        //if we are well beyond the timestamp then we just cancel out of here.
        $nowdatetime = new \DateTime();
        $savedatetime = new \DateTime($cd->isodate);
        $diffInSeconds = $nowdatetime->getTimestamp() - $savedatetime->getTimestamp();
        if ($diffInSeconds > (60 * 60 * 6) || $giveup) {
            //we do not retry after 6 hours, we just report an error and return quietly
            $errorstring .= ' :will not retry';
            $trace->output('s3file:' . $errorstring);

            //send to debug log
            $this->send_debug_data($errorcode,
                    $errorstring, $userid, $contextid);

            //forever fail this task
            $this->do_forever_fail($errorstring, $trace);

            //if its greater than 5 mins we do a delayed retry thing
        } else if ($diffInSeconds > (MINSECS * 5)) {
            $this->do_retry($errorstring, $trace, $cd,(MINSECS * 5));

        } else {
            $errorstring .= ' :will retry';

            //send to debug log
            $this->send_debug_data($errorcode,
                    $errorstring, $userid, $contextid);

            //register a retry soon (45 seconds)
            $this->do_retry($errorstring, $trace, $cd, 45);

        }//end of if/else
    }//end of function handle_S3_error

    protected function do_retry($reason, $trace, $customdata, $delay) {
        $trace->output($reason . ": will try again next cron after $delay seconds");
        $s3_task = new \filter_poodll\task\adhoc_s3_move();
        $s3_task->set_component('filter_poodll');
        $s3_task->set_custom_data($customdata);
        //if we do not set the next run time it can extend the current cron job indef with a recurring task
        $s3_task->set_next_run_time(time()+$delay);
        // queue it
        \core\task\manager::queue_adhoc_task($s3_task);
    }

    protected function do_forever_fail($reason, $trace) {
        $trace->output($reason . ": will not retry ");
    }

    private function send_debug_data($type, $message, $userid = false, $contextid = false) {
        global $CFG;
        //only log if is on in Poodll settings
        if (!$CFG->filter_poodll_debug) {
            return;
        }

        $debugdata = new \stdClass();
        $debugdata->userid = $userid;
        $debugdata->contextid = $contextid;
        $debugdata->type = $type;
        $debugdata->source = 'adhoc_s3_move.php';
        $debugdata->message = $message;
        \filter_poodll\event\debug_log::create_from_data($debugdata)->trigger();
    }
} //end of class