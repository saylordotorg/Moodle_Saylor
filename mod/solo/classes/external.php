<?php
/**
 * External.
 *
 * @package mod_solo
 * @author  Justin Hunt - Poodll.com
 */


namespace mod_solo;


use context_module;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use mod_solo\grades\gradesubmissions;
use mod_solo\utils;
use mod_solo\aitranscript;

/**
 * External class.
 *
 * @package mod_solo
 * @author  Justin Hunt - Poodll.com
 */
class external extends external_api {

    public static function toggle_topic_selected($topicid, $activityid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::toggle_topic_selected_parameters(), [
            'topicid' => $topicid,
            'activityid' => $activityid]);
        extract($params);

        $topic = $DB->get_record(constants::M_TOPIC_TABLE, ['id' => $topicid], '*', MUST_EXIST);
        $mod = $DB->get_record(constants::M_TABLE, ['id' => $activityid], '*', MUST_EXIST);
        if (!$topic || !$mod) {
            return false;
        }
        $cm = get_coursemodule_from_instance(constants::M_MODNAME, $topic->moduleid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        if (!has_capability('mod/solo:selecttopics', $context)) {
            return false;
        }

        $success = utils::toggle_topic_selected($topic->id, $mod->id);
        return $success;
    }

    public static function toggle_topic_selected_parameters() {
        return new external_function_parameters([
            'topicid' => new external_value(PARAM_INT),
            'activityid' => new external_value(PARAM_INT)
        ]);
    }

