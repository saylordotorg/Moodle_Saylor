<?php
/**
 * Lib.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

// TODO Support activity completion.
defined('MOODLE_INTERNAL') || die();

use \mod_wordcards\constants;
use \mod_wordcards\utils;

/**
 * Supported features.
 *
 * @param string $feature FEATURE_xx constant for requested feature.
 * @return mixed True if module supports feature, null if doesn't know.
 */
function wordcards_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_GROUPINGS:
            return false;
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

function wordcards_add_instance(stdClass $module, mod_wordcards_mod_form $mform = null) {
    global $DB;

    $module->timecreated = time();
    $module->timemodified = time();

    if (empty($module->skipreview)) {
        $module->skipreview = 0;
    }

    $module->id = $DB->insert_record('wordcards', $module);


    if(!isset($module->cmidnumber)){
        $module->cmidnumber=null;
    }
    wordcards_grade_item_update($module);

    return $module->id;
}

function wordcards_update_instance(stdClass $module, mod_wordcards_mod_form $mform = null) {
    global $DB;

    $module->timemodified = time();
    $module->id = $module->instance;
    $params = array('id' => $module->instance);
    $oldmod = $DB->get_record(constants::M_TABLE,  $params);
    $oldgradefield = $oldmod->grade;
    $oldgradeoptionsfield = $oldmod->gradeoptions;
    $olddeflanguage = $oldmod->deflanguage;

    if (empty($module->skipreview)) {
        $module->skipreview = 0;
    }

    $success = $DB->update_record('wordcards', $module);

    //Process the hashcode and lang model if it makes sense
    $themod = mod_wordcards_module::get_by_modid($module->instance);
    $themod->set_region_passagehash();

    if(!isset($module->cmidnumber)){
        $module->cmidnumber=null;
    }
    wordcards_grade_item_update($module);
    $update_grades = ($module->grade === $oldgradefield ? false : true);
    if(!$update_grades){ $update_grades = ($module->gradeoptions === $oldgradeoptionsfield ? false : true);}
    if ($update_grades) {
        wordcards_update_grades($module, 0, false);
    }

    //If definitions language has changed update it
    if( $olddeflanguage !==$module->deflanguage){
        utils::update_deflanguage($themod);
    }

    return $success;

}

function wordcards_delete_instance($modid) {
    global $DB;

    $mod = mod_wordcards_module::get_by_modid($modid);
    $mod->delete();

    return true;
}

/**
 * Obtains the completion state.
 *
 * @param object $course The course.
 * @param object $cm The course module.
 * @param int $userid The user ID.
 * @param bool $type Type of comparison (or/and).
 * @return bool True if completed, false if not, else $type.
 */
function wordcards_get_completion_state($course, $cm, $userid, $type) {
    global $CFG;

    $mod = mod_wordcards_module::get_by_cmid($cm->id);
    if ($mod->is_completion_enabled()) {
        return $mod->has_user_completed_activity();
    }

    // Completion option is not enabled, we must return $type.
    return $type;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the data.
 *
 * @param $mform form passed by reference
 */
function wordcards_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'wordcardsheader', get_string('modulenameplural', 'wordcards'));
    $mform->addElement('checkbox', 'reset_wordcard', get_string('deleteallentries','wordcards'));
}

/**
 * Course reset form defaults.
 * @return array
 */
