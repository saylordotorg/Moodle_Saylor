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



function local_mb2builder_get_elements()
{


    $elements = array();
    $elements_dir_contents = scandir(LOCAL_MB2BUILDER_PATH_ELEMENTS);

    if (is_dir(LOCAL_MB2BUILDER_PATH_THEME_ELEMENTS))
    {
        $theme_elements_dir_contents = scandir(LOCAL_MB2BUILDER_PATH_THEME_ELEMENTS);

        foreach ($theme_elements_dir_contents as $element)
        {
        	if($element === '..' || $element === '.')
        	{
        		continue;
        	}

        	if (is_dir(LOCAL_MB2BUILDER_PATH_THEME_ELEMENTS . '/'. $element))
        	{
        		$elements[] = $element;
        	}
        }
    }


    foreach ($elements_dir_contents as $element)
    {
    	if($element === '..' || $element === '.')
    	{
    		continue;
    	}

    	if (is_dir(LOCAL_MB2BUILDER_PATH_ELEMENTS . '/'. $element))
    	{
    		$elements[] = $element;
    	}
    }

    sort($elements);

    return array_unique($elements);

}



function local_mb2builder_get_icons_arr($path, $class_prefix = 'fa-', $output_pref = ''){


    $icons = array();

    if( !file_exists($path) )
    {
        return $icons;
    }

    $css = file_get_contents($path);
    $pattern = '/\.('. $class_prefix .'(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';

    preg_match_all($pattern, $css, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {

        if ($output_pref)
        {
            $match1 = str_replace($class_prefix, $output_pref, $match[1]);
            $icons[$match1] = $match[2];
        }
        else
        {
            $icons[$match[1]] = $match[2];
        }


    }
    return $icons;

}



function local_mb2builder_input_showon ($data)
{

    $output = '';
    $i = 0;

    $data_base = explode(',', $data);

    foreach ($data_base as $data)
    {
        $i++;
        $output .= ' data-showon' . $i . '="' . $data . '"';
    }



    return $output;


}
