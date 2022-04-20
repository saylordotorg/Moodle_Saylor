<?php

namespace mod_readaloud\form;

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Form for Guided Transcription options
 *
 * @package    mod_readaloud
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \mod_readaloud\constants;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class guidedtranscriptionform extends \moodleform {

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;

        //guided transcription options
        $gt_options =  \mod_readaloud\utils::fetch_options_guidedtranscription();
        $mform->addElement('select', 'usecorpus', get_string("usecorpus", constants::M_COMPONENT), $gt_options);
        $mform->setDefault('usecorpus', constants::GUIDEDTRANS_PASSAGE);

        //corpus range options
        $range_options =  \mod_readaloud\utils::fetch_options_corpusrange();
        $mform->addElement('select', 'corpusrange', get_string("corpusrange", constants::M_COMPONENT), $range_options);
        $mform->setDefault('corpusrange', constants::GUIDEDTRANS_PASSAGE);

        //apply this setting to
        $range_options =  \mod_readaloud\utils::fetch_options_applyrange();
        $mform->addElement('select', 'applysettingsrange', get_string("applysettingsrange", constants::M_COMPONENT), $range_options);
        $mform->setDefault('applysettingsrange', constants::APPLY_ACTIVITY);

        $mform->addElement('hidden', 'n');
        $mform->setType('n', PARAM_INT);

        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save'));

    }
}