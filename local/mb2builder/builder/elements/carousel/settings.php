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
	'id' => 'carousel',
	'subid' => 'carousel_item',
	'title' => get_string('carousel', 'local_mb2builder'),
	'icon' => 'fa fa-arrows-h',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'carousel' => get_string('carouseltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'columns'=>array(
			'type'=>'number',
			'section' => 'general',
			'min' => 1,
			'max' => 5,
			'title'=> get_string('columns', 'local_mb2builder'),
			'default' => 4
		),
		'gutter' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('grdwidth', 'local_mb2builder'),
			'options' => array(
				'thin' => get_string('thin', 'local_mb2builder'),
				'normal' => get_string('normal', 'local_mb2builder'),
				0 => get_string('none', 'local_mb2builder')
			),
			'default' => 'normal'
		),
		'link' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('link', 'local_mb2builder'),
			'options' => array(
				1 => get_string('readmorebtn', 'local_mb2builder'),
				2 => get_string('wholeitemlink', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
		),
		'readmoretext'=>array(
			'type'=>'text',
			'section' => 'general',
			'showon' => 'link:1',
			'title'=> get_string('readmoretext', 'local_mb2builder')
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
			'default'=> get_string('carousel', 'local_mb2builder')
		),
		'autoplay' => array(
			'type' => 'list',
			'section' => 'carousel',
			'title'=> get_string('autoplay', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
		),
		'sanimate'=>array(
			'type'=>'number',
			'section' => 'carousel',
			'min' => 300,
			'max' => 2000,
			'title'=> get_string('sanimate', 'local_mb2builder'),
			'default' => 600
		),
		'spausetime'=>array(
			'type'=>'number',
			'section' => 'carousel',
			'min' => 1000,
			'max' => 20000,
			'title'=> get_string('spausetime', 'local_mb2builder'),
			'default' => 7000
		),
		'sloop' => array(
			'type' => 'list',
			'section' => 'carousel',
			'title'=> get_string('loop', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'sdots' => array(
			'type' => 'list',
			'section' => 'carousel',
			'title'=> get_string('pagernav', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'snav' => array(
			'type' => 'list',
			'section' => 'carousel',
			'title'=> get_string('dirnav', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
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
			'image' => array(
				'type' => 'image',
				'section' => 'general',
				'title'=> get_string('image', 'local_mb2builder')
			),
			'content' => array(
				'type' => 'textarea',
				'section' => 'general',
				'title'=> get_string('content', 'local_mb2builder')
			),

			'color'=>array(
				'type'=>'color',
				'section' => 'general',
				'title'=> get_string('color', 'local_mb2builder')
			),
			'link'=>array(
				'type'=>'text',
				'section' => 'general',
				'title'=> get_string('link', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_CAROUSEL', serialize($mb2_settings));
