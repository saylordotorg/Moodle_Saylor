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
	'id' => 'heading',
	'subid' => '',
	'title' => get_string('elheading', 'local_mb2builder'),
	'icon' => 'fa fa-text-height',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'content'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('text', 'local_mb2builder')
		),
		'tag'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('sizelabel', 'local_mb2builder'),
			'options' => array(
				'h1' => 'h1',
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6'
			),
			'default' => 'h4'
		),
		'align'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('alignlabel', 'local_mb2builder'),
			'options' => array(
				'left' => get_string('left', 'local_mb2builder'),
				'right' => get_string('right', 'local_mb2builder'),
				'center' => get_string('center', 'local_mb2builder')
			),
			'default' => 'left'
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('elheading', 'local_mb2builder')
		),
		'color' => array(
			'type' => 'color',
			'section' => 'style',
			'title'=> get_string('color', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_HEADING', serialize($mb2_settings));
