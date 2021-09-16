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
 * readaloud module admin settings and defaults
 *
 * @package    mod
 * @subpackage readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/mod/readaloud/lib.php');

use \mod_readaloud\constants;
use \mod_readaloud\utils;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtextarea(constants::M_COMPONENT . '/defaultwelcome',
            get_string('welcomelabel', constants::M_COMPONENT), get_string('welcomelabel_details', constants::M_COMPONENT),
            get_string('defaultwelcome', constants::M_COMPONENT), PARAM_TEXT));
    $settings->add(new admin_setting_configtextarea(constants::M_COMPONENT . '/defaultfeedback',
            get_string('feedbacklabel', constants::M_COMPONENT), get_string('feedbacklabel_details', constants::M_COMPONENT),
            get_string('defaultfeedback', constants::M_COMPONENT), PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMPONENT . '/targetwpm',
            get_string('targetwpm', constants::M_COMPONENT), get_string('targetwpm_details', constants::M_COMPONENT), 100,
            PARAM_INT));

    $settings->add(new admin_setting_configtext(constants::M_COMPONENT . '/apiuser',
            get_string('apiuser', constants::M_COMPONENT), get_string('apiuser_details', constants::M_COMPONENT), '', PARAM_TEXT));

    $tokeninfo = utils::fetch_token_for_display(get_config(constants::M_COMPONENT, 'apiuser'),
            get_config(constants::M_COMPONENT, 'apisecret'));
    //get_string('apisecret_details', constants::M_COMPONENT)
    $settings->add(new admin_setting_configtext(constants::M_COMPONENT . '/apisecret',
            get_string('apisecret', constants::M_COMPONENT), $tokeninfo, '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/enableai',
            get_string('enableai', constants::M_COMPONENT), get_string('enableai_details', constants::M_COMPONENT), 1));

    //we removed this to simplify things, can bring back as feature later
    $accadjust_options = \mod_readaloud\utils::get_accadjust_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . '/accadjustmethod',
            get_string('accadjustmethod', constants::M_COMPONENT),
            get_string('accadjustmethod_details', constants::M_COMPONENT),
            constants::ACCMETHOD_NONE, $accadjust_options));

    $settings->add(new admin_setting_configtext(constants::M_COMPONENT . '/accadjust',
            get_string('accadjust', constants::M_COMPONENT), get_string('accadjust_details', constants::M_COMPONENT), 0,
            PARAM_INT));

    $regions = \mod_readaloud\utils::get_region_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . '/awsregion',
            get_string('awsregion', constants::M_COMPONENT),
            get_string('awsregion_details',constants::M_COMPONENT), 'useast1', $regions));

    $expiredays = \mod_readaloud\utils::get_expiredays_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . '/expiredays',
            get_string('expiredays', constants::M_COMPONENT), '', '365', $expiredays));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/allowearlyexit',
            get_string('allowearlyexit', constants::M_COMPONENT),
            get_string('allowearlyexit_defaultdetails', constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/submitrawaudio',
            get_string('submitrawaudio', constants::M_COMPONENT), get_string('submitrawaudio_details', constants::M_COMPONENT), 0));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/stricttranscribe',
            get_string('stricttranscribe', constants::M_COMPONENT), get_string('stricttranscribemode_details', constants::M_COMPONENT), 0));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/enablepreview',
            get_string('enablepreview', constants::M_COMPONENT),
            get_string('enablepreview_details', constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/enableshadow',
            get_string('enableshadow', constants::M_COMPONENT),
            get_string('enableshadow_details', constants::M_COMPONENT), 0));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/enablelandr',
            get_string('enablelandr', constants::M_COMPONENT),
            get_string('enablelandr_details', constants::M_COMPONENT), 1));

    //Default recorders
    $rec_options = utils::fetch_options_recorders();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT .'/defaultrecorder',
            new lang_string('defaultrecorder', constants::M_COMPONENT),
            new lang_string('defaultrecorder_details', constants::M_COMPONENT), constants::REC_ONCE, $rec_options));



    //session score method
    $name = 'sessionscoremethod';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = constants::SESSIONSCORE_NORMAL;
    $options = \mod_readaloud\utils::get_sessionscore_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));


    //machine grade method
    $name = 'machinegrademethod';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = constants::MACHINEGRADE_HYBRID;
    $options = \mod_readaloud\utils::get_machinegrade_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));

    //Evaluation view (what students see after an attempt)
    $name = 'humanpostattempt';
    $label = get_string('evaluationview', constants::M_COMPONENT);
    $details = get_string('evaluationview_details', constants::M_COMPONENT);
    $default = constants::POSTATTEMPT_EVALERRORS;
    $options = \mod_readaloud\utils::get_postattempt_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));
    /*
        $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/machinepostattempt',
            get_string('machinepostattempt', constants::M_COMPONENT),
            get_string('machinepostattempt_details',constants::M_COMPONENT),
            constants::POSTATTEMPT_EVAL, $postattempt_options));
        */

    /*
	 $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enabletts',
	 get_string('enabletts', constants::M_COMPONENT), get_string('enabletts_details',constants::M_COMPONENT), 0));
	 */

    //Language options
    $name = 'ttslanguage';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = constants::M_LANG_ENUS;
    $options = \mod_readaloud\utils::get_lang_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));

    //TTS voice
    $name = 'ttsvoice';
    $label = get_string($name, constants::M_COMPONENT);
    $details = "";
    $default = "Amy";
    $options = \mod_readaloud\utils::fetch_ttsvoice_options();
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
