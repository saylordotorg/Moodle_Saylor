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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

class mod_facetoface_session_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $context = context_course::instance($this->_customdata['course']->id);

        // Course Module ID.
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        // Facetoface Instance ID.
        $mform->addElement('hidden', 'f', $this->_customdata['f']);
        $mform->setType('f', PARAM_INT);

        // Facetoface Session ID.
        $mform->addElement('hidden', 's', $this->_customdata['s']);
        $mform->setType('s', PARAM_INT);

        // Copy Session Flag.
        $mform->addElement('hidden', 'c', $this->_customdata['c']);
        $mform->setType('c', PARAM_INT);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $editoroptions = $this->_customdata['editoroptions'];

        // Show all custom fields.
        $customfields = $this->_customdata['customfields'];
        facetoface_add_customfields_to_form($mform, $customfields);

        // Hack to put help files on these custom fields.
        // TODO: add to the admin page a feature to put help text on custom fields.
        if ($mform->elementExists('custom_location')) {
            $mform->addHelpButton('custom_location', 'location', 'facetoface');
        }
        if ($mform->elementExists('custom_venue')) {
            $mform->addHelpButton('custom_venue', 'venue', 'facetoface');
        }
        if ($mform->elementExists('custom_room')) {
            $mform->addHelpButton('custom_room', 'room', 'facetoface');
        }

        $formarray  = array();
        $formarray[] = $mform->createElement('selectyesno', 'datetimeknown', get_string('sessiondatetimeknown', 'facetoface'));
        $formarray[] = $mform->createElement('static', 'datetimeknownhint', '',
            html_writer::tag('span', get_string('datetimeknownhinttext', 'facetoface'), array('class' => 'hint-text')));
        $mform->addGroup($formarray, 'datetimeknown_group', get_string('sessiondatetimeknown', 'facetoface'), array(' '), false);
        $mform->addGroupRule('datetimeknown_group', null, 'required', null, 'client');
        $mform->setDefault('datetimeknown', false);
        $mform->addHelpButton('datetimeknown_group', 'sessiondatetimeknown', 'facetoface');

        $repeatarray = array();
        $repeatarray[] = &$mform->createElement('hidden', 'sessiondateid', 0);
        $mform->setType('sessiondateid', PARAM_INT);
        $repeatarray[] = &$mform->createElement('date_time_selector', 'timestart', get_string('timestart', 'facetoface'));
        $repeatarray[] = &$mform->createElement('date_time_selector', 'timefinish', get_string('timefinish', 'facetoface'));
        $checkboxelement = &$mform->createElement('checkbox', 'datedelete', '', get_string('dateremove', 'facetoface'));
        unset($checkboxelement->_attributes['id']); // Necessary until MDL-20441 is fixed.
        $repeatarray[] = $checkboxelement;
        $repeatarray[] = &$mform->createElement('html', html_writer::empty_tag('br')); // Spacer.

        $repeatcount = $this->_customdata['nbdays'];

        $repeatoptions = array();
        $repeatoptions['timestart']['disabledif'] = array('datetimeknown', 'eq', 0);
        $repeatoptions['timefinish']['disabledif'] = array('datetimeknown', 'eq', 0);
        $mform->setType('timestart', PARAM_INT);
        $mform->setType('timefinish', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatcount, $repeatoptions, 'date_repeats', 'date_add_fields',
                               1, get_string('dateadd', 'facetoface'), true);

        if (has_capability('mod/facetoface:configurecancellation', $context)) {
            $mform->addElement('advcheckbox', 'allowcancellations', get_string('allowcancellations', 'facetoface'));
            $mform->setDefault('allowcancellations', $this->_customdata['facetoface']->allowcancellationsdefault);
            $mform->addHelpButton('allowcancellations', 'allowcancellations', 'facetoface');
        }

        $mform->addElement('text', 'capacity', get_string('capacity', 'facetoface'), 'size="5"');
        $mform->addRule('capacity', null, 'required', null, 'client');
        $mform->setType('capacity', PARAM_INT);
        $mform->setDefault('capacity', 10);
        $mform->addHelpButton('capacity', 'capacity', 'facetoface');

        $mform->addElement('checkbox', 'allowoverbook', get_string('allowoverbook', 'facetoface'));
        $mform->addHelpButton('allowoverbook', 'allowoverbook', 'facetoface');

        $mform->addElement('text', 'duration', get_string('duration', 'facetoface'), 'size="5"');
        $mform->setType('duration', PARAM_TEXT);
        $mform->addHelpButton('duration', 'duration', 'facetoface');

        if (!get_config(null, 'facetoface_hidecost')) {
            $formarray  = array();
            $formarray[] = $mform->createElement('text', 'normalcost', get_string('normalcost', 'facetoface'), 'size="5"');
            $formarray[] = $mform->createElement('static', 'normalcosthint', '', html_writer::tag('span',
                get_string('normalcosthinttext', 'facetoface'), array('class' => 'hint-text')));
            $mform->addGroup($formarray, 'normalcost_group', get_string('normalcost', 'facetoface'), array(' '), false);
            $mform->setType('normalcost', PARAM_TEXT);
            $mform->addHelpButton('normalcost_group', 'normalcost', 'facetoface');

            if (!get_config(null, 'facetoface_hidediscount')) {
                $formarray  = array();
                $formarray[] = $mform->createElement('text', 'discountcost', get_string('discountcost', 'facetoface'), 'size="5"');
                $formarray[] = $mform->createElement('static', 'discountcosthint', '', html_writer::tag('span',
                    get_string('discountcosthinttext', 'facetoface'), array('class' => 'hint-text')));
                $mform->addGroup($formarray, 'discountcost_group', get_string('discountcost', 'facetoface'), array(' '), false);
                $mform->setType('discountcost', PARAM_TEXT);
                $mform->addHelpButton('discountcost_group', 'discountcost', 'facetoface');
            }
        }

        $mform->addElement('editor', 'details_editor', get_string('details', 'facetoface'), null, $editoroptions);
        $mform->setType('details_editor', PARAM_RAW);
        $mform->addHelpButton('details_editor', 'details', 'facetoface');

        // Choose users for trainer roles.
        $rolenames = facetoface_get_trainer_roles();

        if ($rolenames) {

            // Get current trainers.
            $currenttrainers = facetoface_get_trainers($this->_customdata['s']);

            // Loop through all selected roles.
            $headershown = false;
            foreach ($rolenames as $role => $rolename) {
                $rolename = $rolename->name;

                // Attempt to load users with this role in this course.
                $usernamefields = facetoface_get_all_user_name_fields(true);
                $rs = $DB->get_recordset_sql("
                    SELECT
                        u.id,
                        {$usernamefields}
                    FROM
                        {role_assignments} ra
                    LEFT JOIN
                        {user} u
                      ON ra.userid = u.id
                    WHERE
                        contextid = {$context->id}
                    AND roleid = {$role}
                ");

                if (!$rs) {
                    continue;
                }

                $choices = array();
                foreach ($rs as $roleuser) {
                    $choices[$roleuser->id] = fullname($roleuser);
                }
                $rs->close();

                // Show header (if haven't already).
                if ($choices && !$headershown) {
                    $mform->addElement('header', 'trainerroles', get_string('sessionroles', 'facetoface'));
                    $headershown = true;
                }

                // If only a few, use checkboxes.
                if (count($choices) < 4) {
                    $roleshown = false;
                    foreach ($choices as $cid => $choice) {

                        // Only display the role title for the first checkbox for each role.
                        if (!$roleshown) {
                            $roledisplay = $rolename;
                            $roleshown = true;
                        } else {
                            $roledisplay = '';
                        }

                        $mform->addElement('advcheckbox', 'trainerrole[' . $role . '][' . $cid . ']', $roledisplay, $choice,
                            null, array('', $cid));
                        $mform->setType('trainerrole[' . $role . '][' . $cid . ']', PARAM_INT);
                    }
                } else {
                    $mform->addElement('select', 'trainerrole[' . $role . ']', $rolename, $choices,
                        array('multiple' => 'multiple'));
                    $mform->setType('trainerrole[' . $role . ']', PARAM_SEQUENCE);
                }

                // Select current trainers.
                if ($currenttrainers) {
                    foreach ($currenttrainers as $role => $trainers) {
                        $t = array();
                        foreach ($trainers as $trainer) {
                            $t[] = $trainer->id;
                            $mform->setDefault('trainerrole[' . $role . '][' . $trainer->id . ']', $trainer->id);
                        }

                        $mform->setDefault('trainerrole[' . $role . ']', implode(',', $t));
                    }
                }
            }
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $dateids = $data['sessiondateid'];
        $dates = count($dateids);
        for ($i = 0; $i < $dates; $i++) {
            $starttime = $data["timestart"][$i];
            $endtime = $data["timefinish"][$i];
            $removecheckbox = empty($data["datedelete"]) ? array() : $data["datedelete"];
            if ($starttime > $endtime && !isset($removecheckbox[$i])) {
                $errstr = get_string('error:sessionstartafterend', 'facetoface');
                $errors['timestart'][$i] = $errstr;
                $errors['timefinish'][$i] = $errstr;
                unset($errstr);
            }
        }

        if (!empty($data['datetimeknown'])) {
            $datefound = false;
            for ($i = 0; $i < $data['date_repeats']; $i++) {
                if (empty($data['datedelete'][$i])) {
                    $datefound = true;
                    break;
                }
            }

            if (!$datefound) {
                $errors['datetimeknown'] = get_string('validation:needatleastonedate', 'facetoface');
            }
        }

        return $errors;
    }
}
