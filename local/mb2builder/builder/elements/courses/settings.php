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
	'id' => 'courses',
	'subid' => '',
	'title' => get_string('courses', 'local_mb2builder'),
	'icon' => 'fa fa-book',
	'tabs' => array(
		'general' => get_string('generaltab', 'local_mb2builder'),
		'layouttab' => get_string('layouttab', 'local_mb2builder'),
		'carousel' => get_string('carouseltab', 'local_mb2builder'),
		'price' => get_string('pricetab', 'local_mb2builder'),
		'style' => get_string('styletab', 'local_mb2builder')
	),
	'attr' => array(

		'excats'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('categories', 'local_mb2builder'),
			'options' => array(
				0 => get_string('showall', 'local_mb2builder'),
				'exclude' => get_string('exclude', 'local_mb2builder'),
				'include' => get_string('include', 'local_mb2builder')
			),
			'default' => 0
		),
		'catids'=>array(
			'type'=>'text',
			'section' => 'general',
			'showon' => 'excats:exclude|include',
			'title'=> get_string('catidslabel', 'local_mb2builder'),
			'desc'=> get_string('catidsdesc', 'local_mb2builder')
		),
		'excourses'=>array(
			'type'=>'list',
			'section' => 'general',
			'title'=> get_string('courses', 'local_mb2builder'),
			'options' => array(
				0 => get_string('showall', 'local_mb2builder'),
				'exclude' => get_string('exclude', 'local_mb2builder'),
				'include' => get_string('include', 'local_mb2builder')
			),
			'default' => 0
		),
		'courseids'=>array(
			'type'=>'text',
			'section' => 'general',
			'showon' => 'excourses:exclude|include',
			'title' => get_string('cids', 'local_mb2builder'),
			'desc' => get_string('cidsdesc', 'local_mb2builder')
		),
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
			'default'=> get_string('courses', 'local_mb2builder')
		),
		'layout'=>array(
			'type'=>'list',
			'section' => 'layouttab',
			'title'=> get_string('layouttab', 'local_mb2builder'),
			'options' => array(
				0 => get_string('none', 'local_mb2builder'),
				'cols' => get_string('columns', 'local_mb2builder'),
				'slidercols' => get_string('slidercolumns', 'local_mb2builder')
			),
			'default' => 'cols'
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
		'prestyle' => array(
			'type' => 'list',
			'section' => 'style',
			'title' => get_string('prestyle', 'local_mb2builder'),
			'options' => array(
				0 => get_string('none', 'local_mb2builder'),
			),
			'default' => 0
		),
		'courseprices'=>array(
			'type'=>'textarea',
			'section' => 'price',
			'title' => get_string('courseprices', 'local_mb2builder'),
			'desc' => get_string('coursepricesdesc', 'local_mb2builder')
		),
		'currency' => array(
			'type' => 'list',
			'section' => 'price',
			'title'=> get_string('currency', 'local_mb2builder'),
			'options' => array('ALL:4c,65,6b'=>'ALL','AFN:60b'=>'AFN','ARS:24'=>'ARS','AWG:192'=>'AWG','AUD:24'=>'AUD','AZN:43c,430,43d'=>'AZN','BSD:24'=>'BSD','BBD:24'=>'BBD','BYR:70,2e'=>'BYR','BZD:42,5a,24'=>'BZD','BMD:24'=>'BMD','BOB:24,62'=>'BOB','BAM:4b,4d'=>'BAM','BWP:50'=>'BWP','BGN:43b,432'=>'BGN','BRL:52,24'=>'BRL','BND:24'=>'BND','KHR:17db'=>'KHR','CAD:24'=>'CAD','KYD:24'=>'KYD','CLP:24'=>'CLP','CNY:a5'=>'CNY','COP:24'=>'COP','CRC:20a1'=>'CRC','HRK:6b,6e'=>'HRK','CUP:20b1'=>'CUP','CZK:4b,10d'=>'CZK','DKK:6b,72'=>'DKK','DOP:52,44,24'=>'DOP','XCD:24'=>'XCD','EGP:a3'=>'EGP','SVC:24'=>'SVC','EEK:6b,72'=>'EEK','EUR:20ac'=>'EUR','FKP:a3'=>'FKP','FJD:24'=>'FJD','GHC:a2'=>'GHC','GIP:a3'=>'GIP','GTQ:51'=>'GTQ','GGP:a3'=>'GGP','GYD:24'=>'GYD','HNL:4c'=>'HNL','HKD:24'=>'HKD','HUF:46,74'=>'HUF','ISK:6b,72'=>'ISK','INR:20a8'=>'INR','IDR:52,70'=>'IDR','IRR:fdfc'=>'IRR','IMP:a3'=>'IMP','ILS:20aa'=>'ILS','JMD:4a,24'=>'JMD','JPY:a5'=>'JPY','JEP:a3'=>'JEP','KZT:43b,432'=>'KZT','KES:4b,73,68,73'=>'KES','KGS:43b,432'=>'KGS','LAK:20ad'=>'LAK','LVL:4c,73'=>'LVL','LBP:a3'=>'LBP','LRD:24'=>'LRD','LTL:4c,74'=>'LTL','MKD:434,435,43d'=>'MKD','MYR:52,4d'=>'MYR','MUR:20a8'=>'MUR','MXN:24'=>'MXN','MNT:20ae'=>'MNT','MZN:4d,54'=>'MZN','NAD:24'=>'NAD','NPR:20a8'=>'NPR','ANG:192'=>'ANG','NZD:24'=>'NZD','NIO:43,24'=>'NIO','NGN:20a6'=>'NGN','KPW:20a9'=>'KPW','NOK:6b,72'=>'NOK','OMR:fdfc'=>'OMR','PKR:20a8'=>'PKR','PAB:42,2f,2e'=>'PAB','PYG:47,73'=>'PYG','PEN:53,2f,2e'=>'PEN','PHP:50,68,70'=>'PHP','PLN:7a,142'=>'PLN','QAR:fdfc'=>'QAR','RON:6c,65,69'=>'RON','RUB:440,443,431'=>'RUB','SHP:a3'=>'SHP','SAR:fdfc'=>'SAR','RSD:414,438,43d,2e'=>'RSD','SCR:20a8'=>'SCR','SGD:24'=>'SGD','SBD:24'=>'SBD','SOS:53'=>'SOS','ZAR:52'=>'ZAR','KRW:20a9'=>'KRW','LKR:20a8'=>'LKR','SEK:6b,72'=>'SEK','CHF:43,48,46'=>'CHF','SRD:24'=>'SRD','SYP:a3'=>'SYP','TWD:4e,54,24'=>'TWD','THB:e3f'=>'THB','TTD:54,54,24'=>'TTD','TRY:54,4c'=>'TRY','TRL:20a4'=>'TRL','TVD:24'=>'TVD','UAH:20b4'=>'UAH','GBP:a3'=>'GBP','USD:24'=>'USD','UYU:24,55'=>'UYU','UZS:43b,432'=>'UZS','VEF:42,73'=>'VEF','VND:20ab'=>'VND','YER:fdfc'=>'YER','ZWD:5a,24'=>'ZWD'),
			'default' => 'USD:24'
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


define('LOCAL_MB2BUILDER_SETTINGS_COURSES', serialize($mb2_settings));
