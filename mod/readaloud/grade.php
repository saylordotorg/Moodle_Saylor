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
 * Redirect the user to the appropriate submission related page
 *
 * @package   mod_readaloud
 * @category  grade
 * @copyright 2015 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod_readaloud\constants;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$id = required_param('id', PARAM_INT);          // Course module ID
$itemnumber = optional_param('itemnumber', 0,
        PARAM_INT); // Item number, may be != 0 for activities that allow more than one grade per user
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional)

$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$module = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, false, $cm);

if (has_capability('mod/readaloud:manageattempts', context_module::instance($cm->id))) {
    redirect('grading.php?id=' . $id);
} else {
    redirect('view.php?id=' . $id);
}


