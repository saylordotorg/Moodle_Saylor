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
 * Moodle's saylor theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_saylor
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Invert Navbar to dark background.


    // Custom CSS file.
    $name = 'theme_saylor/customcss';
    $title = get_string('customcss', 'theme_saylor');
    $description = get_string('customcssdesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // footerline setting
    $name = 'theme_saylor/fsize';
    $title = get_string('fsize', 'theme_saylor');
    $description = get_string('fsizedesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);

    // set pictures for frontpage
    $name = 'theme_saylor/p1';
    $title = get_string('p1', 'theme_saylor');
    $description = get_string('p1desc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);


    $name = 'theme_saylor/fpheight';
    $title = get_string('fpheight', 'theme_saylor');
    $description = get_string('fpheightdesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);

     $name = 'theme_saylor/intheight';
    $title = get_string('intheight', 'theme_saylor');
    $description = get_string('intheightdesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);


    // marketing spots
    $name = 'theme_saylor/market1';
    $title = get_string('market1', 'theme_saylor');
    $description = get_string('market1desc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'theme_saylor/market2';
    $title = get_string('market2', 'theme_saylor');
    $description = get_string('market2desc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'theme_saylor/market3';
    $title = get_string('market3', 'theme_saylor');
    $description = get_string('market3desc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'theme_saylor/market4';
    $title = get_string('market4', 'theme_saylor');
    $description = get_string('market4desc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    // logo
    $name = 'theme_saylor/logo';
    $title = get_string('logo', 'theme_saylor');
    $description = get_string('logodesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Turn on fluid width
    $name = 'theme_saylor/headeralign';
    $title = get_string('headeralign', 'theme_saylor');
    $description = get_string('headeraligndesc', 'theme_saylor');
    $default = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // link color setting
    $name = 'theme_saylor/linkcolor';
    $title = get_string('linkcolor', 'theme_saylor');
    $description = get_string('linkcolordesc', 'theme_saylor');
    $default = '#033e55';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // link hover color setting
    $name = 'theme_saylor/linkhover';
    $title = get_string('linkhover', 'theme_saylor');
    $description = get_string('linkhoverdesc', 'theme_saylor');
    $default = '#666666';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // main color setting
    $name = 'theme_saylor/maincolor';
    $title = get_string('maincolor', 'theme_saylor');
    $description = get_string('maincolordesc', 'theme_saylor');
    $default = '#173140';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // main color setting
    $name = 'theme_saylor/secondcolor';
    $title = get_string('secondcolor', 'theme_saylor');
    $description = get_string('secondcolordesc', 'theme_saylor');
    $default = '#9b7b2a';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // heading color setting
    $name = 'theme_saylor/backcolor';
    $title = get_string('backcolor', 'theme_saylor');
    $description = get_string('backcolordesc', 'theme_saylor');
    $default = '#eeeeee';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Foot note setting
    // footerline setting
    $name = 'theme_saylor/footnote';
    $title = get_string('footnote', 'theme_saylor');
    $description = get_string('footnotedesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);

    // social settings
    $name = 'theme_saylor/socialone';
    $title = get_string('socialone', 'theme_saylor');
    $description = get_string('socialonedesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    $name = 'theme_saylor/socialtwo';
    $title = get_string('socialtwo', 'theme_saylor');
    $description = get_string('socialtwodesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    $name = 'theme_saylor/socialthree';
    $title = get_string('socialthree', 'theme_saylor');
    $description = get_string('socialthreedesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    $name = 'theme_saylor/socialfour';
    $title = get_string('socialfour', 'theme_saylor');
    $description = get_string('socialfourdesc', 'theme_saylor');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);
}
