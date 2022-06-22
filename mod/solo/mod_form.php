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
 * The main solo configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

use \mod_solo\constants;
use \mod_solo\utils;

/**
 * Module instance settings form
 */
class mod_solo_mod_form extends moodleform_mod {

    public function __construct($current, $section, $cm, $course, $ajaxformdata=null, $customdata=null) {
        global $CFG;
        $this->current   = $current;
        $this->_instance = $current->instance;
        $this->_section  = $section;
        $this->_cm       = $cm;
        $this->_course   = $course;
        if ($this->_cm) {
            $this->context = context_module::instance($this->_cm->id);
        } else {
            $this->context = context_course::instance($course->id);
        }
        // Set the course format.
        require_once($CFG->dirroot . '/course/format/lib.php');
        $this->courseformat = course_get_format($course);
        // Guess module name if not set.
        if (is_null($this->_modname)) {
            $matches = array();
            if (!preg_match('/^mod_([^_]+)_mod_form$/', get_class($this), $matches)) {
                debugging('Rename form to mod_xx_mod_form, where xx is name of your module');
                print_error('unknownmodulename');
            }
            $this->_modname = $matches[1];
        }
        $this->init_features();
        $action = 'modedit.php';
        moodleform::__construct(  $action, $customdata, 'post', '', null, true, $ajaxformdata);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
    	global $CFG, $COURSE;

        $mform = $this->_form;

        //Add this activity specific form fields
        //We want to do this procedurally because in setup tabs we want to show a subset of this form
        // with just the activity specific fields,and we use a custom form and the same elements
         utils::add_mform_elements($mform,$this->context);

          // Grade.
            $this->standard_grading_coursemodule_elements();

            // add standard elements, common to all modules
            $this->standard_coursemodule_elements();
            // add standard buttons, common to all modules
            $this->add_action_buttons();


    }

	public function data_preprocessing(&$form_data) {
        if ($this->current->instance) {
            $form_data = (array) utils::prepare_file_and_json_stuff((object) $form_data, $this->context);
            $form_data = (array) utils::steps_to_sequence((object)$form_data);
        }
	}

    /**
     * Add elements for setting the custom completion rules.
     *
     * @category completion
     * @return array List of added element names, or names of wrapping group elements.
     */
    public function add_completion_rules() {

        $mform = $this->_form;
        //time limits
        $yesno_options = array(0 => get_string("no", constants::M_COMPONENT),
                1 => get_string("yes", constants::M_COMPONENT));
        //the size attribute doesn't work because the attributes are applied on the div container holding the select
        $mform->addElement('select','completionallsteps',get_string('completionallsteps', constants::M_COMPONENT), $yesno_options,array("size"=>"5"));
        $mform->setDefault('convlength',constants::DEF_CONVLENGTH);
        $mform->addHelpButton('completionallsteps', 'completionallsteps', constants::M_MODNAME);
        return ['completionallsteps'];
    }

    /**
     * Called during validation to see whether some module-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionallsteps']) && $data['completionallsteps'] != 0);
    }

  public function validation($data, $files) {
        $errors = parent::validation($data, $files);
       
          if (!empty($data['viewend'])) {
            if ($data['viewend'] < $data['viewstart']) {
                $errors['viewend'] = "End date should be after Start Date";
            }
        }



        return $errors;
    }
}
