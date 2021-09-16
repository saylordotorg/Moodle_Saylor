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
 * Library of interface functions and constants for module readaloud
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the readaloud specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;
use \mod_readaloud\utils;

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function readaloud_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the readaloud.
 *
 * @param $mform form passed by reference
 */
function readaloud_reset_course_form_definition(&$mform) {
    $mform->addElement('header', constants::M_MODNAME . 'header', get_string('modulenameplural', constants::M_COMPONENT));
    $mform->addElement('advcheckbox', 'reset_' . constants::M_MODNAME, get_string('deletealluserdata', constants::M_COMPONENT));
}

/**
 * Course reset form defaults.
 *
 * @param object $course
 * @return array
 */
function readaloud_reset_course_form_defaults($course) {
    return array('reset_' . constants::M_MODNAME => 1);
}

function readaloud_editor_with_files_options($context) {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $context, 'subdirs' => true);
}

function readaloud_editor_no_files_options($context) {
    return array('maxfiles' => 0, 'noclean' => true, 'context' => $context);
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function readaloud_reset_gradebook($courseid, $type = '') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {" . constants::M_TABLE . "} l, {course_modules} cm, {modules} m
             WHERE m.name='" . constants::M_MODNAME . "' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array("course" => $courseid);
    if ($moduleinstances = $DB->get_records_sql($sql, $params)) {
        foreach ($moduleinstances as $moduleinstance) {
            readaloud_grade_item_update($moduleinstance, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * readaloud attempts for course $data->courseid.
 *
 * @global stdClass
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function readaloud_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', constants::M_COMPONENT);
    $status = array();

    if (!empty($data->{'reset_' . constants::M_MODNAME})) {
        $sql = "SELECT l.id
                         FROM {" . constants::M_TABLE . "} l
                        WHERE l.course=:course";

        $params = array("course" => $data->courseid);
        $DB->delete_records_select(constants::M_USERTABLE, constants::M_MODNAME . "id IN ($sql)", $params);
        //delete AI grades
        $DB->delete_records_select(constants::M_AITABLE, constants::M_MODNAME . "id IN ($sql)", $params);

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            readaloud_reset_gradebook($data->courseid);
        }

        $status[] = array('component' => $componentstr, 'item' => get_string('deletealluserdata', constants::M_COMPONENT),
                'error' => false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates(constants::M_MODNAME, array('available', 'deadline'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}

/**
 * Create grade item for activity instance
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $moduleinstance object with extra cmidnumber
 * @param array|object $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function readaloud_grade_item_update($moduleinstance, $grades = null) {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir . '/gradelib.php');
    }

    if (array_key_exists('cmidnumber', (array)$moduleinstance)) { //it may not be always present
        $params = array('itemname' => $moduleinstance->name, 'idnumber' => $moduleinstance->cmidnumber);
    } else {
        $params = array('itemname' => $moduleinstance->name);
    }

    //if we are machine grading we need to fetch the error estimate
    //hard coded to no error estimate since we turned off the feature
    if (false && $moduleinstance->machgrademethod = constants::MACHINEGRADE_HYBRID &&
                    utils::can_transcribe($moduleinstance) && $moduleinstance->accadjustmethod != constants::ACCMETHOD_NONE) {
        $errorestimate = \mod_readaloud\utils::estimate_errors($moduleinstance->id);
    } else {
        $errorestimate = 0;
    }

    if ($moduleinstance->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $moduleinstance->grade;
        $params['grademin'] = 0;
    } else if ($moduleinstance->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$moduleinstance->grade;

        // Make sure current grade fetched correctly from $grades
        $currentgrade = null;
        if (!empty($grades)) {
            if (is_array($grades)) {
                $currentgrade = reset($grades);
            } else {
                $currentgrade = $grades;
            }
        }

        // When converting a score to a scale, use scale's grade maximum to calculate it.
        if (!empty($currentgrade) && $currentgrade->rawgrade !== null) {
            $grade = grade_get_grades($moduleinstance->course, 'mod', constants::M_MODNAME, $moduleinstance->id,
                    $currentgrade->userid);
            $params['grademax'] = reset($grade->items)->grademax;
        }
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    } else if (!empty($grades)) {
        // Need to calculate raw grade (Note: $grades has many forms)
        if (is_object($grades)) {
            $grades = array($grades->userid => $grades);
        } else if (array_key_exists('userid', $grades)) {
            $grades = array($grades['userid'] => $grades);
        }
        foreach ($grades as $key => $grade) {
            if (!is_array($grade)) {
                $grades[$key] = $grade = (array) $grade;
            }
            //check raw grade isnt null otherwise we insert a grade of 0
            if ($grade['rawgrade'] !== null) {
                $grades[$key]['rawgrade'] = (($grade['rawgrade'] + $errorestimate) * $params['grademax'] / 100);
            } else {
                //setting rawgrade to null just in case user is deleting a grade
                $grades[$key]['rawgrade'] = null;
            }
        }
    }

    return grade_update('mod/' . constants::M_MODNAME, $moduleinstance->course, 'mod', constants::M_MODNAME, $moduleinstance->id, 0,
            $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moduleinstance
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function readaloud_update_grades($moduleinstance, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($moduleinstance->grade == 0) {
        readaloud_grade_item_update($moduleinstance);

    } else if ($grades = readaloud_get_user_grades($moduleinstance, $userid)) {
        readaloud_grade_item_update($moduleinstance, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        readaloud_grade_item_update($moduleinstance, $grade);

    } else {
        readaloud_grade_item_update($moduleinstance);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $moduleinstance
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function readaloud_get_user_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;

    $params = array("moduleid" => $moduleinstance->id);
    $cantranscribe = utils::can_transcribe($moduleinstance);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $user = "AND u.id = :userid";
    } else {
        $user = "";

    }

    switch ($moduleinstance->gradeoptions) {
        case constants::M_GRADEHIGHEST:

            //aigrades sql
            $ai_sql = "SELECT u.id, u.id AS userid,MAX(ai.sessionscore) AS rawgrade
                  FROM {user} u, {" . constants::M_AITABLE . "} ai INNER JOIN {" . constants::M_USERTABLE . "} attempt ON ai.attemptid = attempt.id
                 WHERE  u.id = attempt.userid AND ai.readaloudid = :moduleid
                       $user
              GROUP BY u.id";

            $human_sql = "SELECT u.id, u.id AS userid,MAX(a.sessionscore) AS rawgrade
                  FROM {user} u,  {" . constants::M_USERTABLE . "} a 
                 WHERE  u.id = a.userid AND a.readaloudid = :moduleid
                       $user
              GROUP BY u.id";

            //from which table do we get these grades..
            if ($moduleinstance->machgrademethod == constants::MACHINEGRADE_HYBRID && $cantranscribe) {
                $airesults = $DB->get_records_sql($ai_sql, $params);
                $results = $DB->get_records_sql($human_sql, $params);
                //here we loop through and choose the higher of the ai or human grades
                foreach ($results as $result) {
                    foreach ($airesults as $airesult) {
                        if ($airesult->id == $result->id) {
                            if ($airesult->rawgrade > $result->rawgrade) {
                                $result->rawgrade = $airesult->rawgrade;
                            }
                            break;
                        }
                    }
                }
            } elseif($moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY && $cantranscribe){
                $results = $DB->get_records_sql($ai_sql, $params);

            } else {
                $results = $DB->get_records_sql($human_sql, $params);
            }

            break;

        case constants::M_GRADELATEST:
        default;

            //aigrades sql
            $ai_sql = "SELECT u.id, u.id AS userid, MAX(ai.sessionscore) AS rawgrade
                      FROM {user} u, {" . constants::M_AITABLE . "} ai INNER JOIN {" . constants::M_USERTABLE . "} attempt ON ai.attemptid = attempt.id
                     WHERE attempt.id= (SELECT max(id) FROM {" . constants::M_USERTABLE . "} iattempt WHERE iattempt.userid=u.id AND iattempt.readaloudid = ai.readaloudid)  AND u.id = attempt.userid AND ai.readaloudid = :moduleid
                           $user
                  GROUP BY u.id, ai.sessionscore";

            //human_sql
            $human_sql = "SELECT u.id, u.id AS userid, MAX(a.sessionscore) AS rawgrade
                          FROM {user} u, {" . constants::M_USERTABLE . "} a
                         WHERE a.id= (SELECT max(id) FROM {" . constants::M_USERTABLE . "} ia WHERE ia.userid=u.id AND ia.readaloudid = a.readaloudid)  AND u.id = a.userid AND a.readaloudid = :moduleid
                               $user
                      GROUP BY u.id";

            //hybrid sql
            $hybrid_sql = "SELECT u.id, MAX(attempt.sessiontime) as sessiontime, MAX(attempt.sessionscore) as humangrade, u.id AS userid, MAX(ai.sessionscore) AS aigrade
                      FROM {user} u, {" . constants::M_AITABLE . "} ai INNER JOIN {" . constants::M_USERTABLE . "} attempt ON ai.attemptid = attempt.id
                     WHERE attempt.id= (SELECT max(id) FROM {" . constants::M_USERTABLE . "} iattempt WHERE iattempt.userid=u.id AND iattempt.readaloudid = ai.readaloudid)  AND u.id = attempt.userid AND ai.readaloudid = :moduleid
                           $user
                  GROUP BY u.id";

            //from which table do we get these grades..
            if ($moduleinstance->machgrademethod == constants::MACHINEGRADE_HYBRID && $cantranscribe) {
                $results = $DB->get_records_sql($hybrid_sql, $params);
                //sessiontime is our indicator that a human grade has been saved.
                foreach ($results as $result) {
                    if ($result->sessiontime > 0) {
                        $result->rawgrade = $result->humangrade;
                    } else {
                        $result->rawgrade = $result->aigrade;
                    }
                }

            } elseif($moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY && $cantranscribe){
                $results = $DB->get_records_sql($ai_sql, $params);

            } else {
                $results = $DB->get_records_sql($human_sql, $params);
            }
    }

    //return results
    return $results;
}

function readaloud_get_completion_state($course, $cm, $userid, $type) {
    return readaloud_is_complete($course, $cm, $userid, $type);
}

//this is called internally only
function readaloud_is_complete($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get module object
    if (!($moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance)))) {
        throw new Exception("Can't find module with cmid: {$cm->instance}");
    }

    if (!($moduleinstance->mingrade > 0)) {
        return $type;
    }

    $cantranscribe = utils::can_transcribe($moduleinstance);
    $params = array('userid' => $userid, 'moduleid' => $moduleinstance->id);
    if ($moduleinstance->machgrademethod == constants::MACHINEGRADE_HYBRID && $cantranscribe) {
        //choose greater or  ai or human score
        $sql = "SELECT  GREATEST(MAX(ai.sessionscore), MAX(a.sessionscore)) AS grade
                      FROM {" . constants::M_AITABLE . "} ai
                      INNER JOIN {" . constants::M_USERTABLE . "} a ON a.id = ai.attemptid
                     WHERE a.userid = :userid AND a." . constants::M_MODNAME . "id = :moduleid";

    } elseif($moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY && $cantranscribe) {

        //choose AI grades only
        $sql = "SELECT  MAX(ai.sessionscore) AS grade
                      FROM {" . constants::M_AITABLE . "} ai
                      INNER JOIN {" . constants::M_USERTABLE . "} a ON a.id = ai.attemptid
                     WHERE a.userid = :userid AND a." . constants::M_MODNAME . "id = :moduleid";

    } else {
        //choose human grades only
        $sql = "SELECT  MAX( sessionscore  ) AS grade
                      FROM {" . constants::M_USERTABLE . "}
                     WHERE userid = :userid AND " . constants::M_MODNAME . "id = :moduleid";
    }

    $result = $DB->get_field_sql($sql, $params);
    if ($result === false) {
        return false;
    }

    //check completion reqs against satisfied conditions
    $success = $result >= $moduleinstance->mingrade;

    //return our success flag
    return $success;
}

/**
 * A task called from scheduled or adhoc
 *
 * @param progress_trace trace object
 *
 */
function readaloud_dotask(progress_trace $trace) {
    $trace->output('executing dotask');
}

function readaloud_get_editornames() {
    return array('passage', 'welcome', 'feedback');
}

function readaloud_process_editors(stdClass $readaloud, mod_readaloud_mod_form $mform = null) {
    global $DB;
    $cmid = $readaloud->coursemodule;
    $context = context_module::instance($cmid);
    $editors = readaloud_get_editornames();
    $itemid = 0;
    $edoptions = readaloud_editor_no_files_options($context);
    foreach ($editors as $editor) {
        $readaloud = file_postupdate_standard_editor($readaloud, $editor, $edoptions, $context, constants::M_COMPONENT, $editor,
            $itemid);
    }
    return $readaloud;
}

/**
 * Saves a new instance of the readaloud into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $readaloud An object from the form in mod_form.php
 * @param mod_readaloud_mod_form $mform
 * @return int The id of the newly inserted readaloud record
 */
function readaloud_add_instance(stdClass $readaloud, mod_readaloud_mod_form $mform = null) {
    global $DB;

    $readaloud->timecreated = time();
    $readaloud = readaloud_process_editors($readaloud, $mform);

    //do phonetics
    [$thephonetic,$thepassagesegments] = utils::update_create_phonetic_segments($readaloud,false);
    $readaloud->phonetic = $thephonetic;
    $readaloud->passagesegments = $thepassagesegments;

    //we want to process the hashcode and lang model if it makes sense
    if(utils::needs_lang_model($readaloud)){
        $passagehash = utils::fetch_passagehash($readaloud);
        if($passagehash){
            $readaloud->passagehash =$passagehash;
            //build a lang model
            $ret = utils::fetch_lang_model($readaloud->passage, $readaloud->ttslanguage, $readaloud->region);
            if ($ret && isset($ret->success) && $ret->success){
                $readaloud->passagehash =$readaloud->region . '|'  .$passagehash;
            }else{
                $readaloud->passagehash =null;
            }
        }else{
            $readaloud->passagehash =null;
        }
    }

    //we want to create a polly record and speechmarks, if we have a passage
    if(!empty($readaloud->passage)) {
        $config = get_config(constants::M_COMPONENT);
        $token = utils::fetch_token($config->apiuser,$config->apisecret);
        if($token) {
            $slowpassage = utils::fetch_speech_ssml($readaloud->passage, $readaloud->ttsspeed);
            $speechmarks = utils::fetch_polly_speechmarks($token, $readaloud->region,
                    $slowpassage, 'ssml', $readaloud->ttsvoice);
            if($speechmarks) {
                $matches = utils::speechmarks_to_matches($readaloud->passagesegments,$speechmarks,$readaloud->ttslanguage);
                $breaks = utils::guess_modelaudio_breaks($readaloud->passagesegments, $matches,$readaloud->ttslanguage);
                $readaloud->modelaudiomatches = json_encode($matches);
                $readaloud->modelaudiobreaks = json_encode($breaks);
            }//end of if speechmarks
        }//end of if token
    }


    $readaloud->id = $DB->insert_record(constants::M_TABLE, $readaloud);

    readaloud_grade_item_update($readaloud);
    if (class_exists('\core_completion\api')) {
        $completionexpected = (empty($readaloud->completionexpected) ? null : $readaloud->completionexpected);
        \core_completion\api::update_completion_date_event($readaloud->coursemodule, 'readaloud', $readaloud->id,
                $completionexpected);
    }

    return $readaloud->id;
}

/**
 * Updates an instance of the readaloud in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $readaloud An object from the form in mod_form.php
 * @param mod_readaloud_mod_form $mform
 * @return boolean Success/Fail
 */
function readaloud_update_instance(stdClass $readaloud, mod_readaloud_mod_form $mform = null) {
    global $DB;

    $params = array('id' => $readaloud->instance);
    $oldgradefield = $DB->get_field(constants::M_TABLE, 'grade', $params);

    $readaloud->timemodified = time();
    $readaloud->id = $readaloud->instance;
    $readaloud = readaloud_process_editors($readaloud, $mform);

    //we want to process the hashcode and lang model if it makes sense
    $oldrecord = $DB->get_record(constants::M_TABLE,array('id'=>$readaloud->id));

    //update the phonetic if it has changed
    [$thephonetic,$thepassagesegments] = utils::update_create_phonetic_segments($readaloud,$oldrecord);
    $readaloud->phonetic = $thephonetic;
    $readaloud->passagesegments = $thepassagesegments;

    $readaloud->passagehash = $oldrecord->passagehash;
    $newpassagehash = utils::fetch_passagehash($readaloud);
    if($newpassagehash){
        //check if it has changed, if not do not waste time processing it
        if($oldrecord->passagehash!= ($readaloud->region . '|' . $newpassagehash)) {
            //build a lang model
            if(utils::needs_lang_model($readaloud)) {
                $ret = utils::fetch_lang_model($readaloud->passage, $readaloud->ttslanguage, $readaloud->region);
                if ($ret && isset($ret->success) && $ret->success)  {
                    $readaloud->passagehash = $readaloud->region . '|' . $newpassagehash;
                }//end of if successful
            }//end of if lang model
        }//end of if passage hash chaned
    }//end of if newpassagehash



    //we want to create a polly record and speechmarks, if (!human_modelaudio && passage) && (passage change || voice change || speed change)
    $needspeechmarks =false;
    if(empty($readaloud->modelaudiourl) && !empty($readaloud->passage) && $newpassagehash){
        //if it has changed OR voice has changed we need to do some work
        if($oldrecord->passagehash!= ($readaloud->region . '|' . $newpassagehash) ||
            $oldrecord->ttsvoice != $readaloud->ttsvoice ||
                $oldrecord->ttsspeed != $readaloud->ttsspeed
        ) {
            $needspeechmarks = true;
        }
    }

    //We create the marked up speechmarks. We do not save the modelurl, we only save that in the case of human model audio
    if($needspeechmarks) {
        $config = get_config(constants::M_COMPONENT);
        $token = utils::fetch_token($config->apiuser,$config->apisecret);
        if($token) {
            $slowpassage = utils::fetch_speech_ssml($readaloud->passage, $readaloud->ttsspeed);
            $speechmarks = utils::fetch_polly_speechmarks($token, $readaloud->region,
                    $slowpassage, 'ssml', $readaloud->ttsvoice);
            if($speechmarks) {
                $matches = utils::speechmarks_to_matches($readaloud->passagesegments,$speechmarks,$readaloud->ttslanguage);
                if(false && !empty($oldrecord->modelaudiobreaks) && strlen($oldrecord->modelaudiobreaks)>5){
                    //we no longer sync. It just is not the correct behaviour if the passage/speech has changed
                    $breaks = utils::sync_modelaudio_breaks(json_decode($oldrecord->modelaudiobreaks,true),$matches);
                }else {
                    $breaks = utils::guess_modelaudio_breaks($readaloud->passagesegments, $matches,$readaloud->ttslanguage);
                }
                $readaloud->modelaudiomatches = json_encode($matches);
                $readaloud->modelaudiobreaks = json_encode($breaks);
            } //end of if speechmarks
        } //end of if token
    }

    $success = $DB->update_record(constants::M_TABLE, $readaloud);

    readaloud_grade_item_update($readaloud);
    if (class_exists('\core_completion\api')) {
        $completionexpected = (empty($readaloud->completionexpected) ? null : $readaloud->completionexpected);
        \core_completion\api::update_completion_date_event($readaloud->coursemodule, 'readaloud', $readaloud->id,
                $completionexpected);
    }

    $update_grades = ($readaloud->grade === $oldgradefield ? false : true);
    if ($update_grades) {
        readaloud_update_grades($readaloud, 0, false);
    }

    return $success;
}

/**
 * Removes an instance of the readaloud from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function readaloud_delete_instance($id) {
    global $DB;

    if (!$readaloud = $DB->get_record(constants::M_TABLE, array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records(constants::M_TABLE, array('id' => $readaloud->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function readaloud_user_outline($course, $user, $mod, $readaloud) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $readaloud the module instance record
 * @return void, is supposed to echp directly
 */
function readaloud_user_complete($course, $user, $mod, $readaloud) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in readaloud activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function readaloud_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link readaloud_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function readaloud_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see readaloud_get_recent_mod_activity()}
 *
 * @return void
 */
function readaloud_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function readaloud_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function readaloud_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of readaloud?
 *
 * This function returns if a scale is being used by one readaloud
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $readaloudid ID of an instance of this module
 * @return bool true if the scale is used by the given readaloud instance
 */
function readaloud_scale_used($readaloudid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('id' => $readaloudid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of readaloud.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any readaloud instance
 */
function readaloud_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function readaloud_get_file_areas($course, $cm, $context) {
    return readaloud_get_editornames();
}

/**
 * File browsing support for readaloud file areas
 *
 * @package mod_readaloud
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function readaloud_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the readaloud file areas
 *
 * @package mod_readaloud
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the readaloud's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function readaloud_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    $itemid = (int) array_shift($args);

    require_course_login($course, true, $cm);

    if (!has_capability('mod/readaloud:view', $context)) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_readaloud/$filearea/$itemid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, null, 0, $forcedownload, $options);
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding readaloud nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the readaloud module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function readaloud_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the readaloud settings
 *
 * This function is called when the context for the page is a readaloud module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $readaloudnode {@link navigation_node}
 */
function readaloud_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $readaloudnode = null) {
}
