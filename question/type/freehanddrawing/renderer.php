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
 * freehanddrawing question renderer class.
 *
 * @package	qtype
 * @subpackage freehanddrawing
 * @copyright ETHZ LET <jacob.shapiro@let.ethz.ch> 
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for freehanddrawing questions.
 *
 * @copyright  ETHZ LET <jacob.shapiro@let.ethz.ch>
 * @license	http://opensource.org/licenses/BSD-3-Clause
 */
class qtype_freehanddrawing_renderer extends qtype_renderer {


	
	public static function requireTranslationsIntoJS() {
		global $PAGE;
		foreach (array_keys(get_string_manager()->load_component_strings('qtype_freehanddrawing', current_language())) as $string) {
			$PAGE->requires->string_for_js($string, 'qtype_freehanddrawing');
		}
	}
	
	public static  function strstr_after($haystack, $needle, $case_insensitive = false) {
		$strpos = ($case_insensitive) ? 'stripos' : 'strpos';
		$pos = $strpos($haystack, $needle);
		if (is_int($pos)) {
			return substr($haystack, $pos + strlen($needle));
		}
		// Most likely false or null
		return $pos;
	}

	private static function create_gd_image_from_string($imgString) {
		if ($imgString != '') {
			$imgData = base64_decode(self::strstr_after($imgString, 'base64,'));
			$img =  imagecreatefromstring($imgData);
			imagealphablending( $img, false );
			imagesavealpha( $img, true );
			return $img;
		}
	}
	
