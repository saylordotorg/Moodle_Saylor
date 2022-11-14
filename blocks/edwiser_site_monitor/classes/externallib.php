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
 * Local class of edwiser_site_monitor external api functions
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/blocks/edwiser_site_monitor/classes/utility.php');

use external_function_parameters;
use external_single_structure;
use external_value;
use context_system;
use external_api;
use moodle_url;
use stdClass;

/**
 * This class implements services for block_edwiser_site_monitor
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class externallib extends external_api {

    /**
     * Describes the parameters for get blocks function
     *
     * @return external_function_parameters
     */
    public static function get_live_status_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get live_status of server
     *
     * @return array
     */
    public static function get_live_status() {
        self::validate_context(context_system::instance());
        utility::edwiser_site_monitor_log_usage();
        $usage = usage::get_instance();
        return array(
            "cpu"       => $usage->get_cpu_usage(),
            "memory"    => $usage->get_memory_usage(),
            "storage"   => $usage->get_storage_usage(),
            "liveusers" => $usage->get_live_users()
        );
    }

    /**
     * Returns description of method parameters for get live_status function
     *
     * @return external_single_structure
     */
    public static function get_live_status_returns() {
        return new external_single_structure([
            "cpu"       => new external_value(PARAM_FLOAT, "cpu usage"),
            "memory"    => new external_value(PARAM_FLOAT, "memory usage"),
            "storage"   => new external_value(PARAM_FLOAT, "storage usage"),
            "liveusers" => new external_value(PARAM_INT, "number of live users")
        ]);
    }

    /**
     * Describes the parameters for get blocks function
     *
     * @return external_function_parameters
     */
    public static function get_last_24_hours_usage_parameters() {
        return new external_function_parameters(
            array(
                "timestamp" => new external_value(PARAM_INT, "Timestamp of day to get usage", VALUE_REQUIRED)
            )
        );
    }

    /**
     * Get live_status based on filters
     *
     * @param int $timestamp timestamp of date in integer format
     *
     * @return array
     */
    public static function get_last_24_hours_usage($timestamp) {
        global $DB;
        self::validate_context(context_system::instance());
        utility::edwiser_site_monitor_log_usage();

        if ($timestamp == 0) {
            $timestamp = strtotime(date('d-m-Y', time()));
        }
        $usage = $DB->get_records_sql(
            "SELECT time, cpu, memory, storage
               FROM {block_edwiser_site_monitor}
              WHERE time >= ? AND time < ?
              ORDER BY time ASC", array($timestamp, $timestamp + 24 * 60 * 60)
        );
        $cpu = $memory = $storage = $time = [];
        foreach ($usage as $use) {
            $time[] = date("H:i", $use->time);
            $cpu[] = $use->cpu;
            $memory[] = $use->memory;
            $storage[] = $use->storage;
        }
        return array(
            "time" => json_encode($time),
            "cpu" => json_encode($cpu),
            "memory" => json_encode($memory),
            "storage" => json_encode($storage)
        );
    }

    /**
     * Returns description of method parameters for get live_status function
     *
     * @return external_single_structure
     */
    public static function get_last_24_hours_usage_returns() {
        return new external_single_structure(
            [
            "time" => new external_value(PARAM_RAW, "timeline of usage"),
            "cpu" => new external_value(PARAM_RAW, "cpu usage"),
            "memory" => new external_value(PARAM_RAW, "memory usage"),
            "storage" => new external_value(PARAM_RAW, "storage usage"),
            ]
        );
    }

    /**
     * Describes the parameters for get blocks function
     *
     * @return external_function_parameters
     */
    public static function get_plugins_update_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get live_status based on filters
     *
     * @return array
     */
    public static function get_plugins_update() {
        global $PAGE;
        self::validate_context(context_system::instance());
        $plugins = new plugins();
        $time = time();
        return array(
            'lasttimefetched' => get_string('checkforupdateslast', 'core_plugin', date('d F Y, h:i A e', $time)),
            'plugins' => $PAGE->get_renderer('block_edwiser_site_monitor')->render_from_template(
                'block_edwiser_site_monitor/plugins',
                $plugins->get_plugins()
            )
        );
    }

    /**
     * Returns description of method parameters for get live_status function
     *
     * @return external_single_structure
     */
    public static function get_plugins_update_returns() {
        return new external_single_structure(
            array(
                'lasttimefetched' => new external_value(PARAM_RAW, "Last time when updates is checked"),
                'plugins' => new external_value(PARAM_RAW, "Table of installed edwiser plugins or other plugins and there updates")
            )
        );
    }

    /**
     * Describes the parameters for get blocks function
     *
     * @return external_function_parameters
     */
    public static function send_contactus_email_parameters() {
        return new external_function_parameters(
            array(
                'firstname' => new external_value(PARAM_RAW, "firstname of user", VALUE_REQUIRED),
                'lastname' => new external_value(PARAM_RAW, "lastname of user", VALUE_REQUIRED),
                'email' => new external_value(PARAM_EMAIL, "email of user", VALUE_REQUIRED),
                'subject' => new external_value(PARAM_RAW, "subject for email", VALUE_REQUIRED),
                'message' => new external_value(PARAM_RAW, "message for email", VALUE_REQUIRED)
            )
        );
    }

    /**
     * Send email to edwiser@wisdmlabs.com with submitted data in the contact us form
     *
     * @param  string $firstname First name of user
     * @param  string $lastname  Last name of user
     * @param  string $email     Email id of user
     * @param  string $subject   Subject for email
     * @param  string $message   Message body for email
     * @return array             status, header and message
     */
    public static function send_contactus_email($firstname, $lastname, $email, $subject, $message) {
        self::validate_context(context_system::instance());
        $admin = get_admin();
        $admin->email     = $email;
        $admin->firstname = $firstname;
        $admin->lastname  = $lastname;
        $support        = new stdClass;
        $support->id    = -99;
        $support->email = ESM_SUPPORT_EMAIL;
        $status = utility::edwiser_site_monitor_send_email(
            $admin,
            $support,
            $subject,
            $message,
            $email
        );
        $subject = get_string('thankssubject', 'block_edwiser_site_monitor');
        $message = get_string('thanksmessage', 'block_edwiser_site_monitor', array(
            'user'  => $firstname,
            'email' => ESM_SUPPORT_EMAIL
        ));
        $admin->firstname = 'Edwiser';
        $admin->lastname  = '';
        $status &= utility::edwiser_site_monitor_send_email(
            $admin,
            $admin,
            $subject,
            $message
        );
        if (!$status) {
            return array(
                'status' => false,
                'header' => get_string('failed', 'block_edwiser_site_monitor'),
                'message' => get_string('emailfailed', 'block_edwiser_site_monitor') . get_string(
                    'checksettings',
                    'block_edwiser_site_monitor',
                    array(
                        'link' => (new moodle_url(
                            '/admin/settings.php',
                            array(
                                'section' => 'outgoingmailconfig'
                            )
                        ))->__toString(),
                        'text' => get_string('outgoingmailconfig', 'core_admin')
                    )
                )
            );
        }
        return array(
            'status' => true,
            'header' => get_string('success'),
            'message' => get_string('emailsuccess', 'block_edwiser_site_monitor')
        );
    }

    /**
     * Returns description of method parameters for get live_status function
     *
     * @return external_single_structure
     */
    public static function send_contactus_email_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, "Email send status"),
                'header' => new external_value(PARAM_ALPHA, "Email sending status header"),
                'message' => new external_value(PARAM_RAW, "Email sending status message")
            )
        );
        return new external_value(PARAM_RAW, "Status of sent email");
    }
}
