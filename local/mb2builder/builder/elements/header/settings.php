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
 * @package    local_mb2builder
 * @copyright  2018 - 2020 Mariusz Boloz (https://mb2themes.com/)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();



$mb2_settings = array(
	'id' => 'header',
	'subid' => '',
	'title' => get_string('header', 'local_mb2builder'),
	'icon' => 'fa fa-window-maximize',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'type'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('type', 'local_mb2builder'),
			'options' => array(
				'dark' => get_string('dark', 'local_mb2builder'),
				'dark-striped' => get_string('dark_striped', 'local_mb2builder'),
				'light' => get_string('light', 'local_mb2builder'),
				'light-striped' => get_string('light_striped', 'local_mb2builder')
			),
			'default' => 'dark'
		),
		'title'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('title', 'local_mb2builder')
		),
		'subtitle'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('subtext', 'local_mb2builder')
		),
		'image'=>array(
			'type'=>'image',
			'section' => 'general',
			'title'=> get_string('bgimagelabel', 'local_mb2builder')
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('header', 'local_mb2builder')
		),
		'margin'=>array(
			'type'=>'text',
			'section' => 'style',
			'title'=> get_string('marginlabel', 'local_mb2builder'),
			'desc'=> get_string('margindesc', 'local_mb2builder')
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_HEADER', serialize($mb2_settings));
