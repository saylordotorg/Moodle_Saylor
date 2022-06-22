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
 * A mod_solo adhoc task
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_solo\task;

defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;
use \mod_solo\utils;


/**
 * A mod_solo adhoc task to fetch back transcriptions from Amazon S3
 *
 * @package    mod_solo
 * @since      Moodle 3.7
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class solo_s3_adhoc extends \core\task\adhoc_task {

   	 /**
     *  Run the tasks
     */
	 public function execute(){
	     global $DB;
		$trace = new \text_progress_trace();

		//CD should contain activityid / attemptid / #modulecontextid / #cmid
         $cd =  $this->get_custom_data();
		//$trace->output($cd->somedata)

         $activity = $DB->get_record(constants::M_TABLE,array('id'=>$cd->activityid));
         if(!\mod_solo\utils::can_transcribe($activity)){
             $this->do_forever_fail('This activity does not support transcription',$trace);
             return;
         }

         $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE, array('id'=>$cd->attemptid));
         if($attempt){

             if(!$attempt->filename){
                 $this->do_retry('Audio file appears to not be ready yet',$trace,$cd);
                 return;
             }
             if($attempt->transcript){
                 //woa!! Its already been got. This can happen if user goes to selfreview page which will try and do the
                 //retrieve if transcripts are not back. It can also happen if streaming transcription is going
                 $trace->output("Transcript has already been fetched. Nothing to do");
                 return;
             }

             //do all the processing (grades, diffs, etc) if needed and return the attempt
             $attempt =  utils::process_attempt($activity,$attempt,$cd->modulecontextid,$cd->cmid,$trace);
             if(!$attempt){
                 $this->do_retry('Transcripts are not ready yet',$trace,$cd);
                 return;
             };


         }else{
             $this->do_forever_fail('This attempt could not be found: ' . $cd->attemptid,$trace);
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
        $s3_task = new \mod_solo\task\solo_s3_adhoc();
        $s3_task->set_component('mod_solo');
        $s3_task->set_custom_data($customdata);
        //if we do not set the next run time it can extend the current cron job indef with a recurring task
        $s3_task->set_next_run_time(time()+$delay);
        // queue it
        \core\task\manager::queue_adhoc_task($s3_task);
    }

    protected function do_forever_fail($reason,$trace){
        $trace->output($reason . ": will not retry ");
	}

}

