<?php
// This file is part of POAS question and related behaviours - https://code.google.com/p/oasychev-moodle-plugins/
//
// POAS question is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// POAS question is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question behaviour where the student can submit questions one at a
 * time for immediate feedback with qtype specific hints support.
 *
 * @package    qbehaviour_interactivehints
 * @copyright  2013 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qbehaviour_interactivehints';
$plugin->version   = 2015033000;
$plugin->requires  = 2014111000;
$plugin->release = 'Interactive with hints behaviour 2.8';
$plugin->maturity  = MATURITY_STABLE;

$plugin->dependencies = array(
    'qbehaviour_interactive' => 2014111000,
    'qtype_poasquestion' => 2015033000
);
