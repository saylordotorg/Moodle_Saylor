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
 * Version information for the adaptive behaviour for multi-part questions.
 *
 * @package    qbehaviour_adaptivemultipart
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020103000;
$plugin->requires  = 2014051200;
$plugin->cron      = 0;
$plugin->component = 'qbehaviour_adaptivemultipart';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '4.3.7 for Moodle 3.0+';

$plugin->dependencies = array(
    'qbehaviour_adaptive' => 2014051200,
);
