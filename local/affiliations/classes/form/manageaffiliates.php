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
 * Affiliations
 *
 * This plugin is designed to work with Moodle 3.2+ and allows students to select 
 * which entities they would like to be affiliated with. The student will be placed
 * into the corresponding cohort.
 *
 * @package    local
 * @subpackage affiliations
 * @copyright  2018 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_affiliations\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;
use context_system;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/affiliations/lib.php');

class manageaffiliates extends moodleform {

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        $mform->disable_form_change_checker();

        // Get all the affiliates and load into an array.
        $affiliates = local_affiliations_get_affiliates();

        // Show affiliates currently available.
        $mform->addElement('header', 'currentaffiliatesheader', get_string('currentaffiliatesheader', 'local_affiliations'));

        foreach ($affiliates as $affiliate) {
            $mform->addElement('checkbox', 'deleteaffiliates['.$affiliate->id.']', null, "$affiliate->fullname ($affiliate->shortname)", array('group' => 'deleteaffiliates'));
        }
        $mform->addElement('submit', 'currentaffiliatesdeletebutton', get_string('buttoncurrentaffiliatesdelete', 'local_affiliations'));

        // Text input areas to add new affiliates.
        $mform->addElement('header', 'addaffiliatesheader', get_string('addaffiliatesheader', 'local_affiliations'));
        $mform->addElement('text', 'addaffiliatesname', get_string('addaffiliatesnamelabel', 'local_affiliations'), $attributes=array('size'=>'128'));
        $mform->addHelpButton('addaffiliatesname', 'addaffiliatesnamelabel', 'local_affiliations');
        $mform->setType('addaffiliatesname', PARAM_TEXT);
        $mform->addElement('text', 'addaffiliatescode', get_string('addaffiliatescodelabel', 'local_affiliations'), $attributes=array('size'=>'6'));
        $mform->addHelpButton('addaffiliatescode', 'addaffiliatescodelabel', 'local_affiliations');
        $mform->setType('addaffiliatescode', PARAM_ALPHANUM);
        $mform->addRule('addaffiliatescode', get_string('addaffiliatesmaxlengtherror', 'local_affiliations'), 'maxlength', '6', 'client', false, false);

        $mform->addElement('submit', 'addaffiliatesbutton', get_string('buttonaddaffiliates', 'local_affiliations'));
    }
}
