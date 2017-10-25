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
 * Sayonara
 *
 * This fork of Goodbye is designed to work with Moodle 3.2+ and the Boost theme.
 * The option to delete will be in the user's profile.
 *
 * @package    local
 * @subpackage sayonara
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Goodbye
 *
 * This module has been created to provide users the option to delete their account
 *
 * @package    local
 * @subpackage goodbye, delete your moodle account
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class check_account_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $COURSE;

        $mform = $this->_form;
        $userid = $USER->id;
        $strrequired = get_string('required');

        $farewell = get_config('local_sayonara', 'farewell');
        if (empty($farewell)) {
            $farewell = get_string('defaultfarewell', 'local_sayonara');
        }
        $mform->addElement('static', 'farewell', '', get_config('local_sayonara', 'farewell'));

        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_TEXT);
        $mform->addRule('username', $strrequired, 'required', null, 'client');

        $mform->addElement('password', 'password', get_string('password'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addRule('password', $strrequired, 'required', null, 'client');

        $mform->addElement('submit', 'submit', get_string('submit'));

    }
}
