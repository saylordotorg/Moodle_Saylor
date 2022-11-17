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

define('AJAX_SCRIPT', true);


require_once(__DIR__ . '/../../../../../config.php');


$contextid = required_param('contextid', PARAM_INT);
list($context, $course, $cm) = get_context_info_array($contextid);
$PAGE->set_url('/lib/editor/atto/plugins/mb2shortcodes/ajax.php');
$PAGE->set_context($context);


require_login($course, false, $cm);
require_sesskey();


$shortcode = required_param('shortcode', PARAM_ALPHA);
$serializedForm = required_param('text', PARAM_RAW);
$serializedForm2 = str_replace(array('%5B','%5D'),array('[',']'), $serializedForm);


parse_str($serializedForm2, $shortcodeArr);
echo atto_mb2shortcodes_write_shortcode($shortcode,$shortcodeArr);


die();


function atto_mb2shortcodes_write_shortcode($shortcode, $arr) 
{
		
	$output = '';
		
	
	$output .= '[' . $shortcode . atto_mb2shortcodes_get_attribs($arr, array('exclude'=>array('text', 'content', 'repeat'))) . ']';	
	$output .= atto_mb2shortcodes_get_text($arr);
	$output .= '[/' . $shortcode . ']';	
	
	
	return $output;	
	
}





function atto_mb2shortcodes_get_attribs($arr, $attribs = array()) 
{	
	
	$output = '';
	
	
	foreach ($arr as $k=>$v)
	{			
				
		$isk = explode(':', $k);		
		
		$output .= (!in_array($isk[0],$attribs['exclude']) && $v!='') ? ' ' . $isk[0] . '="' . $v . '"' : '';
								
	}
	
		
	return $output;		
}





function atto_mb2shortcodes_get_text($arr, $repeateble = '', $attribs = array()) 
{

	$output = '';
	$counter = 0;
	
	foreach ($arr as $k=>$v)
	{			
						
		
		if ($k === 'text' || $k === 'content')
		{
			
			$output .= $v;
					
		}
		elseif($k === 'repeat')
		{		
			
			$output .= atto_mb2shortcodes_get_repeat($v);
				
		}	
				
	}
	
		
	return $output;	
	
}









function atto_mb2shortcodes_get_repeat($arr) 
{
	
	
	$output = '';
	$i = 0;
	
	
	$arrKeys = array_keys($arr);
	$isKey = $arrKeys[0];
	$itemsCount = count($arr[$isKey]);
	$tabName = explode(':', $isKey);
	
	
	for ($i = 0; $i<$itemsCount; $i++)
	{
		
		$isFirstP = $i == 0 ? '</p>' : '';		
		$output .= $isFirstP . '<p>[' . $tabName[1] . atto_mb2shortcodes_get_repeat_attr($arr, $i, array('exclude'=>array('text', 'content', 'repeat'))) . ']';
		
		
		$output .= atto_mb2shortcodes_get_repeat_text($arr, $i);			
		
		
		$isLastP = (($i+1) == $itemsCount) ? '<p>' : '';		
		$output .= '[/' . $tabName[1] . ']</p>' . $isLastP;
		
	}
	
	
	
	
	
	
	
	
	return $output;
	
	
}








function atto_mb2shortcodes_get_repeat_text($arr,$cointer)
{
	
	$output = '';
	
	foreach ($arr as $k=>$v)
	{
			
						
			
		foreach ($v as $k2=>$v2)
		{
				
			if ($k2 == $cointer)
			{
				
				$isK = explode(':', $k);
				
				
				if ($isK[0] === 'text' || $isK[0] === 'content')
				{
					$output .= $v2;
					
				}
				
				
			}
					
		}
			
	}
	
	return $output;
	
}




function atto_mb2shortcodes_get_repeat_attr($arr, $cointer, $attribs)
{
	
	$output = '';
	
	foreach ($arr as $k=>$v)
	{
			
						
			
		foreach ($v as $k2=>$v2)
		{
				
			if ($k2 == $cointer)
			{
				
				$isK = explode(':', $k);
				
				
				$output .= (!in_array($isK[0],$attribs['exclude']) && $v2!='') ? ' ' . $isK[0] . '="' . $v2 . '"' : '';
			}
					
		}
			
	}
	
	return $output;
	
} 