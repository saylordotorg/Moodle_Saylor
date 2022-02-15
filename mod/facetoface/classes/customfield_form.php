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
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

class mod_facetoface_customfield_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), 'maxlength="255" size="50"');
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'shortname', get_string('shortname'), 'maxlength="255" size="25"');
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->setType('shortname', PARAM_ALPHANUM);

        $options = array(
            CUSTOMFIELD_TYPE_TEXT        => get_string('field:text', 'facetoface'),
            CUSTOMFIELD_TYPE_SELECT      => get_string('field:select', 'facetoface'),
            CUSTOMFIELD_TYPE_MULTISELECT => get_string('field:multiselect', 'facetoface')
        );
        $mform->addElement('select', 'type', get_string('setting:type', 'facetoface'), $options);
        $mform->addRule('type', null, 'required', null, 'client');
        $mform->setDefault('type', 0);

        $mform->addElement('text', 'defaultvalue', get_string('setting:defaultvalue', 'facetoface'), 'maxlength="255" size="30"');
        $mform->setType('defaultvalue', PARAM_TEXT);

        $mform->addElement('textarea', 'possiblevalues', get_string('setting:possiblevalues', 'facetoface'), 'rows="5" cols="30"');
        $mform->setType('possiblevalues', PARAM_TEXT);
        $mform->disabledIf('possiblevalues', 'type', 'eq', 0);

        $mform->addElement('checkbox', 'required', get_string('required'));
        $mform->setDefault('required', false);
        $mform->addElement('checkbox', 'isfilter', get_string('setting:isfilter', 'facetoface'));
        $mform->setDefault('isfilter', false);
        $mform->addElement('checkbox', 'showinsummary', get_string('setting:showinsummary', 'facetoface'));
        $mform->setDefault('showinsummary', true);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;

        $errors = array();
        $where  = "id <> ? AND shortname = ?";
        $params = array($data['id'], $data['shortname']);

        if ($DB->record_exists_select('facetoface_session_field', $where, $params)) {
            $errors['shortname'] = get_string('error:shortnametaken', 'facetoface');
        }

        return $errors;
    }
}
