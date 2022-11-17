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



$mb2_settings_row = array(
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'rowheader' => get_string('rowheader', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr'=>array(
		'rowheader' => array(
			'type' => 'list',
			'section' => 'rowheader',
			'title'=> get_string('rowheader', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
		),
		'rowheader_content' => array(
			'type' => 'textarea',
			'section' => 'rowheader',
			'showon' => 'rowheader:1',
			'title'=> get_string('rowheadercontent', 'local_mb2builder')
		),
		'rowheader_textcolor' => array(
			'type' => 'color',
			'section' => 'rowheader',
			'showon' => 'rowheader:1',
			'title'=> get_string('textcolor', 'local_mb2builder')
		),
		'rowheader_bgcolor' => array(
			'type' => 'color',
			'section' => 'rowheader',
			'showon' => 'rowheader:1',
			'title'=> get_string('bgcolor', 'local_mb2builder')
		),
		'rowlang' => array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('language', 'core'),
			'desc'=> get_string('languagedesc', 'local_mb2builder')
		),
		'rowaccess' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('elaccess', 'local_mb2builder'),
			'options' => array(
				0 => get_string('elaccessall', 'local_mb2builder'),
				1 => get_string('elaccessusers', 'local_mb2builder'),
				2 => get_string('elaccesguests', 'local_mb2builder')
			),
			'default' => 0
		),
		'rowhidden' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('hidden', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 0
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
		'pt' => array(
			'type'=>'number',
			'section' => 'general',
			'title'=> get_string('ptlabel', 'local_mb2builder'),
			'min'=> 0,
			'max' => 200,
			'default'=> 0,
		),
		'pb' => array(
			'type'=>'number',
			'section' => 'general',
			'title'=> get_string('pblabel', 'local_mb2builder'),
			'min'=> 0,
			'max' => 200,
			'default'=> 0,
		),
		'admin_label' => array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('row', 'local_mb2builder'),
		),
		'bgcolor' => array(
			'type' => 'color',
			'section' => 'style',
			'title'=> get_string('bgcolor', 'local_mb2builder')
		),
		'scheme' => array(
			'type' => 'list',
			'section' => 'style',
			'title'=> get_string('scheme', 'local_mb2builder'),
			'options' => array(
				'light' => get_string('light', 'local_mb2builder'),
				'dark' => get_string('dark', 'local_mb2builder')
			),
			'default' => 'light'
		),
		'prbg' => array(
			'type' => 'list',
			'section' => 'style',
			'title'=> get_string('prestyle', 'local_mb2builder'),
			'options' => array(
				0 => get_string('none', 'local_mb2builder'),
				'gradient20' => get_string('gradient20', 'local_mb2builder'),
				'gradient40' => get_string('gradient40', 'local_mb2builder'),
				'strip1' => get_string('strip1', 'local_mb2builder'),
				'strip2' => get_string('strip2', 'local_mb2builder'),
				'strip3' => get_string('strip3', 'local_mb2builder')
			),
			'default' => 0
		),
		'bgimage' => array(
			'type' => 'image',
			'showon' => 'prbg:0',
			'section' => 'style',
			'title'=> get_string('bgimage', 'local_mb2builder')
		),
		'custom_class' => array(
			'type'=>'text',
			'section' => 'style',
			'title'=> get_string('customclasslabel', 'local_mb2builder'),
			'desc'=> get_string('customclassdesc', 'local_mb2builder'),
			'default'=> ''
		)

	)
);

define('LOCAL_MB2BUILDER_SETTINGS_ROW', serialize($mb2_settings_row));
