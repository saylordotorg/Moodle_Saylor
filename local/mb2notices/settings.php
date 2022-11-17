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

require_once( __DIR__ . '/lib.php' );
require_once( __DIR__ . '/classes/fields.php' );
require_once( __DIR__ . '/classes/helper.php' );

if ($hassiteconfig && has_capability('local/mb2notices:view', context_system::instance()))
{

	$ADMIN->add('root', new admin_category('local_mb2notices', get_string('pluginname', 'local_mb2notices', null, true)));
    $page = new admin_externalpage('local_mb2notices_managenotices', get_string('managenotices', 'local_mb2notices'), new moodle_url('/local/mb2notices/index.php'));
    $ADMIN->add('local_mb2notices', $page);

	$page = new admin_settingpage('local_mb2notices_options', get_string('options', 'local_mb2notices', null, true));

	$name = 'local_mb2notices/noticetype';
	$title = get_string('noticetype','local_mb2notices');
	$setting = new admin_setting_configselect($name, $title, '', 'slide', array(
		'primary' => get_string('primarytype','local_mb2notices'),
		'secondary' => get_string('secondarytype','local_mb2notices'),
		'info' => get_string('infotype','local_mb2notices'),
		'warning' => get_string('warningtype','local_mb2notices'),
		'danger' => get_string('dangertype','local_mb2notices'),
		'success' => get_string('successtype','local_mb2notices')
	));
	$page->add($setting);

	$name = 'local_mb2notices/showtitle';
	$title = get_string('showtitle','local_mb2notices');
	$setting = new admin_setting_configcheckbox($name, $title, '', 0);
	$page->add($setting);

	$name = 'local_mb2notices/position';
	$title = get_string('position','local_mb2notices');
	$setting = new admin_setting_configselect( $name, $title, '', 'content', array(
		'top' => get_string('top','local_mb2notices'),
		'content' => get_string('content','local_mb2notices'),
		'bottom' => get_string('bottom','local_mb2notices')
	));
	$page->add($setting);

	$name = 'local_mb2notices/rolestudent';
	$title = get_string('rolestudent','local_mb2notices');
	$setting = new admin_setting_configselect( $name, $title, '', 'student', Mb2noticesHelper::get_roles_to_select() );
	$page->add($setting);

	$name = 'local_mb2notices/roleteacher';
	$title = get_string('roleteacher','local_mb2notices');
	$setting = new admin_setting_configselect( $name, $title, '', 'editingteacher', Mb2noticesHelper::get_roles_to_select() );
	$page->add($setting);

	$name = 'local_mb2notices/rolecustom1';
	$title = get_string('rolecustom','local_mb2notices', array( 'num'=> 1 ) );
	$setting = new admin_setting_configselect( $name, $title, '', '', Mb2noticesHelper::get_roles_to_select() );
	$page->add($setting);

	$name = 'local_mb2notices/rolecustom2';
	$title = get_string('rolecustom','local_mb2notices', array( 'num'=> 2 ) );
	$setting = new admin_setting_configselect( $name, $title, '', '', Mb2noticesHelper::get_roles_to_select() );
	$page->add($setting);

	$name = 'local_mb2notices/rolecustom3';
	$title = get_string('rolecustom','local_mb2notices', array( 'num'=> 3 ) );
	$setting = new admin_setting_configselect( $name, $title, '', '', Mb2noticesHelper::get_roles_to_select() );
	$page->add($setting);

	$name = 'local_mb2notices/canclose';
	$title = get_string('canclose','local_mb2notices');
	$setting = new admin_setting_configcheckbox($name, $title, '', 0);
	$page->add($setting);

	$name = 'local_mb2notices/cookieexpiry';
	$title = get_string('cookieexpiry','local_mb2notices');
	$setting = new admin_setting_configtext($name, $title, '', 3);
	$page->add($setting);

	$name = 'local_mb2notices/textcolor';
	$title = get_string('textcolor','local_mb2notices');
	$setting = new admin_setting_configmb2color($name, $title, '', '');
	$page->add($setting);

	$name = 'local_mb2notices/bgcolor';
	$title = get_string('bgcolor','local_mb2notices');
	$setting = new admin_setting_configmb2color($name, $title, '', '');
	$page->add($setting);

	$ADMIN->add('local_mb2notices', $page);
}
