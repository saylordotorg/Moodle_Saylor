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
 * Course format with flexible number of nested sections
 *
 * @package    format_flexsections
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2020051101;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2018051700;        // Requires this Moodle 3.5 or above.
$plugin->release   = "3.5.2";
$plugin->maturity  = MATURITY_STABLE;
$plugin->component = 'format_flexsections';    // Full name of the plugin (used for diagnostics).
$plugin->supported = [35, 311]; // Moodle 3.5 - 3.11 are supported. Moodle 4.0 is NOT supported!
