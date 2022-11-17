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
	'id' => 'button',
	'subid' => '',
	'title' => get_string('button', 'local_mb2builder'),
	'icon' => 'fa fa-link',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'text'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('text', 'local_mb2builder')
		),
		'icon' => array(
			'type'=>'icon',
			'section' => 'general',
			'title'=> get_string('icon', 'local_mb2builder')
		),
		'link'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('link', 'local_mb2builder'),
			'default' => '#'
		),
		'target'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('linktarget', 'local_mb2builder'),
			'options' => array(
				'' => get_string('linktargetself', 'local_mb2builder'),
				'_blank' => get_string('linktargetblank', 'local_mb2builder')
			)
		),
		'type'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('type', 'local_mb2builder'),
			'options' => array(
				'default' => get_string('default', 'local_mb2builder'),
				'primary' => get_string('primary', 'local_mb2builder'),
				'secondary' => get_string('secondary', 'local_mb2builder'),
				'success' => get_string('success', 'local_mb2builder'),
				'warning' => get_string('warning', 'local_mb2builder'),
				'info' => get_string('info', 'local_mb2builder'),
				'danger' => get_string('danger', 'local_mb2builder'),
				'inverse' => get_string('inverse', 'local_mb2builder'),
				'link' => get_string('link', 'local_mb2builder')
			),
			'default' => 'primary'
		),
		'size'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('sizelabel', 'local_mb2builder'),
			'options' => array(
				'' => get_string('default', 'local_mb2builder'),
				'xs' => get_string('xsmall', 'local_mb2builder'),
				'sm' => get_string('small', 'local_mb2builder'),
				'lg' => get_string('large', 'local_mb2builder'),
				'xlg' => get_string('xlarge', 'local_mb2builder')
			),
		),
		'fw' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('fullwidth', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'rounded' => array(
			'type' => 'list',
			'section' => 'style',
			'title'=> get_string('rounded', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'border' => array(
			'type' => 'list',
			'section' => 'style',
			'title'=> get_string('border', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'center' => array(
			'type' => 'list',
			'section' => 'general',
			'showon' => 'fw:0',
			'title'=> get_string('center', 'local_mb2builder'),
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
			'default'=> get_string('button', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_BUTTON', serialize($mb2_settings));
