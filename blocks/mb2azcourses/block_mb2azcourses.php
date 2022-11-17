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
 * @package		Mb2 A-Z Courses
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2018 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/

defined('MOODLE_INTERNAL') || die;



class block_mb2azcourses extends block_base
{


	private $headerhidden = true;
	protected $editorcontext = null;




	public function init()
	{
        $this->title = get_string('mb2azcourses', 'block_mb2azcourses');
    }



	public function instance_allow_multiple() {
        return true;
    }



	function applicable_formats() {
        return array('all' => true);
    }



	function has_config()
	{
		return false;
	}



	function specialization()
	{

		$allUrl = isset($this->config->alllink) ? $this->config->alllink : '';
		$title = isset($this->config->title) ? $this->config->title : '';

		$this->title = '';

		if ($allUrl == '')
		{
			$this->title = $title ? format_string($title) : '';//format_string(get_string('mb2azcourses', 'block_mb2azcourses'));
		}


    }





	function config_print()
	{
		if (!$this->has_config()) {
			return false;
	  	}
	}




	public function get_content()
	{


		global $CFG, $PAGE, $USER, $DB, $OUTPUT;


		$output = '';
		$showBlock = true;
		$items = array();
		$cls = '';


		$PAGE->requires->js('/blocks/mb2azcourses/scripts/mb2azcourses.js');


		// Language tag
		$currentLang = current_language();
		$langField = self::mb2azcourses_setting('langtag');
		$langArr = explode(',', $langField);

		if ($langField !='')
		{
			if (!in_array($currentLang, $langArr))
			{
				$showBlock = false;
			}
		}


		if ($this->content !== NULL)
		{
		  return $this->content;
		}


		// Get type of content
		$cid = self::mb2azcourses_setting('courseid',1); // This param is commented in form file
		$blockTitle = isset($this->config->title) ? $this->config->title : '';
		$alphabet = self::mb2azcourses_setting('alphabet') !='' ? explode(',', strtoupper(self::mb2azcourses_setting('alphabet'))) : range('A','Z');
		$layout =  self::mb2azcourses_setting('layout','list');


		// Block css class
		$customcls = self::mb2azcourses_setting('customcls');
		$cls .= $customcls !='' ? ' ' . $customcls : '';
		$cls .= ' layout-' . $layout;


		$output .= '<div id="mb2azc' . $this->context->id . '" class="mb2azc mb2azcourses' . $this->context->id . $cls . ' mb2azcourses-clr">';
		$output .= '<div class="mb2azc-inner">';
		$output .= self::mb2azcourses_setting('textbefore') !='' ? '<div class="mb2azc-before mb2azcourses-clr">' . format_text(self::mb2azcourses_setting('textbefore'), FORMAT_HTML) . '</div>' : '';


		$items = self::mb2azcourses_courses($cid);
		$firstTitleChar = self::mb2azcourses_first_char($items);


		// Add clearfic class for list
		$itemCount = count($items);


		if ($itemCount>0)
		{
			$output .= '<ul class="mb2azc-alphabet mb2azc-clr">';

			foreach ($alphabet as $letter)
			{

				$isLink = in_array(trim($letter), $firstTitleChar);

				$lisctCls = $isLink  ? ' islink' : ' nolink';

				$output .= '<li class="mb2azc-alphabet-item' . $lisctCls . '">';
				$output .= $isLink ? '<a class="mb2azc-scroll" href="#mb2az_' . $this->context->id . '_' . trim($letter) . '" data-plus="' . self::mb2azcourses_setting('plus',0) . '">' :
				'<span title="' . get_string('nocontentfor','block_mb2azcourses',trim($letter)) . '">';
				$output .= trim($letter);
				$output .= $isLink ? '</a>' : '</span>';
				$output .= '</li>';

			}


			// Check if is some additional content
			// if yes add '#' link to alphabet list
			$additional = array_diff($firstTitleChar,$alphabet);


			if (!empty($additional))
			{
				$output .= '<li class="mb2azc-alphabet-item islink">';
				$output .= '<a class="mb2azc-scroll" href="#mb2az_' . $this->context->id . '_add" data-plus="' . self::mb2azcourses_setting('plus',0) . '">#</a>';
				$output .= '</li>';

				$alphabet[] = '#';
			}


			$output .= '</ul>';


			$output .= '<ul class="mb2azc-letters mb2azc-clr">';


			foreach ($alphabet as $letter)
			{

				if (in_array(trim($letter), $firstTitleChar) || trim($letter) === '#')
				{

					switch ($layout)
					{
						default:
						$output .= self::mb2azcourses_layout_normal($items,trim($letter),$alphabet);
					}

				}

			} // end foreach items

			$output .= '</ul>';
		}


		$output .= '</div>';
		$output .= self::mb2azcourses_setting('textbeafter') !='' ? '<div class="mb2azc-after mb2azcourses-clr">' . format_text(self::mb2azcourses_setting('textbeafter'), FORMAT_HTML) . '</div>' : '';
		$output .= '</div>';


		$this->content =  new stdClass;
		$this->content->text = $showBlock ? $output : NULL;
		$this->content->footer = '';


		return $this->content;

	}






