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
	'id' => 'video',
	'subid' => '',
	'title' => get_string('elvideo', 'local_mb2builder'),
	'icon' => 'fa fa-film',
	'type'=> 'general',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(
		'videoid'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('videoidlabel', 'local_mb2builder'),
			'desc'=> get_string('videoiddesc', 'local_mb2builder'),
			'default' => 'https://youtu.be/wop3FMhoLGs'
		),
		'custom_video'=>array(
			'type'=>'image',
			'section' => 'general',
			'title'=> get_string('customvideolabel', 'local_mb2builder')
		),
		'videospacer1'=>array( 'type'=>'spacer', 'section' => 'general' ),
		'width'=>array(
			'type'=>'number',
			'min' => 0,
			'max' => 1500,
			'section' => 'general',
			'title'=> get_string('widthlabel', 'local_mb2builder')
		),
		'bg_image'=>array(
			'type'=>'image',
			'section' => 'general',
			'title'=> get_string('bgimagelabel', 'local_mb2builder')
		),
		'admin_label'=>array(
			'type'=>'text',
			'section' => 'general',
			'title'=> get_string('adminlabellabel', 'local_mb2builder'),
			'desc'=> get_string('adminlabeldesc', 'local_mb2builder'),
			'default'=> get_string('elvideo', 'local_mb2builder')
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


define('LOCAL_MB2BUILDER_SETTINGS_VIDEO', serialize($mb2_settings));
