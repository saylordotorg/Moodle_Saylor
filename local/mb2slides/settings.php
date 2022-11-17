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
 * @package    local_mb2slides
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

require_once( __DIR__ . '/lib.php' );
require_once( __DIR__ . '/classes/fields.php' );

if ($hassiteconfig && has_capability('local/mb2slides:view', context_system::instance()))
{

	$ADMIN->add('root', new admin_category('local_mb2slides', get_string('pluginname', 'local_mb2slides', null, true)));
    $page = new admin_externalpage('local_mb2slides_manageslides', get_string('manageslides', 'local_mb2slides'), new moodle_url('/local/mb2slides/index.php'));
    $ADMIN->add('local_mb2slides', $page);

	$page = new admin_settingpage('local_mb2slides_options', get_string('options', 'local_mb2slides', null, true));

	$page->add(new admin_setting_configmb2start('theme_mb2mcl/slideroptstart', get_string('slideroptions','local_mb2slides')));


		$name = 'local_mb2slides/contentwidth';
		$title = get_string('contentwidth','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/slidermargin';
		$title = get_string('slidermargin','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, get_string('slidermargindesc', 'local_mb2slides'), '');
		$page->add($setting);


		$name = 'local_mb2slides/animtype';
		$title = get_string('animtype','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', 'slide', array(
			'slide' => get_string('animtypeslide','local_mb2slides'),
			'fade' => get_string('animtypefade','local_mb2slides')
		));
		$page->add($setting);

		$name = 'local_mb2slides/captionanim';
		$title = get_string('captionanimtype','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', '1', array(
			'none' => get_string('none','local_mb2slides'),
			'1' => get_string('captionanimtype1','local_mb2slides')
		));
		$page->add($setting);

		$name = 'local_mb2slides/animauto';
		$title = get_string('animauto','local_mb2slides');
		$setting = new admin_setting_configcheckbox($name, $title, '', 1);
		$page->add($setting);

		$name = 'local_mb2slides/animspeed';
		$title = get_string('animspeed','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 800);
		$page->add($setting);

		$name = 'local_mb2slides/animpause';
		$title = get_string('animpause','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 7000);
		$page->add($setting);

		$name = 'local_mb2slides/animloop';
		$title = get_string('animloop','local_mb2slides');
		$setting = new admin_setting_configcheckbox($name, $title, '', 1);
		$page->add($setting);

		$name = 'local_mb2slides/navdir';
		$title = get_string('navdir','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', 1, array(
			0 => get_string('none','local_mb2slides'),
			1 => get_string('yes','local_mb2slides'),
			2 => get_string('captionnavdir','local_mb2slides')
		));
		$page->add($setting);

		$name = 'local_mb2slides/navpager';
		$title = get_string('navpager','local_mb2slides');
		$setting = new admin_setting_configcheckbox($name, $title, '', 0);
		$page->add($setting);

	$page->add(new admin_setting_configmb2end('theme_mb2mcl/slideroptend'));

	$page->add(new admin_setting_configmb2start('theme_mb2mcl/appearancestart', get_string('appearance','local_mb2slides')));

		$name = 'local_mb2slides/linkbtn';
		$title = get_string('linkbtn','local_mb2slides');
		$setting = new admin_setting_configcheckbox($name, $title, '', 0);
		$page->add($setting);

		$name = 'local_mb2slides/linkbtncls';
		$title = get_string('linkbtncls','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 'btn btn-lg btn-primary');
		$page->add($setting);

		$name = 'local_mb2slides/linkbtntext';
		$title = get_string('linkbtntext','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', '');
		$page->add($setting);

		$page->add(new admin_setting_configmb2spacer('local_mb2slides/optspacer0'));

		$cstylepre_arr = array(
			'border'=>get_string('border', 'local_mb2slides'),
            'gradient'=>get_string('gradient', 'local_mb2slides'),
			'circle'=>get_string('circle', 'local_mb2slides'),
			'strip-light'=>get_string('striplight', 'local_mb2slides'),
			'strip-dark'=>get_string('stripdark', 'local_mb2slides'),
			'fullwidth'=>get_string('fullwidth', 'local_mb2slides'),
			//'fromtheme'=>get_string('fromtheme', 'local_mb2slides'),
			'custom'=>get_string('custom', 'local_mb2slides')
		);

		$name = 'local_mb2slides/chalign';
		$title = get_string('chalign','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', 'left', array(
			'left' => get_string('left','local_mb2slides'),
			'right' => get_string('right','local_mb2slides'),
			'center' => get_string('center','local_mb2slides')
		));
		$page->add($setting);

		$name = 'local_mb2slides/cvalign';
		$title = get_string('cvalign','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', 'center', array(
			'top' => get_string('top','local_mb2slides'),
			'bottom' => get_string('bottom','local_mb2slides'),
			'center' => get_string('center','local_mb2slides')
		));
		$page->add($setting);

		$name = 'local_mb2slides/captionw';
		$title = get_string('captionw','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 560);
		$page->add($setting);

		$name = 'local_mb2slides/cstylepre';
		$title = get_string('cstylepre','local_mb2slides');
		$setting = new admin_setting_configselect($name, $title, '', 'custom', $cstylepre_arr);
		$page->add($setting);

		$name = 'local_mb2slides/cshadow';
		$title = get_string('cshadow','local_mb2slides');
		$setting = new admin_setting_configcheckbox($name, $title, '', 0);
		$page->add($setting);

		$page->add(new admin_setting_configmb2spacer('local_mb2slides/optspacer1'));

		$name = 'local_mb2slides/imagecolor';
		$title = get_string('imagecolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/cbgcolor';
		$title = get_string('cbgcolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/cbordercolor';
		$title = get_string('cbordercolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/titlecolor';
		$title = get_string('titlecolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/desccolor';
		$title = get_string('desccolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$name = 'local_mb2slides/btncolor';
		$title = get_string('btncolor','local_mb2slides');
		$setting = new admin_setting_configmb2color($name, $title, '', '');
		$page->add($setting);

		$page->add(new admin_setting_configmb2spacer('local_mb2slides/optspacer2'));

		$name = 'local_mb2slides/titlefs';
		$title = get_string('titlefs','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 2.4);
		$page->add($setting);

		$name = 'local_mb2slides/descfs';
		$title = get_string('descfs','local_mb2slides');
		$setting = new admin_setting_configtext($name, $title, '', 1);
		$page->add($setting);


	$page->add(new admin_setting_configmb2end('theme_mb2mcl/appearanceend'));



	$ADMIN->add('local_mb2slides', $page);
}
