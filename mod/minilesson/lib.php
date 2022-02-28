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
 * Library of interface functions and constants for module minilesson
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the minilesson specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_minilesson\constants;
use \mod_minilesson\utils;

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
function minilesson_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
		case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_GROUPINGS: return false;
        case FEATURE_GROUPS: return true;
        default:                        return null;
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the minilesson.
 *
 * @param $mform form passed by reference
 */
function minilesson_reset_course_form_definition(&$mform) {
    $mform->addElement('header', constants::M_MODNAME . 'header', get_string('modulenameplural', constants::M_COMPONENT));
    $mform->addElement('advcheckbox', 'reset_' . constants::M_MODNAME , get_string('deletealluserdata',constants::M_COMPONENT));
}

/**
 * Course reset form defaults.
 * @param object $course
 * @return array
 */
function minilesson_reset_course_form_defaults($course) {
    return array('reset_' . constants::M_MODNAME =>1);
}


function minilesson_editor_with_files_options($context){
	return array('maxfiles' => EDITOR_UNLIMITED_FILES,
               'noclean' => true, 'context' => $context, 'subdirs' => true);
}

function minilesson_editor_no_files_options($context){
	return array('maxfiles' => 0, 'noclean' => true,'context'=>$context);
}
function minilesson_picturefile_options($context){
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
        'noclean' => true, 'context' => $context, 'subdirs' => true, 'accepted_types' => array('image'));
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function minilesson_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {" . constants::M_TABLE . "} l, {course_modules} cm, {modules} m
             WHERE m.name='" . constants::M_MODNAME . "' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array ("course" => $courseid);
    if ($moduleinstances = $DB->get_records_sql($sql,$params)) {
        foreach ($moduleinstances as $moduleinstance) {
            minilesson_grade_item_update($moduleinstance, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * minilesson attempts for course $data->courseid.
 *
 * @global stdClass
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function minilesson_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', constants::M_COMPONENT);
    $status = array();

    if (!empty($data->{'reset_' . constants::M_MODNAME})) {
        $sql = "SELECT l.id
                         FROM {".constants::M_TABLE."} l
                        WHERE l.course=:course";

        $params = array ("course" => $data->courseid);
        $DB->delete_records_select(constants::M_ATTEMPTSTABLE, "moduleid IN ($sql)", $params);


        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            minilesson_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletealluserdata', constants::M_COMPONENT), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates(constants::M_MODNAME, array('available', 'deadline'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
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
function minilesson_grade_item_update($moduleinstance, $grades=null) {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (array_key_exists('cmidnumber', (array) $moduleinstance)) { //it may not be always present
        $params = array('itemname'=>$moduleinstance->name, 'idnumber'=>$moduleinstance->cmidnumber);
    } else {
        $params = array('itemname'=>$moduleinstance->name);
    }


    if ($moduleinstance->grade > 0) {
        $params['gradetype']  = GRADE_TYPE_VALUE;
        $params['grademax']   = $moduleinstance->grade;
        $params['grademin']   = 0;
    } else if ($moduleinstance->grade < 0) {
        $params['gradetype']  = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$moduleinstance->grade;

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
            $grade = grade_get_grades($moduleinstance->course, 'mod',
                    constants::M_MODNAME, $moduleinstance->id, $currentgrade->userid);
            $params['grademax']   = reset($grade->items)->grademax;
        }
    } else {
        $params['gradetype']  = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
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
                $grades[$key]['rawgrade'] = ($grade['rawgrade'] * $params['grademax'] / 100);
            } else {
                //setting rawgrade to null just in case user is deleting a grade
                $grades[$key]['rawgrade'] = null;
            }
        }
    }

    return grade_update('mod/' . constants::M_MODNAME,
            $moduleinstance->course, 'mod', constants::M_MODNAME, $moduleinstance->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moduleinstance
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function minilesson_update_grades($moduleinstance, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($moduleinstance->grade == 0) {
        minilesson_grade_item_update($moduleinstance);

    } else if ($grades = minilesson_get_user_grades($moduleinstance, $userid)) {
        minilesson_grade_item_update($moduleinstance, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        minilesson_grade_item_update($moduleinstance, $grade);

    } else {
        minilesson_grade_item_update($moduleinstance);
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
function minilesson_get_user_grades($moduleinstance, $userid=0) {
    global $CFG, $DB;

    $params = array("moduleid" => $moduleinstance->id);
    $cantranscribe = utils::can_transcribe($moduleinstance);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $user = "AND u.id = :userid";
    }
    else {
        $user="";

    }

    //human_sql
    $human_sql = "SELECT u.id, u.id AS userid, a.sessionscore AS rawgrade
                      FROM {user} u, {". constants::M_ATTEMPTSTABLE ."} a
                     WHERE a.id= (SELECT max(id) FROM {". constants::M_ATTEMPTSTABLE ."} ia WHERE ia.userid=u.id AND ia.moduleid = a.moduleid AND ia.status = " . constants::M_STATE_COMPLETE . ") ".
                     " AND u.id = a.userid AND a.moduleid = :moduleid 
                           $user
                  GROUP BY u.id, a.sessionscore";


     $results = $DB->get_records_sql($human_sql, $params);

    //return results
    return $results;
}


function minilesson_get_completion_state($course,$cm,$userid,$type) {
	return minilesson_is_complete($course,$cm,$userid,$type);
}


//this is called internally only 
function minilesson_is_complete($course,$cm,$userid,$type) {
	 global $CFG,$DB;
	 
	  global $CFG,$DB;

	// Get module object
    if(!($moduleinstance=$DB->get_record(constants::M_TABLE,array('id'=>$cm->instance)))) {
        throw new Exception("Can't find module with cmid: {$cm->instance}");
    }

    //check if the min grade condition is enabled
    if($moduleinstance->mingrade==0){
        return $type;
    }

	$params = array('moduleid'=>$moduleinstance->id, 'userid'=>$userid);
	$sql = "SELECT  MAX( sessionscore  ) AS grade
                      FROM {". constants::M_ATTEMPTSTABLE ."}
                     WHERE userid = :userid AND moduleid = :moduleid" .
                     " AND status=" .constants::M_STATE_COMPLETE;
	$result = $DB->get_field_sql($sql, $params);
	if($result===false){return false;}
	 
	//check completion reqs against satisfied conditions
	switch ($type){
		case COMPLETION_AND:
			$success = $result >= $moduleinstance->mingrade;
			break;
		case COMPLETION_OR:
			$success = $result >= $moduleinstance->mingrade;
	}
	//return our success flag
	return $success;
}


/**
 * A task called from scheduled or adhoc
 *
 * @param progress_trace trace object
 *
 */
function minilesson_dotask(progress_trace $trace) {
    $trace->output('executing dotask');
}

function minilesson_get_editornames(){
	//return array('welcome');
    return array();
}

/**
 * Saves a new instance of the minilesson into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $minilesson An object from the form in mod_form.php
 * @param mod_minilesson_mod_form $mform
 * @return int The id of the newly inserted minilesson record
 */
function minilesson_add_instance(stdClass $minilesson, mod_minilesson_mod_form $mform = null) {
    global $DB;

    $minilesson->timecreated = time();
	$minilesson = minilesson_process_files($minilesson,$mform);
    $minilesson->id = $DB->insert_record(constants::M_TABLE, $minilesson);

    if(!isset($minilesson->cmidnumber)){
        $minilesson->cmidnumber=null;
    }
    minilesson_grade_item_update($minilesson);

    return  $minilesson->id;

}


function minilesson_process_files(stdClass $minilesson, mod_minilesson_mod_form $mform = null) {
	global $DB;
    $cmid = $minilesson->coursemodule;
    $context = context_module::instance($cmid);
	$editors = minilesson_get_editornames();
	$itemid=0;
	$edoptions = minilesson_editor_no_files_options($context);
	foreach($editors as $editor){
		$minilesson = file_postupdate_standard_editor( $minilesson, $editor, $edoptions,$context,constants::M_COMPONENT,$editor,$itemid);
	}

	return $minilesson;
}

/**
 * Updates an instance of the minilesson in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $minilesson An object from the form in mod_form.php
 * @param mod_minilesson_mod_form $mform
 * @return boolean Success/Fail
 */
function minilesson_update_instance(stdClass $minilesson, mod_minilesson_mod_form $mform = null) {

    global $DB;

    $minilesson->timemodified = time();
    $minilesson->id = $minilesson->instance;
    $minilesson = minilesson_process_files($minilesson,$mform);
    $params = array('id' => $minilesson->instance);
    $oldgradefield = $DB->get_field(constants::M_TABLE, 'grade', $params);

    //if region has changed we will need a new scorer. So lets flag that if necessary
    $oldrecord = $DB->get_record(constants::M_TABLE,array('id'=>$minilesson->id));
    $needs_new_langmodels=false;
    if($minilesson->region!=$oldrecord->region) {
        $needs_new_langmodels=true;
    }

    //perform our update
    $success = $DB->update_record(constants::M_TABLE, $minilesson);

    //update lang models if required
    if($needs_new_langmodels) {
        \mod_minilesson\local\rsquestion\helper::update_all_langmodels($minilesson);
    }

    if(!isset($minilesson->cmidnumber)){
        $minilesson->cmidnumber=null;
    }
    minilesson_grade_item_update($minilesson);
    $update_grades = ($minilesson->grade === $oldgradefield ? false : true);
    if ($update_grades) {
        minilesson_update_grades($minilesson, 0, false);
    }

    return $success;
}

/**
 * Removes an instance of the minilesson from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function minilesson_delete_instance($id) {
    global $DB;

    if (! $minilesson = $DB->get_record(constants::M_TABLE, array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records(constants::M_TABLE, array('id' => $minilesson->id));

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
function minilesson_user_outline($course, $user, $mod, $minilesson) {

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
 * @param stdClass $minilesson the module instance record
 * @return void, is supposed to echp directly
 */
function minilesson_user_complete($course, $user, $mod, $minilesson) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in minilesson activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function minilesson_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link minilesson_print_recent_mod_activity()}.
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
function minilesson_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see minilesson_get_recent_mod_activity()}

 * @return void
 */
function minilesson_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function minilesson_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function minilesson_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of minilesson?
 *
 * This function returns if a scale is being used by one minilesson
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $moduleid ID of an instance of this module
 * @return bool true if the scale is used by the given minilesson instance
 */
function minilesson_scale_used($moduleid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('id' => $moduleid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of minilesson.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any minilesson instance
 */
function minilesson_scale_used_anywhere($scaleid) {
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
function minilesson_get_file_areas($course, $cm, $context) {
    return minilesson_get_editornames();
}

/**
 * File browsing support for minilesson file areas
 *
 * @package mod_minilesson
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
function minilesson_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the minilesson file areas
 *
 * @package mod_minilesson
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the minilesson's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function minilesson_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
       global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
	
	$itemid = (int)array_shift($args);

    require_course_login($course, true, $cm);

    if (!has_capability('mod/minilesson:view', $context)) {
        return false;
    }


        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_minilesson/$filearea/$itemid/$relativepath";

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
          return false;
        }

        // finally send the file
        send_stored_file($file, null, 0, $forcedownload, $options);
}

function minilesson_output_fragment_preview($args){
    global $DB,$PAGE;
    $args = (object) $args;
    $context = $args->context;

    $cm         = get_coursemodule_from_id('minilesson', $context->instanceid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record('minilesson', array('id' => $cm->instance), '*', MUST_EXIST);

    $renderer = $PAGE->get_renderer('mod_minilesson');
    $comp_test =  new \mod_minilesson\comprehensiontest($cm);
    $ret = $renderer->show_quiz_preview($comp_test, $args->itemid);
    $ret .= $renderer->fetch_activity_amd($cm, $moduleinstance,$args->itemid);
    return $ret;
}

function minilesson_output_fragment_mform($args) {
    global $CFG, $PAGE, $DB;

    $args = (object) $args;
    $context = $args->context;
    $formname = $args->formname;
    $mform= null;
    $o = '';



    list($ignored, $course) = get_context_info_array($context->id);

    //get filechooser and html editor options
    $editoroptions = \mod_minilesson\local\rsquestion\helper::fetch_editor_options($course, $context);
    $filemanageroptions = \mod_minilesson\local\rsquestion\helper::fetch_filemanager_options($course,3);

    // get the objects we need
    $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

    $item=false;
    if($args->itemid){
        $item = $DB->get_record(constants::M_QTABLE, array('id'=>$args->itemid,constants::M_MODNAME => $cm->instance),
                '*', MUST_EXIST);
        if($item) {
            $data = $item;
            $data->itemid = $item->id;
            $data->id = $cm->id;

            //If rich text, use editor otherwise use filepicker
            if($moduleinstance->richtextprompt==constants::M_PROMPT_RICHTEXT) {
                //init our editor field
                $data = file_prepare_standard_editor($data, constants::TEXTQUESTION, $editoroptions, $context,
                        constants::M_COMPONENT,
                        constants::TEXTQUESTION_FILEAREA, $data->itemid);
            }else{

                //make sure the media upload fields are in the correct state
                $fs = get_file_storage();
                $files = $fs->get_area_files( $context->id,  constants::M_COMPONENT,constants::MEDIAQUESTION,$data->itemid);
                if($files){
                    $data->addmedia = 1;
                }else{
                    $data->addmedia = 0;
                }
                if(!empty($data->{constants::TTSQUESTION})){
                    $data->addttsaudio = 1;
                }else{
                    $data->addttsaudio = 0;
                }
                if(!empty($data->{constants::MEDIAIFRAME})){
                    $data->addiframe = 1;
                }else{
                    $data->addiframe = 0;
                }
                if(!empty($data->{constants::YTVIDEOID})){
                    $data->addyoutubeclip = 1;
                }else{
                    $data->addyoutubeclip = 0;
                }
                if(!empty($data->{constants::QUESTIONTEXTAREA})){
                    $edoptions = constants::ITEMTEXTAREA_EDOPTIONS;
                    $data = file_prepare_standard_editor($data, constants::QUESTIONTEXTAREA, $edoptions, $context, constants::M_COMPONENT,
                            constants::TEXTQUESTION_FILEAREA, $data->itemid);
                    $data->addtextarea = 1;
                }else{
                    $data->addtextarea = 0;
                }



                //init our itemmedia field
                $draftitemid = file_get_submitted_draft_itemid(constants::MEDIAQUESTION);
                file_prepare_draft_area($draftitemid, $context->id, constants::M_COMPONENT,
                        constants::MEDIAQUESTION, $data->itemid,
                        $filemanageroptions);
                $data->{constants::MEDIAQUESTION} = $draftitemid;

            }
        }
    }


    //get the mform for our item
    switch($formname){


        case constants::TYPE_MULTICHOICE:
            $mform = new \mod_minilesson\local\rsquestion\multichoiceform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_MULTIAUDIO:
            $mform = new \mod_minilesson\local\rsquestion\multiaudioform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_DICTATIONCHAT:
            $mform = new \mod_minilesson\local\rsquestion\dictationchatform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_DICTATION:
            $mform = new \mod_minilesson\local\rsquestion\dictationform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_SPEECHCARDS:
            $mform = new \mod_minilesson\local\rsquestion\speechcardsform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_LISTENREPEAT:
            $mform = new \mod_minilesson\local\rsquestion\listenrepeatform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_PAGE:
            $mform = new \mod_minilesson\local\rsquestion\pageform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_SMARTFRAME:
            $mform = new \mod_minilesson\local\rsquestion\smartframeform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::TYPE_SHORTANSWER:
            $mform = new \mod_minilesson\local\rsquestion\shortanswerform(null,
                    array('editoroptions'=>$editoroptions,
                            'filemanageroptions'=>$filemanageroptions,
                            'moduleinstance'=>$moduleinstance)
            );
            break;

        case constants::NONE:
        default:
            print_error('No item type specifified');
            return 0;

    }

   //if we have item data set it
    if($item){
        $mform->set_data($data);
    }

    if(!empty($mform)) {
        ob_start();
        $mform->display();
        $o .= ob_get_contents();
        ob_end_clean();
    }

    return $o;
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding minilesson nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the minilesson module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function minilesson_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the minilesson settings
 *
 * This function is called when the context for the page is a minilesson module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $minilessonnode {@link navigation_node}
 */
function minilesson_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $minilessonnode=null) {
}

function mod_minilesson_get_fontawesome_icon_map() {
    return [
        'mod_minilesson:print' => 'fa-print',
        'mod_minilesson:volume-up' => 'fa-volume-up',
        'mod_minilesson:close' => 'fa-close'
    ];
}

function mod_minilesson_cm_info_dynamic(cm_info $cm) {
    global $USER,$DB;

         $moduleinstance= $DB->get_record('minilesson', array('id' => $cm->instance,), '*', MUST_EXIST);
        if(method_exists($cm,'override_customdata')) {
            $cm->override_customdata('duedate', $moduleinstance->viewend);
            $cm->override_customdata('allowsubmissionsfromdate', $moduleinstance->viewstart);
        }
    
}
function minilesson_get_coursemodule_info($coursemodule) {
    global $DB;

        if(!$moduleinstance= $DB->get_record('minilesson', array('id' => $coursemodule->instance,), '*')){
            return false;
        }
        $result = new cached_cm_info();
        if ($coursemodule->showdescription) {
            if (time() > $moduleinstance->viewstart) {
                $result->content = format_module_intro('minilesson', $moduleinstance, $coursemodule->id, false);
            }
        }
        $result->name = $moduleinstance->name;
        $result->customdata['duedate'] = $moduleinstance->viewend;
        $result->customdata['allowsubmissionsfromdate'] = $moduleinstance->viewstart;
        return $result;
}
