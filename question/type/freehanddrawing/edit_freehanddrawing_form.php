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
 * Defines the editing form for the freehanddrawing question type.
 *
 * @package    qtype
 * @subpackage freehanddrawing
 * @copyright  ETHZ LET <jacob.shapiro@let.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once (dirname(__FILE__) . '/renderer.php');

class qtype_freehanddrawing_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG, $USER;
        
        $usercontext = context_user::instance($USER->id);
        $bgImageArray = null;
        $canvasTextAreaPreexistingAnswer = '';
        $noBackgroundImageSelectedYetStyle = '';
        $eraserHTMLParams = '';

       	$bgImageArray = qtype_freehanddrawing_renderer::get_image_for_files($usercontext->id, 'user', 'draft',  file_get_submitted_draft_itemid('qtype_freehanddrawing_image_file'));
        if ($bgImageArray !== null) {
        	$canvasHTMLParams = "width=\"".$bgImageArray[1]."\" height=\"".$bgImageArray[2]."\"style=\"background:url('$bgImageArray[0]')\"";
        	$noBackgroundImageSelectedYetStyle = 'style="display: none;"';
        } else {
        	if (array_key_exists('id', $this->question) === true) {
        		$question = $this->question;
        		if (array_key_exists('contextid', $question) === false || array_key_exists('answers', $question) === false) {
        			$question = question_bank::load_question($question->id, false);
        		}
        		// Question already exists! We are in edit mode.
        		
        		// --------------------------------------------------------
        		// This is in case duplicates are requested to be made (so that the saving code in question.php would know there was a pre-existing question):
        		$mform->addElement('hidden', 'pre_existing_question_id', $question->id);
        		$mform->setType('pre_existing_question_id', PARAM_INT);
        		// --------------------------------------------------------
        		
        		
        		
        		$bgImageArray = qtype_freehanddrawing_renderer::get_image_for_question($question);
        		// This is the structure of the array:
        		// 0 image dataURL string
        		// 1 width
        		// 2 height
        		// 3 filename string
        		
        		
        		$canvasHTMLParams = "width=\"".$bgImageArray[1]."\" height=\"".$bgImageArray[2]."\"style=\"background:url('$bgImageArray[0]')\"";
        		
        		$noBackgroundImageSelectedYetStyle = 'style="display: none;"';
        		 
        		$canvasTextAreaPreexistingAnswer = reset($question->answers)->answer;
        		
  				// This will be a UI aid to make sure the user knows a file has been chosen rather than just displaying the empty file picker widget 
				// which doesn't indicate that there is already a background image file associated with the question.
        		$mform->addElement('header', 'qtype_freehanddrawing_drawing_background_image_selected', get_string('drawing_background_image', 'qtype_freehanddrawing'));
        		$mform->addElement('html', "<div class=\"fitem\"><div class=\"fitemtitle\">" .
        				get_string("selected_background_image_filename", "qtype_freehanddrawing")."</div><div class=\"felement\"><a HREF='$bgImageArray[0]'>$bgImageArray[3]</a>&nbsp;<input type=\"button\" class=\"fp-btn-choose\" value=\"Choose a different file...\" name=\"qtype_freehanddrawing_image_filechoose_another\"></DIV></DIV>");
        	
        	
        	} else {
        		// No draft bg image, no pre-existing saved files
        		// Seems like we are in "add new" (BRAND new) form mode
        		// In this case the canvas shouldn't be visible until a bg image has been chosen.
        		$canvasHTMLParams = 'style="display: none;"';
        		$eraserHTMLParams = 'style="display: none;"';
        	}	
        }
        
        // File picker
        $mform->addElement('header', 'qtype_freehanddrawing_drawing_background_image', get_string('drawing_background_image', 'qtype_freehanddrawing'));
        $mform->setExpanded('qtype_freehanddrawing_drawing_background_image');
        $mform->addElement('filepicker', 'qtype_freehanddrawing_image_file', get_string('file'), null,
                           array('maxbytes' => 1572864/*1.5MB*/, 'maxfiles' => 1, 'accepted_types' => array('.png', '.jpg', '.jpeg', '.gif')));
        $mform->addElement('html', "<div class=\"fitem\"><div class=\"fitemtitle\">" .
        		get_string("accepted_background_image_file_types", "qtype_freehanddrawing")."</div><div class=\"felement\">PNG, JPG, GIF</DIV></DIV>");
        
        // Drawing Parameters and *actual* canvas
        $mform->addElement('header', 'qtype_freehanddrawing_drawing', get_string('drawing', 'qtype_freehanddrawing'));
        $mform->setExpanded('qtype_freehanddrawing_drawing');
        $mform->addElement('select', 'radius',
        		get_string('set_radius', 'qtype_freehanddrawing'), array(
        				1 => 1,
        				3 => 3,
        				5 => 5,
        				7 => 7,
        				9 => 9,
        				11 => 11,
        				13 => 13,
        				15 => 15,
        				17 => 17,
        				19 => 19,
        				21 => 21,
        				23 => 23,
        				25 => 25,
        				27 => 27,
        				29 => 29,
        				31 => 31,
        				33 => 33,
        				35 => 35,
        				37 => 37,
        				39 => 39,
        		));
        $mform->addElement('select', 'threshold',
        		get_string('threshold_for_correct_answers', 'qtype_freehanddrawing'), array(
        				30 => 30,
        				35 => 35,
        				40 => 40,
        				45 => 45,
        				50 => 50,
        				55 => 55,
        				60 => 60,
        				65 => 65,
        				70 => 70,
        				75 => 75,
        				80 => 80,
        				85 => 85,
        				90 => 90,
        				95 => 95,
        				100 => 100));
        $mform->addElement('textarea', 'qtype_freehanddrawing_textarea_id_0', get_string("drawingrawdata", "qtype_freehanddrawing"), 'class="qtype_freehanddrawing_textarea" wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('qtype_freehanddrawing_textarea_id_0', $canvasTextAreaPreexistingAnswer);
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">' . 
        		get_string("drawanswer", "qtype_freehanddrawing").'</div><div class="felement"><div class="qtype_freehanddrawing_no_background_image_selected_yet" '.$noBackgroundImageSelectedYetStyle.'>' . 
        		get_string('nobackgroundimageselectedyet', 'qtype_freehanddrawing') . 
        		'</div><div class="qtype_freehanddrawing_container_div" '.$eraserHTMLParams.'><img ALT="'.get_string("erase_canvas", "qtype_freehanddrawing").'" SRC="'.$CFG->wwwroot . '/question/type/freehanddrawing/pix/Empty-frame.png" CLASS="qtype_freehanddrawing_eraser" ID="qtype_freehanddrawing_eraser_id_0" '.$eraserHTMLParams.'><img ALT="'.get_string("eraser_tool", "qtype_freehanddrawing").'" SRC="'.$CFG->wwwroot . '/question/type/freehanddrawing/pix/Eraser-icon.png" CLASS="qtype_freehanddrawing_eraser_tool" ID="qtype_freehanddrawing_eraser_tool_id_0" '.$eraserHTMLParams.'><canvas class="qtype_freehanddrawing_canvas" '.$canvasHTMLParams.'>');

        $this->add_interactive_settings();


    }
    public function js_call() {
        global $PAGE;
        qtype_freehanddrawing_renderer::requireTranslationsIntoJS();
        $PAGE->requires->yui_module('moodle-qtype_freehanddrawing-form', 'Y.Moodle.qtype_freehanddrawing.form.init', array(0, 0));
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);
        $this->js_call();        
        return $question;
    }

    public function validation($data, $files) {
    	global $USER;
        $errors = parent::validation($data, $files);
        
        $bgWidth = 0;
        $bgHeight = 0;
        
        // Check that there is _any_ bg-image
        // Step 1: Prefer files given in this current form over pre-existing files (since if both exist, the new files will be the ones that get saved).
       
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['qtype_freehanddrawing_image_file'], 'id');
        if (count($draftfiles) < 2) {
        	// No files given in the form, check if they maybe pre-exist:
        	if (array_key_exists('id', $this->question) === true) {
        		$question = $this->question;
        		if (array_key_exists('contextid', $question) === false || array_key_exists('answers', $question) === false) {
        			$question = question_bank::load_question($question->id, false);
        		}
        		$oldfiles   = $fs->get_area_files($question->contextid,  'qtype_freehanddrawing', 'qtype_freehanddrawing_image_file', $question->id, 'id');
				if (count($oldfiles) < 2) {
					// We are in trouble.
					$errors["qtype_freehanddrawing_image_file"] = get_string('backgroundfilemustbegiven', 'qtype_freehanddrawing');
				} else {
					foreach ($oldfiles as $file) {
						if ($file->is_directory()) {
							continue;
						}
						$image = imagecreatefromstring($file->get_content());
						$bgWidth = imagesx($image);
						$bgHeight = imagesy($image);
						imagedestroy($image);
					}
				}
        	} else {
        		$errors["qtype_freehanddrawing_image_file"] = get_string('backgroundfilemustbegiven', 'qtype_freehanddrawing');
        	}
        } else {
        	foreach ($draftfiles as $file) {
        		if ($file->is_directory()) {
        			continue;
        		}
         		$image = imagecreatefromstring($file->get_content());
        		$bgWidth = imagesx($image);
        		$bgHeight = imagesy($image);
        		imagedestroy($image);
        	}
        }   
        // Check that there is a "drawing" by the user (=teacher):
        if ($data['qtype_freehanddrawing_textarea_id_0'] == '') {
        	$errors["qtype_freehanddrawing_textarea_id_0"] = get_string('drawingmustbegiven', 'qtype_freehanddrawing');
        } else {
        	if (qtype_freehanddrawing_renderer::isDataURLAValidDrawing($data['qtype_freehanddrawing_textarea_id_0'], $bgWidth, $bgHeight) == false) {
        		$errors["qtype_freehanddrawing_textarea_id_0"] = get_string('drawingmustbegiven', 'qtype_freehanddrawing');
        	}
        }
        
        // Check that the drawing parameters make sense:
        
        if ($data['radius'] < 1 || $data['radius'] > 100) {
        	$errors['radius'] = get_string('radius_must_be_reasonable', 'qtype_freehanddrawing');
        }
        
        if ($data['threshold'] <= 0 || $data['threshold'] > 100) {
        	$errors['threshold'] = get_string('threshold_must_be_reasonable', 'qtype_freehanddrawing');
        }
        
        return $errors;
    }

    public function qtype() {
        return 'freehanddrawing';
    }
}
