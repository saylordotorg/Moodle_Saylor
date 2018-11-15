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
 * @package   theme_saylor
 * @copyright 2018 Saylor Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.                         
    $settings = new theme_boost_admin_settingspage_tabs('themesettingsaylor', get_string('configtitle', 'theme_saylor'));             
 
    // Each page is a tab - the first is the "General" tab.                                                                         
    $page = new admin_settingpage('theme_saylor_general', get_string('generalsettings', 'theme_saylor'));                             
 
    // Replicate the preset setting from boost.                                                                                     
    $name = 'theme_saylor/preset';                                                                                                   
    $title = get_string('preset', 'theme_saylor');                                                                                   
    $description = get_string('preset_desc', 'theme_saylor');                                                                        
    $default = 'default.scss';                                                                                                      
 
    // We list files in our own file area to add to the drop down. We will provide our own function to                              
    // load all the presets from the correct paths.                                                                                 
    $context = context_system::instance();                                                                                          
    $fs = get_file_storage();                                                                                                       
    $files = $fs->get_area_files($context->id, 'theme_saylor', 'preset', 0, 'itemid, filepath, filename', false);                    
 
    $choices = [];                                                                                                                  
    foreach ($files as $file) {                                                                                                     
        $choices[$file->get_filename()] = $file->get_filename();                                                                    
    }                                                                                                                               
    // These are the built in presets from Boost.                                                                                   
    $choices['default.scss'] = 'default.scss';                                                                                      
    $choices['plain.scss'] = 'plain.scss';                                                                                          
 
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Preset files setting.                                                                                                        
    $name = 'theme_saylor/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_saylor');                                                                               
    $description = get_string('presetfiles_desc', 'theme_saylor');                                                                   
 
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0, array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);
    // Must add the page after definiting all the settings!
    $settings->add($page);                                                                                                       
}