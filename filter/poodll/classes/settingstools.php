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

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

use \filter_poodll\constants;

/**
 *
 * This is a class containing static functions for general PoodLL filter things
 * like embedding recorders and managing them
 *
 * @package   filter_poodll
 * @since      Moodle 3.1
 * @copyright  2016 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settingstools {

    public static function fetch_jumpcat_items($poodllcat) {
        $items = array();
        $url = new \moodle_url('/admin/category.php', array('category' => $poodllcat));
        $items[] = new \admin_setting_heading('filter_poodll_jumpcat_settings', get_string('jumpcat_heading', 'filter_poodll'),
                get_string('jumpcat_explanation', 'filter_poodll', $url->out(false)));
        return $items;
    }

    public static function fetch_general_items() {
        global $CFG, $OUTPUT;
        $items = array();

        $items[] = new \admin_setting_heading('filter_poodll_registration_settings',
                get_string('filter_poodll_registration_heading', 'filter_poodll'),
                get_string('filter_poodll_registration_explanation', 'filter_poodll'));

        //cloud poodll credentials
        $items[] = new \admin_setting_configtext('filter_poodll/cpapiuser', get_string('cpapiuser', 'filter_poodll'),
                get_string('cpapiuser_details', 'filter_poodll'), '');

        $cloudpoodll_apiuser = get_config('filter_poodll', 'cpapiuser');
        $cloudpoodll_apisecret = get_config('filter_poodll', 'cpapisecret');
        $show_below_apisecret='';

        //if we have an API user and secret we fetch token
        if(!empty($cloudpoodll_apiuser) && !empty($cloudpoodll_apisecret)) {
            $lm = new \filter_poodll\licensemanager();
            $tokeninfo = $lm->fetch_token_for_display($cloudpoodll_apiuser, $cloudpoodll_apisecret);
            $show_below_apisecret=$tokeninfo;
        //if we have no API user and secret we show a "fetch from elsewhere on site" or "take a free trial" link
        }else{
            $amddata=['apppath'=>$CFG->wwwroot . '/' .constants::M_URL];
            $cp_components=['qtype_cloudpoodll','mod_readaloud','mod_wordcards','mod_solo','mod_minilesson','mod_englishcentral','mod_pchat',
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


        $items[] = new \admin_setting_configtext('filter_poodll/cpapisecret', get_string('cpapisecret', 'filter_poodll'),
            $show_below_apisecret, '');

        //Adding Amazon AWS regions
        $options = self::fetch_awsregion_options();
        $items[] = new \admin_setting_configselect('filter_poodll_aws_region', get_string('awsregion', 'filter_poodll'),
                get_string('awsregion_desc', 'filter_poodll'), awsremote::REGION_USE1, $options);


        $items[] =
                new \admin_setting_configcheckbox('filter_poodll_cloudrecording', get_string('usecloudrecording', 'filter_poodll'),
                        get_string('usecloudrecording_desc', 'filter_poodll'), 1);
        $items[] = new \admin_setting_configcheckbox('filter_poodll_cloudnotifications',
                get_string('usecloudnotifications', 'filter_poodll'), get_string('usecloudnotifications_desc', 'filter_poodll'), 1);

        //html5 recorder settings.
        $items[] = new \admin_setting_heading('filter_poodll_html5recorder_settings',
                get_string('filter_poodll_html5recorder_heading', 'filter_poodll'), '');
        $audiooptions = self::fetch_html5_recorder_items("audio");
        $videooptions = self::fetch_html5_recorder_items("video");
        $items[] = new \admin_setting_configselect('filter_poodll/html5recorder_skin_audio',
                get_string('html5recorder_skin_audio', 'filter_poodll'),
                '', 'onetwothree', $audiooptions);
        $items[] = new \admin_setting_configselect('filter_poodll/html5recorder_skin_video',
                get_string('html5recorder_skin_video', 'filter_poodll'),
                '', 'onetwothree', $videooptions);
        $items[] = new \admin_setting_configtext('filter_poodll/skinstyleaudio', get_string('skinstyleaudio', 'filter_poodll'),
                get_string('skinstyleaudio_details', 'filter_poodll'), '');
        $items[] = new \admin_setting_configtext('filter_poodll/skinstylevideo', get_string('skinstylevideo', 'filter_poodll'),
                get_string('skinstylevideo_details', 'filter_poodll'), '');

        $items[] = new \admin_setting_configcheckbox('filter_poodll_download_media_ok',
            get_string('showdownloadicon', 'filter_poodll'), get_string('showdownloadicon_desc', 'filter_poodll'), 0);


        //PoodLL Whiteboard
        $items[] = new \admin_setting_heading('filter_poodll_whiteboard_setting',
                get_string('filter_poodll_whiteboard_heading', 'filter_poodll'), '');
        $options = array('drawingboard' => 'Drawing Board(js)', 'literallycanvas' => 'Literally Canvas(js)');
        $items[] =
                new \admin_setting_configselect('filter_poodll_defaultwhiteboard', get_string('defaultwhiteboard', 'filter_poodll'),
                        '', 'literallycanvas', $options);
        $items[] = new \admin_setting_configtext('filter_poodll_whiteboardwidth', get_string('wboardwidth', 'filter_poodll'), '',
                '600', PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_whiteboardheight', get_string('wboardheight', 'filter_poodll'), '',
                '350', PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_autosavewhiteboard', get_string('wboardautosave', 'filter_poodll'),
                get_string('wboardautosave_details', 'filter_poodll'), 2000, PARAM_INT);
        $items[] = new \admin_setting_configcheckbox('filter_poodll_whiteboardnozoom', get_string('wboardnozoom', 'filter_poodll'),
                get_string('wboardnozoom_details', 'filter_poodll'), 0);

        return $items;

    }// end of fetch general items

    public static function fetch_advanced_items() {
        global $CFG;
        $items = array();

        //legacy license key stuff
        $regkey_desc = get_string('registrationkey_explanation', 'filter_poodll');
        if ($CFG && property_exists($CFG, 'filter_poodll_registrationkey') && !empty($CFG->filter_poodll_registrationkey)) {
            $lm = new \filter_poodll\licensemanager();
            $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
            $license_details = $lm->fetch_license_details();
            $display_license_details = get_string('license_details', 'filter_poodll', $license_details);
            $regkey_desc .= $display_license_details;
        }
        $items[] =
                new \admin_setting_configtextarea('filter_poodll_registrationkey', get_string('registrationkey', 'filter_poodll'),
                        $regkey_desc, '');

        /*
       //File Conversions
       */
        $items[] = new \admin_setting_heading('filter_poodll_transcode_settings', get_string('transcode_heading', 'filter_poodll'),
                get_string('transcode_heading_desc', 'filter_poodll'));
        $items[] = new \admin_setting_configcheckbox('filter_poodll_videotranscode', get_string('videotranscode', 'filter_poodll'),
                get_string('videotranscodedetails', 'filter_poodll'), 1);
        $items[] = new \admin_setting_configcheckbox('filter_poodll_audiotranscode', get_string('audiotranscode', 'filter_poodll'),
                get_string('audiotranscodedetails', 'filter_poodll'), 1);
        $items[] = new \admin_setting_configcheckbox('filter_poodll_ffmpeg', get_string('ffmpeg', 'filter_poodll'),
                get_string('ffmpeg_details', 'filter_poodll'), 0);
        $items[] = new \admin_setting_configtext('filter_poodll_ffmpeg_mp3opts', get_string('mp3opts', 'filter_poodll'),
                get_string('mp3opts_details', 'filter_poodll'), '');
        $items[] = new \admin_setting_configtext('filter_poodll_ffmpeg_mp4opts', get_string('mp4opts', 'filter_poodll'),
                get_string('mp4opts_details', 'filter_poodll'), '');
        $items[] = new \admin_setting_configcheckbox('filter_poodll_bgtranscode_video',
                get_string('bgtranscode_video', 'filter_poodll'), get_string('bgtranscodedetails_video', 'filter_poodll'), 1);
        $items[] = new \admin_setting_configcheckbox('filter_poodll_bgtranscode_audio',
                get_string('bgtranscode_audio', 'filter_poodll'), get_string('bgtranscodedetails_audio', 'filter_poodll'), 1);

        /*
         * Placeholder file and related settings
         */
        $items[] =
                new \admin_setting_heading('filter_poodll/placeholder_settings', get_string('placeholder_heading', 'filter_poodll'),
                        get_string('placeholder_heading_desc', 'filter_poodll'));
        //audio file
        $name = 'filter_poodll/placeholderaudiofile';
        $title = get_string('placeholderaudiofile', 'filter_poodll');
        $description = get_string('placeholderaudiofile_desc', 'filter_poodll');
        $itemid = 0;
        $setting = new \admin_setting_configstoredfile($name, $title, $description, poodlltools::CUSTOM_PLACEHOLDERAUDIO_FILEAREA,
                $itemid, array('accepted_types' => 'audio'));
        $setting->set_updatedcallback('filter_poodll_update_placeholderaudiohash');
        $items[] = $setting;
        //the length in seconds to one decimal place of the audio file
        $items[] = new \admin_setting_configtext('filter_poodll/placeholderaudiosecs',
                get_string('placeholderaudiosecs', 'filter_poodll'),
                get_string('placeholderaudiosecs_details', 'filter_poodll'), 0, PARAM_FLOAT);

        //video file
        $name = 'filter_poodll/placeholdervideofile';
        $title = get_string('placeholdervideofile', 'filter_poodll');
        $description = get_string('placeholdervideofile_desc', 'filter_poodll');
        $itemid = 0;
        $setting = new \admin_setting_configstoredfile($name, $title, $description, poodlltools::CUSTOM_PLACEHOLDERVIDEO_FILEAREA,
                $itemid, array('accepted_types' => 'video'));
        $setting->set_updatedcallback('filter_poodll_update_placeholdervideohash');
        $items[] = $setting;
        //the length in seconds to one decimal place of the video file
        $items[] = new \admin_setting_configtext('filter_poodll/placeholdervideosecs',
                get_string('placeholdervideosecs', 'filter_poodll'),
                get_string('placeholdervideosecs_details', 'filter_poodll'), 0, PARAM_FLOAT);

        /*
       //Logging
       */
        $items[] = new \admin_setting_heading('filter_poodll_debug_settings', get_string('debug_heading', 'filter_poodll'), '');
        $items[] = new \admin_setting_configcheckbox('filter_poodll_debug', get_string('debug_enable', 'filter_poodll'),
                get_string('debug_enable_details', 'filter_poodll'), 0);

        return $items;
    }

    public static function fetch_legacy_items() {
        global $CFG;
        $items = array();

        $items[] = new \admin_setting_heading('filter_poodll_recorderorder_heading', get_string('recorderorder', 'filter_poodll'),
            get_string('recorderorder_desc', 'filter_poodll'));

        //PoodLL player type settings.
        $items[] = new \admin_setting_configtext('filter_poodll_recorderorder_audio',
            get_string('recorderorder_audio', 'filter_poodll'),
            get_string('recorderorder_audio_desc', 'filter_poodll'), 'media,mobile,flashaudio,red5,upload', PARAM_TEXT);

        //PoodLL player type settings.
        $items[] = new \admin_setting_configtext('filter_poodll_recorderorder_video',
            get_string('recorderorder_video', 'filter_poodll'),
            get_string('recorderorder_video_desc', 'filter_poodll'), 'media,mobile,red5,upload', PARAM_TEXT);

        //PoodLL player type settings.
        $items[] = new \admin_setting_configtext('filter_poodll_recorderorder_whiteboard',
            get_string('recorderorder_whiteboard', 'filter_poodll'),
            get_string('recorderorder_whiteboard_desc', 'filter_poodll'), 'upload', PARAM_TEXT);

        //PoodLL player type settings.
        $items[] = new \admin_setting_configtext('filter_poodll_recorderorder_snapshot',
            get_string('recorderorder_snapshot', 'filter_poodll'),
            get_string('recorderorder_snapshot_desc', 'filter_poodll'), 'snapshot,upload', PARAM_TEXT);

        //Allow Adobe Flash on Android
        $items[] =
            new \admin_setting_configcheckbox('filter_poodll_flash_on_android', get_string('flashonandroid', 'filter_poodll'),
                get_string('flashonandroid_desc', 'filter_poodll'), 0);

        $items[] = new \admin_setting_configcheckbox('filter_poodll_html5ondsafari', get_string('html5ondsafari', 'filter_poodll'),
            get_string('html5ondsafaridetails', 'filter_poodll'), 0);

        //PoodLL Network Settings.
        $items[] = new \admin_setting_heading('filter_poodll_network_settings',
                get_string('filter_poodll_network_heading', 'filter_poodll'), '');
        $items[] = new \admin_setting_configtext('filter_poodll_servername', get_string('servername', 'filter_poodll'), '',
                'tokyo.poodll.com');
        $items[] = new \admin_setting_configtext('filter_poodll_serverid', get_string('serverid', 'filter_poodll'), '', 'poodll');
        $items[] = new \admin_setting_configtext('filter_poodll_serverport', get_string('serverport', 'filter_poodll'), '', '1935',
                PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_serverhttpport', get_string('serverhttpport', 'filter_poodll'), '',
                '443', PARAM_INT);
        $items[] =
                new \admin_setting_configcheckbox('filter_poodll_autotryports', get_string('autotryports', 'filter_poodll'), '', 1);

        //mp3 recorder settings.
        $items[] = new \admin_setting_heading('filter_poodll_mp3recorder_settings',
                get_string('filter_poodll_mp3recorder_heading', 'filter_poodll'), '');
        $options = array('normal' => get_string('normal', 'filter_poodll'), 'tiny' => get_string('tiny', 'filter_poodll'));
        $items[] =
                new \admin_setting_configselect('filter_poodll_mp3recorder_size', get_string('size', 'filter_poodll'), '', 'normal',
                        $options);
        $items[] =
                new \admin_setting_configcheckbox('filter_poodll_miccanpause', get_string('miccanpause', 'filter_poodll'), '', 0);
        $items[] =
                new \admin_setting_configcheckbox('filter_poodll_mp3recorder_nocloud', get_string('mp3_nocloud', 'filter_poodll'),
                        get_string('mp3_nocloud_details', 'filter_poodll'), 0);

        //video capture settings.
        $items[] = new \admin_setting_heading('filter_poodll_camera_settings',
                get_string('filter_poodll_camera_heading', 'filter_poodll'), '');
        $items[] = new \admin_setting_configtext('filter_poodll_studentcam', get_string('studentcam', 'filter_poodll'), '', '');
        $options = array('160' => '160x120', '320' => '320x240', '480' => '480x360', '640' => '640x480', '800' => '800x600',
                '1024' => '1024x768', '1280' => '1280x1024', '1600' => '1600x1200',);
        $items[] = new \admin_setting_configselect('filter_poodll_capturewidth', get_string('capturewidth', 'filter_poodll'), '',
                '480', $options);
        //$items[] = new \admin_setting_configtext('filter_poodll_captureheight', get_string('captureheight', 'filter_poodll'), '', '240', PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_capturefps', get_string('capturefps', 'filter_poodll'), '', '17',
                PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_bandwidth', get_string('bandwidth', 'filter_poodll'), '', '0',
                PARAM_INT);
        $items[] =
                new \admin_setting_configtext('filter_poodll_picqual', get_string('picqual', 'filter_poodll'), '', '7', PARAM_INT);

        //audio capture settings
        $items[] =
                new \admin_setting_heading('filter_poodll_mic_settings', get_string('filter_poodll_mic_heading', 'filter_poodll'),
                        '');
        $items[] = new \admin_setting_configtext('filter_poodll_studentmic', get_string('studentmic', 'filter_poodll'), '', '');
        $items[] =
                new \admin_setting_configtext('filter_poodll_micrate', get_string('micrate', 'filter_poodll'), '', '22', PARAM_INT);
        $items[] =
                new \admin_setting_configtext('filter_poodll_micsilencelevel', get_string('micsilencelevel', 'filter_poodll'), '',
                        '1', PARAM_INT);
        $items[] =
                new \admin_setting_configtext('filter_poodll_micgain', get_string('micgain', 'filter_poodll'), '', '50', PARAM_INT);
        $items[] = new \admin_setting_configtext('filter_poodll_micecho', get_string('micecho', 'filter_poodll'), '', 'yes');
        $items[] = new \admin_setting_configtext('filter_poodll_micloopback', get_string('micloopback', 'filter_poodll'), '', 'no');

        // PoodLL Flashcards
        $items[] = new \admin_setting_heading('filter_poodll_flashcards_settings',
                get_string('filter_poodll_flashcards_heading', 'filter_poodll'), '');
        $options = array('poodll' => 'PoodLL');//removed owl  2017/05/16, 'owl'=>"Owl"
        $items[] =
                new \admin_setting_configselect('filter_poodll_flashcards_type', get_string('flashcardstype', 'filter_poodll'), '',
                        'poodll', $options);

        return $items;

    }

    public static function fetch_mobile_items($conf) {
        global $CFG;

        $items = array();
        $items[] = new \admin_setting_configcheckbox('filter_poodll_mobile_show', get_string('mobile_show', 'filter_poodll'),
                get_string('mobile_show_desc', 'filter_poodll'), 0);

        $quality_options =
                array('low' => get_string('lowquality', 'filter_poodll'), 'medium' => get_string('mediumquality', 'filter_poodll'),
                        'high' => get_string('highquality', 'filter_poodll'));
        $items[] = new \admin_setting_configselect('filter_poodll_mobile_audio_quality',
                get_string('mobile_audio_quality', 'filter_poodll'), '', 'medium', $quality_options);
        $items[] = new \admin_setting_configselect('filter_poodll_mobile_video_quality',
                get_string('mobile_video_quality', 'filter_poodll'), '', 'medium', $quality_options);

        $camera_options =
                array('front' => get_string('camerafront', 'filter_poodll'), 'back' => get_string('cameraback', 'filter_poodll'));
        $items[] = new \admin_setting_configselect('filter_poodll_mobile_default_camera',
                get_string('default_camera', 'filter_poodll'), '', 'front', $camera_options);

        return $items;
    }

    public static function fetch_extension_items($conf) {
        //init return array
        $items = array();

        //add extensions csv list
        $defaultexts = implode(',', \filter_poodll\filtertools::fetch_default_extensions());
        $items[] = new \admin_setting_configtext('filter_poodll/extensions',
                get_string('extensions', 'filter_poodll'),
                get_string('extensions_desc', 'filter_poodll'),
                $defaultexts, PARAM_RAW, 70);

        //loop though extensions and offer a dropdownlist of players for each
        //get player option list
        $playeroptions = \filter_poodll\filtertools::fetch_players_list($conf);

        //if we have no players (could happen ...) provide something
        if (count($playeroptions) < 1) {
            $playeroptions[''] = get_string('none');
        }

        $extensions = \filter_poodll\filtertools::fetch_extensions();
        foreach ($extensions as $ext) {
            $ext = trim($ext);
            switch ($ext) {
                case 'youtube':
                    $def_player = '1';
                    break;
                case 'rss':
                    $def_player = '1';
                    break;
                default:
                    $def_player = '1';
            }
            $items[] = new \admin_setting_configcheckbox('filter_poodll/handle' . $ext,
                    get_string('handle', 'filter_poodll', strtoupper($ext)), '', 0);
            $items[] = new \admin_setting_configselect('filter_poodll/useplayer' . $ext,
                    get_string('useplayer', 'filter_poodll', strtoupper($ext)), get_string('useplayerdesc', 'filter_poodll'),
                    $def_player, $playeroptions);
        }
        return $items;
    }//end of fetch extension items

    public static function fetch_awsregion_options(){
        $options = array(awsremote::REGION_APN1 => get_string('REGION_APN1', 'filter_poodll'),
                awsremote::REGION_APS1 => get_string('REGION_APS1', 'filter_poodll'),
                awsremote::REGION_APSE1 => get_string('REGION_APSE1', 'filter_poodll'),
                awsremote::REGION_APSE2 => get_string('REGION_APSE2', 'filter_poodll'),
                awsremote::REGION_EUW1 => get_string('REGION_EUW1', 'filter_poodll'),
                awsremote::REGION_EUW2 => get_string('REGION_EUW2', 'filter_poodll'),
                awsremote::REGION_EUC1 => get_string('REGION_EUC1', 'filter_poodll'),

                awsremote::REGION_USE1 => get_string('REGION_USE1', 'filter_poodll'),
                awsremote::REGION_CAC1 => get_string('REGION_CAC1', 'filter_poodll'),
                awsremote::REGION_SAE1 => get_string('REGION_SAE1', 'filter_poodll'),
                awsremote::REGION_AFS1 => get_string('REGION_AFS1', 'filter_poodll'),
                awsremote::REGION_MES1 => get_string('REGION_MES1', 'filter_poodll')

        );
        return $options;
    }

    //The options for HTML5 recorder skins
    public static function fetch_html5_recorder_items($mediatype = "audio") {
        $items = array();
        switch ($mediatype) {
            case "video":
                $items['standard'] = get_string('plain_recorder', 'filter_poodll');
                $items['bmr'] = get_string('bmr_recorder', 'filter_poodll');
                $items['onetwothree'] = get_string('onetwothree_recorder', 'filter_poodll');
                $items['once'] = get_string('once_recorder', 'filter_poodll');
                $items['upload'] = get_string('upload_recorder', 'filter_poodll');
                $items['screen'] = get_string('screen_recorder', 'filter_poodll');
                break;

            case "audio":
            default:
                $items['standard'] = get_string('plain_recorder', 'filter_poodll');
                $items['bmr'] = get_string('bmr_recorder', 'filter_poodll');
                $items['onetwothree'] = get_string('onetwothree_recorder', 'filter_poodll');
                $items['once'] = get_string('once_recorder', 'filter_poodll');
                $items['fresh'] = get_string('fresh_recorder', 'filter_poodll');
                $items['gold'] = get_string('gold_recorder', 'filter_poodll');
                $items['upload'] = get_string('upload_recorder', 'filter_poodll');
        }

        return $items;

    }

    public static function fetch_widget_items() {

        $items = array();

        $items[] = new \admin_setting_configtext('filter_poodll/templatecount',
                get_string('templatecount', 'filter_poodll'),
                get_string('templatecount_desc', 'filter_poodll'),
                filtertools::FILTER_POODLL_TEMPLATE_COUNT, PARAM_INT, 20);
        return $items;

    }//end of function fetch widget items

    //make a readable template name for menus and lists etc
    public static function fetch_template_title($conf, $tindex, $typeprefix = true) {
        //template display name
        $tname = '';
        if ($conf && property_exists($conf, 'templatename_' . $tindex)) {
            $tname = $conf->{'templatename_' . $tindex};
        }
        if (empty($tname) && $conf && property_exists($conf, 'templatekey_' . $tindex)) {
            $tname = $conf->{'templatekey_' . $tindex};
        }
        if (empty($tname)) {
            $tname = $tindex;
        }

        if (!$typeprefix) {
            return $tname;
        }

        if ($conf && property_exists($conf, 'templatekey_' . $tindex) && property_exists($conf, 'template_showatto_' . $tindex) &&
                $conf->{'template_showatto_' . $tindex} > 0) {
            $templatetitle = get_string('templatepagewidgetheading', 'filter_poodll', $tname);
        } else if ($conf && property_exists($conf, 'templatekey_' . $tindex) &&
                property_exists($conf, 'template_showplayers_' . $tindex) && $conf->{'template_showplayers_' . $tindex} > 0) {
            $templatetitle = get_string('templatepageplayerheading', 'filter_poodll', $tname);
        } else {
            $templatetitle = get_string('templatepageheading', 'filter_poodll', $tname);
        }
        return $templatetitle;
    }

    /*
    public static function fetch_template_table(){

        $items=array();
        $items[] =new \filter_poodll\poodlltemplatetable('filter_poodll/templatetable',
            get_string('templates', 'filter_poodll'), '');
        return $items;

    }
    */

    public static function fetch_template_pages($conf) {
        $pages = array();

        //Add the template pages
        if ($conf && property_exists($conf, 'templatecount')) {
            $templatecount = $conf->templatecount;
        } else {
            $templatecount = filtertools::FILTER_POODLL_TEMPLATE_COUNT;
        }

        //fetch preset data, just once so we do nto need to repeat the call a zillion times
        $presetdata = poodllpresets::fetch_presets();

        for ($tindex = 1; $tindex <= $templatecount; $tindex++) {

            $templatetitle = \filter_poodll\settingstools::fetch_template_title($conf, $tindex);

            //template settings Page Settings
            $settings_page =
                    new \admin_settingpage('filter_poodll_templatepage_' . $tindex, $templatetitle, 'moodle/site:config', true);

            //template page heading
            $settings_page->add(new \admin_setting_heading('filter_poodll/templateheading_' . $tindex,
                    get_string('templateheading', 'filter_poodll', $templatetitle), ''));

            //presets
            $settings_page->add(new poodllpresets('filter_poodll/templatepresets_' . $tindex,
                    get_string('presets', 'filter_poodll'), get_string('presets_desc', 'filter_poodll'), $tindex, $presetdata));

            //template name
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templatename_' . $tindex,
                    get_string('templatename', 'filter_poodll', $tindex),
                    get_string('templatename_desc', 'filter_poodll'),
                    '', PARAM_TEXT));

            //template key
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templatekey_' . $tindex,
                    get_string('templatekey', 'filter_poodll', $tindex),
                    get_string('templatekey_desc', 'filter_poodll'),
                    '', PARAM_ALPHANUMEXT));

            //template version
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templateversion_' . $tindex,
                    get_string('templateversion', 'filter_poodll', $tindex),
                    get_string('templateversion_desc', 'filter_poodll'),
                    '', PARAM_TEXT));

            //template instructions
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/templateinstructions_' . $tindex,
                    get_string('templateinstructions', 'filter_poodll', $tindex),
                    get_string('templateinstructions_desc', 'filter_poodll'),
                    '', PARAM_RAW));

            //template show in atto editor
            $yesno = array('0' => get_string('no'), '1' => get_string('yes'));
            $settings_page->add(new \admin_setting_configselect('filter_poodll/template_showatto_' . $tindex,
                    get_string('template_showatto', 'filter_poodll', $tindex),
                    get_string('template_showatto_desc', 'filter_poodll'),
                    0, $yesno));

            //template show in player list
            $yesno = array('0' => get_string('no'), '1' => get_string('yes'));
            $settings_page->add(new \admin_setting_configselect('filter_poodll/template_showplayers_' . $tindex,
                    get_string('template_showplayers', 'filter_poodll', $tindex),
                    get_string('template_showplayers_desc', 'filter_poodll'),
                    0, $yesno));

            //template body
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/template_' . $tindex,
                    get_string('template', 'filter_poodll', $tindex),
                    get_string('template_desc', 'filter_poodll'), ''));

            //template body end
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/templateend_' . $tindex,
                    get_string('templateend', 'filter_poodll', $tindex),
                    get_string('templateend_desc', 'filter_poodll'), ''));

            //template defaults
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/templatedefaults_' . $tindex,
                    get_string('templatedefaults', 'filter_poodll', $tindex),
                    get_string('templatedefaults_desc', 'filter_poodll'), ''));

            //template page JS heading
            $settings_page->add(new \admin_setting_heading('filter_poodll/templateheading_js' . $tindex,
                    get_string('templateheadingjs', 'filter_poodll', $templatetitle), ''));

            //additional JS (external link)
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templaterequire_js_' . $tindex,
                    get_string('templaterequire_js', 'filter_poodll', $tindex),
                    get_string('templaterequire_js_desc', 'filter_poodll'),
                    '', PARAM_RAW, 50));

            //template amd
            $yesno = array('0' => get_string('no'), '1' => get_string('yes'));
            $settings_page->add(new \admin_setting_configselect('filter_poodll/template_amd_' . $tindex,
                    get_string('templaterequire_amd', 'filter_poodll', $tindex),
                    get_string('templaterequire_amd_desc', 'filter_poodll'),
                    1, $yesno));

            //template shim
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templaterequire_js_shim_' . $tindex,
                    get_string('templaterequire_js_shim', 'filter_poodll', $tindex),
                    get_string('templaterequire_js_shim_desc', 'filter_poodll'),
                    '', PARAM_TEXT, 50));

            //template body script
            $setting = new \admin_setting_configtextarea('filter_poodll/templatescript_' . $tindex,
                    get_string('templatescript', 'filter_poodll', $tindex),
                    get_string('templatescript_desc', 'filter_poodll'),
                    '', PARAM_RAW);
            $setting->set_updatedcallback('filter_poodll_update_revision');
            $settings_page->add($setting);

            //template page CSS heading
            $settings_page->add(new \admin_setting_heading('filter_poodll/templateheading_css_' . $tindex,
                    get_string('templateheadingcss', 'filter_poodll', $templatetitle), ''));

            //additional CSS (external link)
            $settings_page->add(new \admin_setting_configtext('filter_poodll/templaterequire_css_' . $tindex,
                    get_string('templaterequire_css', 'filter_poodll', $tindex),
                    get_string('templaterequire_css_desc', 'filter_poodll'),
                    '', PARAM_RAW, 50));

            //template body css
            $setting = new \admin_setting_configtextarea('filter_poodll/templatestyle_' . $tindex,
                    get_string('templatestyle', 'filter_poodll', $tindex),
                    get_string('templatestyle_desc', 'filter_poodll'),
                    '', PARAM_RAW);
            $setting->set_updatedcallback('filter_poodll_update_revision');
            $settings_page->add($setting);

            //dataset
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/dataset_' . $tindex,
                    get_string('dataset', 'filter_poodll', $tindex),
                    get_string('dataset_desc', 'filter_poodll'),
                    '', PARAM_RAW));

            //dataset vars
            $settings_page->add(new \admin_setting_configtext('filter_poodll/datasetvars_' . $tindex,
                    get_string('datasetvars', 'filter_poodll', $tindex),
                    get_string('datasetvars_desc', 'filter_poodll'),
                    '', PARAM_RAW, 50));

            //alternative content
            $defvalue = '';
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/templatealternate_' . $tindex,
                    get_string('templatealternate', 'filter_poodll', $tindex),
                    get_string('templatealternate_desc', 'filter_poodll'),
                    $defvalue, PARAM_RAW));
            $settings_page->add(new \admin_setting_configtextarea('filter_poodll/templatealternate_end_' . $tindex,
                    get_string('templatealternate_end', 'filter_poodll', $tindex),
                    get_string('templatealternate_end_desc', 'filter_poodll'),
                    $defvalue, PARAM_RAW));

            $pages[] = $settings_page;
        }

        return $pages;
    }//end of function fetch template pages

}//end of class
