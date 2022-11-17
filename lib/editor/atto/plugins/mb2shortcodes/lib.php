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
 * @package		Mb2 Shortcodes Button
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2017 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/

defined('MOODLE_INTERNAL') || die();




/**
 * Initialise the strings required for JS.
 *
 * @return void
 */
 
if (!function_exists('atto_mb2shortcodes_strings_for_js'))
{
	
	function atto_mb2shortcodes_strings_for_js() {
		global $PAGE;
	
		// In order to prevent extra strings to be imported, comment/uncomment the characters
		// which are enabled in the JavaScript part of this plugin.
		$PAGE->requires->strings_for_js(
			array(
						
				
				// Plugin
				'pluginname',
				'insertshortcode',
							
				
				// General string			
				'active',
				'after',
				'align',
				'before',
				'bgcolor',
				'bgimage',
				'bottom',
				'center',
				'color',
				'color_desc',
				'content',
				'custom_color',
				'danger',
				'dark',
				'dark_striped',
				'default',
				'description',
				'gray',
				'horizontal',
				'height',			
				'htmltag',
				'icon',
				'image',
				'none',
				'info',
				'inverse',
				'large',
				'left',
				'light',
				'light_striped',
				'link',
				'link_target',
				'link_target_blank',
				'link_target_self',
				'margin',
				'margin_desc',
				'name',
				'nline',
				'no',
				'normal',
				'primary',
				'right',
				'rounded',
				'size',
				'small',
				'style',
				'subtext',
				'success',
				'text',
				'title',
				'top',
				'type',
				'warning',
				'width',
				'xlarge',
				'xsmall',
				'yes',		
				
				
				
				
				
				// Dialog
				'generate',
				'newritem',
				'insertshortcode',
				'selectshortcode',
				'addimage',
				'addicon',
				'generateshortcode',
				'backtochooser',
				
				// Shortcodes: Box content
				'boxcontent',
				'linktext',
				
				
				// Shortcodes: Box icon
				'boxicon',
				
				
				// Shortcodes: Box image
				'boximg',
				
				
				// Shortcodes: Button
				'button',
				'button_border',
				'button_fw',
				'button_rounded',
				'button_ttpos',
				'button_tttext',			
				
				
				// Shortcodes: Accordion
				'accordion',
				'accordion_parent',
				
				
				// Shortcodes: Columns
				'columns',
				'column_13',
				'column_23',
				'column_12',
				'column_14',
				'column_34',
				
				
				// Shortcodes: Gap
				'gap',
				'gap_smallscreen',
				
				
				// Shortcodes: Headings
				'header',
				
				
				// Shortcodes: Headings
				'headings',
				
				
				// Shortcodes: Highlight
				'highlight',			
				
				
				// Shortcodes: Icon
				'icon',
				'icon_spin',
				'icon_sizebg',
				'icon_rotate',
				'icon_rotate_90',
				'icon_rotate_180',
				'icon_rotate_270',
				'icon_rotate_fh',
				'icon_rotate_fv',
				'icon_text_pos',
				
				
				// Shortcodes: Image
				'widthdesc',
				'alttext',
				'pausetime',
				'animtime',
				
				
				// Shotcodes: Line
				'line',
				'double',
				
				
				// Shotcodes: List
				'list',
				
				// Shortcodes: Slider
				'slider',
				'loop',
				'nav',
				'dots',
				'autoplay',
				'gutter',
				
				
				// Shotcodes: Tabs
				'tabs',
				'tabs_pos',
				
				
				// Shotcodes: Video
				'video',
				'video_id',
				'video_bg_image',
				'video_ratio',
				'video_id_desc'
				
				
			),
			'atto_mb2shortcodes'
		);
	}


}





if (!function_exists('atto_mb2shortcodes_params_for_js'))
{

	function atto_mb2shortcodes_params_for_js($elementid, $options, $fpoptions) {
	   
		global $OUTPUT;
		global $PAGE;
		
		
		$context = $options['context'];
		if (!$context) {
			$context = context_system::instance();
		}
		
		
		
		// Tex example librarys.
		$library = array(
				'group1' => array(
					'groupname' => 'librarygroup1',
					'elements' => get_config('atto_equation', 'librarygroup1'),
				),
				'group2' => array(
					'groupname' => 'librarygroup2',
					'elements' => get_config('atto_equation', 'librarygroup2'),
				),
				'group3' => array(
					'groupname' => 'librarygroup3',
					'elements' => get_config('atto_equation', 'librarygroup3'),
				),
				'group4' => array(
					'groupname' => 'librarygroup4',
					'elements' => get_config('atto_equation', 'librarygroup4'),
				));
	
		return array('contextid' => $context->id,
					 'library' => $library,
					 'texdocsurl' => get_docs_url('Using_TeX_Notation'));
		
	}

}