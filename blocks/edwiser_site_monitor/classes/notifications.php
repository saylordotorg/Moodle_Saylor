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
 * Local class of edwiser_site_monitor_usage_warning
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor;

defined('MOODLE_INTERNAL') || die;

/**
 * Class to send site notifiation to admin user
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifications {

    /**
     * Check whether post is notified before
     *
     * @param string $subject Subject name of post
     * @return bool true if notified before or false
     */
    public function notified_before($subject) {
        global $DB;
        return $DB->get_field_sql(
            'SELECT 1
               FROM {notifications}
              WHERE subject = ?
                AND component = "block_edwiser_site_monitor"
                AND eventtype = "notifications"',
            array($subject)
        );
    }

    /**
     * Notify post using message api
     *
     * @param object $admin admin user object
     * @param string $subject Message subject
     * @param string $message Message body. Can be text or html or mixture of both
     * @param string $url post url
     * @param string $urlname name of url
     * @return int messageid or false
     */
    private function notify($admin, $subject, $message, $url, $urlname = '') {
        if ($urlname == '') {
            $urlname = $subject;
        }
        $data                    = new \core\message\message();
        $data->component         = 'block_edwiser_site_monitor';
        $data->name              = 'notifications';
        $data->userfrom          = $admin;
        $data->userto            = $admin;
        $data->subject           = $subject;
        $data->notification      = 1;
        $data->fullmessagehtml   = $message;
        $data->fullmessageformat = FORMAT_MARKDOWN;
        $data->contexturl        = $url;
        $data->contexturlname    = $urlname;
        return message_send($data);
    }

    /**
     * Check for the latest post on edwiser site
     */
    public function check() {
        global $DB;
        $admin = get_admin();
        if (!utility::edwiser_site_monitor_notification_allowed($admin)) {
            return false;
        }
        $posts = utility::get_edwiser_news();
        if (empty($posts)) {
            return;
        }
        // Last two month timestamp is 2(month) * 30(day) * 24(hour) * 60(minute) * 60(second) = 5184000(second).
        $last2month = time() - 5184000;
        $messages = [];
        foreach ($posts as $post) {
            if (strtotime($post->date) >= $last2month && !$this->notified_before($post->subject)) {
                $messageid = $this->notify($admin, $post->subject, $post->message, $post->url);
                if ($messageid) {
                    $messages[] = array(
                        'notificationid' => $messageid
                    );
                }

            }
        }
        if (!empty($messages)) {
            $DB->insert_records('message_popup_notifications', $messages);
        }
    }
}
