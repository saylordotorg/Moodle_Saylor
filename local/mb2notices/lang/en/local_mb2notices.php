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
 *
 * @package    local_mb2notices
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mb2 Notices';

// Roles
$string['mb2notices:manageitems'] = 'Manage notices';
$string['mb2notices:view'] = 'View notice';

// Global strings
$string['none'] = 'None';
$string['left'] = 'Left';
$string['right'] = 'Right';
$string['center'] = 'Center';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';
$string['content'] = 'Content';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['all'] = 'All';
$string['show'] = 'Show';
$string['hide'] = 'Hide';

// Moodle menu
$string['managenotices'] = 'Manage notices';
$string['editnotice'] = 'Edit notice';
$string['enablenotice'] = 'Show notice';
$string['disablenotice'] = 'Hide notice';
$string['deletenotice'] = 'Delete notice';
$string['addnotice'] = "Add notice";
$string['noticeupdated'] = 'Notice <strong>{$a->title}</strong> updated.';
$string['noticecreated'] = 'Notice created.';
$string['noticedeleted'] = 'Notice deleted.';
$string['confirmdeletenotice'] = 'Do you really want to delete notice: <strong>{$a->title}</strong>?';
$string['access'] = 'Access';

// Notice edit form
$string['title'] = 'Title';
$string['enable'] = 'Notice visibility';
$string['timestart'] = 'Start time';
$string['timeend'] = 'End time';
$string['access'] = 'Who can see this notice';
$string['accesseveryone'] = 'Everyone';
$string['accessusers'] = 'Users';
$string['accessguests'] = 'Guests';
$string['accesstudents'] = 'Students';
$string['accesteachers'] = 'Teachers';
$string['guestscansee'] = 'Notice is visible only for guests.';
$string['userscansee'] = 'Notice is visible only for logged in users.';
$string['image'] = 'Image';
$string['showtitle'] = 'Display title';
$string['enddatebeforestartdate'] = 'The notice end time must be after the start time.';
$string['showon'] = 'Where this notice appears';
$string['showoneverywhere'] = 'Entire site';
$string['showonfrontpage'] = 'Front page';
$string['showoncourse'] = 'Course page';
$string['showondashboard'] = 'Dasboard page';
$string['showonloginpage'] = 'Login page';
$string['showoncalendarpage'] = 'Calendar page';
$string['courseids'] = 'Course IDs';
$string['courseids_help'] = 'Leave this field empty if you want to show notice on all courses.<br/><br/>If you want to show notice only on specific course or courses type course ID or comma separated IDs, for example: 2,15,25<br/><br/>If you want to show notice on all courses except for specific course or courses type course ID or comma separated IDs with minus ("-") character, for example:-2,-15,-25';
$string['userids'] = 'User IDs';
$string['userids_help'] = 'Leave this field empty if you want to show notice for all users.<br/><br/>If you want to show notice only for specific user or users type user ID or comma separated IDs, for example: 4,24,32<br/><br/>If you want to show notice for all users except for specific user or users type user ID or comma separated IDs with minus ("-") character, for example:-7,-18,-45';
$string['position'] = 'Position';

// Notices list table
$string['hiddenfinished'] = 'After finish';
$string['hiddenwait'] = 'Before start';
$string['visibleto'] = 'Visible to';
$string['strftimedatemonthabbr'] = '%d %b %Y';
$string['strftimedatetimeshort'] = '%d/%m/%y, %H:%M';

// Options
$string['options'] = 'Global options';
$string['noticetype'] = 'Notice type';
$string['primarytype'] = 'Primary';
$string['secondarytype'] = 'Secondary';
$string['infotype'] = 'Info';
$string['warningtype'] = 'Warning';
$string['dangertype'] = 'Danger';
$string['successtype'] = 'Success';
$string['canclose'] = ' Users can close notice';
$string['cookieexpiry'] = 'Cookie expiry time (days)';
$string['textcolor'] = 'Text color';
$string['bgcolor'] = 'Background color';
$string['useglobal'] = 'Use global';
$string['useglobal_help'] = 'Leave this option empty to use global value.';
$string['rolestudent'] = 'Student role';
$string['roleteacher'] = 'Teacher role';
$string['rolecustom'] = 'Custom role {$a->num}';
