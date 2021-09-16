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
 * format_board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @copyright  2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Board format';
$string['currentsection'] = 'This topic';
$string['editsection'] = 'Edit topic';
$string['deletesection'] = 'Delete topic';
$string['sectionname'] = 'Topic';
$string['section0name'] = 'General';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';
$string['page-course-view-topics'] = 'Any course main page in topics format';
$string['page-course-view-topics-x'] = 'Any course page in topics format';
$string['showdefaultsectionname'] = 'Show default sections name';
$string['showdefaultsectionname_help'] = 'If no name is set for the section will not show anything.<br>
By definition an unnamed topic is displayed as <strong>Topic N</strong>.';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['sectionlayout'] = 'Sections layout';
$string['sectionlayout_help'] = 'Set the theme that the sections should be displayed:<br><br>
<strong>Clean</strong><br>
Is a theme that will display the sections without adding borders or colors. The sections have a margin of 40px.<br><br>
<strong>Blocks</strong><br>
Is a theme that will display the sections within blocks with title and stylized borders.
The section summary have 0px of spacing relative to the edge, that can be used images to ilustrate the top of the block.';
$string['none'] = 'Clean';
$string['blocks'] = 'Blocks';
$string['widthcol'] = 'Group width';
$string['widthcol_help'] = 'The grouping of sections will became a column if the width is set in sum result with 99%/100%.
<i>Example: Set Group width 1 = 33%, Group width 2 = 33% and Group width 3 = 33%, the result will be a layout with 3 columns.</i>';
$string['numsectionscol'] = 'Number of sections to group';
$string['numsectionscol_help'] = 'Set the number of sections that are within the group.<br>The width sections will be inherited by the width group.';
$string['unlimited'] = 'Unlimited';
$string['color'] = 'Color';
$string['color_help'] = 'Define a color in hexadecimal. <i>Example: #fab747</i><br>If want use the default color leave empty.';
