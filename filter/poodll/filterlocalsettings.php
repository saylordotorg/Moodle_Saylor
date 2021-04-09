<?php
/*
 * Video Easy Moodle filter
* Copyright (C) 2014 Justin hunt
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * PoodLL filter local settings
 *
 * @package    filter
 * @subpackage poodll
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class poodll_filter_local_settings_form extends filter_local_settings_form {
    protected function definition_inner($mform) {
        global $CFG;

        //get the players we use and the extensions we handle
        $siteconf = get_config('filter_poodll');
        $playeroptions = \filter_poodll\filtertools::fetch_players_list($siteconf);
        $extensions = \filter_poodll\filtertools::fetch_extensions();

        //create player select list
        $playeroptions['sitedefault'] = get_string('sitedefault', 'filter_poodll');

        $mform->addElement('header', 'extensions', get_string('extensions', 'filter_poodll'));
        $mform->setExpanded('extensions', true);

        //add extensions checkbox and dropdown list
        foreach ($extensions as $ext) {
            $ext = trim($ext);
            //extension checkbox	
            $elname = 'handle' . $ext;
            $mform->addElement('advcheckbox', $elname,
                    get_string('handle', 'filter_poodll', $ext),
                    '',
                    array('group' => 1), array(0, 1));
            $mform->setType($elname, PARAM_INT);
            $mform->setDefault($elname,
                    ($siteconf && property_exists($siteconf, 'handle' . $ext)) ? $siteconf->{'handle' . $ext} : 0);

            //player dropdown list
            $elname = 'useplayer' . $ext;
            $mform->addElement('select', $elname, get_string('useplayer', 'filter_poodll', strtoupper($ext)), $playeroptions);
            $mform->setDefault($elname, 'sitedefault');
        }
        /*
                mappings
                filter_poodll_html5recorder_skin -> 'filter_poodll/html5recorder_skin_audio'
                filter_poodll_html5recorder_skin -> 'filter_poodll/html5recorder_skin_video'
                filter_poodll_html5recorder_skinstyle_audio ->  'filter_poodll/skinstyleaudio'
                filter_poodll_html5recorder_skinstyle_video -> 'filter_poodll/skinstylevideo'
        */

        $mform->addElement('header', 'html5recorder', get_string('filter_poodll_html5recorder_heading', 'filter_poodll'));
        $mform->setExpanded('html5recorder', true);

        //add html5 recorder information
        //Recorder skin dropdown list and skin style text box
        $audiooptions = \filter_poodll\settingstools::fetch_html5_recorder_items("audio");
        $videooptions = \filter_poodll\settingstools::fetch_html5_recorder_items("video");
        $audiooptions['sitedefault'] = get_string('sitedefault', 'filter_poodll');
        $videooptions['sitedefault'] = get_string('sitedefault', 'filter_poodll');

        $elname = 'html5recorder_skin_audio';
        $mform->addElement('select', $elname, get_string($elname, 'filter_poodll'), $audiooptions);
        $mform->setDefault($elname, 'sitedefault');

        $elname = 'skinstyleaudio';
        $mform->addElement('text', $elname, get_string($elname, 'filter_poodll'), array('size' => '30'));
        $mform->setType($elname, PARAM_TEXT);

        $elname = 'html5recorder_skin_video';
        $mform->addElement('select', $elname, get_string($elname, 'filter_poodll'), $videooptions);
        $mform->setDefault($elname, 'sitedefault');

        $elname = 'skinstylevideo';
        $mform->addElement('text', $elname, get_string($elname, 'filter_poodll'), array('size' => '30'));
        $mform->setType($elname, PARAM_TEXT);

    }
}