function wordcards_reset_course_form_defaults($course) {
    return array('reset_wordcard'=>0);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * wordcards user data for course $data->courseid.
 *
 * @global object
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function wordcards_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'wordcards');
    $status = array();

    if (!empty($data->reset_wordcard)) {

        // Find all wordcards of the course.
        $wordcards = $DB->get_fieldset_select('wordcards', 'id', 'course = :course', array('course' => $data->courseid));
        list($termssql, $termsparams) = $DB->get_in_or_equal($wordcards, SQL_PARAMS_NAMED);

        // Retrieve the terms.
        $terms = $DB->get_fieldset_select('wordcards_terms', 'id', 'modid ' . $termssql, $termsparams);
        list($sql, $params) = $DB->get_in_or_equal($terms, SQL_PARAMS_NAMED);

        $DB->delete_records_select('wordcards_associations', 'termid ' . $sql, $params);
        $DB->delete_records_list('wordcards_progress', 'modid', $wordcards);
        $DB->delete_records_select('wordcards_seen', 'termid ' . $sql, $params);

        $status[] = array('component' => $componentstr, 'item' => get_string('removeuserdata', 'wordcards'), 'error' => false);
    }

    // remove all grades from gradebook
    if (empty($data->reset_gradebook_grades)) {
        wordcards_reset_gradebook($data->courseid);
    }

    // PS: No wordcards date fields need to be shifted (i.e. need to be modified because the course start/end date changed)

    return $status;
}


/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function wordcards_reset_gradebook($courseid, $type = '') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {" . constants::M_TABLE . "} l, {course_modules} cm, {modules} m
             WHERE m.name='" . constants::M_MODNAME . "' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array("course" => $courseid);
    if ($moduleinstances = $DB->get_records_sql($sql, $params)) {
        foreach ($moduleinstances as $moduleinstance) {
            wordcards_grade_item_update($moduleinstance, 'reset');
        }
    }
}

/**
 * Serves the files from the  file areas
 *
 * @package mod_tquiz
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the tquiz's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function wordcards_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    if ($filearea === 'audio' or $filearea === 'image'  or $filearea === 'model_sentence_audio') {

        $itemid = (int) array_shift($args);

        require_course_login($course, true, $cm);

        if (!has_capability('mod/wordcards:view', $context)) {
            return false;
        }

        $fs = get_file_storage();
        $areafiles = $fs->get_area_files($context->id,'mod_wordcards',$filearea,$itemid);
        if($areafiles){
            $file = array_pop($areafiles);
            if($file->is_directory()){
                if($areafiles) {
                    $file = array_pop($areafiles);
                }
            }
            // finally send the file
            if($file && !$file->is_directory()) {
                send_stored_file($file, null, 0, $forcedownload, $options);
            }
        }
    }
    return false;
}


////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding readseed nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the readseed module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function wordcards_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the wordcards settings
 *
 * This function is called when the context for the page is a wordcards module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $wordcardsnode {@link navigation_node}
 */
function wordcards_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $wordcardsnode=null) {
}

//////////////////////////////////////////////////////////////////////////////
// API to update/select grades
//////////////////////////////////////////////////////////////////////////////

/**
 * Create grade item for given Wordcards
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $moduleinstance object with extra cmidnumber
 * @param array|object $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function wordcards_grade_item_update($moduleinstance, $grades=null) {
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
            $grade = grade_get_grades($moduleinstance->course, 'mod', 'wordcards', $moduleinstance->id, $currentgrade->userid);
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

    return grade_update('mod/wordcards', $courseid, 'mod', 'wordcards', $moduleinstance->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moduleinstance
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function wordcards_update_grades($moduleinstance, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/lib/gradelib.php');

    if (empty($moduleinstance->grade)) {
        $grades = null;
    } else if ($grades = wordcards_get_user_grades($moduleinstance, $userid)) {
        // do nothing
    } else if ($userid && $nullifnone) {
        $grades = (object)array('userid' => $userid, 'rawgrade' => null);
    } else {
        $grades = null;
    }

    wordcards_grade_item_update($moduleinstance, $grades);
}

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $id of wordcards
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function wordcards_get_user_grades($moduleinstance, $userid=0) {

    global $CFG, $DB;

    $params = array("moduleid" => $moduleinstance->id);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $user = "AND a.userid = :userid";
    }
    else {
        $user="";
    }

    //Highest grade
    if($moduleinstance->gradeoptions == constants::M_GRADEHIGHEST){
        $grade_sql = "SELECT a.userid as id, a.userid AS userid,MAX(a.totalgrade) AS rawgrade
                  FROM {" . constants::M_ATTEMPTSTABLE . "} a 
                 WHERE  a.modid = :moduleid
                       $user
              GROUP BY a.userid";

    //latest grade
    }else {
        //grade_sql
        $grade_sql = "SELECT a.userid as id, a.userid AS userid, a.totalgrade AS rawgrade
                      FROM {" . constants::M_ATTEMPTSTABLE . "} a
                     WHERE a.id= (SELECT max(id) FROM {" . constants::M_ATTEMPTSTABLE . "} ia WHERE ia.userid=a.userid AND ia.modid = a.modid)  
                     AND a.modid = :moduleid
                           $user
                  GROUP BY a.userid, a.totalgrade";
    }

    $results = $DB->get_records_sql($grade_sql, $params);
    return $results;
}

/**
 * Is a given scale used by the instance of wordcards?
 *
 * This function returns if a scale is being used by one wordcards
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $moduleid ID of an instance of this module
 * @return bool true if the scale is used by the given instance
 */
