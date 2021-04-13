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
 * Privacy Subsystem implementation for mod_readaloud.
 *
 * @package    mod_readaloud
 * @copyright  2018 Justin Hunt https://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_readaloud\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_readaloud\constants;

defined('MOODLE_INTERNAL') || die();

//3.3 user_provider not backported so we use this switch to avoid errors when using same codebase for 3.3 and higher
if (interface_exists('\core_privacy\local\request\core_userlist_provider')) {
    interface the_user_provider extends \core_privacy\local\request\core_userlist_provider {
    }
} else {
    interface the_user_provider {
    }

    ;
}

/**
 * Privacy Subsystem for mod_readaloud
 *
 * @copyright  2018 Justin Hunt https://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        // This plugin is a core_user_data_provider.
        \core_privacy\local\request\plugin\provider,
        //user provider 3.4 and above
        the_user_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Return meta data about this plugin.
     *
     * @param  collection $collection A list of information to add to.
     * @return collection Return the collection after adding to it.
     */
    public static function _get_metadata(collection $collection) {

        $userdetail = [
                'id' => 'privacy:metadata:attemptid',
                'readaloudid' => 'privacy:metadata:readaloudid',
                'userid' => 'privacy:metadata:userid',
                'filename' => 'privacy:metadata:filename',
                'wpm' => 'privacy:metadata:wpm',
                'accuracy' => 'privacy:metadata:accuracy',
                'sessionscore' => 'privacy:metadata:sessionscore',
                'sessiontime' => 'privacy:metadata:sessiontime',
                'sessionerrors' => 'privacy:metadata:sessionerrors',
                'sessionendword' => 'privacy:metadata:sessionendword',
                'errorcount' => 'privacy:metadata:errorcount',
                'timemodified' => 'privacy:metadata:timemodified'
        ];
        $collection->add_database_table(constants::M_USERTABLE, $userdetail, 'privacy:metadata:attempttable');

        $aidetail = [
                'attemptid' => 'privacy:metadata:attemptid',
                'readaloudid' => 'privacy:metadata:readaloudid',
                'wpm' => 'privacy:metadata:wpm',
                'accuracy' => 'privacy:metadata:accuracy',
                'sessionscore' => 'privacy:metadata:sessionscore',
                'sessiontime' => 'privacy:metadata:sessiontime',
                'sessionerrors' => 'privacy:metadata:sessionerrors',
                'sessionendword' => 'privacy:metadata:sessionendword',
                'errorcount' => 'privacy:metadata:errorcount',
                'transcript' => 'privacy:metadata:transcriptpurpose',
                'fulltranscript' => 'privacy:metadata:fulltranscriptpurpose',
                'timemodified' => 'privacy:metadata:timemodified'
        ];
        $collection->add_database_table(constants::M_AITABLE, $aidetail, 'privacy:metadata:aitable');

        $collection->add_external_location_link('cloud.poodll.com', [
                'userid' => 'privacy:metadata:cloudpoodllcom:userid'
        ], 'privacy:metadata:cloudpoodllcom');
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function _get_contexts_for_userid($userid) {

        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {" . constants::M_TABLE . "} actt ON actt.id = cm.instance
            INNER JOIN {" . constants::M_USERTABLE . "} usert ON usert.readaloudid = actt.id
                 WHERE usert.userid = :theuserid";
        $params = [
                'contextlevel' => CONTEXT_MODULE,
                'modname' => constants::M_MODNAME,
                'theuserid' => $userid
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     *
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        // Find users with glossary entries.
        $sql = "SELECT usert.userid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN  {" . constants::M_TABLE . "} actt ON actt.id = cm.instance
                  JOIN {" . constants::M_USERTABLE . "} usert ON usert.readaloudid = actt.id
                 WHERE c.id = :contextid";

        $params = [
                'contextid' => $context->id,
                'contextlevel' => CONTEXT_MODULE,
                'modname' => constants::M_MODNAME,
        ];

        $userlist->add_from_sql('userid', $sql, $params);

    }

    /**
     * Export personal data for the given approved_contextlist.
     *
     * User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT usert.id as attemptid,
                       cm.id AS cmid,
                       usert.userid AS userid,
                       usert.wpm,
                       usert.accuracy,
                       usert.sessionscore,
                       usert.sessiontime,
                       usert.sessionerrors,
                       usert.sessionendword,
                       usert.sessionscore,
                       usert.errorcount,
                       usert.filename,
                       ait.transcript,
                       ait.fulltranscript
                       usert.timemodified
                  FROM {" . constants::M_USERTABLE . "} usert
                  JOIN {" . constants::M_TABLE . "} actt ON usert.readaloudid = actt.id
                  JOIN {" . constants::M_AITABLE . "} ait ON usert.id = ait.attemptid
                  JOIN {course_modules} cm ON actt.id = cm.instance
                  JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                  JOIN {context} c ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                 WHERE c.id {$contextsql}
                   AND usert.userid = :userid
               ORDER BY usert.id, cm.id";
        $params = [
                        'userid' => $user->id,
                        'modulename' => constants::M_MODNAME,
                        'contextlevel' => CONTEXT_MODULE
                ] + $contextparams;

        $attempts = $DB->get_recordset_sql($sql, $params);

        foreach ($attempts as $attempt) {
            $attempt->timemodified = \core_privacy\local\request\transform::datetime($attempt->timemodified);
            $context = \context_module::instance($attempt->cmid);
            self::export_attempt_data_for_user($attempt, $context, $user);
        }
        $attempts->close();
    }

    /**
     * Export the supplied personal data for a single readaloud attempt along with any generic data or area files.
     *
     * @param array $attemptdata the personal data to export
     * @param \context_module $context the context of the readaloud.
     * @param \stdClass $user the user record
     */
    protected static function export_attempt_data_for_user(array $attemptdata, \context_module $context, \stdClass $user) {
        // Fetch the generic module data for the choice.
        $contextdata = helper::get_context_data($context, $user);

        // Merge with choice data and write it.
        $contextdata = (object) array_merge((array) $contextdata, $attemptdata);
        writer::with_context($context)->export_data([], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        if (!$cm = get_coursemodule_from_id(constants::M_MODNAME, $context->instanceid)) {
            return;
        }

        $instanceid = $cm->instance;

        $attempts = $DB->get_records(constants::M_USERTABLE, ['readaloudid' => $instanceid], '', 'id');

        // Delete AI data
        $DB->delete_records_list(constants::M_AITABLE, 'attemptid', array_keys($attempts));

        // Now delete all attempts
        $DB->delete_records(constants::M_USERTABLE, ['readaloudid' => $instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {

                $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

                $entries = $DB->get_records(constants::M_USERTABLE, ['readaloudid' => $instanceid, 'userid' => $userid],
                        '', 'id');

                if (!$entries) {
                    continue;
                }

                list($insql, $inparams) = $DB->get_in_or_equal(array_keys($entries), SQL_PARAMS_NAMED);
                // Delete related entry aliases.
                $DB->delete_records_list(constants::M_AITABLE, 'attemptid', array_keys($entries));

                // Now delete all user related entries.
                $DB->delete_records(constants::M_USERTABLE, ['readaloudid' => $instanceid, 'userid' => $userid]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();
        $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
        list($userinsql, $userinparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $attemptswhere = "readaloudid = :instanceid AND userid {$userinsql}";
        $userinstanceparams = $userinparams + ['instanceid' => $instanceid];

        $attemptsset = $DB->get_recordset_select(constants::M_USERTABLE, $attemptswhere, $userinstanceparams, 'id', 'id');
        $attempts = [];

        foreach ($attemptsset as $attempt) {
            $attempts[] = $attempt->id;
        }

        $attemptsset->close();

        if (!$attempts) {
            return;
        }

        // Delete related entry aliases.
        $DB->delete_records_list(constants::M_AITABLE, 'attemptid', $attempts);

        // Now delete all AI attempt evals.
        $deletewhere = "readaloudid = :instanceid AND userid {$userinsql}";
        $DB->delete_records_select(constants::M_USERTABLE, $deletewhere, $userinstanceparams);
    }
}
