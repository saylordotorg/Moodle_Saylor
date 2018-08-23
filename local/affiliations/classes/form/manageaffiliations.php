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

class manageaffiliations extends moodleform {

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        //$mform->disable_form_change_checker();

        $userid = $USER->id;
        $affiliates = local_affiliations_get_affiliates();

        $addaffiliationsdescription = get_config('local_affiliations', 'addaffiliationsdescription');
        if (empty($addaffiliationsdescription)) {
            $addaffiliationsdescription = get_string('addaffiliationsdescriptiondefault', 'local_affiliations');
        }
        $mform->addElement('header', 'manageaffiliationsheader', get_string('manageaffiliations', 'local_affiliations'));

        $mform->addElement('static', 'addaffiliationsdescription', '', $addaffiliationsdescription);

        // Loop through the affiliates and add a checkbox.
        foreach ($affiliates as $affiliate) {
            // Is this user a member of this affiliate's cohort?
            $ismember = local_affiliations_is_member($userid, $affiliate->id);

            $mform->addElement('advcheckbox', 'submittedaffiliations['.$affiliate->id.']', '', "$affiliate->fullname");
            // Set the default state of the checkbox based on whether that user is already a part of the affiliate's cohort.
            $mform->setDefault('submittedaffiliations['.$affiliate->id.']', (int)$ismember);
        }
        

        $this->add_action_buttons(true, get_string('submit'));

    }


}