	public static function compare_drawings($teacherAnswer, $studentAnswer, $createBlendedImg = false) {

	//	ini_set('memory_limit', '-1');
		
		// Beginning of dataURL string: "data:image/png;base64,"
		
		$onlyShowCorrectAnswer = true;
		
		if ($studentAnswer != '') {
			// no answer given by student--that's fine, we can still show the right answer.
			$onlyShowCorrectAnswer = false;
			$currentAnswerImg = self::create_gd_image_from_string($studentAnswer);			
		}
		
		$correctAnswerImg = self::create_gd_image_from_string($teacherAnswer);
		
		$width = imagesx($correctAnswerImg);
		$height = imagesy($correctAnswerImg);
		
		if ($createBlendedImg ===  true) {
			// Create a copy just to have somewhere to write to. It doesn't matter that the $teacherAnswer is not blank
			// we don't need blank, since in fact more pixels than the ones in the teacher answer picture are going to be drawn into.
			$blendedImg = self::create_gd_image_from_string($teacherAnswer);
			$green = imagecolorallocate($blendedImg, 0, 255, 0);
			$blue = imagecolorallocate($blendedImg, 0, 0, 255);
			$red = imagecolorallocate($blendedImg, 255, 0, 0);
		}
			
		$matchingPixels = 0;
		$matchPercentage = 0;
		$teacherOnlyPixels = 0;
		$studentOnlyPixels = 0;
		
		
		// *************
		// WARNING: THE FOLLOWING IS SPAHGETTI CODE FOR OPTIMIZATION PURPOSES. SORRY.
		// *************
		
		
		if (!$onlyShowCorrectAnswer) {
			if ($createBlendedImg ===  true) {
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						if ((((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255)) && (((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255))) {

							$matchingPixels++;

							imagesetpixel($blendedImg, $x, $y, $green);

						} else if (((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) && !((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255)) {

							$teacherOnlyPixels++;

							imagesetpixel($blendedImg, $x, $y, $blue);

						} else if (!((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) && ((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255)) {

							$studentOnlyPixels++;

							imagesetpixel($blendedImg, $x, $y, $red);

						}

					}
				}
			} else {
				// DO NO CREATE BLENDED IMAGE
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						if ((((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255)) && (((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255))) {

							$matchingPixels++;

						} else if (((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) && !((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255)) {

							$teacherOnlyPixels++;

						} else if (!((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) && ((imagecolorat($currentAnswerImg, $x, $y) & 0xFF) == 255)) {

							$studentOnlyPixels++;
						}

					}
				}
				// --- DO NOT CREATE BLENDED IMAGE
			}
		} else {
			if ($createBlendedImg ===  true) {
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						// ONLY SHOW CORRECT ANSWER -- NO INPUT FROM USER
						if ((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) {

							$teacherOnlyPixels++;

							imagesetpixel($blendedImg, $x, $y, $blue);

						}

					}
				}
			} else {
				// DO NOT CREATE BLENDED IMAGE
				for ($x = 0; $x < $width; $x++) {
					for ($y = 0; $y < $height; $y++) {
						// ONLY SHOW CORRECT ANSWER -- NO INPUT FROM USER
						if ((imagecolorat($correctAnswerImg, $x, $y) & 0xFF) == 255) {

							$teacherOnlyPixels++;
						}

					}
				}
				// --- DO NOT CREATE BLENDED IMAGE
			}

		}


// 		for ($x = 0; $x < $width; $x++) {
// 			for ($y = 0; $y < $height; $y++) {
// 				$rgbCorrectAns = imagecolorat($correctAnswerImg, $x, $y);
// 				//$rgbCorrectAnsArray = array(($rgbCorrectAns >> 16) & 0xFF, ($rgbCorrectAns >> 8) & 0xFF, $rgbCorrectAns & 0xFF);
// 				$rgbCorrectAnsArray = array(0, 0, $rgbCorrectAns & 0xFF);
// 				if (!$onlyShowCorrectAnswer) {
// 					// VALIDATE STUDENT ANSWER
// 					$rgbCurrentAns = imagecolorat($currentAnswerImg, $x, $y);
// 					//$rgbCurrentAnsArray = array(($rgbCurrentAns >> 16) & 0xFF, ($rgbCurrentAns >> 8) & 0xFF, $rgbCurrentAns & 0xFF);
// 					$rgbCurrentAnsArray = array(0, 0, $rgbCurrentAns & 0xFF);
// 					if ((($rgbCorrectAnsArray[2] == 255/* && $rgbCorrectAnsArray[0] == 0 && $rgbCorrectAnsArray[1] == 0*/)) && (($rgbCurrentAnsArray[2] == 255/* && $rgbCurrentAnsArray[0] == 0 && $rgbCurrentAnsArray[1] == 0*/))) {
// 					 //if (self::isBlue($rgbCorrectAnsArray)  && self::isBlue($rgbCurrentAnsArray)) {
// 						$matchingPixels++;
// 						if ($createBlendedImg ===  true) {
// 							imagesetpixel($blendedImg, $x, $y, $green);
// 						}
// 					} else if (($rgbCorrectAnsArray[2] == 255/* && $rgbCorrectAnsArray[0] == 0 && $rgbCorrectAnsArray[1] == 0*/) && !($rgbCurrentAnsArray[2] == 255/* && $rgbCurrentAnsArray[0] == 0 && $rgbCurrentAnsArray[1] == 0*/)) {
// 					//} else if (self::isBlue($rgbCorrectAnsArray)  && !self::isBlue($rgbCurrentAnsArray)) {
// 						$teacherOnlyPixels++;
// 						if ($createBlendedImg ===  true) {
// 							imagesetpixel($blendedImg, $x, $y, $blue);
// 						}
// 					} else if (!($rgbCorrectAnsArray[2] == 255/* && $rgbCorrectAnsArray[0] == 0 && $rgbCorrectAnsArray[1] == 0*/) && ($rgbCurrentAnsArray[2] == 255/* && $rgbCurrentAnsArray[0] == 0 && $rgbCurrentAnsArray[1] == 0*/)) {
// 					//} else if (!self::isBlue($rgbCorrectAnsArray)  && self::isBlue($rgbCurrentAnsArray)) {
// 						$studentOnlyPixels++;
// 						if ($createBlendedImg ===  true) {
// 							imagesetpixel($blendedImg, $x, $y, $red);
// 						}
// 					}
// 				} else {
// 					// ONLY SHOW CORRECT ANSWER -- NO INPUT FROM USER
// 					if ($rgbCorrectAnsArray[2] == 255/* && $rgbCorrectAnsArray[0] == 0 && $rgbCorrectAnsArray[1] == 0*/) {
// 					//if (self::isBlue($rgbCorrectAnsArray)) {
// 						$teacherOnlyPixels++;
// 						if ($createBlendedImg ===  true) {
// 							imagesetpixel($blendedImg, $x, $y, $blue);
// 						}
// 					}
// 				}
// 			}
// 		}
		
		
		imagedestroy($correctAnswerImg);
		
		if (!$onlyShowCorrectAnswer) {
			imagedestroy($currentAnswerImg);
			$matchPercentage = ($matchingPixels / ($matchingPixels + $teacherOnlyPixels + $studentOnlyPixels))*100;
		}
		
		if ($createBlendedImg ===  true) {
			$blendedImgDataURL = self::toDataURL_from_gdImage($blendedImg);
			imagedestroy($blendedImg);
			return array($blendedImgDataURL, $matchPercentage);
		}
		
		return $matchPercentage;
	}
	private static function isBlue($array) {
		if ($array[0] == 0 && $array[1] == 0 && $array[2] == 255) {
			return true;
		}
		return false;
	}
	
	public static function toDataURL_from_gdImage($gdImage) {
//		ini_set('memory_limit', '-1');
		ob_start();
		imagepng($gdImage);
		$ImgData = ob_get_contents();
		ob_end_clean();
		
		
		stream_wrapper_register("BlobDataAsFileStream", "blob_data_as_file_stream");
		
		//Store $swf_blob_data to the data stream
		blob_data_as_file_stream::$blob_data_stream = $ImgData;
		
		//Run getimagesize() on the data stream
		$image_size = getimagesize('BlobDataAsFileStream://');
		
		stream_wrapper_unregister("BlobDataAsFileStream");
		
		$ImgDataURL = 'data:' . $image_size['mime'] . ';base64,' . base64_encode($ImgData);
		return $ImgDataURL;
	}
	
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
    	
    	global $CFG;
    	
    	
    	// A unique instance id for this particular canvas presentation. Will help refer back to it afterwards.
    	$canvasInstanceID = uniqid(); 

		$question = $qa->get_question();
		
		$currentAnswer = $qa->get_last_qt_var('answer');

		$inputname = $qa->get_qt_field_name('answer');

		$bgimageArray = self::get_image_for_question($question);
		
		if ($bgimageArray === null) {
			return html_writer::tag('div', '<h1>Unable to fetch canvas background image file');
		}

        qtype_freehanddrawing_renderer::requireTranslationsIntoJS();
        
		$canvas = "<div class=\"qtype_freehanddrawing_id_" . $question->id ."\" data-canvas-instance-id=\"$canvasInstanceID\">";
		
		if ($options->readonly) {
			$readonlyCanvas = ' readonly-canvas';
			$correctAnswer = reset($question->answers)->answer;
			list($blendedImgDataURL, $matchPercentage) = self::compare_drawings($correctAnswer, $currentAnswer, true);
			if ($options->correctness === 0) {
				$this->page->requires->yui_module('moodle-qtype_freehanddrawing-form', 'Y.Moodle.qtype_freehanddrawing.form.init', array($question->id, $question->radius, $currentAnswer, $canvasInstanceID));
			} else {
				$this->page->requires->yui_module('moodle-qtype_freehanddrawing-form', 'Y.Moodle.qtype_freehanddrawing.form.init', array($question->id, $question->radius, $blendedImgDataURL, $canvasInstanceID));
			}
			$fraction = ($matchPercentage /  ($question->threshold));
			$feedbackimg = $this->feedback_image($fraction);
			$canvas .= "<h1>".sprintf('%0.2f', $matchPercentage)."% ".get_string("out_of_necessary", "qtype_freehanddrawing")." ".sprintf('%0.2f', $question->threshold )."%.</h1><hr>" . $feedbackimg . "<hr>";
		} else {
			$readonlyCanvas = '';
			$this->page->requires->yui_module('moodle-qtype_freehanddrawing-form', 'Y.Moodle.qtype_freehanddrawing.form.init', array($question->id, $question->radius, 'undefined', $canvasInstanceID));
			$canvas .= '<img ALT="'.get_string("erase_canvas", "qtype_freehanddrawing").'" SRC="'.$CFG->wwwroot . '/question/type/freehanddrawing/pix/Empty-frame.png" CLASS="qtype_freehanddrawing_eraser">';
            $canvas .= '<img ALT="'.get_string("eraser_tool", "qtype_freehanddrawing").'" SRC="'.$CFG->wwwroot . '/question/type/freehanddrawing/pix/Eraser-icon.png" CLASS="qtype_freehanddrawing_eraser_tool">';
			$canvas .= "<textarea class=\"qtype_freehanddrawing_textarea\" name=\"$inputname\" id=\"qtype_freehanddrawing_textarea_id_".$question->id."\" rows=20 cols=50>$currentAnswer</textarea>";
			
		}

		$canvas .= "<canvas class=\"qtype_freehanddrawing_canvas".$readonlyCanvas."\" width=\"".$bgimageArray[1]."\" height=\"".$bgimageArray[2]."\"style=\"background:url('$bgimageArray[0]')\"></canvas></div>";
		
		
		$questiontext = $question->format_questiontext($qa);


		$result = html_writer::tag('div', $questiontext . $canvas, array('class' => 'qtext'));


		if ($qa->get_state() == question_state::$invalid) {
			$result .= html_writer::nonempty_tag('div',
					$question->get_validation_error(array('answer' => $currentAnswer)),
					array('class' => 'validationerror'));
		}
		return $result;
	}

	public function specific_feedback(question_attempt $qa) {
		$question = $qa->get_question();

		$answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
		if (!$answer || !$answer->feedback) {
			return '';
		}

		return $question->format_text($answer->feedback, $answer->feedbackformat,
				$qa, 'question', 'answerfeedback', $answer->id);
	}

	public function correct_response(question_attempt $qa) {
		return ''; /* still not sure what kind of text should be given back for this....*/
		$question = $qa->get_question();

		$answer = $question->get_matching_answer($question->get_correct_response());
		if (!$answer) {
			return '';
		}

		return get_string('correctansweris', 'qtype_freehanddrawing', s($answer->answer));
	}




    public static function get_image_for_question($question) {
    	return self::get_image_for_files($question->contextid,  'qtype_freehanddrawing', 'qtype_freehanddrawing_image_file', $question->id);
    }
    
    public static function get_image_for_files($context, $component, $filearea, $itemid) {
    	$fs = get_file_storage();
    	$draftfiles = $fs->get_area_files($context,  $component, $filearea, $itemid, 'id');
    	if ($draftfiles) {
    		foreach ($draftfiles as $file) {
    			if ($file->is_directory()) {
    				continue;
    			}
    			// Prefer to send dataURL instead of mess with the plugin file API which turned out to be quite cumbersome. Anyway this should really speed things up for the browser
    			// as it reduces HTTP requests.
    			// ----------
    			//$url = moodle_url::make_pluginfile_url($question->contextid, $componentname, $filearea, "$qubaid/$slot/$question->id", '/', $file->get_filename());
    			// ----------
    			if ($file->get_content() == null) {
    				return null;
    			}
    			$image = imagecreatefromstring($file->get_content());
    			if ($image === FALSE) {
    				return null;
    			}
    			$width = imagesx($image);
    			$height = imagesy($image);
    			$ImgDataURL = self::toDataURL_from_gdImage($image);
    			imagedestroy($image);
    			return array($ImgDataURL, $width, $height, $file->get_filename());
    		}
    	}
    	return null;
    }
    public static function isDataURLAValidDrawing($dataURL, $bgWidth, $bgHeight) {
    	$imgData = base64_decode(qtype_freehanddrawing_renderer::strstr_after($dataURL, 'base64,'));
    	$imgGDResource =  imagecreatefromstring($imgData);
    	if ($imgGDResource === FALSE) {
    		return false;
    	} else {
    		// Check that it has non-zero dimensions (would've been nice to check that its dimensions fit those of the uploaded file but perhaps that is an overkill??)
    		if (imagesx($imgGDResource) != $bgWidth || imagesy($imgGDResource) != $bgHeight) {
    			return false;
    		} else {
    			// Check that the image is non-empty
    			if (self::isImageTransparent($imgGDResource, $bgWidth, $bgHeight) === true) {
    				return false;
    			}
    		}
    		imagedestroy($imgGDResource);
    		return true;
    	}
    	return false;
    }
    
    
    private static function isImageTransparent($gdImage, $width, $height) {
    	for ($x = 0; $x < $width; $x++) {
    		for ($y = 0; $y < $height; $y++) {
    			// Check the alpha channel (4th byte from the right) if it's completely transparent
    			if (((imagecolorat($gdImage, $x, $y) >> 24) & 0xFF) !== 127/*127 means completely transparent*/) {
    				// Something is painted, great!
    				return false;
    			}
    		}
    	}
    	return true;
    }




}











// Take from http://php.net/manual/en/function.getimagesize.php
// Because I couldn't find a way to apply imagegetsize() on a raw blob of data
// instead of a filename. imagegetsize() is necessary to obtain the MIME
// type of the image.


// Le'ts hope this doesn't create too much overhead.

/*
 ----------------------------------------------------------------------
PHP Blob Data As File Stream v1.0 (C) 2012 Alex Yam <alexyam@live.com>
This code is released under the MIT License.
----------------------------------------------------------------------
[Summary]

A simple class for PHP functions to read and write blob data as a file
using a stream wrapper.

Particularly useful for running getimagesize() to get the width and
height of .SWF Flash files that are stored in the database as blob data.

Tested on PHP 5.3.10.

----------------------------------------------------------------------
[Usage Example]

//Include
include('./blob_data_as_file_stream.php');

//Register the stream wrapper
stream_wrapper_register("BlobDataAsFileStream", "blob_data_as_file_stream");

//Fetch a .SWF file from the Adobe website and store it into a variable.
//Replace this with your own fetch-swf-blob-data-from-database code.
$swf_url = 'http://www.adobe.com/swf/software/flash/about/flashAbout_info_small.swf';
$swf_blob_data = file_get_contents($swf_url);

//Store $swf_blob_data to the data stream
blob_data_as_file_stream::$blob_data_stream = $swf_blob_data;

//Run getimagesize() on the data stream
$swf_info = getimagesize('BlobDataAsFileStream://');
var_dump($swf_info);

----------------------------------------------------------------------
[Usage Output]

array(5) {
[0]=>
int(159)
[1]=>
int(91)
[2]=>
int(13)
[3]=>
string(23) "width="159" height="91""
["mime"]=>
string(29) "application/x-shockwave-flash"
}

*/

class blob_data_as_file_stream {

	private static $blob_data_position = 0;
	public static $blob_data_stream = '';

	public static function stream_open($path,$mode,$options,&$opened_path){
		static::$blob_data_position = 0;
		return true;
	}

	public static function stream_seek($seek_offset,$seek_whence){
		$blob_data_length = strlen(static::$blob_data_stream);
		switch ($seek_whence) {
			case SEEK_SET:
				$new_blob_data_position = $seek_offset;
				break;
			case SEEK_CUR:
				$new_blob_data_position = static::$blob_data_position+$seek_offset;
				break;
			case SEEK_END:
				$new_blob_data_position = $blob_data_length+$seek_offset;
				break;
			default:
				return false;
		}
		if (($new_blob_data_position >= 0) AND ($new_blob_data_position <= $blob_data_length)){
			static::$blob_data_position = $new_blob_data_position;
			return true;
		}else{
			return false;
		}
	}

	public static function stream_tell(){
		return static::$blob_data_position;
	}

	public static function stream_read($read_buffer_size){
		$read_data = substr(static::$blob_data_stream,static::$blob_data_position,$read_buffer_size);
		static::$blob_data_position += strlen($read_data);
		return $read_data;
	}

	public static function stream_write($write_data){
		$write_data_length=strlen($write_data);
		static::$blob_data_stream = substr(static::$blob_data_stream,0,static::$blob_data_position).
		$write_data.substr(static::$blob_data_stream,static::$blob_data_position+=$write_data_length);
		return $write_data_length;
	}

	public static function stream_eof(){
		return static::$blob_data_position >= strlen(static::$blob_data_stream);
	}

}
