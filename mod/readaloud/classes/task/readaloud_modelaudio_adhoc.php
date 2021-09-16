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

/**
 * A mod_readaloud modelaudio adhoc task
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_readaloud\task;

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;
use \mod_readaloud\utils;
use \mod_readaloud\diff;

/**
 * A mod_readaloud adhoc task to fetch back transcriptions from Amazon S3 for model audio
 *
 * @package    mod_readaloud
 * @since      Moodle 2.7
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class readaloud_modelaudio_adhoc extends \core\task\adhoc_task {

    /**
     *  Run the tasks
     */
    public function execute() {
        global $DB;
        $trace = new \text_progress_trace();

        //CD should contain activityid / attemptid and modulecontextid
        $cd = $this->get_custom_data();
        //$trace->output($cd->somedata)

        $activity = $DB->get_record(constants::M_TABLE, array('id' => $cd->activityid));
        if (!$activity) {
                $this->do_forever_fail('This activity has been deleted or can not be fetched', $trace);
                return;
        }

        if (!\mod_readaloud\utils::can_transcribe($activity)) {
            $this->do_forever_fail('This activity does not support transcription', $trace);
            return;
        }

        $transcript = false;
        $fulltranscript = false;
        if ($cd->filename && strpos($cd->filename, 'https') === 0) {
            $transcript = utils::curl_fetch($cd->filename . '.txt');
            if (strpos($transcript, "<Error><Code>AccessDenied</Code>") > 0) {
                $this->do_retry('Model Audio Transcript appears to not be ready yet for ' . $cd->activityid, $trace, $cd);
                return;
            }
            //we should actually just determine if its fast or normal transcoding here
            $fulltranscript = utils::curl_fetch($cd->filename . '.json');
            if (!utils::is_json($fulltranscript)) {
                $fulltranscript = utils::curl_fetch($cd->filename . '.gjson');
            }
        }

        if (!empty($transcript) && !utils::is_json($fulltranscript)) {
            $this->do_forever_fail('transcript fetched but no full transcript', $trace);
            return;
        }

        if ($fulltranscript) {

            //figure out matches as this is what we need the transcript for
            $debug=false;
            list($ma_matches,$ma_endword,$ma_errors,$errorcount,$debugsequences) =
                    utils::fetch_diff($activity->passage,
                            $activity->alternatives,
                            $transcript,
                            $fulltranscript,
                            $activity->ttslanguage,
                            $activity->phonetic,
                            $debug);


            $transcript_update  =new \stdClass();
            $transcript_update->id = $activity->id;
            $transcript_update->modelaudiotrans = diff::cleanText($transcript);
            $transcript_update->modelaudiofulltrans = $fulltranscript;
            $transcript_update->modelaudiomatches = json_encode($ma_matches);
            $breaks = utils::guess_modelaudio_breaks($activity->passage,$ma_matches,$activity->ttslanguage);
            $transcript_update->modelaudiobreaks = json_encode($breaks);
            $success = $DB->update_record(constants::M_TABLE, $transcript_update);
            return;

        } else {
            $this->do_forever_fail('Unable to create transcript for some reason for ' . $cd->activityid, $trace);
            return;
        }
    }

    protected function do_retry($reason, $trace, $customdata) {
        if($customdata->taskcreationtime + (HOURSECS * 24) < time()){
            //after 24 hours we give up
            $trace->output($reason . ": Its been more than 24 hours. Giving up on this transcript.");
            return;

        }elseif ($customdata->taskcreationtime + (MINSECS * 15) < time()) {
            //15 minute delay
            $delay = (MINSECS * 15);
        }else{
            //30 second delay
            $delay = 30;
        }
        $trace->output($reason . ": will try again next cron after $delay seconds");
        $modelaudio_task = new \mod_readaloud\task\readaloud_modelaudio_adhoc();
        $modelaudio_task->set_component('mod_readaloud');
        $modelaudio_task->set_custom_data($customdata);
        //if we do not set the next run time it can extend the current cron job indef with a recurring task
        $modelaudio_task->set_next_run_time(time()+$delay);
        // queue it
        \core\task\manager::queue_adhoc_task($modelaudio_task);
    }

    protected function do_forever_fail($reason, $trace) {
        $trace->output($reason . ": will not retry ");
    }

}

