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
 * Defines site settings for the quizanalytics gradebook report
 *
 * @package   gradereport_quizanalytics
 * @author Moumita Adak <moumita.a@dualcube.com>
 * @copyright Dualcube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('gradereport_quizanalytics_cutoff',
        get_string('setcutoff', 'gradereport_quizanalytics'),
        get_string('cutoffdes', 'gradereport_quizanalytics'), 40, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('gradereport_quizanalytics_globalboundary',
        get_string('setglobal', 'gradereport_quizanalytics'),
        get_string('setglobaldes', 'gradereport_quizanalytics'), 1));

    $settings->add(new admin_setting_configtextarea('gradereport_quizanalytics_gradeboundary',
        get_string('gradeboundary', 'gradereport_quizanalytics'),
        get_string('gradeboundarydes', 'gradereport_quizanalytics'),
        '0-60, 61-70, 71-80, 81-90, 91-100'));

    $settings->add(new admin_setting_configtext('gradereport_quizanalytics_fbappid',
        get_string('enterfbappid', 'gradereport_quizanalytics'),
        get_string('fbappiddes', 'gradereport_quizanalytics'), 'Empty'));

    $settings->add(new admin_setting_configtext('gradereport_quizanalytics_apiversion',
        get_string('apiversion', 'gradereport_quizanalytics'),
        get_string('apiversiondes', 'gradereport_quizanalytics'), 'Empty'));

    $settings->add(new admin_setting_configtext('gradereport_quizanalytics_fbsharetitle',
        get_string('fbsharetitle', 'gradereport_quizanalytics'),
        get_string('fbsharetitledes', 'gradereport_quizanalytics'), 'Title'));

    $settings->add(new admin_setting_configtext('gradereport_quizanalytics_fbsharetitle',
        get_string('fbsharetitle', 'gradereport_quizanalytics'),
        get_string('fbsharetitledes', 'gradereport_quizanalytics'),
        get_string('fbsharetitledefault', 'gradereport_quizanalytics')));
}