function wordcards_scale_used($moduleid, $scaleid) {
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
function wordcards_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(constants::M_TABLE, array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

function wordcards_output_fragment_mform($args) {
    global $CFG, $PAGE, $DB;

    $args = (object) $args;
    $context = $args->context;
    $formname = $args->formname;
    $mform= null;
    $o = '';

    // get the objects we need
    $cm = get_coursemodule_from_id(constants::M_MODNAME, $context->instanceid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

    if($args->itemid) {
        $term = $DB->get_record('wordcards_terms', ['modid' => $moduleinstance->id, 'id' => $args->itemid], '*');
    }else{
        $term=false;
    }
    if (!$term) {
        $term = new stdClass();
        $term->id=null;
    }

    list($ignored, $course) = get_context_info_array($context->id);

    //get filechooser and html editor options
    $audiooptions= utils::fetch_filemanager_opts('audio');
    $imageoptions= utils::fetch_filemanager_opts('image');
    file_prepare_standard_filemanager($term, 'audio', $audiooptions, $context, constants::M_COMPONENT, 'audio', $term->id);
    file_prepare_standard_filemanager($term, 'image', $imageoptions, $context, constants::M_COMPONENT, 'image', $term->id);
    file_prepare_standard_filemanager($term, 'model_sentence_audio', $audiooptions, $context, constants::M_COMPONENT, 'model_sentence_audio', $term->id);

    $theform = new mod_wordcards_form_term(null, ['termid' => $term ? $term->id : 0,'ttslanguage'=>$moduleinstance->ttslanguage],null,null,array('class'=>'mod_wordcards_form_term'));
    $theform->set_data($term);

    if(!empty($theform)) {
        ob_start();
        $theform->display();
        $o .= ob_get_contents();
        ob_end_clean();
    }

    return $o;
}

function mod_wordcards_cm_info_dynamic(cm_info $cm) {
    global $USER,$DB;

        $moduleinstance= $DB->get_record('wordcards', array('id' => $cm->instance,), '*', MUST_EXIST);
        if(method_exists($cm,'override_customdata')) {
            $cm->override_customdata('duedate', $moduleinstance->viewend);
            $cm->override_customdata('allowsubmissionsfromdate', $moduleinstance->viewstart);
        }
    
}
function wordcards_get_coursemodule_info($coursemodule) {
    global $DB;

    if(!$moduleinstance= $DB->get_record('wordcards', array('id' => $coursemodule->instance,), '*')){
        return false;
    }
    $result = new cached_cm_info();
    if ($coursemodule->showdescription) {
        if (time() > $moduleinstance->viewstart) {
            $result->content = format_module_intro('wordcards', $moduleinstance, $coursemodule->id, false);
        }
    }
    $result->name = $moduleinstance->name;
    $result->customdata['duedate'] = $moduleinstance->viewend;
    $result->customdata['allowsubmissionsfromdate'] = $moduleinstance->viewstart;
   return $result;
}