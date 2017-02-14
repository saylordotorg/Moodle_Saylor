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
 * Heatmap block settings
 *
 * @package   block_heatmap
 * @copyright 2010 Michael de Raadt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Cache lifespan.
    $options = array(
        0 => get_string('cache0min', 'block_heatmap'),
        (1 * 60) => get_string('cache1min', 'block_heatmap'),
        (5 * 60) => get_string('cache5min', 'block_heatmap'),
        (10 * 60) => get_string('cache10min', 'block_heatmap'),
        (60 * 60) => get_string('cache60min', 'block_heatmap'),
        (120 * 60) => get_string('cache120min', 'block_heatmap'),
    );
    $settings->add(new admin_setting_configselect('block_heatmap/cachelife',
        get_string('cachelife', 'block_heatmap'),
        get_string('cachelife_help', 'block_heatmap'),
        (5 * 60),
        $options)
    );

    // Starting time of log queries.
    $options = array(
        'sincestart' => get_string('sincestart', 'block_heatmap'),
        'sinceforever' => get_string('sinceforever', 'block_heatmap'),
    );
    $settings->add(new admin_setting_configselect('block_heatmap/activitysince',
        get_string('checkforactivity', 'block_heatmap'),
        get_string('checkforactivity_help', 'block_heatmap'),
        'sincestart',
        $options)
    );

    // Control background display and icons.
    $options = array(
        'showboth' => get_string('showboth', 'block_heatmap'),
        'showbackground' => get_string('showbackground', 'block_heatmap'),
        'showicons' => get_string('showicons', 'block_heatmap'),
    );
    $settings->add(new admin_setting_configselect('block_heatmap/whattoshow',
        get_string('whattoshow', 'block_heatmap'),
        '',
        'showboth',
        $options)
    );

}