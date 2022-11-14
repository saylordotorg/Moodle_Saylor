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
 * External api functions for block_edwiser_site_monitor
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_edwiser_site_monitor_get_live_status' => [
        'classname' => 'block_edwiser_site_monitor\externallib',
        'methodname' => 'get_live_status',
        'classpath' => 'blocks/edwiser_site_monitor/classes/externallib.php',
        'description' => 'Get live status of server',
        'type' => 'read',
        'loginrequired' => true,
        'ajax' => true,
    ],
    'block_edwiser_site_monitor_get_last_24_hours_usage' => [
        'classname' => 'block_edwiser_site_monitor\externallib',
        'methodname' => 'get_last_24_hours_usage',
        'classpath' => 'blocks/edwiser_site_monitor/classes/externallib.php',
        'description' => 'Get live status of server',
        'type' => 'read',
        'loginrequired' => true,
        'ajax' => true,
    ],
    'block_edwiser_site_monitor_get_plugins_update' => [
        'classname' => 'block_edwiser_site_monitor\externallib',
        'methodname' => 'get_plugins_update',
        'classpath' => 'blocks/edwiser_site_monitor/classes/externallib.php',
        'description' => 'Get updates of edwiser plugins or other plugins based on parameter',
        'type' => 'read',
        'loginrequired' => true,
        'ajax' => true,
    ],
    'block_edwiser_site_monitor_send_contactus_email' => [
        'classname' => 'block_edwiser_site_monitor\externallib',
        'methodname' => 'send_contactus_email',
        'classpath' => 'blocks/edwiser_site_monitor/classes/externallib.php',
        'description' => 'Send contact us email',
        'type' => 'read',
        'loginrequired' => true,
        'ajax' => true,
    ]
];