    public static function toggle_topic_selected_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function get_grade_submission_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT),
            'cmid' => new external_value(PARAM_INT),
        ]);
    }

    public static function get_grade_submission($userid,  $cmid) {
        $gradesubmissions = new gradesubmissions();
            return ['response' => $gradesubmissions->getSubmissionData($userid,$cmid)];
    }

    public static function get_grade_submission_returns() {
        return new external_function_parameters([
            'response' => new external_multiple_structure(
                new external_single_structure([

                    'id' => new external_value(PARAM_INT, 'ID'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name'),
                    'name' => new external_value(PARAM_TEXT, 'Name'),
                    'transcriber' => new external_value(PARAM_TEXT, 'Transcriber'),
                    'turns' => new external_value(PARAM_TEXT, 'Turns', VALUE_OPTIONAL),
                    'avturn' => new external_value(PARAM_TEXT, 'AV Turn', VALUE_OPTIONAL),
                    'accuracy' => new external_value(PARAM_TEXT, 'Accuracy', VALUE_OPTIONAL),
                    'chatid' => new external_value(PARAM_INT, 'Chat ID', VALUE_OPTIONAL),
                    'filename' => new external_value(PARAM_TEXT, 'File name', VALUE_OPTIONAL),
                    'transcript' => new external_value(PARAM_TEXT, 'Transcript', VALUE_OPTIONAL),
                    'jsontranscript' => new external_value(PARAM_TEXT, 'JSON transcript', VALUE_OPTIONAL),
                    'selftranscript' => new external_value(PARAM_TEXT, 'Self Transcript', VALUE_OPTIONAL),
                    'words' => new external_value(PARAM_TEXT, 'Words', VALUE_OPTIONAL),
                     'uniquewords' => new external_value(PARAM_TEXT, 'Unique Words', VALUE_OPTIONAL),
                    'longwords' => new external_value(PARAM_TEXT, 'Long Words', VALUE_OPTIONAL),
                    'longestturn' => new external_value(PARAM_TEXT, 'Longest Turn', VALUE_OPTIONAL),
                    'targetwords' => new external_value(PARAM_TEXT, 'Target Words', VALUE_OPTIONAL),
                    'totaltargetwords' => new external_value(PARAM_TEXT, 'Total Target Words', VALUE_OPTIONAL),
                    'autogrammarscore' => new external_value(PARAM_TEXT, 'Grammar score', VALUE_OPTIONAL),
                    'autospellscore' => new external_value(PARAM_TEXT, 'Spelling score', VALUE_OPTIONAL),
                    'aiaccuracy' => new external_value(PARAM_TEXT, 'AI Accuracy', VALUE_OPTIONAL),
                    'grade' => new external_value(PARAM_TEXT, 'Grade', VALUE_OPTIONAL),
                    'remark' => new external_value(PARAM_TEXT, 'Remark', VALUE_OPTIONAL),
                    'feedback' => new external_value(PARAM_TEXT, 'Feedback', VALUE_OPTIONAL),
                ])
            )
        ]);
    }

    /**
     * Describes the parameters for submit_rubric_grade_form webservice.
     * @return external_function_parameters
     */
    public static function submit_rubric_grade_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create grade form, encoded as a json array'),
                'studentid' => new external_value(PARAM_INT, 'The id for the student', false),
                'cmid' => new external_value(PARAM_INT, 'The course module id for the item', false),
            )
        );
    }

    /**
     * Submit the rubric grade form.
     *
     * @param int $contextid The context id for the course.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @param $studentid
     * @param $cmid
     * @return int new grade id.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     */
    public static function submit_rubric_grade_form($contextid, $jsonformdata, $studentid, $cmid) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/solo/rubric_grade_form.php');
        require_once($CFG->dirroot . '/grade/grading/lib.php');
        require_once($CFG->dirroot . '/mod/solo/lib.php');

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_rubric_grade_form_parameters(),
            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        $sql = "select  pa.solo, pa.feedback, pa.id as attemptid
        from {" . constants::M_ATTEMPTSTABLE . "} pa
        inner join {" . constants::M_TABLE . "} pc on pa.solo = pc.id
        inner join {course_modules} cm on cm.instance = pc.id and pc.course = cm.course and pa.userid = ?
        where cm.id = ?";

        $modulecontext = context_module::instance($cmid);
        $attempt = $DB->get_record_sql($sql, array($studentid, $cmid));

        if (!$attempt) { return 0; }

        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id'=>$attempt->solo));
        $gradingdisabled=false;
        $gradinginstance = utils::get_grading_instance($attempt->attemptid, $gradingdisabled,$moduleinstance, $modulecontext);

        $mform = new \rubric_grade_form(null, array('gradinginstance' => $gradinginstance), 'post', '', null, true, $data);

        $validateddata = $mform->get_data();

        if ($validateddata) {
            // Insert rubric
            if (!empty($validateddata->advancedgrading['criteria'])) {
                $thegrade=null;
                if (!$gradingdisabled) {
                    if ($gradinginstance) {
                        $thegrade = $gradinginstance->submit_and_get_grade($validateddata->advancedgrading,
                            $attempt->attemptid);
                    }
                }
            }
            $feedbackobject = new \stdClass();
            $feedbackobject->id = $attempt->attemptid;
            $feedbackobject->feedback = $validateddata->feedback;
            $feedbackobject->manualgraded = 1;
            $feedbackobject->grade = $thegrade;
            $DB->update_record(constants::M_ATTEMPTSTABLE, $feedbackobject);
            $grade = new \stdClass();
            $grade->userid = $studentid;
            $grade->rawgrade = $thegrade;
            \solo_grade_item_update($moduleinstance,$grade);
        } else {
            // Generate a warning.
            throw new \moodle_exception('erroreditgroup', 'group');
        }

        return 1;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     * @since Moodle 3.0
     */
    public static function submit_rubric_grade_form_returns() {
        return new external_value(PARAM_INT, 'grade id');
    }


    //---------------
    /**
     * Describes the parameters for submit_simple_grade_form webservice.
     * @return external_function_parameters
     */
    public static function submit_simple_grade_form_parameters() {
        return new external_function_parameters(
                array(
                        'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                        'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create grade form, encoded as a json array'),
                        'studentid' => new external_value(PARAM_INT, 'The id for the student', false),
                        'cmid' => new external_value(PARAM_INT, 'The course module id for the item', false),
                )
        );
    }

    /**
     * Submit the rubric grade form.
     *
     * @param int $contextid The context id for the course.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @param $studentid
     * @param $cmid
     * @return int new grade id.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     */
    public static function submit_simple_grade_form($contextid, $jsonformdata, $studentid, $cmid) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/solo/simple_grade_form.php');
        require_once($CFG->dirroot . '/grade/grading/lib.php');
        require_once($CFG->dirroot . '/mod/solo/lib.php');

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_simple_grade_form_parameters(),
                ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        $sql = "select  pa.solo, pa.feedback, pa.id as attemptid
        from {" . constants::M_ATTEMPTSTABLE . "} pa
        inner join {" . constants::M_TABLE . "} pc on pa.solo = pc.id
        inner join {course_modules} cm on cm.instance = pc.id and pc.course = cm.course and pa.userid = ?
        where cm.id = ?";

        $attempt = $DB->get_record_sql($sql, array($studentid, $cmid));
        if (!$attempt) { return 0; }

        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id'=>$attempt->solo));

        $mform = new \simple_grade_form(null, array(), 'post', '', null, true, $data);

        $validateddata = $mform->get_data();

        if ($validateddata) {
            $feedbackobject = new \stdClass();
            $feedbackobject->id = $attempt->attemptid;
            $feedbackobject->feedback = $validateddata->feedback;
            $feedbackobject->manualgraded = 1;
            $feedbackobject->grade = $validateddata->grade;
            $DB->update_record('solo_attempts', $feedbackobject);
            $grade = new \stdClass();
            $grade->userid = $studentid;
            $grade->rawgrade = $validateddata->grade;
            \solo_grade_item_update($moduleinstance,$grade);
        } else {
            // Generate a warning.
            throw new \moodle_exception('erroreditgroup', 'group');
        }

        return 1;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     * @since Moodle 3.0
     */
    public static function submit_simple_grade_form_returns() {
        return new external_value(PARAM_INT, 'grade id');
    }



    public static function check_for_results_parameters() {
        return new external_function_parameters([
                'attemptid' => new external_value(PARAM_INT)
        ]);
    }

    public static function check_for_results($attemptid) {
        global $DB, $USER;
        //defaults
        $ret = ['ready'=>false];
        $have_humaneval = false;
        $have_aieval =false;

        $params = self::validate_parameters(self::check_for_results_parameters(),
                array('attemptid'=>$attemptid));

        //fetch attempt information
        $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE, array('userid' => $USER->id, 'id' => $attemptid));
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $attempt->solo), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(constants::M_MODNAME, $moduleinstance->id, $moduleinstance->courseid, false, MUST_EXIST);

        if($attempt) {
            $hastranscripts = !empty($attempt->jsontranscript);
            if ($hastranscripts) {
                $have_aieval = true;
            } else {
                $have_aieval= utils::transcripts_are_ready_on_s3($attempt);
            }
        }

        //if no results, thats that. return.
        if($have_aieval || $have_humaneval){
            $ret['ready']=true;
        }
        return json_encode($ret);
    }

    public static function check_for_results_returns() {
        return new external_value(PARAM_RAW);
    }

}
