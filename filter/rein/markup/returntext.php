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
 * Returns the markup for a single widget as a text file in a new window.
 *
 * Returns REIN widget markup as a text file in a new window, allowing for
 * copy and paste into Moodle htmlarea fields.
 *
 * @package   filter_rein
 * @copyright 2013 onwards Remote-Learner {@link http://www.remote-learner.net/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../config.php');
global $CFG;
require_login();

header("Content-Type: text/plain");
$param = required_param('markup', PARAM_ALPHA);
$markupflavor = $param.'.php';
$markup = file_get_contents($markupflavor);
echo $markup;
