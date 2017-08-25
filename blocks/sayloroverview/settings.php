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
 * Settings for the overview block.
 *
 * @package    block_sayloroverview
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/sayloroverview/lib.php');

if ($ADMIN->fulltree) {

    $options = [
        BLOCK_SAYLOROVERVIEW_TIMELINE_VIEW => get_string('timeline', 'block_sayloroverview'),
        BLOCK_SAYLOROVERVIEW_COURSES_VIEW => get_string('courses')
    ];

    $settings->add(new admin_setting_configselect('block_sayloroverview/defaulttab',
        get_string('defaulttab', 'block_sayloroverview'),
        get_string('defaulttab_desc', 'block_sayloroverview'), 'courses', $options));
}
