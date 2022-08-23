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
 * Passaeg helper for readaloud plugin
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_readaloud;

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;

/**
 * passage helper class for mod_readaloud
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class passagehelper {
    protected $modulecontextid = 0;
    protected $attemptid = 0;
    protected $attemptdata = null;
    protected $activitydata = null;
    protected $aidata = null;

    function __construct($attemptid, $modulecontextid = 0) {
        global $DB;
        $this->attemptid = $attemptid;
        $this->modulecontextid = $modulecontextid;
        $attemptdata = $DB->get_record(constants::M_USERTABLE, array('id' => $attemptid));
        if ($attemptdata) {
            $this->attemptdata = $attemptdata;
            $this->activitydata = $DB->get_record(constants::M_TABLE, array('id' => $attemptdata->readaloudid));
            //ai data is useful, but if we don't got it we don't got it.
            if (utils::can_transcribe($this->activitydata)) {
                if ($DB->record_exists(constants::M_AITABLE, array('attemptid' => $attemptid))) {
                    $record = $DB->get_record(constants::M_AITABLE, array('attemptid' => $attemptid));
                    //we only load aidata if we reallyhave some, the presence of ai record is no longer a good check
                    //do we have a transcript ... is the real check
                    if ($record->fulltranscript != '') {
                        $this->aidata = new \stdClass();
                        $this->aidata->sessionscore = $record->sessionscore;
                        $this->aidata->sessionendword = $record->sessionendword;
                        $this->aidata->sessionerrors = $record->sessionerrors;
                        $this->aidata->errorcount = $record->errorcount;
                        $this->aidata->wpm = $record->wpm;
                        $this->aidata->accuracy = $record->accuracy;
                        $this->aidata->sessiontime = $record->sessiontime;
                        $this->aidata->sessionmatches = $record->sessionmatches;
                        $this->aidata->transcript = $record->transcript;
                    }
                }//end of if we have an AI record
            }//end of if we can transcribe
        }//end of if attempt data
    }//end of constructor

    public function get_next_ungraded_id() {
        global $DB;

        $sql = "SELECT tu.*  FROM {" . constants::M_USERTABLE .
                "} tu INNER JOIN {user} u ON tu.userid=u.id WHERE tu.readaloudid=?" .
                " ORDER BY u.lastnamephonetic,u.firstnamephonetic,u.lastname,u.firstname,u.middlename,u.alternatename,tu.id DESC";
        $records = $DB->get_records_sql($sql, array($this->attemptdata->readaloudid));
        $past = false;
        $nextid = false;
        foreach ($records as $data) {
            if ($data->userid == $this->attemptdata->userid) {
                $past = true;
            } else {
                if ($past && $data->sessionscore == 0) {
                    $nextid = $data->id;
                    break;
                }
            }//end of id $data userid
        }//end of for loop
        return $nextid;
    }

    public function update($formdata) {
        global $DB;
        $updatedattempt = new \stdClass();
        $updatedattempt->id = $this->attemptid;
        $updatedattempt->sessiontime = $formdata->sessiontime;
        $updatedattempt->wpm = $formdata->wpm;
        $updatedattempt->accuracy = $formdata->accuracy;
        $updatedattempt->sessionscore = $formdata->sessionscore;
        $updatedattempt->sessionerrors = $formdata->sessionerrors;
        $updatedattempt->sessionendword = $formdata->sessionendword;

        //its a little redundancy but we add error count here to make machine eval. error estimation easier
        $errorcount = utils::count_sessionerrors($formdata->sessionerrors);
        $updatedattempt->errorcount = $errorcount;

        $ret = $DB->update_record(constants::M_USERTABLE, $updatedattempt);
        return $ret;
    }

    public function attemptdetails($property) {
        global $DB;
        switch ($property) {
            case 'userfullname':
                $user = $DB->get_record('user', array('id' => $this->attemptdata->userid));
                $ret = fullname($user);
                break;
            case 'passage':
                    $ret = $this->activitydata->passage;
                break;
            case 'audiourl':
                //we need to consider legacy client side URLs and cloud hosted ones
                $ret = utils::make_audio_URL($this->attemptdata->filename, $this->modulecontextid, constants::M_COMPONENT,
                        constants::M_FILEAREA_SUBMISSIONS,
                        $this->attemptdata->id);

                break;
            case 'somedetails':
                $ret = $this->attemptdata->id . ' ' . $this->activitydata->passage;
                break;

            case 'passagesegments':
                $ret = $this->activitydata->passagesegments;
                break;

            default:
                $ret = $this->attemptdata->{$property};
        }
        return $ret;
    }

    //because we may or ay not use AI data we provide a way for the correct data to be used here
    public function formdetails($property, $force_aimode) {
        $loading_aidata = ($force_aimode || $this->aidata && $this->attemptdata->sessiontime < 1);
        switch ($property) {
            case 'sessiontime':
                if ($loading_aidata) {
                    if($this->aidata->sessiontime>0) {
                        return $this->aidata->sessiontime;
                    }else{
                        // if we have a time limit and not allowing early exit, we use the time limit
                        if ($this->activitydata->timelimit > 0 && !$this->activitydata->allowearlyexit) {
                            $sessiontime = $this->activitydata->timelimit;
                            //else we get it from transcript (it will be stored as aidata sessiontime for next time)
                        } else {
                            //we get the end_time attribute of the final recognised word in the fulltranscript
                            $sessiontime = utils::fetch_duration_from_transcript($this->aidata->fulltranscript);

                            if ($sessiontime < 1) {
                                //this is a guess now, We just don't know it. And should not really get here.
                                $sessiontime = 60;
                            }
                        }
                        return $sessiontime;
                    }

                } else {
                    return $this->attemptdetails('sessiontime');
                }
                break;
            case 'sessionscore':
                if ($loading_aidata) {
                    return $this->aidata->sessionscore;
                } else {
                    return $this->attemptdetails('sessionscore');
                }
                break;
            case 'sessionendword':
                if ($loading_aidata) {
                    return $this->aidata->sessionendword;
                } else {
                    return $this->attemptdetails('sessionendword');
                }
                break;
            case 'sessionerrors':
                if ($loading_aidata) {
                    return $this->aidata->sessionendword;
                } else {
                    return $this->attemptdetails('sessionendword');
                }
                break;
            //should not get here really
            default:
                return $this->attemptdetails($property);

        }
    }

    public function prepare_javascript($reviewmode = false, $force_aimode = false, $readonly = false) {
        global $PAGE;

        //if we are editing and no human has saved, we load AI data to begin with.
        //if we only want ai data, during review screen, again we load ai data
        $loading_aidata = ($force_aimode || $this->aidata && $this->attemptdata->sessiontime < 1);

        //here we set up any info we need to pass into javascript
        $gradingopts = Array();
        $gradingopts['reviewmode'] = $reviewmode;
        $gradingopts['enabletts'] = get_config(constants::M_COMPONENT, 'enabletts');
        $gradingopts['allowearlyexit'] = $this->activitydata->allowearlyexit ? true : false;
        $gradingopts['timelimit'] = $this->activitydata->timelimit;
        $gradingopts['ttslanguage'] = $this->activitydata->ttslanguage;
        $gradingopts['activityid'] = $this->activitydata->id;
        $gradingopts['targetwpm'] = $this->activitydata->targetwpm;
        $gradingopts['sesskey'] = sesskey();
        $gradingopts['attemptid'] = $this->attemptdata->id;
        $gradingopts['dontgrade'] = $this->attemptdata->dontgrade;
        $gradingopts['readonly'] = $readonly;
        $gradingopts['sessionscoremethod'] = $this->activitydata->sessionscoremethod;
        if ($loading_aidata) {
            $gradingopts['sessiontime'] = $this->aidata->sessiontime;
            $gradingopts['sessionerrors'] = $this->aidata->sessionerrors;
            $gradingopts['sessionendword'] = $this->aidata->sessionendword;
            $gradingopts['sessionmatches'] = $this->aidata->sessionmatches;
            $gradingopts['wpm'] = $this->aidata->wpm;
            $gradingopts['accuracy'] = $this->aidata->accuracy;
            $gradingopts['sessionscore'] = $this->aidata->sessionscore;
        } else {
            $gradingopts['sessiontime'] = $this->attemptdata->sessiontime;
            $gradingopts['sessionerrors'] = $this->attemptdata->sessionerrors;
            $gradingopts['sessionendword'] = $this->attemptdata->sessionendword;
            $gradingopts['wpm'] = $this->attemptdata->wpm;
            $gradingopts['accuracy'] = $this->attemptdata->accuracy;
            $gradingopts['sessionscore'] = $this->attemptdata->sessionscore;
        }
        //even in human mode, spot checking is handy so we load ai data for that
        if ($this->aidata) {
            $gradingopts['aidata'] = $this->aidata;
            $gradingopts['sessionmatches'] = $this->aidata->sessionmatches;
        } else {
            $gradingopts['aidata'] = false;
            $gradingopts['sessionmatches'] = false;
        }
        $gradingopts['opts_id'] = 'mod_readaloud_gradenowopts';

        $jsonstring = json_encode($gradingopts);
        $opts_html =
                \html_writer::tag('input', '', array('id' => $gradingopts['opts_id'], 'type' => 'hidden', 'value' => $jsonstring));
        $PAGE->requires->js_call_amd("mod_readaloud/gradenowhelper", 'init', array(array('id' => $gradingopts['opts_id'])));
        $PAGE->requires->strings_for_js(array(
                'spotcheckbutton',
                'gradingbutton',
                'transcript',
                'quickgrade',
                'ok',
                'ng'
        ),
                'mod_readaloud');

        //these need to be returned and echo'ed to the page
        return $opts_html;

    }
}
