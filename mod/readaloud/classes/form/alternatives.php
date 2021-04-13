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
 * Form for Alternatives
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
class alternatives extends \moodleform {

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'alternatesheading', get_string("alternatives", constants::M_COMPONENT));

        //The alternatives declaration
        $mform->addElement('textarea', 'alternatives', get_string("alternatives", constants::M_COMPONENT),
                'wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('alternatives', '');
        $mform->setType('alternatives', PARAM_RAW);
        $mform->addElement('static', 'alternativesdescr', '',
                get_string('alternatives_descr', constants::M_COMPONENT));
        //('n'=>$moduleinstance->id, 'action'=>'machineregradeall'

        $mform->addElement('hidden', 'n');
        $mform->setType('n', PARAM_INT);

        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('machineregradeall', constants::M_COMPONENT));

    }
}