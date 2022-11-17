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
	'id' => 'line',
	'subid' => '',
	'title' => get_string('line', 'local_mb2builder'),
	'icon' => 'fa fa-scissors',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'color'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('color', 'local_mb2builder'),
			'options' => array(
				'' => get_string('dark', 'local_mb2builder'),
				'light' => get_string('light', 'local_mb2builder'),
				'custom' => get_string('custom', 'local_mb2builder'),
			)
		),
		'custom_color'=>array(
			'type'=>'color',
			'showon' => 'color:custom',
			'section' => 'general',
			'title'=> get_string('color', 'local_mb2builder')
		),
		'size'=>array(
			'type'=>'number',
			'min' => 1,
			'max' => 100,
			'section' => 'general',
			'title'=> get_string('sizelabel', 'local_mb2builder'),
			'default' => 1
		),
		'style'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('styletab', 'local_mb2builder'),
			'options' => array(
				'' => get_string('solid', 'local_mb2builder'),
				'dotted' => get_string('dotted', 'local_mb2builder'),
				'dashed' => get_string('dashed', 'local_mb2builder'),
			)
		),
		'double' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('double', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('line', 'local_mb2builder')
		),
		'margin'=>array(
			'type'=>'text',
			'section' => 'style',
			'title'=> get_string('marginlabel', 'local_mb2builder'),
			'desc'=> get_string('margindesc', 'local_mb2builder')
		),
		'custom_class'=>array(
			'type'=>'text',
			'section' => 'style',
			'title'=> get_string('customclasslabel', 'local_mb2builder'),
			'desc'=> get_string('customclassdesc', 'local_mb2builder')
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_LINE', serialize($mb2_settings));
