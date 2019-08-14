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
 * Completion overview report.
 *
 * @package    report_completionoverview
 * @copyright  2017 Jim Harris <jim.harris@twoscope.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2017111702;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2017051500;        // Requires this Moodle version - THIS IS 3.3.
$plugin->component = 'report_completionoverview'; // Full name of the plugin (used for diagnostics).
$plugin->release = '2';
$plugin->maturity  = MATURITY_STABLE;