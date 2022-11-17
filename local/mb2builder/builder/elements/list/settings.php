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
	'id' => 'list',
	'subid' => 'list_item',
	'title' => get_string('list', 'local_mb2builder'),
	'icon' => 'fa fa-list',
	'type'=> 'general',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'style'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('type', 'local_mb2builder'),
			'options' => array(
				'' => get_string('default', 'local_mb2builder'),
				'square' => get_string('square', 'local_mb2builder'),
				'circle' => get_string('circle', 'local_mb2builder'),
				'disc' => get_string('disc', 'local_mb2builder'),
				'number' => get_string('number', 'local_mb2builder'),
				'icon' => get_string('icon', 'local_mb2builder')
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
		'icon'=>array(
			'type'=>'icon',
			'showon' => 'style:icon',
			'section' => 'general',
			'title'=> get_string('icon', 'local_mb2builder')
		),
		'horizontal' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('horizontal', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'align'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('alignlabel', 'local_mb2builder'),
			'options' => array(
				'' => get_string('none', 'local_mb2builder'),
				'left' => get_string('left', 'local_mb2builder'),
				'right' => get_string('right', 'local_mb2builder'),
				'center' => get_string('center', 'local_mb2builder')
			)
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> 'List',
			'onchange' => 1
		),
		'custom_class'=>array(
			'type'=>'text',
			'section' => 'style',
			'title'=> get_string('customclasslabel', 'local_mb2builder'),
			'desc'=> get_string('customclassdesc', 'local_mb2builder'),
			'default'=> ''
		)
	),
	'subelement' => array(
		'tabs' => array(
			'general' => get_string('generaltab', 'local_mb2builder')
		),
		'attr' => array(
			'text'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('text', 'local_mb2builder')
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
			'admin_label'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('adminlabellabel', 'local_mb2builder'),
				'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
				'default'=> 'List item',
				'onchange' => 1
			)
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_LIST', serialize($mb2_settings));
