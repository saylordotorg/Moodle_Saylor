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
	'id' => 'gap',
	'subid' => '',
	'title' => get_string('gap', 'local_mb2builder'),
	'icon' => 'fa fa-arrows-v',
	'type'=> 'general',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder')
	),
	'attr' => array(
		'size'=>array(
			'type'=>'number',
			'section' => 'general',
			'min' => 1,
			'max' => 1000,
			'title'=> get_string('sizelabel', 'local_mb2builder'),
			'default' => 20
		),
		'smallscreen' => array(
			'type' => 'list',
			'section' => 'general',
			'title'=> get_string('smallscreen', 'local_mb2builder'),
			'options' => array(
				1 => get_string('yes', 'local_mb2builder'),
				0 => get_string('no', 'local_mb2builder')
			),
			'default' => 1
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('gap', 'local_mb2builder')
		)
	)
);


define('LOCAL_MB2BUILDER_SETTINGS_GAP', serialize($mb2_settings));
