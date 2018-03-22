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
 * Affiliations
 *
 * This plugin is designed to work with Moodle 3.2+ and allows students to select 
 * which entities they would like to be affiliated with. The student will be placed
 * into the corresponding cohort.
 *
 * @package    local
 * @subpackage affiliations
 * @copyright  2018 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

$string['pluginname'] = 'Affiliations';
$string['pluginsettings'] = 'Affiliations Settings';
$string['currentaffiliatesheader'] = 'Current affiliates';
$string['currentaffiliatesdeleteconfirmation'] = 'Are you sure you want to delete the following affiliates?';
$string['addaffiliatesheader'] = 'Add affiliates';
$string['addaffiliatesconfirmation'] = 'Are you sure you want to add the following affiliate?';
$string['addaffiliateserror'] = 'There was an error adding the affiliate.';
$string['addaffiliatesmaxlengtherror'] = 'The maximum length for the affiliate code is six charachters.';
$string['addaffiliatesmissinginputerror'] = 'Please be sure to include both a name and code for the affiliate.';
$string['addaffiliatesnamelabel'] = 'Affiliate name:';
$string['addaffiliatesnamelabel_help'] = 'Full name of the affiliate.';
$string['addaffiliatescodelabel'] = 'Affiliate code:';
$string['addaffiliatescodelabel_help'] = 'A short alphanumeric code used in creating the cohort name for the affiliate.';
$string['buttoncurrentaffiliatesdelete'] = 'Delete selected affiliates';
$string['buttonaddaffiliates'] = 'Add affiliate';
$string['addaffiliationsdescriptionlabel'] = 'Add affiliations description text:';
$string['addaffiliationsdescriptionhelp'] = 'This text is shown to users when selecting affiliations.';
$string['addaffiliationsdescriptiondefault'] = 'Select the entities you would like to be affiliated with. This will place you in the cohort for that entity. Deselect to remove yourself from the cohort.';
$string['confirmationtextlabel'] = 'Confirmation text:';
$string['confirmationtexthelp'] = 'Text shown in confirmation dialog when users choose affiliates.';
$string['confirmationtextdefault'] = 'Are you sure you want to make these changes to your affiliations?';
$string['manageaffiliates'] = 'Manage affiliates';
$string['manageaffiliatesnochange'] = 'No changes were made to the affiliates. You are being redirected back to the manage affiliates page.';
$string['manageaffiliatesalreadyused'] = 'The affiliate name or code is already in use.';
$string['manageaffiliations'] = 'Manage my affiliations';
$string['manageaffiliationserror'] = 'There was an error managing your affiliations. Please try again.';
$string['manageaffiliationsnochange'] = 'No changes were made to your affiliates.';
$string['manageaffiliationssuccess'] = 'You have successfully updated your affiliations.';
$string['manageaffiliationsaddaffiliations'] = 'Are you sure you want to add the following affiliations?';
$string['manageaffiliationsremoveaffiliations'] = 'Are you sure you want to remove the following affiliations?';
$string['settings'] = 'Settings';

