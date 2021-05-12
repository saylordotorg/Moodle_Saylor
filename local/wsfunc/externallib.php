<?php
 
/**
 * local_wsfunc external lib file
 *
 *
 * @package    local_wsfunc
 * @copyright  2016 SaylorAcademy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
 
class local_wsfunc_external extends external_api {
 
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_visible_courses_parameters() {
        // get_visible_courses_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
            array('cat' => new external_value(PARAM_INT, "Category ID: Get visible courses in the category. (Note: This is recursive and will get courses in sub-categories)", VALUE_DEFAULT, 0))
            );
    }
 
    /**
     * The function itself
     * @return string welcome message
     */
    public static function get_visible_courses($cat) {

        //Parameters validation
        $params = self::validate_parameters(self::get_visible_courses_parameters(),
                array('cat' => $cat));

        $options['recursive'] = true;
        $options['coursecontacts'] = false;
        $options['summary'] = true;
        $options['sort']['idnumber'] = 1;
 
        
        $courselist = \core_course_category::get($params['cat'])->get_courses($options);

        //Note: don't forget to validate the context and check capabilities
        // $context = context_course::instance($course->id, IGNORE_MISSING);
        //     $courseformatoptions = course_get_format($course)->get_format_options();
        //     try {
        //         self::validate_context($context);
        //     } catch (Exception $e) {
        //         $exceptionparam = new stdClass();
        //         $exceptionparam->message = $e->getMessage();
        //         $exceptionparam->courseid = $course->id;
        //         throw new moodle_exception('errorcoursecontextnotvalid', 'webservice', '', $exceptionparam);
        //     }
        //     require_capability('moodle/course:view', $context);
        foreach ($courselist as $course) {
                $id = $course->__get('id');
                $category = $course->__get('category');
                $shortname = $course->__get('shortname');
                $fullname = $course->__get('fullname');
                $startdate = $course->__get('startdate');
                $summary = $course->__get('summary');

                $courses[$id] = array(
                        'id' => $id,
                        'category' => $category,
                        'shortname' => $shortname,
                        'fullname' => $fullname,
                        'startdate' => $startdate,
                        'summary' => $summary
                        );
        }
        ksort($courses);
        $result['courses'] = $courses;
        return $result;
    }
 
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_visible_courses_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure( new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'course id'),
                        'category' => new external_value(PARAM_INT, 'category id'),
                        'shortname' => new external_value(PARAM_TEXT, 'course shortname'),
                        'fullname' => new external_value(PARAM_RAW, 'course fullname'),
                        'startdate' => new external_value(PARAM_ALPHANUM, 'course startdate', VALUE_OPTIONAL),
                        'summary' => new external_value(PARAM_RAW, 'course summary', VALUE_OPTIONAL),
                    ), 'information about one course')
                )  
            )
        );
    }

     /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function get_grades_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'component' => new external_value(
                    PARAM_COMPONENT, 'A component, for example mod_forum or mod_quiz', VALUE_DEFAULT, ''),
                'activityid' => new external_value(PARAM_INT, 'The activity ID', VALUE_DEFAULT, null),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user ID'),
                    'An array of user IDs, leave empty to just retrieve grade item information', VALUE_DEFAULT, array()
                )
            )
        );
    }
    
    /**
     * Returns student course total grade and grades for activities.
     * This function does not return category or manual items.
     *
     * @param  int $courseid        Course id
     * @param  string $component    Component name
     * @param  int $activityid      Activity id
     * @param  array  $userids      Array of user ids
     * @return array                Array of grades
     * @since Moodle 2.7
     */
    public static function get_grades($courseid, $component = null, $activityid = null, $userids = array()) {
        global $CFG, $USER, $DB;
        require_once("$CFG->libdir/externallib.php");
        require_once("$CFG->libdir/gradelib.php");
        require_once("$CFG->dirroot/grade/querylib.php");

        $params = self::validate_parameters(self::get_grades_parameters(),
            array('courseid' => $courseid, 'component' => $component, 'activityid' => $activityid, 'userids' => $userids));

        $gradesarray = array(
            'items'     => array(),
            'outcomes'  => array()
        );

        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        $access = false;
        if (has_capability('moodle/grade:viewall', $coursecontext)) {
            // Can view all user's grades in this course.
            $access = true;

        } else if (count($params['userids']) == 1) {
                // If non-admin (or don't have permission to view all grades in course context) can only supply one userid.
            if ($params['userids'][0] == $USER->id and has_capability('moodle/grade:view', $coursecontext)) {
                // Student can view their own grades in this course.
                $access = true;

            } else if (has_capability('moodle/grade:viewall', context_user::instance($params['userids'][0]))) {
                // User can view the grades of this user. Parent most probably.
                $access = true;
            }
        }
        if (!$access) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error');
        }

        $itemtype = null;
        $itemmodule = null;
        $iteminstance = null;

        if (!empty($params['component'])) {
            list($itemtype, $itemmodule) = normalize_component($params['component']);
        }

        $cm = null;
        if (!empty($itemmodule) && !empty($params['activityid'])) {
            if (!$cm = get_coursemodule_from_id($itemmodule, $params['activityid'])) {
                throw new moodle_exception('invalidcoursemodule');
            }
            $iteminstance = $cm->instance;
        }

        // Load all the module info.
        $modinfo = get_fast_modinfo($params['courseid']);
        $activityinstances = $modinfo->get_instances();

        $gradeparams = array('courseid' => $params['courseid']);
        if (!empty($itemtype)) {
            $gradeparams['itemtype'] = $itemtype;
        }
        if (!empty($itemmodule)) {
            $gradeparams['itemmodule'] = $itemmodule;
        }
        if (!empty($iteminstance)) {
            $gradeparams['iteminstance'] = $iteminstance;
        }

        if ($activitygrades = grade_item::fetch_all($gradeparams)) {
            $canviewhidden = has_capability('moodle/grade:viewhidden', context_course::instance($params['courseid']));
            foreach ($activitygrades as $activitygrade) {

                if ($activitygrade->itemtype != 'course' and $activitygrade->itemtype != 'mod') {
                    // This function currently only supports course and mod grade items. Manual and category not supported.
                    continue;
                }

                $context = $coursecontext;

                if ($activitygrade->itemtype == 'course') {
                    $item = grade_get_course_grades($course->id, $params['userids']);
                    $item->itemnumber = 0;

                    $grades = new stdClass;
                    $grades->items = array($item);
                    $grades->outcomes = array();

                } else {
                    $cm = $activityinstances[$activitygrade->itemmodule][$activitygrade->iteminstance];
                    $instance = $cm->instance;
                    $context = context_module::instance($cm->id, IGNORE_MISSING);

                    $grades = grade_get_grades($params['courseid'], $activitygrade->itemtype,
                                                $activitygrade->itemmodule, $instance, $params['userids']);
                }

                // Convert from objects to arrays so all web service clients are supported.
                // While we're doing that we also remove grades the current user can't see due to hiding.
                foreach ($grades->items as $gradeitem) {
                    // Switch the stdClass instance for a grade item instance so we can call is_hidden() and use the ID.
                    $gradeiteminstance = self::get_grade_item(
                        $course->id, $activitygrade->itemtype, $activitygrade->itemmodule, $activitygrade->iteminstance, 0);
                    if (!$canviewhidden && $gradeiteminstance->is_hidden()) {
                        continue;
                    }

                    // Format mixed bool/integer parameters.
                    $gradeitem->hidden = (empty($gradeitem->hidden)) ? 0 : $gradeitem->hidden;
                    $gradeitem->locked = (empty($gradeitem->locked)) ? 0 : $gradeitem->locked;

                    $gradeitemarray = (array)$gradeitem;
                    $gradeitemarray['grades'] = array();

                    if (!empty($gradeitem->grades)) {
                        foreach ($gradeitem->grades as $studentid => $studentgrade) {
                            if (!$canviewhidden) {
                                // Need to load the grade_grade object to check visibility.
                                $gradegradeinstance = grade_grade::fetch(
                                    array(
                                        'userid' => $studentid,
                                        'itemid' => $gradeiteminstance->id
                                    )
                                );
                                // The grade grade may be legitimately missing if the student has no grade.
                                if (!empty($gradegradeinstance) && $gradegradeinstance->is_hidden()) {
                                    continue;
                                }
                            }

                            // Format mixed bool/integer parameters.
                            $studentgrade->hidden = (empty($studentgrade->hidden)) ? 0 : $studentgrade->hidden;
                            $studentgrade->locked = (empty($studentgrade->locked)) ? 0 : $studentgrade->locked;
                            $studentgrade->overridden = (empty($studentgrade->overridden)) ? 0 : $studentgrade->overridden;

                            if ($gradeiteminstance->itemtype != 'course' and !empty($studentgrade->feedback)) {
                                list($studentgrade->feedback, $studentgrade->feedbackformat) =
                                    external_format_text($studentgrade->feedback, $studentgrade->feedbackformat,
                                    $context->id, $params['component'], 'feedback', null);
                            }

                            $gradeitemarray['grades'][$studentid] = (array)$studentgrade;
                            // Add the student ID as some WS clients can't access the array key.
                            $gradeitemarray['grades'][$studentid]['userid'] = $studentid;
                        }
                    }

                    if ($gradeiteminstance->itemtype == 'course') {
                        $gradesarray['items']['course'] = $gradeitemarray;
                        $gradesarray['items']['course']['activityid'] = 'course';
                    } else {
                        $gradesarray['items'][$cm->id] = $gradeitemarray;
                        // Add the activity ID as some WS clients can't access the array key.
                        $gradesarray['items'][$cm->id]['activityid'] = $cm->id;
                    }
                }

                foreach ($grades->outcomes as $outcome) {
                    // Format mixed bool/integer parameters.
                    $outcome->hidden = (empty($outcome->hidden)) ? 0 : $outcome->hidden;
                    $outcome->locked = (empty($outcome->locked)) ? 0 : $outcome->locked;

                    $gradesarray['outcomes'][$cm->id] = (array)$outcome;
                    $gradesarray['outcomes'][$cm->id]['activityid'] = $cm->id;

                    $gradesarray['outcomes'][$cm->id]['grades'] = array();
                    if (!empty($outcome->grades)) {
                        foreach ($outcome->grades as $studentid => $studentgrade) {
                            if (!$canviewhidden) {
                                // Need to load the grade_grade object to check visibility.
                                $gradeiteminstance = self::get_grade_item($course->id, $activitygrade->itemtype,
                                                                           $activitygrade->itemmodule, $activitygrade->iteminstance,
                                                                           $activitygrade->itemnumber);
                                $gradegradeinstance = grade_grade::fetch(
                                    array(
                                        'userid' => $studentid,
                                        'itemid' => $gradeiteminstance->id
                                    )
                                );
                                // The grade grade may be legitimately missing if the student has no grade.
                                if (!empty($gradegradeinstance ) && $gradegradeinstance->is_hidden()) {
                                    continue;
                                }
                            }

                            // Format mixed bool/integer parameters.
                            $studentgrade->hidden = (empty($studentgrade->hidden)) ? 0 : $studentgrade->hidden;
                            $studentgrade->locked = (empty($studentgrade->locked)) ? 0 : $studentgrade->locked;

                            if (!empty($studentgrade->feedback)) {
                                list($studentgrade->feedback, $studentgrade->feedbackformat) =
                                    external_format_text($studentgrade->feedback, $studentgrade->feedbackformat,
                                    $context->id, $params['component'], 'feedback', null);
                            }

                            $gradesarray['outcomes'][$cm->id]['grades'][$studentid] = (array)$studentgrade;

                            // Add the student ID into the grade structure as some WS clients can't access the key.
                            $gradesarray['outcomes'][$cm->id]['grades'][$studentid]['userid'] = $studentid;
                        }
                    }
                }
            }
        }

        return $gradesarray;
    }

    /**
     * Get a grade item
     * @param  int $courseid        Course id
     * @param  string $itemtype     Item type
     * @param  string $itemmodule   Item module
     * @param  int $iteminstance    Item instance
     * @param  int $itemnumber      Item number
     * @return grade_item           A grade_item instance
     */
    private static function get_grade_item($courseid, $itemtype, $itemmodule = null, $iteminstance = null, $itemnumber = null) {
        $gradeiteminstance = null;
        if ($itemtype == 'course') {
            $gradeiteminstance = grade_item::fetch(array('courseid' => $courseid, 'itemtype' => $itemtype));
        } else {
            $gradeiteminstance = grade_item::fetch(
                array('courseid' => $courseid, 'itemtype' => $itemtype,
                    'itemmodule' => $itemmodule, 'iteminstance' => $iteminstance, 'itemnumber' => $itemnumber));
        }
        return $gradeiteminstance;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function get_grades_returns() {
        return new external_single_structure(
            array(
                'items'  => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'activityid' => new external_value(
                                PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'itemnumber'  => new external_value(PARAM_INT, 'Will be 0 unless the module has multiple grades'),
                            'scaleid' => new external_value(PARAM_INT, 'The ID of the custom scale or 0'),
                            'name' => new external_value(PARAM_RAW, 'The module name'),
                            'grademin' => new external_value(PARAM_FLOAT, 'Minimum grade'),
                            'grademax' => new external_value(PARAM_FLOAT, 'Maximum grade'),
                            'gradepass' => new external_value(PARAM_FLOAT, 'The passing grade threshold'),
                            'locked' => new external_value(PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                            'hidden' => new external_value(PARAM_INT, '0 means not hidden, > 1 is a date to hide until'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'userid' => new external_value(
                                            PARAM_INT, 'Student ID'),
                                        'grade' => new external_value(
                                            PARAM_FLOAT, 'Student grade'),
                                        'locked' => new external_value(
                                            PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                                        'hidden' => new external_value(
                                            PARAM_INT, '0 means not hidden, 1 hidden, > 1 is a date to hide until'),
                                        'overridden' => new external_value(
                                            PARAM_INT, '0 means not overridden, > 1 means overridden'),
                                        'feedback' => new external_value(
                                            PARAM_RAW, 'Feedback from the grader'),
                                        'feedbackformat' => new external_value(
                                            PARAM_INT, 'The format of the feedback'),
                                        'usermodified' => new external_value(
                                            PARAM_INT, 'The ID of the last user to modify this student grade'),
                                        'datesubmitted' => new external_value(
                                            PARAM_INT, 'A timestamp indicating when the student submitted the activity'),
                                        'dategraded' => new external_value(
                                            PARAM_INT, 'A timestamp indicating when the assignment was grades'),
                                        'str_grade' => new external_value(
                                            PARAM_RAW, 'A string representation of the grade'),
                                        'str_long_grade' => new external_value(
                                            PARAM_RAW, 'A nicely formatted string representation of the grade'),
                                        'str_feedback' => new external_value(
                                            PARAM_RAW, 'A formatted string representation of the feedback from the grader'),
                                    )
                                )
                            ),
                        )
                    )
                ),
                'outcomes'  => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'activityid' => new external_value(
                                PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'itemnumber'  => new external_value(PARAM_INT, 'Will be 0 unless the module has multiple grades'),
                            'scaleid' => new external_value(PARAM_INT, 'The ID of the custom scale or 0'),
                            'name' => new external_value(PARAM_RAW, 'The module name'),
                            'locked' => new external_value(PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                            'hidden' => new external_value(PARAM_INT, '0 means not hidden, > 1 is a date to hide until'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'userid' => new external_value(
                                            PARAM_INT, 'Student ID'),
                                        'grade' => new external_value(
                                            PARAM_FLOAT, 'Student grade'),
                                        'locked' => new external_value(
                                            PARAM_INT, '0 means not locked, > 1 is a date to lock until'),
                                        'hidden' => new external_value(
                                            PARAM_INT, '0 means not hidden, 1 hidden, > 1 is a date to hide until'),
                                        'feedback' => new external_value(
                                            PARAM_RAW, 'Feedback from the grader'),
                                        'feedbackformat' => new external_value(
                                            PARAM_INT, 'The feedback format'),
                                        'usermodified' => new external_value(
                                            PARAM_INT, 'The ID of the last user to modify this student grade'),
                                        'str_grade' => new external_value(
                                            PARAM_RAW, 'A string representation of the grade'),
                                        'str_feedback' => new external_value(
                                            PARAM_RAW, 'A formatted string representation of the feedback from the grader'),
                                    )
                                )
                            ),
                        )
                    ), 'An array of outcomes associated with the grade items', VALUE_OPTIONAL
                )
            )
        );

    }
 
     /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_users_courses_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'user id'),
            )
        );
    }

    /**
     * Get list of courses user is enrolled in (only active enrolments are returned).
     * Please note the current user must be able to access the course and have the moodle/course:view capability for the specified user, otherwise the course is not included.
     *
     * @param int $userid
     * @return array of courses
     */
    public static function get_users_courses($userid) {
        global $CFG, $USER, $DB;

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::get_users_courses_parameters(), array('userid'=>$userid));

        $courses = enrol_get_users_courses($params['userid'], true, 'id, shortname, fullname, idnumber, visible,
                   summary, summaryformat, format, showgrades, lang, enablecompletion');
        $result = array();

        foreach ($courses as $course) {
            $context = context_course::instance($course->id, IGNORE_MISSING);
            try {
                self::validate_context($context);
            } catch (Exception $e) {
                // current user can not access this course, sorry we can not disclose who is enrolled in this course!
                continue;
            }

            if ($userid != $USER->id and !(has_capability('moodle/course:view', context_user::instance($params['userid'])) or has_capability('moodle/course:viewparticipants', $context))) {
                // we need capability to view participants in course (in the course context) or view student's courses (in the user context)
                continue;
            }

            if (has_capability('moodle/course:viewparticipants', $context)) {
                list($enrolledsqlselect, $enrolledparams) = get_enrolled_sql($context);
                $enrolledsql = "SELECT COUNT('x') FROM ($enrolledsqlselect) enrolleduserids";
                $enrolledusercount = $DB->count_records_sql($enrolledsql, $enrolledparams);
            }
            else {
                $enrolledusercount = 0;
            }


            list($course->summary, $course->summaryformat) =
                external_format_text($course->summary, $course->summaryformat, $context->id, 'course', 'summary', null);

            $result[$course->id] = array('id' => $course->id, 'shortname' => $course->shortname, 'fullname' => $course->fullname,
                'idnumber' => $course->idnumber, 'visible' => $course->visible, 'enrolledusercount' => $enrolledusercount,
                'summary' => $course->summary, 'summaryformat' => $course->summaryformat, 'format' => $course->format,
                'showgrades' => $course->showgrades, 'lang' => $course->lang, 'enablecompletion' => $course->enablecompletion
                );

            if ($userid != $USER->id and !(has_capability('moodle/user:viewhiddendetails', context_user::instance($params['userid'])) or has_capability('moodle/user:viewhiddendetails', $context))) {
                // Check capabilities. If this is not the user getting course enrollment info for themselves, they have to have moodle/user:viewhiddendetails in the context of the user they are asking about or hidden details for users in a course context (teachers)
                continue;
            }
            else {
                // Get the enrollment dates
                $enrolldatesql = "SELECT ue.id, ue.timestart, ue.timeend, ue.timecreated 
                    FROM {$CFG->prefix}user_enrolments ue
                    JOIN {$CFG->prefix}enrol e on ue.enrolid = e.id 
                    WHERE ue.userid = {$userid} AND e.courseid = {$course->id}";
                
                $enrolldaterecords = $DB->get_records_sql($enrolldatesql);

                unset($enrolldate);
                foreach ($enrolldaterecords as $eid => $record) {
                    // This should grab the latest enrollment record - some users have two enrollment records for some courses in our table; the old one does not have the proper timestart value. 
                    $enrolldate['timestart'] = $enrolldaterecords[$eid]->timestart;
                    $enrolldate['timeend'] = $enrolldaterecords[$eid]->timeend;
                    $enrolldate['timecreated'] = $enrolldaterecords[$eid]->timecreated;

                    break; // <- Hack to get the array value with the highest enrolment id (the array key).
                }

                // Add the enrollment times to $result
                $result[$course->id]['enroltimestart'] = $enrolldate['timestart'];
                $result[$course->id]['enroltimeend'] = $enrolldate['timeend'];
                $result[$course->id]['enroltimecreated'] = $enrolldate['timecreated'];
            }

            

        }

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_users_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of course'),
                    'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'  => new external_value(PARAM_RAW, 'long name of course'),
                    'enrolledusercount' => new external_value(PARAM_INT, 'Number of enrolled users in this course'),
                    'idnumber'  => new external_value(PARAM_RAW, 'id number of course'),
                    'visible'   => new external_value(PARAM_INT, '1 means visible, 0 means hidden course'),
                    'summary'   => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                    'summaryformat' => new external_format_value('summary', VALUE_OPTIONAL),
                    'format'    => new external_value(PARAM_PLUGIN, 'course format: weeks, topics, social, site', VALUE_OPTIONAL),
                    'showgrades' => new external_value(PARAM_BOOL, 'true if grades are shown, otherwise false', VALUE_OPTIONAL),
                    'lang'      => new external_value(PARAM_LANG, 'forced course language', VALUE_OPTIONAL),
                    'enablecompletion' => new external_value(PARAM_BOOL, 'true if completion is enabled, otherwise false',
                                                                VALUE_OPTIONAL),
                    'enroltimestart' => new external_value(PARAM_INT, 'time active enrolment began', VALUE_OPTIONAL),
                    'enroltimeend' => new external_value(PARAM_INT, 'time of active enrolment end', VALUE_OPTIONAL),
                    'enroltimecreated' => new external_value(PARAM_INT, 'time the enrolment record was created', VALUE_OPTIONAL)
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_users_lastaccess_parameters() {
        // get_users_lastaccess_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
            array('userid' => new external_value(PARAM_INT, "User ID to check last access.", VALUE_DEFAULT, 0))
            );
    }

    /**
     * Get the last access time for the specified user.
     *
     * @param int $userid
     * @return int timestamp of lastaccess
     */
    public static function get_users_lastaccess($userid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/user/lib.php");
        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::get_users_lastaccess_parameters(), array('userid'=>$userid));

        // Check capability to view user details in user context
        if (!has_capability('moodle/user:viewhiddendetails', context_user::instance($userid))) {
            throw new moodle_exception('nopermissions', 'error', '', 'You do not have moodle/user:viewhiddendetails for this user!');
        }

        // Get user record
        $user = $DB->get_record('user', array('id'=>$params['userid']), '*', MUST_EXIST);

        // Get user details based on record
        $userdetailfields = array();
        $userdetailfields[] = 'lastaccess';

        $userdetails = user_get_user_details($user, null, $userdetailfields);

        // Pick out last access
        if (isset($userdetails['lastaccess'])) {
            $lastaccess['lastaccess'] = $userdetails['lastaccess'];
        }
        else {
            throw new moodle_exception('missingfield', 'error', '', 'lastaccess');
        }

        // Return lastaccess
        return $lastaccess;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_users_lastaccess_returns() {
        return new external_single_structure(
            array('lastaccess' => new external_value(PARAM_INT, 'Last access time'))
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function programpartner_get_users_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Get user information for users in cohorts assigned via cohort roles.
     *
     * 
     * @return array An array of arrays containing user profiles.
     */
    public static function programpartner_get_users() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . "/cohort/lib.php");
        require_once($CFG->dirroot . "/lib/classes/user.php");

        $cohorts = array();
        $cohortmemberid = array();
        $users = array();

        // Get cohortrole records for the user accessing the API.
        $records = $DB->get_records('tool_cohortroles', array('userid' => $USER->id));
        // Create a list of cohorts the accessing user has been some sort of role to.
        foreach ($records as $record) {
            $cohorts[] = $record->cohortid;
        }

        // Now get a list of userids in each of these cohorts.
        foreach ($cohorts as $cohortid) {
            // Get users in the cohort.
            $cohortmembers = $DB->get_records('cohort_members', array('cohortid' => $cohortid));

            foreach ($cohortmembers as $cohortmember) {
                // Add the userids to a list.
                $cohortmemberid[] = $cohortmember->userid;
            }
        }

        // Get user objects for all userids that were previously found.
        foreach ($cohortmemberid as $userid) {
            $user = core_user::get_user($userid);
            if ($user !== false) {
                $users[] = $user;
            }
        }

        // Finally retrieve each users information.
        $returnedusers = array();
        foreach ($users as $user) {
            $userdetails = user_get_user_details_courses($user);

            // Return the user only if details are returned.
            // Otherwise it means that the $USER was not allowed to search the returned user.
            if (!empty($userdetails)) {
                $returnedusers[] = $userdetails;
            }
        }

        return $returnedusers;

    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function programpartner_get_users_returns() {
        global $CFG;
        require_once($CFG->dirroot . "/user/externallib.php");
        return new external_multiple_structure(core_user_external::user_description());
    }
 
}