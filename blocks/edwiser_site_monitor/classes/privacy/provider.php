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
 * Privacy implementation for block_edwiser_site_monitor.
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use context;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

/**
 * Class to provide privacy information
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // The block_edwiser_site_monitor block stores user provided data.
    \core_privacy\local\metadata\provider,

    // This plugin does not save any user's data.
    \core_privacy\local\request\core_userlist_provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns information about how block_edwiser_site_monitor stores its data.
     *
     * @param  collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('block_edwiser_site_monitor', array(
            'time' => 'privacy:metadata:block_edwiser_site_monitor:time',
            'cpu' => 'privacy:metadata:block_edwiser_site_monitor:cpu',
            'memory' => 'privacy:metadata:block_edwiser_site_monitor:memory',
            'storage' => 'privacy:metadata:block_edwiser_site_monitor:storage',
        ), 'privacy:metadata:block_edwiser_site_monitor:tableexplanation');
        return $collection;
    }

    /**
     * Export all site usage data.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $sql = "SELECT esm.id,
                       esm.time,
                       esm.cpu,
                       esm.memory,
                       esm.storage
                  FROM {block_edwiser_site_monitor} esm";
        $usage = $DB->get_records($sql);
        if (!empty($usage)) {
            writer::get_writer_instance()->export_data($contextlist, $usage);
        }
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param  int         $userid       The user to search.
     * @return contextlist $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
    }


    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        static::delete_data();
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        static::delete_data();
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        static::delete_data();
    }

    /**
     * Delete data.
     */
    protected static function delete_data() {
        global $DB;
        $DB->delete_records('block_edwiser_site_monitor');
    }
}
