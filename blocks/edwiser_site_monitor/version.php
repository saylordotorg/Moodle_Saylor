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
 * Global Search version details.
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2022040700;                   // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2017111301;                   // Requires this Moodle version.
$plugin->release   = '2.1.0';                      // Human-friendly version name.
$plugin->maturity  = MATURITY_STABLE;              // This version's maturity level.
$plugin->component = 'block_edwiser_site_monitor'; // Component of the plugin.
$plugin->cron      = 300;                          // Period for cron to check this plugin (secs).
