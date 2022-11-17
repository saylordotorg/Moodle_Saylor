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
	'id' => 'boxescontent',
	'subid' => 'boxcontent',
	'title' => get_string('elboxescontent', 'local_mb2builder'),
	'icon' => 'fa fa-commenting',
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
				1 => '1',
				2 => '2',
				3 => '3',
				4 => '4',
				5 => '5',
				6 => '6'
			)
		),
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
		'height'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('height', 'local_mb2builder')
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('elboxescontent', 'local_mb2builder')
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
			'general' => get_string('generaltab', 'local_mb2builder')
		),
		'attr' => array(
			'title'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('title', 'local_mb2builder')
			),
			'content'=>array(
				'type'=>'editor',
				'section' => 'general',
				'title'=> get_string('text', 'local_mb2builder')
			),
			'color'=>array(
				'type'=>'list',
				'section' => 'general',
				'title'=> get_string('color', 'local_mb2builder'),
				'options' => array(
					'primary' => get_string('primary', 'local_mb2builder'),
					'secondary' => get_string('secondary', 'local_mb2builder'),
					'success' => get_string('success', 'local_mb2builder'),
					'warning' => get_string('warning', 'local_mb2builder'),
					'info' => get_string('info', 'local_mb2builder'),
					'danger' => get_string('danger', 'local_mb2builder'),
					'inverse' => get_string('inverse', 'local_mb2builder')
				),
				'default' => 'primary'
			),
			'icon'=>array(
				'type'=>'icon',
				'section' => 'general',
				'title'=> get_string('icon', 'local_mb2builder')
			),
			'link'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('link', 'local_mb2builder')
			),
			'link_target'=>array(
				'type'=>'list',
				'section' => 'general',
				'title'=> get_string('linktarget', 'local_mb2builder'),
				'options' => array(
					'' => get_string('linktargetself', 'local_mb2builder'),
					'_blank' => get_string('linktargetblank', 'local_mb2builder')
				)
			),
			'linktext' => array(
				'type' => 'text',
				'section' => 'general',
				'title'=> get_string('readmore', 'local_mb2builder')
			),
			'admin_label'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('adminlabellabel', 'local_mb2builder'),
				'desc'=> get_string('adminlabeldesc', 'local_mb2builder')
			)
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_BOXESCONTENT', serialize($mb2_settings));