	function mb2azcourses_courses ($catid)
	{

		global $CFG, $USER, $DB, $OUTPUT;


		require_once($CFG->dirroot . '/course/lib.php');
		if ($CFG->version < 2018120300)
		{
			include_once($CFG->libdir . '/coursecatlib.php');
		}


		$output = array();
		$showitem = true;
		$showitemCat = true;

		$catsArr = explode(',', str_replace(' ', '', self::mb2azcourses_setting('catids')));
		$coursesArr = explode(',', str_replace(' ', '', self::mb2azcourses_setting('courseids')));
		$exCats = self::mb2azcourses_setting('excats','exclude');
		$exCourses = self::mb2azcourses_setting('excourses','exclude');

        $coursesList = get_courses('all','c.fullname ASC');
		$itemCount = count($coursesList);

		if ($itemCount>0)
		{
			foreach ($coursesList as $course)
			{

				// Check if some category are included/excluded
				if ($catsArr)
				{
					$showitemCat = false;

					if ($exCats === 'exclude')
					{
						if (!in_array($course->category,$catsArr))
						{
							$showitemCat = true;
						}
					}
					elseif ($exCats === 'include')
					{
						if (in_array($course->category,$catsArr))
						{
							$showitemCat = true;
						}
					}
				}


				if ($coursesArr)
				{
					$showitem = false;

					if ($exCourses === 'exclude')
					{
						if (!in_array($course->id,$coursesArr))
						{
							$showitem = true;
						}
					}
					elseif ($exCourses === 'include')
					{

						if (in_array($course->id,$coursesArr))
						{
							$showitem = true;
						}
					}
				}

				$course->showitem = ($course->id == 1 || !$showitem || !$showitemCat) ? false : true;

				// Get image url
				// If attachment is empty get image from post
				$imgUrlAtt = self::mb2azcourses_image_url(array(), false, $course->id);
				$imgNameAtt = self::mb2azcourses_image_url(array(), true, $course->id);

				$imgUrlPost = '';
				$imgNamePost = '';

				$course->imgurl = $imgUrlAtt !='' ? $imgUrlAtt : $imgUrlPost;
				$course->imgname = $imgNameAtt !='' ? $imgNameAtt : $imgNamePost;

				// Define item elements
				$course->link = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id));
				$course->title = $course->fullname;
				$course->description = $course->summary;
				$course->details = '';
				$course->redmoretext = '';


			}


			$output = $coursesList;

		}



		return $output;


	}






	function mb2azcourses_layout_normal ($items,$letter,$alphabet)
	{

		$output = '';


		$letterId = $letter === '#' ? 'add' : $letter;


		$output .= '<li class="mb2azc-letter" id="mb2az_' . $this->context->id . '_' . $letterId . '">';
		$output .= '<div class="mb2azc-header mb2azc-clr">';
		$output .= '<h4>' . $letter . '</h4>';
		$output .= '<a class="mb2azc-ttop mb2azc-scroll" data-plus="' .
		self::mb2azcourses_setting('plusttop',70) . '" href="#mb2azc' . $this->context->id . '">&uarr; ' . get_string('ttop', 'block_mb2azcourses') . '</a>';
		$output .= '</div>';
		$output .= '<ul class="mb2azc-items">';

		foreach ($items as $item)
		{

			$showItem = $letter === '#'
			? (!in_array(strtoupper(mb_substr(trim($item->title),0,1)),$alphabet) && $item->showitem)
			: (strtoupper(mb_substr(trim($item->title),0,1)) === $letter && $item->showitem);


			if ($showItem)
			{

				$output .= '<li class="mb2azc-item">';
				$output .= '<a href="' . $item->link . '">' . $item->title . '</a>';
				$output .= '</li>';

			}

		}

		$output .= '</ul>';
		$output .= '</li>';

		return $output;

	}






	function mb2azcourses_first_char ($items)
	{

		$charts = array();


		foreach ($items as $item)
		{
			if ($item->showitem)
			{
				// Convert all strings to uppercase
				$charts[] = strtoupper(mb_substr(trim($item->fullname),0,1,'UTF-8'));
			}
		}


		return array_unique($charts);

	}







	function mb2azcourses_setting($name, $default = '', $global = '')
	{

		if (isset($this->config->$name))
		{
			$output = ($global !='' && $this->config->$name == '') ? $this->config->$global : $this->config->$name;
		}
		else
		{
			$output = $default;
		}


		return $output;

	}






	function mb2azcourses_wordlimit($string, $limit = 999, $end = '...')
	{

		$output = $string;


		if ($limit < 999)
		{
			$content_limit = strip_tags($string);
			$words = explode(' ', $content_limit);
			$new_string = implode(' ', array_splice($words, 0, $limit));
			$word_count = str_word_count($string);
			$end_char = ($end !='' && $word_count > $limit) ? $end : '';

			$output = $new_string . $end_char;
		}


		return $output;

	}







	function mb2azcourses_image_url($attribs = array(), $name = false, $courseId = 0)
	{


		global $CFG;

		require_once($CFG->libdir . '/filelib.php');

		$output = '';
		$files = !empty($attribs) ? get_file_storage()->get_area_files($attribs['context'], $attribs['mod'], $attribs['area'], $attribs['itemid']) : array();


		if ($courseId)
		{

			if ($CFG->version >= 2018120300)
			{
				$courseObj = new core_course_list_element(get_course($courseId));
			}
			else
			{
				$courseObj = new course_in_list(get_course($courseId));
			}

			$files = $courseObj->get_course_overviewfiles();
		}


		foreach ($files as $file)
		{

			if ($file->is_valid_image())
			{

				$mooUrl = $courseId ?
				moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), null, $file->get_filepath(), $file->get_filename()) :
				moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

				$output .= $name === true ? $file->get_filename() :	$mooUrl;
			}

		}

		return $output;


	}





}
