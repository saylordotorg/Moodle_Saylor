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
 * My programs block installation.
 *
 * @package    enrol_programs
 * @copyright  Copyright (c] 2022 Open LMS (https://www.openlms.net/]
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add My programs block to all dashboards.
 */
function xmldb_block_myprograms_install() {
    global $DB;

    $defaultmypage = $DB->get_record('my_pages', ['userid' => null, 'name' => '__default', 'private' => 1]);
    if (!$defaultmypage) {
        // There is no default dashboard page, too bad!
        return;
    }

    // Add the block to the default /my.
    $systemcontext = context_system::instance();
    $page = new moodle_page();
    $page->set_context($systemcontext);
    $page->blocks->add_region('content');
    $page->blocks->add_block('myprograms', 'content', 0, false, 'my-index', $defaultmypage->id);

    $defaultblock = $DB->get_record('block_instances', [
        'parentcontextid' => $systemcontext->id,
        'blockname' => 'myprograms',
        'pagetypepattern' => 'my-index',
        'subpagepattern' => $defaultmypage->id,
    ], '*', MUST_EXIST);

    // Update all cloned user dashboard pages.
    $select = "private = 1 AND name = '__default' AND userid IS NOT NULL";
    $userpages = $DB->get_recordset_select('my_pages', $select);
    foreach ($userpages as $userpage) {
        $usercontext = context_user::instance($userpage->userid, IGNORE_MISSING);
        if (!$usercontext) {
            continue;
        }
        // NOTE: this code needs to be in sync with current my_copy_page() defined in /my/lib.php.
        $instance = clone($defaultblock);
        unset($instance->id);
        $instance->parentcontextid = $usercontext->id;
        $instance->subpagepattern = $userpage->id;
        $instance->timecreated = time();
        $instance->timemodified = $instance->timecreated;
        $instance->id = $DB->insert_record('block_instances', $instance);
        context_block::instance($instance->id);  // Just creates the context record.
    }
    $userpages->close();
}

