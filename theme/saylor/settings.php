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
 * @package   theme_saylor
 * @copyright 2021 Saylor Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // $settings = new theme_saylor_admin_settingspage_tabs('themesettingsaylor', get_string('configtitle', 'theme_saylor'));
    $settings = new theme_boost_admin_settingspage_tabs('themesettingsaylor', get_string('configtitle', 'theme_saylor'));
    $page = new admin_settingpage('theme_saylor_general', get_string('generalsettings', 'theme_saylor'));

    // Preset.
    $name = 'theme_saylor/preset';
    $title = get_string('preset', 'theme_saylor');
    $description = get_string('preset_desc', 'theme_saylor');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_saylor', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configthemepreset($name, $title, $description, $default, $choices, 'saylor');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_saylor/presetfiles';
    $title = get_string('presetfiles','theme_saylor');
    $description = get_string('presetfiles_desc', 'theme_saylor');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);
    /*
    // Background image setting.
    $name = 'theme_saylor/backgroundimage';
    $title = get_string('backgroundimage', 'theme_saylor');
    $description = get_string('backgroundimage_desc', 'theme_saylor');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $body-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_saylor/brandcolor';
    $title = get_string('brandcolor', 'theme_saylor');
    $description = get_string('brandcolor_desc', 'theme_saylor');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    */
    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_saylor_advanced', get_string('advancedsettings', 'theme_saylor'));
    /*
    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_saylor/scsspre',
        get_string('rawscsspre', 'theme_saylor'), get_string('rawscsspre_desc', 'theme_saylor'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_saylor/scss', get_string('rawscss', 'theme_saylor'),
        get_string('rawscss_desc', 'theme_saylor'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    */
    // This is the descriptor for alert one.
    $name = 'theme_saylor/alert1info';
    $heading = get_string('alert1', 'theme_saylor');
    $information = get_string('alertinfodesc', 'theme_saylor');
    $setting = new admin_setting_heading($name, $heading, $information);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Enable alert.
    $name = 'theme_saylor/enablealert';
    $title = get_string('enablealert', 'theme_saylor');
    $description = get_string('enablealertdesc', 'theme_saylor');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert type.
    $name = 'theme_saylor/alert1type';
    $title = get_string('alerttype', 'theme_saylor');
    $description = get_string('alerttypedesc', 'theme_saylor');
    $alertinfo = get_string('alert_info', 'theme_saylor');
    $alertwarning = get_string('alert_warning', 'theme_saylor');
    $alertgeneral = get_string('alert_general', 'theme_saylor');
    $default = 'primary';
    $choices = array('primary' => $alertinfo, 'danger' => $alertwarning, 'success' => $alertgeneral);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert title.
    $name = 'theme_saylor/alert1title';
    $title = get_string('alerttitle', 'theme_saylor');
    $description = get_string('alerttitledesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert text.
    $name = 'theme_saylor/alert1text';
    $title = get_string('alerttext', 'theme_saylor');
    $description = get_string('alerttextdesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // This is the descriptor for alert two.
    $name = 'theme_saylor/alert2info';
    $heading = get_string('alert2', 'theme_saylor');
    $setting = new admin_setting_heading($name, $heading, $information);
    $page->add($setting);

    // Enable alert.
    $name = 'theme_saylor/enable2alert';
    $title = get_string('enablealert', 'theme_saylor');
    $description = get_string('enablealertdesc', 'theme_saylor');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert type.
    $name = 'theme_saylor/alert2type';
    $title = get_string('alerttype', 'theme_saylor');
    $description = get_string('alerttypedesc', 'theme_saylor');
    $alertinfo = get_string('alert_info', 'theme_saylor');
    $alertwarning = get_string('alert_warning', 'theme_saylor');
    $alertgeneral = get_string('alert_general', 'theme_saylor');
    $default = 'primary';
    $choices = array('primary' => $alertinfo, 'danger' => $alertwarning, 'success' => $alertgeneral);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert title.
    $name = 'theme_saylor/alert2title';
    $title = get_string('alerttitle', 'theme_saylor');
    $description = get_string('alerttitledesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert text.
    $name = 'theme_saylor/alert2text';
    $title = get_string('alerttext', 'theme_saylor');
    $description = get_string('alerttextdesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // This is the descriptor for alert three.
    $name = 'theme_saylor/alert3info';
    $heading = get_string('alert3', 'theme_saylor');
    $information = get_string('alertinfodesc', 'theme_saylor');
    $setting = new admin_setting_heading($name, $heading, $information);
    $page->add($setting);

    // Enable alert.
    $name = 'theme_saylor/enable3alert';
    $title = get_string('enablealert', 'theme_saylor');
    $description = get_string('enablealertdesc', 'theme_saylor');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert type.
    $name = 'theme_saylor/alert3type';
    $title = get_string('alerttype', 'theme_saylor');
    $description = get_string('alerttypedesc', 'theme_saylor');
    $alertinfo = get_string('alert_info', 'theme_saylor');
    $alertwarning = get_string('alert_warning', 'theme_saylor');
    $alertgeneral = get_string('alert_general', 'theme_saylor');
    $default = 'primary';
    $choices = array('primary' => $alertinfo, 'danger' => $alertwarning, 'success' => $alertgeneral);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert title.
    $name = 'theme_saylor/alert3title';
    $title = get_string('alerttitle', 'theme_saylor');
    $description = get_string('alerttitledesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Alert text.
    $name = 'theme_saylor/alert3text';
    $title = get_string('alerttext', 'theme_saylor');
    $description = get_string('alerttextdesc', 'theme_saylor');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
