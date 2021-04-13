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
 * Model Audio for readaloud plugin
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_readaloud;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

use \mod_readaloud\constants;

/**
 * Event observer for mod_readaloud
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modelaudioform extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        //do we show the next button
       // $shownext = $this->_customdata['shownext'];
        //$mform->addElement('text', 'name', 'BB', array('size'=>70));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savemodelaudio', constants::M_COMPONENT));
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('submit', 'uploadaudio', get_string('uploadmodelaudio', constants::M_COMPONENT),null,false);


        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        //	$mform->closeHeaderBefore('buttonar');

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed,
        $mform->addElement('hidden', 'action');
        $mform->addElement('hidden', 'n');
        $mform->addElement('hidden', 'modelaudiourl', null,
                array('class' => constants::M_MODELAUDIO_FORM_URLFIELD, 'id' => constants::M_MODELAUDIO_FORM_URLFIELD));

        $mform->setType('action', PARAM_TEXT);
        $mform->setType('n', PARAM_INT);
        $mform->setType('modelaudiourl', PARAM_TEXT);
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        // $this->add_action_buttons();
    }
}

