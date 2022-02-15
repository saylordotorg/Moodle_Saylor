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
 * Copyright (C) 2007-2011 Catalyst IT (http://www.catalyst.net.nz)
 * Copyright (C) 2011-2013 Totara LMS (http://www.totaralms.com)
 * Copyright (C) 2014 onwards Catalyst IT (http://www.catalyst-eu.net)
 *
 * @package    mod
 * @subpackage facetoface
 * @copyright  2014 onwards Catalyst IT <http://www.catalyst-eu.net>
 * @author     Stacey Walker <stacey@catalyst-eu.net>
 * @author     Alastair Munro <alastair.munro@totaralms.com>
 * @author     Aaron Barnes <aaron.barnes@totaralms.com>
 * @author     Francois Marier <francois@catalyst.net.nz>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class mod_facetoface_signup_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $manageremail = $this->_customdata['manageremail'];
        $showdiscountcode = $this->_customdata['showdiscountcode'];

        $mform->addElement('hidden', 's', $this->_customdata['s']);
        $mform->setType('s', PARAM_INT);

        $mform->addElement('hidden', 'backtoallsessions', $this->_customdata['backtoallsessions']);
        $mform->setType('backtoallsessions', PARAM_INT);

        if ($manageremail === false) {
            $mform->addElement('hidden', 'manageremail', '');
            $mform->setType('manageremail', PARAM_EMAIL);
        } else {
            $mform->addElement('html', get_string('manageremailinstructionconfirm', 'facetoface')); // Instructions.
            $mform->addElement('text', 'manageremail', get_string('manageremail', 'facetoface'), 'size="35"');
            $mform->addRule('manageremail', null, 'required', null, 'client');
            $mform->addRule('manageremail', null, 'email', null, 'client');
            $mform->setType('manageremail', PARAM_EMAIL);
        }

        if ($showdiscountcode) {
            $mform->addElement('text', 'discountcode', get_string('discountcode', 'facetoface'), 'size="6"');
            $mform->addHelpButton('discountcode', 'discountcodelearner', 'facetoface');
        } else {
            $mform->addElement('hidden', 'discountcode', '');
        }
        $mform->setType('discountcode', PARAM_TEXT);

        $options = array(
            MDL_F2F_BOTH => get_string('notificationboth', 'facetoface'),
            MDL_F2F_TEXT => get_string('notificationemail', 'facetoface'),
            MDL_F2F_ICAL => get_string('notificationical', 'facetoface')
        );
        $mform->addElement('select', 'notificationtype', get_string('notificationtype', 'facetoface'), $options);
        $mform->addHelpButton('notificationtype', 'notificationtype', 'facetoface');
        $mform->addRule('notificationtype', null, 'required', null, 'client');
        $mform->setDefault('notificationtype', 0);

        $this->add_action_buttons(true, get_string('signup', 'facetoface'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $manageremail = $data['manageremail'];
        if (!empty($manageremail)) {
            if (!facetoface_check_manageremail($manageremail)) {
                $errors['manageremail'] = facetoface_get_manageremailformat();
            }
        }

        return $errors;
    }
}
