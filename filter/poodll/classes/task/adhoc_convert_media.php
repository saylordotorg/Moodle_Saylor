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
 * This is an adhoc task for converting media with FFMPEG
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_convert_media extends \core\task\adhoc_task {

    const LOG_DID_NOT_CONVERT = 1;
    const LOG_MISSING_FILENAME = 2;
    const LOG_NO_FILE_FOUND_IN_DB = 3;
    const LOG_STORED_FILE_PROBLEM = 4;
    const LOG_SPLASHFILE_MAKE_FAIL = 5;
    const LOG_UNABLE_TO_CONVERT = 6;

    public function execute() {
        //NB: seems any exceptions not thrown HERE, kill subsequent tasks
        //so wrap some function calls in try catch to prevent that happening

        global $DB, $CFG;
        //get passed in data we need to perform conversion
        $cd = $this->get_custom_data();

        //find the file in the files database
        $fs = get_file_storage();
        switch ($cd->convext) {
            case '.mp3':
                $mediatype = 'audio';
                break;
            case '.mp4':
                $mediatype = 'video';
                break;
            default:
                $mediatype = 'audio';

        }
        if (!property_exists($cd, 'filename')) {
            $this->handle_error(self::LOG_MISSING_FILENAME, 'missing filename in custom data:', $cd);
            return;
        }

        //fetch any file records, that currently hold the placeholder file
        //usually just one, but occasionally there will be two (1 in draft, and 1 in perm)
        $placeholder_file_recs = \filter_poodll\poodlltools::fetch_placeholder_file_record($mediatype, $cd->filename);
        //do the replace, if it succeeds yay. If it fails ... try again. The user may just not have saved yet
        if (!$placeholder_file_recs) {
            $nofilefoundmessage = 'could not find ' . $cd->filename . ' in the DB. Possibly user has not saved yet';
            $this->handle_error(self::LOG_NO_FILE_FOUND_IN_DB, $nofilefoundmessage, $cd);
            throw new \file_exception('storedfileproblem', $nofilefoundmessage);
            return;
        }

        //do the conversion
        $throwawayfilename = 'temp_' . $cd->filename;
        try {
            $convertedfile = \filter_poodll\poodlltools::convert_with_ffmpeg($cd->filerecord,
                    $cd->originalfilename,
                    $cd->convfilenamebase,
                    $cd->convext,
                    $throwawayfilename);
        } catch (Exception $e) {
            $this->handle_error(self::LOG_DID_NOT_CONVERT, 'could not get convert:' . $cd->filename . ':' . $e->getMessage(), $cd);
            return;
        }

        try {
            //loop through all the placeholder files and replace them
            foreach ($placeholder_file_recs as $file_rec) {

                //fetch the placeholder file store file
                $placefile = $fs->get_file_by_id($file_rec->id);
                if (!$placefile) {
                    $this->handle_error(self::LOG_STORED_FILE_PROBLEM, 'something wrong with sf:' . $cd->filename, $cd);
                    return;
                }

                //replace the placeholder(original) file with the converted one
                if ($convertedfile) {
                    $placefile->replace_file_with($convertedfile);
                } else {
                    $this->handle_error(self::LOG_UNABLE_TO_CONVERT, 'unable to convert ' . $cd->originalfilename, $cd);
                    return;
                }

                ///log what we just did
                //if we got here then the task was completed successfully
                $cd->outfilename = $cd->filename;
                $cd->infilename = $cd->originalfilename;
                $cd->filerecord = $file_rec;
                \filter_poodll\event\adhoc_convert_completed::create_from_task($cd)->trigger();
            }

        } catch (Exception $e) {
            $this->handle_error(self::LOG_UNABLE_TO_CONVERT, 'unable to convert ' . $cd->originalfilename, $cd);
            return;
            return;
        }

    }

    private function handle_error($errorcode, $errorstring, $cd) {
        //throwing errors will see the process retrying. 
        //however there is little point in retrying.
        $throwerrors = false;

        //data for logging
        $contextid = $cd->filerecord->contextid;
        $userid = $cd->filerecord->userid;

        if ($throwerrors) {
            //log error
            $this->send_debug_data($errorcode,
                    $errorstring, $userid, $contextid);

            throw new \file_exception('storedfileproblem', $errorstring);
        } else {
            mtrace('storedfileproblem:' . $errorstring);
            mtrace(print_r($cd, true));

            $this->send_debug_data($errorcode,
                    $errorstring, $userid, $contextid);
        }
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
        $debugdata->source = 'adhoc_convert_media.php';
        $debugdata->message = $message;
        \filter_poodll\event\debug_log::create_from_data($debugdata)->trigger();
    }
} 