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
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_quickmail\repos\role_repo;
use block_quickmail\repos\group_repo;
use block_quickmail\repos\user_repo;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';

    // Authorization.
    /**
     * Checks if the given user can send the given type of message in the given context, throwing an exception if not
     *
     * @param  string  $sendtype  broadcast|compose
     * @param  object  $user
     * @param  object  $context   an instance of a SYSTEM or COURSE context
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_can_send($sendtype, $user, $context) {
        if (!self::user_can_send($sendtype, $user, $context)) {
            $capability = $sendtype == 'broadcast' ? 'myaddinstance' : 'cansend';

            throw new required_capability_exception($context, 'block/quickmail:' . $capability, 'nopermissions', '');
        }
    }

    /**
     * Checks if the given user can create notifications in the given context, throwing an exception if not
     *
     * NOTE: this first checks if notifications are enabled in the block config, if NOT,
     * then any user will be redirected to the course view
     *
     * @param  object  $user
     * @param  object  $context   an instance of a COURSE context
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_can_create_notifications($user, $context) {
        if (!block_quickmail_config::block('notifications_enabled')) {
            $moodleurl = new \moodle_url('/course/view.php', ['id' => $context->instanceid]);

            redirect($moodleurl,
                block_quickmail_string::get('redirect_back_to_course_from_notifications_not_enabled'),
                2,
                \core\output\notification::NOTIFY_INFO);
        }

        self::require_user_capability('createnotifications', $user, $context);
    }

    /**
     * Checks if the given user has the given capability in the given context, throwing an exception if not
     *
     * @param  string $capability
     * @param  mixed  $user
     * @param  object $context  an instance of a context
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_capability($capability, $user, $context) {
        if (!self::user_has_capability($capability, $user, $context)) {
            throw new required_capability_exception($context, 'block/quickmail:' . $capability, 'nopermissions', '');
        }
    }

    /**
     * Checks if the given user has the ability to message within the given course id
     *
     * @param  object  $user
     * @param  int     $courseid
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_has_course_message_access($user, $courseid) {
        $sendtype = $courseid == SITEID
            ? 'broadcast'
            : 'compose';

        $context = $sendtype == 'broadcast'
            ? context_system::instance()
            : context_course::instance($courseid);

        self::require_user_can_send($sendtype, $user, $context);
    }

    /**
     * Reports whether or not the given user can send the given type of message in the given context
     *
     * @param  string  $sendtype                broadcast|compose
     * @param  object  $user
     * @param  object  $context                  an instance of a SYSTEM or COURSE context
     * @param  bool    $includestudentaccess   if true (default), will check a course's "allowstudents" config for access
     * @return bool
     */
    public static function user_can_send($sendtype, $user, $context, $includestudentaccess = true) {
        // Must be a valid send_type.
        if (!in_array($sendtype, ['broadcast', 'compose'])) {
            return false;
        }

        // If we're broadcasting, only allow admins.
        if ($sendtype == 'broadcast') {
            // Make sure we have the correct context (system).
            if (get_class($context) !== 'context_system') {
                return false;
            }

            return self::user_has_capability('myaddinstance', $user, $context);
        }

        // Otherwise, we're composing.
        // Make sure we have the correct context (course).
        if (get_class($context) !== 'context_course') {
            return false;
        }

        if (self::user_has_capability('cansend', $user, $context)) {
            return true;
        }

        // If we're checking for student access AND this course allows students to send.
        if ($includestudentaccess && block_quickmail_config::course($context->instanceid, 'allowstudents')) {
            global $CFG;

            // Iterate over system's "student" roles.
            foreach (explode(',', $CFG->gradebookroles) as $roleid) {
                // If the user is associated with one of these roles in the (course) context.
                if (user_has_role_assignment($user->id, $roleid, $context->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Reports whether or not the given user can create notifications in the given context
     *
     * NOTE: this first checks if notifications are enabled in the block config and returns false if not
     *
     * @param  object  $user
     * @param  object  $context   an instance of a COURSE context
     * @return bool
     */
    public static function user_can_create_notifications($user, $context) {
        if (!block_quickmail_config::block('notifications_enabled')) {
            return false;
        }

        return self::user_has_capability('createnotifications', $user, $context);
    }

    /**
     * Reports whether or not the authenticated user has the given capability within the given context
     *
     * @param  string $capability
     * @param  object $user
     * @param  object $context
     * @return bool
     */
    public static function user_has_capability($capability, $user, $context) {
        // Always allow site admins.
        // TODO: Change this to a role capability?
        if (is_siteadmin($user)) {
            return true;
        }

        return has_capability('block/quickmail:' . $capability, $context, $user);
    }

    // Compose Page Data.
    /**
     * Returns an array of role/group/user data for a given course and context
     *
     * This is intended for feeding recipient data to the /compose.php page
     *
     * The returned array includes:
     * - all course roles [id => name]
     * - all course groups [id => name]
     * - all actively enrolled users [id => "fullname"]
     *
     * @param  object  $course
     * @param  object  $user
     * @param  bool    $includeusergroupinfo
     * @param  context $coursecontext
     * @return array
     */
    public static function get_compose_message_recipients($course, $user, $includeusergroupinfo = false, $coursecontext) {

        // Initialize a container for the collection of user data results.
        $courseuserdata = [
            'roles' => [],
            'groups' => [],
            'users' => [],
        ];

        // Roles.
        // Get all roles explicitly selectable for this user, allowing only those white-listed by config.
        $roles = role_repo::get_course_selectable_roles($course, $coursecontext);

        // Format and add each role to the results.
        foreach ($roles as $role) {
            $courseuserdata['roles'][] = [
                'id' => $role->id,
                'name' => $role->name == '' ? $role->shortname : $role->name,
            ];
        }

        // Groups.
        // Get all groups explicitly selectable for this user.
        $groups = group_repo::get_course_user_selectable_groups($course, $user, $includeusergroupinfo, $coursecontext);

        // Create a user group name container regardless of whether we are including or not.
        $usergroupnames = [];

        // Iterate through each group.
        foreach ($groups as $group) {
            // If we're including user group info, add that now.
            if ($includeusergroupinfo) {
                // For each member, add group name to user's key in container.
                foreach ($group->members as $key => $userid) {
                    $usergroupnames[$userid][] = $group->name;
                }
            }

            // Add this group's data to the results container.
            $courseuserdata['groups'][] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }

        // Users.
        // Get all users explicitly selectable for this user.
        $users = user_repo::get_course_user_selectable_users($course, $user, $coursecontext);

        // Add each user to the results collection.
        foreach ($users as $user) {
            $username = $user->firstname . ' ' . $user->lastname;

            // If this user belongs to any groups, append them as a list to the user name value.
            if (array_key_exists($user->id, $usergroupnames)) {
                $username .= ' (' . implode(', ', $usergroupnames[$user->id]) . ')';
            }

            $courseuserdata['users'][] = [
                'id' => $user->id,
                'name' => $username,
            ];
        }

        return $courseuserdata;
    }

    // Notification Models.
    /**
     * Returns notification model types as an associative array, keyed by type
     *
     * NOTE: if a type is given, will return an array of that type's models only
     *
     * @param  string  $type  reminder|event
     * @return array
     */
    public static function get_model_notification_types($type = '') {
        $models = [
            'reminder' => [
                'course_non_participation',
                'course_grade_range'
            ],
            'event' => [
                'course_entered',
            ]
        ];

        return $type ? $models[$type] : $models;
    }

    // Schedule Helpers.
    /**
     * Returns an array for time unit form selection
     *
     * @param  array   $includes      a list of time units to include in the selection
     * @param  string  $defaulttext  lang string to display for default (empty) selection, default to "select"
     * @return array
     */
    public static function get_time_unit_selection_array($includes = [], $defaulttext = 'select') {
        $options = [
            '' => get_string($defaulttext),
            'minute' => ucfirst(get_string('minutes')),
            'hour' => ucfirst(get_string('hours')),
            'day' => ucfirst(get_string('days')),
            'week' => ucfirst(get_string('weeks')),
            'month' => ucfirst(get_string('months')),
        ];

        return self::array_filter_key($options, function($unit) use ($includes) {
            return in_array($unit, $includes) || $unit == '';
        });
    }

    /**
     * Reports whether or not this user prefers multiselect recipient selection over autocomplete
     *
     * @param  object  $user
     * @return bool
     */
    public static function user_prefers_multiselect_recips($user) {
        return get_user_preferences('block_quickmail_preferred_picker', 'autocomplete', $user) == 'multiselect';
    }

    // Utilities.
    /**
     * Returns an array of the given array filtered by key via the given callback
     *
     * This is necessary for PHP versions less than 5.6
     *
     * @param  array     $array
     * @param  callable  $callback
     * @return array
     */
    public static function array_filter_key(array $array, $callback) {
        $matchedkeys = array_filter(array_keys($array), $callback);

        return array_intersect_key($array, array_flip($matchedkeys));
    }

    /**
     * Returns the base web path for any of this block's pages
     *
     * @return string
     */
    public static function base_url() {
        /* This may not be necessary or required?
         * require_once('../../config.php');
         */

        global $CFG;

        return $CFG->wwwroot . '/blocks/quickmail/';
    }

    /**
     * Returns a calculated amount of seconds from the given params, if any
     *
     * Note: time_unit currently limited to: minute, hour, or day
     *
     * @param  string  $timeunit
     * @param  string  $timeamount
     * @return int
     */
    public static function calculate_seconds_from_time_params($timeunit = '', $timeamount = '') {
        $result = 0;

        if ($timeunit && $timeamount) {
            $amount = (int) $timeamount;

            if (in_array($timeunit, ['minute', 'hour', 'day']) && $amount > 0) {
                $seconds = 60;
                $mult = 1;

                if ($timeunit == 'hour') {
                    $mult = 60;
                } else if ($timeunit == 'day') {
                    $mult = 1440;
                }

                $result = $amount * $seconds * $mult;
            }
        }

        return $result;
    }

    /**
     * Returns the system's custom user profile fields as array
     *
     * @return array  [shortname => name]
     */
    public static function get_user_profile_field_array() {
        global $DB;

        $userprofilefields = [];

        if ($profilefields = $DB->get_records('user_info_field')) {
            foreach ($profilefields as $profilefield) {
                $userprofilefields[$profilefield->shortname] = $profilefield->name;
            }
        }

        return $userprofilefields;
    }

}
