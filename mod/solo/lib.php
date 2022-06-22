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
 * Library of interface functions and constants for module solo
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the solo specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_solo\constants;
use mod_solo\utils;


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
function solo_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
		case FEATURE_COMPLETION_HAS_RULES: return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_ADVANCED_GRADING:        return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_GROUPS:
            return true;
        default:
            //cute hack to work on M4.0 and above
            if(defined('FEATURE_MOD_PURPOSE') && defined('MOD_PURPOSE_ASSESSMENT') && $feature=='mod_purpose'){
                return "assessment";
            }else{
                return null;
            }
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the module.
 *
 * @param $mform form passed by reference
 */
function solo_reset_course_form_definition(&$mform) {
    $mform->addElement('header', constants::M_MODNAME . 'header', get_string('modulenameplural', constants::M_COMPONENT));
    $mform->addElement('advcheckbox', 'reset_' . constants::M_MODNAME , get_string('deletealluserdata',constants::M_COMPONENT));
}

/**
 * Course reset form defaults.
 * @param object $course
 * @return array
 */
function solo_reset_course_form_defaults($course) {
    return array('reset_' . constants::M_MODNAME =>1);
}


function solo_editor_with_files_options($context){
	return array('maxfiles' => EDITOR_UNLIMITED_FILES,
               'noclean' => true, 'context' => $context, 'subdirs' => true);
}

