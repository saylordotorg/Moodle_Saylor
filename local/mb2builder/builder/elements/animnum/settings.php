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
	'id' => 'animnum',
	'subid' => 'animnum_item',
	'title' => get_string('animnum', 'local_mb2builder'),
	'icon' => 'fa fa-bar-chart',
	'type'=> 'general',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'columns'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('columns', 'local_mb2builder'),
			'options' => array(
				1 => '1',
				2 => '2',
				3 => '3',
				4 => '4',
				5 => '5'
			)
		),
		'size_number'=>array(
			'type'=>'number',
			'section' => 'general',
			'min' => 1,
			'max' => 5,
			'title'=> get_string('numsize', 'local_mb2builder'),
			'default'=> 3
		),
		'size_icon'=>array(
			'type'=>'number',
			'min' => 1,
			'max' => 5,
			'section' => 'general',
			'title'=> get_string('iconsize', 'local_mb2builder'),
			'default'=> 3
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('animnum', 'local_mb2builder')
		),
		'color_number'=>array(
			'type'=>'color',
			'section' => 'style',
			'title'=> get_string('numcolor', 'local_mb2builder')
		),
		'color_icon'=>array(
			'type'=>'color',
			'section' => 'style',
			'title'=> get_string('iconcolor', 'local_mb2builder')
		),
		'color_title'=>array(
			'type'=>'color',
			'section' => 'style',
			'title'=> get_string('titlecolor', 'local_mb2builder')
		),
		'color_subtitle'=>array(
			'type'=>'color',
			'section' => 'style',
			'title'=> get_string('subtitlecolor', 'local_mb2builder')
		),
		'color_bg'=>array(
			'type'=>'color',
			'section' => 'style',
			'title'=> get_string('bgcolor', 'local_mb2builder')
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

	),
	'subelement' => array(
		'tabs' => array(
			'general' => get_string('generaltab', 'local_mb2builder'),
			'style' => get_string('styletab', 'local_mb2builder')
		),
		'attr' => array(
			'number'=>array(
				'type' => 'number',
				'section' => 'general',
				'title' => get_string('number', 'local_mb2builder'),
				'default' => 0
			),
			'title'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('title', 'local_mb2builder')
			),
			'subtitle'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('subtitle', 'local_mb2builder')
			),
			'icon'=>array(
				'type'=>'icon',
				'section' => 'general',
				'title'=> get_string('icon', 'local_mb2builder')
			),
			'admin_label'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('adminlabellabel', 'local_mb2builder'),
				'desc'=> get_string('adminlabeldesc', 'local_mb2builder')
			),
			'color_number'=>array(
				'type'=>'color',
				'section' => 'style',
				'title'=> get_string('numcolor', 'local_mb2builder')
			),
			'color_icon'=>array(
				'type'=>'color',
				'section' => 'style',
				'title'=> get_string('iconcolor', 'local_mb2builder')
			),
			'color_title'=>array(
				'type'=>'color',
				'section' => 'style',
				'title'=> get_string('titlecolor', 'local_mb2builder')
			),
			'color_subtitle'=>array(
				'type'=>'color',
				'section' => 'style',
				'title'=> get_string('subtitlecolor', 'local_mb2builder')
			),
			'color_bg'=>array(
				'type'=>'color',
				'section' => 'style',
				'title'=> get_string('bgcolor', 'local_mb2builder')
			)
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_ANIMNUM', serialize($mb2_settings));
