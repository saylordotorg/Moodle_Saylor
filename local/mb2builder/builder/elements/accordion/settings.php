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
	'id' => 'accordion',
	'subid' => 'accordion_item',
	'title' => get_string('accordion', 'local_mb2builder'),
	'icon' => 'fa fa-bars',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'parent' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('accordionparent', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
		),
		'accordion_active' => array(
			'type' => 'number',
			'section' => 'general',
			'min' => 0,
			'max'=> 99,
			'title'=> get_string('accordionopen', 'local_mb2builder'),
			'default' => 1
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('accordion', 'local_mb2builder')
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
			'title' => array(
				'type' => 'text',
				'section' => 'general',
				'title'=> get_string('title', 'local_mb2builder')
			),
			'icon' => array(
				'type' => 'icon',
				'section' => 'general',
				'title'=> get_string('icon', 'local_mb2builder')
			),
			'content' => array(
				'type' => 'editor',
				'section' => 'general',
				'title'=> get_string('content', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_ACCORDION', serialize($mb2_settings));
