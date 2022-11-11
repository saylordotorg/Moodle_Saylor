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
 * Edwiser Site Monitor block installation.
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Create block instance for admin on installation
 *
 * @return bool true
 */
function xmldb_block_edwiser_site_monitor_install() {
    global $DB;
    $systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => 1));

    $page = new moodle_page();
    $page->set_context(context_system::instance());

    // Selecting default region for blocks i.e. content.
    $page->blocks->add_region('content');

    // Adding multiple blocks.
    if ($systempage) {
        $page->blocks->add_block('edwiser_site_monitor', 'content', -2, false, 'my-index', $systempage->id);
    }
    $admin = get_admin();
    if ($admin != false) {
        $page->set_context(context_user::instance($admin->id));
        $page->blocks->add_block('edwiser_site_monitor', 'content', -2, false, 'my-index');
    }
    block_edwiser_site_monitor\utility::update_notification_configs();
    return true;
}
