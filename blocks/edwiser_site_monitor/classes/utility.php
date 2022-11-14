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
 * Utility class for edwiser site monitor
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor;

defined('MOODLE_INTERNAL') || die;

define('ESM_PLUGINS_LIST', "https://edwiser.org/edwiserupdates.json");
define('ESM_NEWS_LIST', "https://edwiser.org/edwisernews.json");
define('ESM_PRIVACY_POLICY_LINK', "https://edwiser.org/privacy-policy/");
define('ESM_SUPPORT_EMAIL', "edwiser@wisdmlabs.com");

use Exception;
use stdClass;
use context_user;

/**
 * Utility functions for block_edwiser_site_monitor
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    /**
     * Send email to user
     *
     * @param  stdClass $from        which user is sending email
     * @param  stdClass $to          to which user this email will be sent
     * @param  stdClass $subject     subject of email
     * @param  stdClass $messagehtml email body
     * @param  boolean  $replyto     to whom user can reply
     * @return boolean               email sending status
     */
    public static function edwiser_site_monitor_send_email($from, $to, $subject, $messagehtml, $replyto = false) {
        $messagetext = html_to_text($messagehtml);
        return email_to_user($to, $from, $subject, $messagetext, $messagehtml);
    }

    /**
     * Get background color class based on value
     *
     * @param  integer $value usage value
     * @return string
     */
    public static function get_color_class_from_value($value) {
        if ($value < 30) {
            return 'bg-success';
        }
        if ($value < 80) {
            return 'bg-warning';
        }
        return 'bg-danger';
    }

    /**
     * Get edwiser plugin list from plugin list url
     *
     * @return array plugin list
     */
    public static function get_edwiser_plugin_list() {
        try {
            $plugins = @file_get_contents(ESM_PLUGINS_LIST);
            return json_decode($plugins);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Get edwiser plugin list from plugin list url
     *
     * @return array plugin list
     */
    public static function get_edwiser_news() {
        try {
            $news = @file_get_contents(ESM_NEWS_LIST);
            return json_decode($news);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Get values ratio in used and total value
     *
     * @param  int $percent usage value
     * @param  int $total   total usage
     * @return string
     */
    public static function get_values_ratio($percent, $total) {
        return round($total * $percent / 100, 2)."G/".round($total, 2)."G";
    }

    /**
     * Updated notification message configurations for blogs post notifications
     */
    public static function update_notification_configs() {
        set_config('block_edwiser_site_monitor_notifications_disable', '0', 'message');
        set_config('airnotifier_provider_block_edwiser_site_monitor_notifications_permitted', 'permitted', 'message');
        set_config('email_provider_block_edwiser_site_monitor_notifications_permitted', 'disallowed', 'message');
        set_config('jabber_provider_block_edwiser_site_monitor_notifications_permitted', 'permitted', 'message');
        set_config('popup_provider_block_edwiser_site_monitor_notifications_permitted', 'permitted', 'message');
        set_config('message_provider_block_edwiser_site_monitor_notifications_loggedin', 'popup', 'message');
        set_config('message_provider_block_edwiser_site_monitor_notifications_loggedoff', 'popup', 'message');
    }

    /**
     * Check if admin permitted to receive notification
     *
     * @param object $admin admin user object
     * @return bool True if allowed to send notification
     */
    public static function edwiser_site_monitor_notification_allowed($admin) {
        global $CFG;
        if (get_config('block_edwiser_site_monitor_notifications_disable', 'message') == 1) {
            return false;
        }
        if (get_config('message', 'popup_provider_block_edwiser_site_monitor_notifications_permitted') != 'permitted') {
            return false;
        }
        $timetosee = 300;
        if (isset($CFG->block_online_users_timetosee)) {
            $timetosee = $CFG->block_online_users_timetosee * 60;
        }
        $time = time() - $timetosee;
        $setting = get_config('message', 'message_provider_block_edwiser_site_monitor_notifications_loggedin');
        if (stripos($setting, 'popup') !== false && $admin->lastaccess >= $time) {
            return true;
        }
        $setting = get_config('message', 'message_provider_block_edwiser_site_monitor_notifications_loggedoff');
        if (stripos($setting, 'popup') !== false && $admin->lastaccess < $time) {
            return true;
        }
        return false;
    }

    /**
     * Call cron on the edwiser_site_monitor.
     *
     * @return boolean
     */
    public static function edwiser_site_monitor_log_usage() {
        global $DB;
        $usage = usage::get_instance();
        $data          = new stdClass;
        $data->time    = time();
        $data->cpu     = $usage->get_cpu_usage();
        $data->memory  = $usage->get_memory_usage();
        $data->storage = $usage->get_storage_usage();
        $context = context_user::instance(get_admin()->id);
        $instance = $DB->get_record(
            'block_instances',
            array('blockname' => 'edwiser_site_monitor', 'parentcontextid' => $context->id)
        );
        if ($instance && $instance->configdata != '') {
            $config = unserialize(base64_decode($instance->configdata));
            new usage_warning($config, $data->cpu, $data->memory, $data->storage);
        }
        $DB->insert_record('block_edwiser_site_monitor', $data);
        $DB->delete_records_select('block_edwiser_site_monitor', 'time < ?', array(time() - 24 * 60 * 60 * 7));
    }
}
