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
	'id' => 'announcements',
	'subid' => '',
	'title' => get_string('announcements', 'local_mb2builder'),
	'icon' => 'fa fa-bullhorn',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'layouttab' => get_string('layouttab', 'local_mb2builder'),
		'carousel' => get_string('carouseltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'limit'=>array(
			'type'=>'number',
			'section' => 'general',
			'min' => 1,
			'max' => 99,
			'title'=> get_string('itemsperpage', 'local_mb2builder'),
			'default' => 8
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('announcements', 'local_mb2builder')
		),
		'layout'=>array(
			'type'=>'list',
			'section' => 'layouttab',
			'title'=> get_string('layouttab', 'local_mb2builder'),
			'options' => array(
				0 => get_string('none', 'local_mb2builder'),
				'list' => get_string('list', 'local_mb2builder'),
				'cols' => get_string('columns', 'local_mb2builder'),
				'slidercols' => get_string('slidercolumns', 'local_mb2builder')
			),
			'default' => 'list'
		),
		'colnum'=>array(
			'type'=>'number',
			'section' => 'layouttab',
			'showon' => 'layout:cols|slidercols',
			'min' => 1,
			'max' => 5,
			'title'=> get_string('columns', 'local_mb2builder'),
			'default' => 4
		),
		'gridwidth' => array(
			'type' => 'list',
			'section' => 'layouttab',
			'showon' => 'layout:cols|slidercols',
			'title'=> get_string('grdwidth', 'local_mb2builder'),
			'options' => array(
				'thin' => get_string('thin', 'local_mb2builder'),
				'normal' => get_string('normal', 'local_mb2builder')
			),
			'default' => 'normal'
		),
		'image' => array(
			'type' => 'list',
			'section' => 'layouttab',
			'title'=> get_string('image', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
		),
		'titlelimit'=>array(
			'type'=>'number',
			'section' => 'layouttab',
			'min' => 0,
			'max' => 25,
			'title'=> get_string('titlelimit', 'local_mb2builder'),
			'default' => 6
		),
		'desclimit'=>array(
			'type'=>'number',
			'section' => 'layouttab',
			'min' => 0,
			'max' => 999,
			'title'=> get_string('desclimit', 'local_mb2builder'),
			'default' => 25
		),
		'itemdate' => array(
			'type' => 'list',
			'section' => 'layouttab',
			'title'=> get_string('itemdate', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'link' => array(
			'type' => 'list',
			'section' => 'layouttab',
			'title'=> get_string('link', 'local_mb2builder'),
			'options' => array(
				1 => get_string('readmorebtn', 'local_mb2builder'),
				2 => get_string('wholeitemlink', 'local_mb2builder'),
				0 => get_string('titleandimage', 'local_mb2builder')
			),
			'default' => 1
		),
		'readmoretext'=>array(
			'type'=>'text',
			'section' => 'layouttab',
			'showon' => 'link:1',
			'title'=> get_string('readmoretext', 'local_mb2builder')
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
		'prestyle' => array(
			'type' => 'list',
			'section' => 'style',
			'title'=> get_string('prestyle', 'local_mb2builder'),
			'options' => array(
				0 => get_string('none', 'local_mb2builder')
			),
			'default' => 0
		),
		'colors'=>array(
			'type'=>'textarea',
			'section' => 'style',
			'title'=> get_string('colors', 'local_mb2builder'),
			'desc'=> get_string('colorsdesc', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_ANNOUNCEMENTS', serialize($mb2_settings));
