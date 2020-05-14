<?php
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
 * auth_nsdc
 *
 *
 * @package    auth
 * @subpackage nsdc
 * @copyright  2020 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_nsdc/pluginname', '',
            new lang_string('auth_nsdcdescription', 'auth_nsdc')));

    // Are we using the  production server?
    $name = 'auth_nsdc/production';
    $label = get_string('settingsnsdcproductionlabel', 'auth_nsdc');
    $help = get_string('settingsnsdcproductionhelp', 'auth_nsdc');
    $setting = new admin_setting_configcheckbox($name, $label, $help, 0, 1, 0);
    $settings->add($setting);

    // SSO Key.
    $settings->add(new admin_setting_configtext('auth_nsdc/key', get_string('settingsnsdckeylabel', 'auth_nsdc'),
            get_string('settingsnsdckeydescription', 'auth_nsdc'), '', PARAM_RAW));
    // SSO IV.
    $settings->add(new admin_setting_configtext('auth_nsdc/iv', get_string('settingsnsdcivlabel', 'auth_nsdc'),
            get_string('settingsnsdcivdescription', 'auth_nsdc'), '', PARAM_RAW));
    // Base email.
    $settings->add(new admin_setting_configtext('auth_nsdc/baseemail', get_string('settingsnsdcbaseemaillabel', 'auth_nsdc'),
            get_string('settingsnsdcbaseemaildescription', 'auth_nsdc'), '', PARAM_RAW));
    // API Key.
    $settings->add(new admin_setting_configtext('auth_nsdc/apikey', get_string('settingsnsdcapikeylabel', 'auth_nsdc'),
            get_string('settingsnsdcapikeydescription', 'auth_nsdc'), '', PARAM_RAW));

}
