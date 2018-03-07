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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG, $USER, $DB;

    $moderator = get_admin();
    $site = get_site();


    // Add Affiliations category to local plugins node.
    $ADMIN->add('localplugins', new admin_category('affiliationsroot', new lang_string('pluginname', 'local_affiliations')));
    // Add regular settings page to the affiliations node.
    $settings = new admin_settingpage('local_affiliations', get_string('settings', 'local_affiliations'));
    $ADMIN->add('affiliationsroot', $settings);

    // Add affiliations description that is shown when a user is adding affiliations.
    $name = 'local_affiliations/addaffiliationsdescription';
    $title = get_string('addaffiliationsdescriptionlabel', 'local_affiliations');
    $description = get_string('addaffiliationsdescriptionhelp', 'local_affiliations');
    $setting = new admin_setting_confightmleditor($name, $title, $description, get_string('addaffiliationsdescriptiondefault', 'local_affiliations'));
    $settings->add($setting);

    // Confirmation text that is shown when a user is adding affiliations.
    $name = 'local_affiliations/confirmationtext';
    $title = get_string('confirmationtextlabel', 'local_affiliations');
    $description = get_string('confirmationtexthelp', 'local_affiliations');
    $setting = new admin_setting_confightmleditor($name, $title, $description, get_string('confirmationtextdefault', 'local_affiliations'));
    $settings->add($setting);

    // Add manage affiliates page to the affiliations node.
    $ADMIN->add('affiliationsroot', new admin_externalpage('local_affiliations_manageaffiliates', get_string('manageaffiliates', 'local_affiliations'),
            new moodle_url('/local/affiliations/admin/manageaffiliates.php')));



}

