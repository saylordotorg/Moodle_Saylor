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
 * Columns for export
 *
 * @package   gradeexport_checklist
 * @copyright 2010 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// This lists the fields to be included from the 'user' table
// (checklist items will appear in the columns to the right of these fields)
// You can include either standard user field names or custom fields
// There is also a special '_groups' field, that lists all the groups the user is a member of.

// The second part of each array entry is the text to appear at the top of the column.

$checklistexportusercolumns = [
    'lastname' => get_string('lastname'),
    'firstname' => get_string('firstname'),
    'username' => get_string('username'),
    '_groups' => 'Groups(s)',
    '_enroldate' => get_string('enroldate', 'gradeexport_checklist'),
    '_startdate' => get_string('startdate', 'gradeexport_checklist'),
    '_percent' => get_string('percent', 'gradeexport_checklist') // Percentage of items student has completed.
];

// The output from the default setting above would be:
// | Surname | First name | Username | Groups(s)        | Checklistitem1 | Checklistitem2 | etc.
// | Smith   | Bob        | bobsmith | Group A, Group B |                |              1 |
// Where '1' indicates the item is checked-off.
