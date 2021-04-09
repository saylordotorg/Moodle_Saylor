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
 * @package filter_poodll
 * @copyright  2017 Justin Hunt (https://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \filter_poodll\poodlltools;

/**
 * Serves files for this plugin
 *
 *
 */
function filter_poodll_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $conf = get_config('filter_poodll');

    if ($context->contextlevel == CONTEXT_SYSTEM) {
        if ($filearea === poodlltools::CUSTOM_PLACEHOLDERAUDIO_FILEAREA ||
                $filearea === poodlltools::CUSTOM_PLACEHOLDERVIDEO_FILEAREA) {
            return poodlltools::internal_file_serve($filearea, $args, $forcedownload, $options);
        }
    }
    send_file_not_found();
}

/**
 * after an update of the placeholder file. We need to store the new file hash
 *
 */
function filter_poodll_update_placeholderaudiohash() {
    //set the default content hash
    $contenthash = poodlltools::AUDIO_PLACEHOLDER_HASH;
    $config = get_config('filter_poodll');

    //get file details
    $syscontext = \context_system::instance();
    $component = 'filter_poodll';
    $itemid = 0;
    $filepath = '/';
    $filename = $config->placeholderaudiofile;

    if ($filename) {
        $fs = get_file_storage();
        $thefile = $fs->get_file($syscontext->id, $component, poodlltools::CUSTOM_PLACEHOLDERAUDIO_FILEAREA, $itemid, $filepath,
                $filename);
        if ($thefile) {
            $contenthash = $thefile->get_contenthash();
        }
    }
    set_config('placeholderaudiohash', $contenthash, 'filter_poodll');
}

/**
 * after an update of the placeholder file. We need to store the new file hash
 *
 */
function filter_poodll_update_placeholdervideohash() {
    //set the default content hash
    $contenthash = poodlltools::VIDEO_PLACEHOLDER_HASH;
    $config = get_config('filter_poodll');

    //get file details
    $syscontext = \context_system::instance();
    $component = 'filter_poodll';
    $itemid = 0;
    $filepath = '/';
    $filename = $config->placeholdervideofile;

    if ($filename) {
        $fs = get_file_storage();
        $thefile = $fs->get_file($syscontext->id, $component, poodlltools::CUSTOM_PLACEHOLDERVIDEO_FILEAREA, $itemid, $filepath,
                $filename);
        if ($thefile) {
            $contenthash = $thefile->get_contenthash();
        }
    }
    set_config('placeholdervideohash', $contenthash, 'filter_poodll');
}

/**
 * called back on customcss or custom js update, to bump the rev flag
 * this is appended to the customcss url (and sometimes js) so will force a cache refresh
 *
 */
function filter_poodll_update_revision() {
    set_config('revision', time(), 'filter_poodll');
}
