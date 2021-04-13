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
 * Grade Now for readaloud plugin
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
class gradenowform extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->addElement('header', 'General', '');

        //do we show the next button
        $shownext = $this->_customdata['shownext'];
        //$mform->addElement('text', 'name', 'BB', array('size'=>70));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        if ($shownext) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('saveandnext', constants::M_COMPONENT));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        //	$mform->closeHeaderBefore('buttonar');

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed,
        $mform->addElement('hidden', 'action');
        $mform->addElement('hidden', 'attemptid');
        $mform->addElement('hidden', 'n');
        $mform->addElement('hidden', 'sessiontime', null,
                array('class' => constants::M_GRADING_FORM_SESSIONTIME, 'id' => constants::M_GRADING_FORM_SESSIONTIME));
        $mform->addElement('hidden', 'sessionerrors', null,
                array('class' => constants::M_GRADING_FORM_SESSIONERRORS, 'id' => constants::M_GRADING_FORM_SESSIONERRORS));
        $mform->addElement('hidden', 'wpm', null,
                array('class' => constants::M_GRADING_FORM_WPM, 'id' => constants::M_GRADING_FORM_WPM));
        $mform->addElement('hidden', 'accuracy', null,
                array('class' => constants::M_GRADING_FORM_ACCURACY, 'id' => constants::M_GRADING_FORM_ACCURACY));
        $mform->addElement('hidden', 'sessionscore', null,
                array('class' => constants::M_GRADING_FORM_SESSIONSCORE, 'id' => constants::M_GRADING_FORM_SESSIONSCORE));
        $mform->addElement('hidden', 'sessionendword', null,
                array('class' => constants::M_GRADING_FORM_SESSIONENDWORD, 'id' => constants::M_GRADING_FORM_SESSIONENDWORD));
        $mform->setType('action', PARAM_TEXT);
        $mform->setType('attemptid', PARAM_INT);
        $mform->setType('n', PARAM_INT);
        $mform->setType('sessiontime', PARAM_INT);
        $mform->setType('sessionerrors', PARAM_TEXT);
        $mform->setType('sessionscore', PARAM_INT);
        $mform->setType('accuracy', PARAM_INT);
        $mform->setType('wpm', PARAM_INT);
        $mform->setType('sessionendword', PARAM_INT);
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        // $this->add_action_buttons();
    }
}