function solo_editor_no_files_options($context){
	return array('maxfiles' => 0, 'noclean' => true,'context'=>$context);
}
function solo_filemanager_options($context){
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
        'noclean' => true, 'context' => $context, 'subdirs' => true, 'accepted_types' => array('image','audio','video'));
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function solo_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {" . constants::M_TABLE . "} l, {course_modules} cm, {modules} m
             WHERE m.name='" . constants::M_MODNAME . "' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array ("course" => $courseid);
    if ($moduleinstances = $DB->get_records_sql($sql,$params)) {
        foreach ($moduleinstances as $moduleinstance) {
            solo_grade_item_update($moduleinstance, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * solo attempts for course $data->courseid.
 *
 * @global stdClass
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function solo_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', constants::M_COMPONENT);
    $status = array();

    if (!empty($data->{'reset_' . constants::M_MODNAME})) {
        $sql = "SELECT l.id
                         FROM {".constants::M_TABLE."} l
                        WHERE l.course=:course";

        $params = array ("course" => $data->courseid);
        $DB->delete_records_select(constants::M_ATTEMPTSTABLE, constants::M_MODNAME . " IN ($sql)", $params);
        $DB->delete_records_select(constants::M_STATSTABLE, constants::M_MODNAME . " IN ($sql)", $params);
        $DB->delete_records_select(constants::M_AITABLE, "moduleid IN ($sql)", $params);

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            solo_reset_gradebook($data->courseid);
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

function solo_get_filemanagernames(){
    return array('topicmedia','modelmedia');
}

function solo_get_editornames(){
	return array('tips');
}

/**
 * Saves a new instance of the module into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $moduleinstance An object from the form in mod_form.php
 * @param mod_solo_mod_form $mform
 * @return int The id of the newly inserted module record
 */
function solo_add_instance(stdClass $moduleinstance, mod_solo_mod_form $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
	$moduleinstance = solo_process_editors($moduleinstance,$mform);
    $moduleinstance = solo_process_filemanagers($moduleinstance,$mform);
    $moduleinstance = solo_process_autogradeoptions($moduleinstance,$mform);
    $moduleinstance = utils::sequence_to_steps($moduleinstance);
    $moduleinstance->id = $DB->insert_record(constants::M_TABLE, $moduleinstance);
    solo_grade_item_update($moduleinstance);
	return $moduleinstance->id;
}


function solo_process_editors(stdClass $moduleinstance, $mform = null) {
	global $DB;
    $cmid = $moduleinstance->coursemodule;
    $context = context_module::instance($cmid);
	$editors = solo_get_editornames();
	$itemid=0;
	$edoptions = solo_editor_no_files_options($context);
	foreach($editors as $editor){
		$moduleinstance = file_postupdate_standard_editor( $moduleinstance, $editor, $edoptions,$context,constants::M_COMPONENT,$editor,$itemid);
	}
	return $moduleinstance;
}

function solo_process_autogradeoptions(stdClass $moduleinstance, $mform) {
    $ag_options = new \stdClass();
    $ag_options->graderatioitem = $moduleinstance->graderatioitem;
    $ag_options->gradewordcount = $moduleinstance->gradewordcount;
    $ag_options->gradebasescore = $moduleinstance->gradebasescore;

    for ($bonusno=1;$bonusno<=4;$bonusno++) {
        $ag_options->{'bonusdirection' . $bonusno} = $moduleinstance->{'bonusdirection' . $bonusno} ;
        $ag_options->{'bonuspoints' . $bonusno}  = $moduleinstance->{'bonuspoints' . $bonusno};
        $ag_options->{'bonus' . $bonusno} = $moduleinstance->{'bonus' . $bonusno};
    }

    $moduleinstance->autogradeoptions=json_encode($ag_options);
    return $moduleinstance;

}

function solo_process_filemanagers(stdClass $moduleinstance, $mform = null) {
    global $DB;
    $cmid = $moduleinstance->coursemodule;
    $context = context_module::instance($cmid);
    $itemid=0;
    $filemanagers = solo_get_filemanagernames();
    $filemanageroptions = solo_filemanager_options($context);
    foreach($filemanagers as $fm){
        if (property_exists($moduleinstance, $fm)) {
            file_save_draft_area_files($moduleinstance->{$fm},
                    $context->id, constants::M_COMPONENT,
                    $fm, $itemid,
                    $filemanageroptions);
        }
    }

    return $moduleinstance;
}


/**
 * Updates an instance of the module in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php
 * @param mod_solo_mod_form $mform
 * @return boolean Success/Fail
 */
function solo_update_instance(stdClass $moduleinstance, mod_solo_mod_form $mform = null) {
    global $DB;


    $params = array('id' => $moduleinstance->instance);
    $oldgradefield = $DB->get_field(constants::M_TABLE, 'grade', $params);

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

	$moduleinstance = solo_process_editors($moduleinstance,$mform);
    $moduleinstance = solo_process_filemanagers($moduleinstance,$mform);
    $moduleinstance = solo_process_autogradeoptions($moduleinstance,$mform);
    $moduleinstance = utils::sequence_to_steps($moduleinstance);
	$success = $DB->update_record(constants::M_TABLE, $moduleinstance);
    solo_grade_item_update($moduleinstance);

    $update_grades = ($moduleinstance->grade === $oldgradefield ? false : true);
    if ($update_grades) {
        solo_update_grades($moduleinstance, 0, false);
    }

	return $success;
}

/**
 * Removes an instance of the module from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function solo_delete_instance($id) {
    global $DB;

    if (! $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records(constants::M_TABLE, array('id' => $moduleinstance->id));
    $DB->delete_records(constants::M_ATTEMPTSTABLE, array(constants::M_MODNAME => $moduleinstance->id));
    $DB->delete_records(constants::M_STATSTABLE, array(constants::M_MODNAME => $moduleinstance->id));
    $DB->delete_records(constants::M_AITABLE, array('moduleid' => $moduleinstance->id));
    $DB->delete_records(constants::M_SELECTEDTOPIC_TABLE, array('moduleid' => $moduleinstance->id));
    $DB->delete_records_select(constants::M_SELECTEDTOPIC_TABLE,
            "topicid IN (SELECT id FROM {".constants::M_TOPIC_TABLE."} t WHERE t.moduleid = ?)",
            array('moduleid' => $moduleinstance->id));
    $DB->delete_records(constants::M_TOPIC_TABLE, array('moduleid' => $moduleinstance->id));

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
function solo_user_outline($course, $user, $mod, $moduleinstance) {

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
 * @param stdClass $moduleinstance the module instance record
 * @return void, is supposed to echp directly
 */
function solo_user_complete($course, $user, $mod, $moduleinstance) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in solo activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function solo_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link solo_print_recent_mod_activity()}.
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
function solo_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see solo_get_recent_mod_activity()}

 * @return void
 */
function solo_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
/*
function solo_cron () {
    global $CFG;

    return true;
}
*/

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function solo_get_extra_capabilities() {
    return array();
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
function solo_get_file_areas($course, $cm, $context) {
    return array_merge(solo_get_editornames(),solo_get_filemanagernames());
}

/**
 * File browsing support for solo file areas
 *
 * @package mod_solo
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
function solo_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the solo file areas
 *
 * @package mod_solo
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the solo's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function solo_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
       global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

	$itemid = (int)array_shift($args);

    require_course_login($course, true, $cm);

    if (!has_capability('mod/solo:view', $context)) {
        return false;
    }


        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_solo/$filearea/$itemid/$relativepath";

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
 * Extends the global navigation tree by adding solo nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the solo module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function solo_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the solo settings
 *
 * This function is called when the context for the page is a solo module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $moduleinstancenode {@link navigation_node}
 */
function solo_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $moduleinstancenode=null) {
}

//////////////////////////////////////////////////////////////////////////////
// API to update/select grades
//////////////////////////////////////////////////////////////////////////////

/**
 * Create grade item for given solo
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $moduleinstance object with extra cmidnumber
 * @param array|object $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function solo_grade_item_update($moduleinstance, $grades=null) {
    global $CFG;
    require_once($CFG->dirroot.'/lib/gradelib.php');

    $params = array('itemname' => $moduleinstance->name);
    if (array_key_exists('cmidnumber', (array)$moduleinstance)) {
        $params['idnumber'] = $moduleinstance->cmidnumber;
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
        if (! empty($grades)) {
            if (is_array($grades)) {
                $currentgrade = reset($grades);
            } else {
                $currentgrade = $grades;
            }
        }

        // When converting a score to a scale, use scale's grade maximum to calculate it.
        if (! empty($currentgrade) && $currentgrade->rawgrade !== null) {
            $grade = grade_get_grades($moduleinstance->course, 'mod', 'solo', $moduleinstance->id, $currentgrade->userid);
            $params['grademax'] = reset($grade->items)->grademax;
        }
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
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

    if (is_object($moduleinstance->course)) {
        $courseid = $moduleinstance->course->id;
    } else {
        $courseid = $moduleinstance->course;
    }

    return grade_update('mod/solo', $courseid, 'mod', 'solo', $moduleinstance->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moduleinstance
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function solo_update_grades($moduleinstance, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/lib/gradelib.php');

    if (empty($moduleinstance->grade)) {
        $grades = null;
    } else if ($grades = solo_get_user_grades($moduleinstance, $userid)) {
        // do nothing
    } else if ($userid && $nullifnone) {
        $grades = (object)array('userid' => $userid, 'rawgrade' => null);
    } else {
        $grades = null;
    }

    solo_grade_item_update($moduleinstance, $grades);
}

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $id of solo
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function solo_get_user_grades($moduleinstance, $userid=0) {

    global $CFG, $DB;



    $params = array("moduleid" => $moduleinstance->id);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $user = "AND u.id = :userid";
    }
    else {
        $user="";
    }

    //grade_sql
    //added MAX to grade to keep postgresql happy
    $grade_sql = "SELECT u.id, u.id AS userid, MAX(grade) AS rawgrade
                      FROM {user} u, {". constants::M_ATTEMPTSTABLE ."} a
                     WHERE a.id= (SELECT max(id) FROM {". constants::M_ATTEMPTSTABLE ."} ia WHERE ia.userid=u.id AND ia.solo = a.solo)  AND u.id = a.userid AND a.solo = :moduleid
                           $user
                  GROUP BY u.id";


    $results = $DB->get_records_sql($grade_sql, $params);
    return $results;
}

/**
 * Is a given scale used by the instance of solo?
 *
 * This function returns if a scale is being used by one solo
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $moduleid ID of an instance of this module
 * @return bool true if the scale is used by the given instance
 */
function solo_scale_used($moduleid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('id' => $moduleid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of module.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any module instance
 */
function solo_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

function mod_solo_grading_areas_list() {
    return [
        'solo' => 'solo',
    ];
}

/**
 * Displays the solo popup window for grading.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 * @throws dml_exception
 */
function mod_solo_output_fragment_rubric_grade_form($args) {
    global $DB;

    require_once('rubric_grade_form.php');

    $args = (object)$args;
    $o = '';

    // Get form data for the form if parsed to push to mform.
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $sql = "select  pa.solo, pa.feedback, pa.id as attemptid
        from {" . constants::M_ATTEMPTSTABLE . "} pa
        inner join {" . constants::M_TABLE . "} pc on pa.solo = pc.id
        inner join {course_modules} cm on cm.instance = pc.id and pc.course = cm.course and pa.userid = ?
        where cm.id = ?";

    $modulecontext = context_module::instance($args->cmid);
    $attempt = $DB->get_record_sql($sql, array($args->studentid, $args->cmid));

    if (!$attempt) {
        return "";
    }

    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $attempt->solo));
    $gradingdisabled = false;
    $gradinginstance = utils::get_grading_instance($attempt->attemptid, $gradingdisabled, $moduleinstance, $modulecontext);

    $mform = new rubric_grade_form(null, array('gradinginstance' => $gradinginstance), 'post', '', null, true, $formdata);

    if ($mform->is_cancelled()) {
        // Window closes.
    }

    $feedbackdata = [];
    $feedbackdata['feedback'] = $attempt->feedback;
    $mform->set_data($feedbackdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    // Display the form. Ob* functions used since this is called in an ajax call.
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Displays the solo popup window for grading.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 * @throws dml_exception
 */
function mod_solo_output_fragment_simple_grade_form($args) {
    global $DB;

    require_once('simple_grade_form.php');

    $args = (object)$args;
    $o = '';

    // Get form data for the form if parsed to push to mform.
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $sql = "select  pa.solo, pa.feedback, pa.id as attemptid, pa.grade as grade
        from {" . constants::M_ATTEMPTSTABLE . "} pa
        inner join {" . constants::M_TABLE . "} pc on pa.solo = pc.id
        inner join {course_modules} cm on cm.instance = pc.id and pc.course = cm.course and pa.userid = ?
        where cm.id = ?";

    $modulecontext = context_module::instance($args->cmid);
    $attempt = $DB->get_record_sql($sql, array($args->studentid, $args->cmid));

    if (!$attempt) {
        return "";
    }

    $mform = new simple_grade_form(null, array(), 'post', '', null, true, $formdata);

    if ($mform->is_cancelled()) {
        // Window closes.
    }

    $formdata = [];
    $formdata['grade'] = $attempt->grade;
    $formdata['feedback'] = $attempt->feedback;
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    // Display the form. Ob* functions used since this is called in an ajax call.
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Obtains the completion state for this instance based on completed step count
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function solo_get_completion_state($course,$cm,$userid,$type) {
    global $CFG,$DB;

    // Get  module details
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
    $attempthelper = new \mod_solo\attempthelper($cm);

    // If completion option is enabled, evaluate it and return true/false
    if($moduleinstance->completionallsteps) {
        $latestattempt = $attempthelper->fetch_latest_attempt();
        if ($latestattempt && $latestattempt->completedsteps == constants::STEP_SELFTRANSCRIBE){
            return true;
        }else{
            return false;
        }
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

function mod_solo_cm_info_dynamic(cm_info $cm) {
    global $USER,$DB;

        $moduleinstance= $DB->get_record('solo', array('id' => $cm->instance,), '*', MUST_EXIST);
        if(method_exists($cm,'override_customdata')) {
            $cm->override_customdata('duedate', $moduleinstance->viewend);
            $cm->override_customdata('allowsubmissionsfromdate', $moduleinstance->viewstart);
        }
}
function solo_get_coursemodule_info($coursemodule) {
    global $DB;

    if(!$moduleinstance= $DB->get_record('solo', array('id' => $coursemodule->instance,), '*')){
        return false;
    }
    $result = new cached_cm_info();
    if ($coursemodule->showdescription) {
        if (time() > $moduleinstance->viewstart) {
            $result->content = format_module_intro('solo', $moduleinstance, $coursemodule->id, false);
        }
    }
    $result->name = 'solo';
    $result->name = $moduleinstance->name;
    $result->customdata['duedate'] = $moduleinstance->viewend;
    $result->customdata['allowsubmissionsfromdate'] = $moduleinstance->viewstart;
    return $result;
}