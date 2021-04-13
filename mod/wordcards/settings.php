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
 * Wordcards module admin settings and defaults
 *
 * @package    mod
 * @subpackage wordcards
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \mod_wordcards\constants;
use \mod_wordcards\utils;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext(constants::M_COMPONENT .  '/apiuser',
            get_string('apiuser', constants::M_COMPONENT), get_string('apiuser_details', constants::M_COMPONENT), '', PARAM_TEXT));

    $tokeninfo =   utils::fetch_token_for_display(get_config(constants::M_COMPONENT,'apiuser'),get_config(constants::M_COMPONENT,'apisecret'));
    //get_string('apisecret_details', constants::M_COMPONENT)
    $settings->add(new admin_setting_configtext(constants::M_COMPONENT .  '/apisecret',
        get_string('apisecret', constants::M_COMPONENT),$tokeninfo , '', PARAM_TEXT));

    $regions = utils::get_region_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/awsregion', get_string('awsregion', constants::M_COMPONENT), '', 'useast1', $regions));

    $expiredays = utils::get_expiredays_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/expiredays', get_string('expiredays', constants::M_COMPONENT), '', '365', $expiredays));

    //Language options
    $name = 'ttslanguage';
    $label = get_string($name, constants::M_COMPONENT);
    $details ="";
    $default = constants::M_LANG_ENUS;
    $options = utils::get_lang_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));

    // Transcriber options
    $name = 'transcriber';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
    $options = utils::fetch_options_transcribers();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));

    // Items per page options
    $name = 'itemsperpage';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = 10;
    $settings->add(new admin_setting_configtext(constants::M_COMPONENT . "/$name",
            $label, $details, $default, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enablesetuptab',
            get_string('enablesetuptab', constants::M_COMPONENT), get_string('enablesetuptab_details',constants::M_COMPONENT), 0));




}
