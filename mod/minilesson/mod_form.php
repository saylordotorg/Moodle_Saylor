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
 * The main minilesson configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

use \mod_minilesson\constants;
use \mod_minilesson\utils;


/**
 * Module instance settings form
 */
class mod_minilesson_mod_form extends moodleform_mod {

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
        moodleform::__construct($action, $customdata, 'post', '', null, true, $ajaxformdata);
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
        $cmid = isset($this->_cm->id) ? $this->_cm->id : false;
        utils::add_mform_elements($mform,$this->context, $cmid);

        // Grade.
        $this->standard_grading_coursemodule_elements();

        //grade options
        //for now we hard code this to latest attempt
        $mform->addElement('hidden', 'gradeoptions',constants::M_GRADELATEST);
        $mform->setType('gradeoptions', PARAM_INT);

        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
	
	
    /**
     * This adds completion rules
	 * The values here are just dummies. They don't work in this project until you implement some sort of grading
	 * See lib.php minilesson_get_completion_state()
     */
	 function add_completion_rules() {
		$mform =& $this->_form;  
		$config = get_config(constants::M_COMPONENT);
    
		//timer options
        //Add a place to set a mimumum time after which the activity is recorded complete
       $mform->addElement('static', 'mingradedetails', '',get_string('mingradedetails', constants::M_COMPONENT));
       $options= array(0=>get_string('none'),20=>'20%',30=>'30%',40=>'40%',50=>'50%',60=>'60%',70=>'70%',80=>'80%',90=>'90%',100=>'40%');
       $mform->addElement('select', 'mingrade', get_string('mingrade', constants::M_COMPONENT), $options);
	   
		return array('mingrade');
	}
	
	function completion_rule_enabled($data) {
		return ($data['mingrade']>0);
	}
	
	public function data_preprocessing(&$form_data) {
		 if ($this->current->instance) {
             $form_data = utils::prepare_file_and_json_stuff($form_data,$this->context);

		}
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
