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
 * filter poodll installation tasks
 *
 * @package    filter_poodll
 * @copyright  2016 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Install the plugin.
 */
function xmldb_filter_poodll_install() {
    $presets = \filter_poodll\poodllpresets::fetch_presets();
    $forinstall = array('fff', 'flowplayer', 'mediaelementvideo', 'videojs', 'nativevideo', 'audiojs_shim', 'mediaelementaudio',
            'nativeaudio', 'youtubeplayer', 'youtube','pw-onceaudio', 'pw-multiplayeraudio','pw-poodllaudio','superinteractiveaudio','superinteractivevideo',
            'tabs', 'tabitem', 'accordian', 'accordianitem',
            'Button-Maker','countdown','dice','flipclock','icontoggle','lightbox2','poodllcalc','popover','popuprecorder','speechcards',
            'textblockreader','tta','selecttoread');
    $templateindex = 0;
    foreach ($presets as $preset) {
        if (in_array($preset['key'], $forinstall)) {
            $templateindex++;
            //set the config
            \filter_poodll\poodllpresets::set_preset_to_config($preset, $templateindex);
        }
    }//end of for each presets	

    //Set the handlers
    set_config('handlemp4', 1, 'filter_poodll');
    set_config('handlemp3', 1, 'filter_poodll');
    set_config('handleyoutube', 1, 'filter_poodll');
    set_config('useplayermp4', 'fff', 'filter_poodll');
    set_config('useplayermp3', 'mediaelementaudio', 'filter_poodll');
    set_config('useplayeryoutube', 'youtubeplayer', 'filter_poodll');
    set_config('useplayerwebm', 'nativevideo', 'filter_poodll');
    set_config('useplayerflv', 'fff', 'filter_poodll');
    set_config('useplayerogv', 'nativevideo', 'filter_poodll');
    set_config('useplayerogg', 'nativeaudio', 'filter_poodll');
}
