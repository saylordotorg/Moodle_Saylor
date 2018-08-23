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
 * freehanddrawing question type definition class.
 *
 * @package    qtype
 * @subpackage freehanddrawing
 * @copyright  ETHZ LET <jacob.shapiro@let.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once (dirname(__FILE__) . '/renderer.php');


/**
 * Represents a freehanddrawing question.
 *
 * @copyright  ETHZ LET <jacob.shapiro@let.ethz.ch>
 * @license    http://opensource.org/licenses/BSD-3-Clause
 */
class qtype_freehanddrawing_question extends question_graded_by_strategy implements question_response_answer_comparer {

    public $threshold;
    public $radius;

    /** @var array of question_answer. */
    public $answers = array();

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
    	return get_string('no_response_summary', 'qtype_freehanddrawing');
    }

    public function is_complete_response(array $response) {
    	if (array_key_exists('answer', $response)) {
    		if ($response['answer'] != '') {
    			$bgImageArray = qtype_freehanddrawing_renderer::get_image_for_question($this);
    			if (qtype_freehanddrawing_renderer::isDataURLAValidDrawing($response['answer'], $bgImageArray[1], $bgImageArray[2])) {
    				return true;
    			}
    		}
    	}
    	return false;
    }
	public function is_gradable_response(array $response) {
		return self::is_complete_response($response);
	}
    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_freehanddrawing');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }
    
    public function get_correct_response() {
    	return array('answer' => reset($this->answers)->answer);
    }
    public function get_right_answer_summary() {
    	return get_string('no_correct_answer_summary', 'qtype_freehanddrawing');
    }
    public function compare_response_with_answer(array $response, question_answer $answer) {

    	
    	if ($answer->answer === '' || array_key_exists('answer', $response) === FALSE) {
    		return false;
    	}
    	
    	$matchPercentage = qtype_freehanddrawing_renderer::compare_drawings($answer->answer, $response['answer']);
    	
    	if (($this->threshold) <= $matchPercentage) {
    		$answer->fraction = 1;
    		return true;
    	}
    	
    	$answer->fraction = 0;
    	return false;
    	
    	
	}

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'qtype_freehanddrawing' && $filearea == 'qtype_freehanddrawing_image_file') {
            $question = $qa->get_question();                                                                                                                                
            $itemid = reset($args);                                                                                                                                         
            return ($itemid == $question->id);                                                                                                                            
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
