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
 * solo module admin settings and defaults
 *
 * @package    mod
 * @subpackage solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/mod/solo/lib.php');

use \mod_solo\constants;
use \mod_solo\utils;

if ($ADMIN->fulltree) {


    $settings->add(new admin_setting_configtext(constants::M_COMPONENT .  '/apiuser',
        get_string('apiuser', constants::M_COMPONENT), get_string('apiuser_details', constants::M_COMPONENT), '', PARAM_TEXT));

    $cloudpoodll_apiuser=get_config(constants::M_COMPONENT,'apiuser');
    $cloudpoodll_apisecret=get_config(constants::M_COMPONENT,'apisecret');
    $show_below_apisecret='';
//if we have an API user and secret we fetch token
    if(!empty($cloudpoodll_apiuser) && !empty($cloudpoodll_apisecret)) {
        $tokeninfo = utils::fetch_token_for_display($cloudpoodll_apiuser,$cloudpoodll_apisecret);
        $show_below_apisecret=$tokeninfo;
//if we have no API user and secret we show a "fetch from elsewhere on site" or "take a free trial" link
    }else{
        $amddata=['apppath'=>$CFG->wwwroot . '/' .constants::M_URL];
        $cp_components=['filter_poodll','qtype_cloudpoodll','mod_readaloud','mod_wordcards','mod_minilesson','mod_englishcentral','mod_pchat',
            'atto_cloudpoodll','tinymce_cloudpoodll', 'assignsubmission_cloudpoodll','assignfeedback_cloudpoodll'];
        foreach($cp_components as $cp_component){
            switch($cp_component){
                case 'filter_poodll':
                    $apiusersetting='cpapiuser';
                    $apisecretsetting='cpapisecret';
                    break;
                case 'mod_englishcentral':
                    $apiusersetting='poodllapiuser';
                    $apisecretsetting='poodllapisecret';
                    break;
                default:
                    $apiusersetting='apiuser';
                    $apisecretsetting='apisecret';
            }
            $cloudpoodll_apiuser=get_config($cp_component,$apiusersetting);
            if(!empty($cloudpoodll_apiuser)){
                $cloudpoodll_apisecret=get_config($cp_component,$apisecretsetting);
                if(!empty($cloudpoodll_apisecret)){
                    $amddata['apiuser']=$cloudpoodll_apiuser;
                    $amddata['apisecret']=$cloudpoodll_apisecret;
                    break;
                }
            }
        }
        $show_below_apisecret=$OUTPUT->render_from_template( constants::M_COMPONENT . '/managecreds',$amddata);
    }


    $settings->add(new admin_setting_configtext(constants::M_COMPONENT .  '/apisecret',
        get_string('apisecret', constants::M_COMPONENT),$show_below_apisecret , '', PARAM_TEXT));


    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/multipleattempts',
            get_string('multiattempts', constants::M_COMPONENT), get_string('multiattempts_details',constants::M_COMPONENT), 0));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enabletranscription',
            get_string('enabletranscription', constants::M_COMPONENT), get_string('enabletranscription_details',constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enableautograde',
            get_string('enableautograde', constants::M_COMPONENT), get_string('enableautograde_details',constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enableai',
        get_string('enableai', constants::M_COMPONENT), get_string('enableai_details',constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enablesuggestions',
        get_string('default_enablesuggestions', constants::M_COMPONENT), get_string('enablesuggestions_details',constants::M_COMPONENT), 1));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enabletts',
        get_string('default_enabletts', constants::M_COMPONENT), get_string('enabletts_details',constants::M_COMPONENT), 0));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enablegallery',
        get_string('enablegallery', constants::M_COMPONENT), get_string('enablegallery_details',constants::M_COMPONENT), 0));

    $regions = \mod_solo\utils::get_region_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/awsregion',
            get_string('awsregion', constants::M_COMPONENT), '', 'useast1', $regions));

    $expiredays = \mod_solo\utils::get_expiredays_options();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/expiredays',
            get_string('expiredays', constants::M_COMPONENT), '', '365', $expiredays));

	 $langoptions = \mod_solo\utils::get_lang_options();
	 $settings->add(new admin_setting_configselect(constants::M_COMPONENT .  '/ttslanguage',
             get_string('ttslanguage', constants::M_COMPONENT), '',
             constants::M_LANG_ENUS, $langoptions));


    // Transcriber options
    $name = 'transcriber';
    $label = get_string($name, constants::M_COMPONENT);
    $details = get_string($name . '_details', constants::M_COMPONENT);
    $default = constants::TRANSCRIBER_OPEN;
    $options = utils::fetch_options_transcribers();
    $settings->add(new admin_setting_configselect(constants::M_COMPONENT . "/$name",
            $label, $details, $default, $options));


    $settings->add(new admin_setting_confightmleditor(constants::M_COMPONENT . '/speakingtips',get_string('speakingtips', constants::M_COMPONENT),
            get_string('speakingtips_details', constants::M_COMPONENT),get_string('speakingtips_default', constants::M_COMPONENT)));

	$settings->add(new admin_setting_configtext(constants::M_COMPONENT .  '/attemptsperpage',
        get_string('attemptsperpage', constants::M_COMPONENT), get_string('attemptsperpage_details', constants::M_COMPONENT), 10, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enablelocalpost',
        get_string('enablelocalpost', constants::M_COMPONENT), get_string('enablelocalpost_details',constants::M_COMPONENT), 0));


    $settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT .  '/enablesetuptab',
            get_string('enablesetuptab', constants::M_COMPONENT), get_string('enablesetuptab_details',constants::M_COMPONENT), 0));


}
