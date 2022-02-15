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
 * Privacy Subsystem implementation for mod_facetoface.
 *
 * @package    mod_facetoface
 * @copyright  2018 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_facetoface\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Implementation of the privacy subsystem plugin provider for the facetoface activity module.
 *
 * @copyright  2018 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin stores personal data.
    \core_privacy\local\metadata\provider,

    // This plugin deals with user lists
    \core_privacy\local\request\core_userlist_provider,

    // This plugin is a core_user_data_provider.
    \core_privacy\local\request\plugin\provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'facetoface_signups',
            [
                'id'        => 'privacy:metadata:facetoface_signups:id',
                'sessionid' => 'privacy:metadata:facetoface_signups:sessionid',
                'userid' => 'privacy:metadata:userid',
                'mailedreminder' => 'privacy:metadata:facetoface_signups:mailedreminder',
                'discountcode' => 'privacy:metadata:facetoface_signups:discountcode',
                'notificationtype' => 'privacy:metadata:facetoface_signups:notificationtype',
            ],
            'privacy:metadata:facetoface_signups'
        );

        $collection->add_database_table(
            'facetoface_signups_status',
            [
                'signupid' => 'privacy:metadata:facetoface_signups_status:signupid',
                'statuscode' => 'privacy:metadata:facetoface_signups_status:statuscode',
                'grade' => 'privacy:metadata:facetoface_signups_status:grade',
                'note' => 'privacy:metadata:facetoface_signups_status:note',
                'timecreated' => 'privacy:metadata:facetoface_signups_status:timecreated',
            ],
            'privacy:metadata:facetoface_signups_status'
        );

        $collection->add_database_table(
            'facetoface_session_roles',
            [
                'userid' => 'privacy:metadata:userid',
                'roleid' => 'privacy:metadata:roleid',
            ],
            'privacy:metadata:facetoface_session_roles'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        // Fetch all facetoface contexts with userdata.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {facetoface} f ON f.id = cm.instance
            INNER JOIN {facetoface_sessions} fs ON fs.facetoface = f.id
            INNER JOIN {facetoface_signups} fsi ON fsi.sessionid = fs.id
                 WHERE fsi.userid = :userid";

        $params = [
            'modname'       => 'facetoface',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Remove contexts different from COURSE_MODULE.
        $contexts = array_reduce($contextlist->get_contexts(), function($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->id;
            }
            return $carry;
        }, []);

        if (empty($contexts)) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        list($insql, $inparams) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED);

        // Get Facetofacesessions.
        $sql = "SELECT ss.id,
                       ss.sessionid,
                       ss.mailedreminder,
                       ss.discountcode,
                       ss.notificationtype,
                       fss.statuscode,
                       fss.grade,
                       fss.note,
                       fss.timecreated,
                       ctx.id as contextid
                  FROM {facetoface_signups} ss
                  JOIN {facetoface_sessions} s ON s.id = ss.sessionid
                  JOIN {facetoface} f ON f.id = s.facetoface
                  JOIN {course_modules} cm ON cm.instance = f.id
                  JOIN {context} ctx ON ctx.instanceid = cm.id
             LEFT JOIN {facetoface_signups_status} fss ON fss.signupid = ss.id
                 WHERE ctx.id $insql
                   AND ss.userid = :userid";
        $params = array_merge($inparams, ['userid' => $userid]);

        $signups = [];
        $signupstatus = [];
        $sessions = $DB->get_recordset_sql($sql, $params);
        foreach ($sessions as $session) {
            if (empty($signups[$session->contextid][$session->id])) {
                if ($session->mailedreminder > 100) { // Mailed reminder uses magic numbers or timestamp.
                    $session->mailedreminder = transform::datetime($session->mailedreminder);
                }
                $signups[$session->contextid][$session->id] = (object)[
                    'id' => $session->id,
                    'sessionid' => $session->sessionid,
                    'mailedreminder' => $session->mailedreminder,
                ];
            }
            $signupstatus[$session->contextid][$session->id][] = (object)[
                'statuscode' => $session->id,
                'grade' => $session->grade,
                'note' => $session->note,
                'timecreated' => transform::datetime($session->timecreated),
            ];
        }
        $sessions->close();

        array_walk($signups, function($data, $contextid) {
            $context = \context::instance_by_id($contextid);
            writer::with_context($context)->export_related_data(
                [],
                'sessions',
                (object)['signups' => $data]
            );
        });

        array_walk($signupstatus, function($data, $contextid) {
            $context = \context::instance_by_id($contextid);
            array_walk($data, function($data, $attempt) use ($context) {
                writer::with_context($context)->export_related_data(
                    [],
                    'signupstatus',
                    (object)['status' => $data]
                );
            });
        });

        // Get Facetofaceroles.
        $sql = "SELECT sr.roleid,
                       r.shortname,
                       ctx.id as contextid
                  FROM {facetoface_session_roles} sr
                  JOIN {role} r on r.id = sr.roleid
                  JOIN {facetoface_sessions} s ON s.id = sr.sessionid
                  JOIN {facetoface} f ON f.id = s.facetoface
                  JOIN {course_modules} cm ON cm.instance = f.id
                  JOIN {context} ctx ON ctx.instanceid = cm.id
                 WHERE ctx.id $insql
                   AND sr.userid = :userid";
        $params = array_merge($inparams, ['userid' => $userid]);
        $roles = $DB->get_recordset_sql($sql, $params);
        foreach ($roles as $role) {
            $context = \context::instance_by_id($role->contextid);
            writer::with_context($context)->export_related_data(
                [],
                'trainer',
                (object)['role' => $role->shortname]
            );
        }
        $roles->close();
    }



    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('facetoface', $context->instanceid)) {
            $params = array('fid' => $cm->instance);
            $f2fselect = "IN (SELECT s.id FROM {facetoface_sessions} s
                                JOIN {facetoface} f ON f.id = s.facetoface
                                WHERE f.id = :fid)";

            $transaction = $DB->start_delegated_transaction();
            $DB->delete_records_select('facetoface_signups_status',
                'signupid IN (SELECT id FROM {facetoface_signups} WHERE sessionid ' . $f2fselect . ')', $params);
            $DB->delete_records_select('facetoface_signups', 'sessionid ' . $f2fselect, $params);
            $DB->delete_records_select('facetoface_session_roles', 'sessionid ' . $f2fselect, $params);

            $transaction->allow_commit();
        }

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

            if (!$context instanceof \context_module) {
                return;
            }

            if ($cm = get_coursemodule_from_id('facetoface', $context->instanceid)) {
                $params = array('userid' => $userid, 'fid' => $cm->instance);
                $f2fselect = "IN (SELECT s.id FROM {facetoface_sessions} s
                                JOIN {facetoface} f ON f.id = s.facetoface
                                WHERE f.id = :fid)";

                $transaction = $DB->start_delegated_transaction();
                $DB->delete_records_select('facetoface_signups_status',
                    'signupid IN (SELECT id FROM {facetoface_signups} WHERE userid = :userid AND sessionid ' . $f2fselect . ')',
                    $params);
                $DB->delete_records_select('facetoface_signups', 'userid = :userid AND sessionid ' . $f2fselect, $params);
                $DB->delete_records_select('facetoface_session_roles', 'userid = :userid AND sessionid ' . $f2fselect, $params);

                $transaction->allow_commit();
            }
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!is_a($context, \context_module::class)) {
            return;
        }

        $sql = "
            SELECT f2fsignups.userid
            FROM {course_modules} cm
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = 'facetoface'
                INNER JOIN {context} c ON c.instanceid = cm.id
                INNER JOIN {facetoface} f2f ON f2f.id = cm.instance
                INNER JOIN {facetoface_sessions} f2fsessions ON f2fsessions.facetoface = f2f.id
                INNER JOIN {facetoface_signups} f2fsignups ON f2fsignups.sessionid = f2fsessions.id
            WHERE c.contextlevel = :contextlevel AND c.id = :contextid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'contextid'    => $context->id
        ];
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if (!is_a($context, \context_module::class)) {
            return;
        }

        $cm = get_coursemodule_from_id('facetoface', $context->instanceid);
        $userids = $userlist->get_userids();
        list ($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        // Get session id from module id
        $sessionid = $DB->get_records_select(
            'facetoface_sessions',
            "facetoface = :facetoface",
            [
                'facetoface' => $cm->instance
            ]
        );

        $inparams['sessionid'] = $sessionid;

        $DB->delete_records_select(
            'facetoface_signups',
            "sessionid = :sessionid AND userid $insql",
            $inparams
        );
    }
}
