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
 * @package		Mb2 Content
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2018 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/

defined('MOODLE_INTERNAL') || die;


require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/gdlib.php');


class block_mb2content extends block_base
{


	private $headerhidden = true;
	protected $editorcontext = null;




	public function init()
	{
        $this->title = get_string('mb2content', 'block_mb2content');
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
		$style = isset($this->config->style) ? $this->config->style : '';
		$title = isset($this->config->title) ? $this->config->title : '';

		$this->title = '';


		if ($allUrl == '')
		{
			$this->title = $title ? format_string($title) : '';
		}


		if ($style === 'ticker')
		{
			$this->title = '';
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
		$listCls = '';
		$colCls = '';
		$sliderData = '';
		$i = 0;
		$x = 0;
		$z = 0;
		$showBlock = true;
		$items = array();


		// Language tag
		$currentLang = current_language();
		$langField = self::mb2content_setting('langtag');
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
		// announcement, blog, event
		$ctype = self::mb2content_setting('ctype', 'announcement');
		$cid = self::mb2content_setting('courseid',1); // This param is commented in form file
		$allUrl = self::mb2content_setting('alllink');
		$blockTitle = isset($this->config->title) ? $this->config->title : '';
		$readmore = self::mb2content_setting('readmore', 0);
		$wholelink = $readmore ? false : self::mb2content_setting('wholelink', 0);
		$cropimg = self::mb2content_setting('cropimg', 0);
		$style = self::mb2content_setting('style','none');
		$imgnum = self::mb2content_setting('imgnum', 1);
		$cols = self::mb2content_setting('colnum', 3);
		$multiCols = (($style === 'imgli' && ($imgnum+1) > 2) || ($style === 'cols' && $cols > 2));
		$shortdate = self::mb2content_setting('shortdate',1);
		$isPrice = (self::mb2content_setting('courseprices') && $ctype === 'course');


		// Block css class
		$customcls = self::mb2content_setting('customcls');
		$cls = ' mb2content-s' . $style;
		$cls .= ' mb2content-t' . $ctype;
		$cls .= $customcls !='' ? ' ' . $customcls : '';
		$cls .= ($style === 'imgli' || $style === 'cols')  ? ' mb2content-cols' : '';
		$cls .= ($ctype === 'event' && $shortdate) ? ' mb2content-shortdate' : '';
		$cls .= $multiCols ? ' mb2content-mcols' : '';
		$cls .= $style === 'slidercols' ? ' mb2content-carousel' : ' mb2content-nocarousel';
		$cls .= ' mb2content-g' . self::mb2content_setting('gutter');
		$cls .= $isPrice ? ' isprice' : ' noprice';


		// Slider style
		if ($style === 'slidercols' || $style === 'ticker')
		{
			$PAGE->requires->jquery();
			if (self::mb2content_setting('loadowl', 0) == 1)
			{
				$PAGE->requires->js('/blocks/mb2content/assets/OwlCarousel2/owl.carousel.js');
			}
			$PAGE->requires->js('/blocks/mb2content/scripts/mb2content.js');


			$cls .= ' mb2content-slider';
			$listCls .= ' owl-carousel';
			$colCls .= ' item';
			$sliderData = self::mb2content_sliderdata();
		}


		$output .= '<div class="mb2content mb2content' . $this->context->id . $cls . ' mb2content-clr">';
		$output .= '<div class="mb2content-inner">';

		$output .= self::mb2content_setting('textbefore') !='' ? '<div class="mb2content-before mb2content-clr">' . format_text(self::mb2content_setting('textbefore'), FORMAT_HTML) . '</div>' : '';


		if ($style === 'ticker' && $blockTitle)
		{
			$output .= '<div class="mb2content-tickertitle"><h3 class="mb2content-blocktitle">' . $blockTitle . '</h3></div>';
		}


		if ($blockTitle && $allUrl)
		{
			$output .= '<div class="mb2content-header mb2content-clr">';
			$output .= $blockTitle !='' ? '<h3 class="mb2content-blocktitle">' . $blockTitle . '</h3>' : '';
			$output .= $allUrl !='' ? '<a class="mb2content-alllink" href="' . $allUrl . '">' . get_string('viewall', 'block_mb2content') . '</a>' : '';
			$output .= '</div>';
		}

		switch ($ctype)
		{
			case ('event') :
				$items = self::mb2content_events($cid);
			break;

			case ('category') :
				$items = self::mb2content_categories($cid);
			break;

			case ('course') :
				$items = self::mb2content_courses($cid);
			break;

			default :
				$items = self::mb2content_announcements($cid);
		}


		// Add clearfic class for list
		$listCls .= ' clearfix';
		$itemCount = count($items);



		if ($itemCount>0)
		{


			// Some style variables
			$addtext = '';
			$addtextW = '';
			$listColW = '';
			$addtextWstyle = '';
			$listColWstyle = '';
			$colCls = '';
			$parentColCls = '';
			$addtextpos = 'left';
			$addtextposStyle = '';
			$addtextposStyle2 = '';


			// Style 'columns'
			if ($style === 'cols' || $style === 'slidercols')
			{
				$addtext = self::mb2content_setting('addtext');

				if ($addtext !='')
				{
					$addtextpos = self::mb2content_setting('addtextpos', 'left');
					$addtextW = round((self::mb2content_setting('addtextw', 25)/100)*100,10);
					$listColW = round(100-$addtextW,10);
					$addtextposStyle = $addtextpos === 'right' ? 'left:' . $listColW . '%;' : '';
					$addtextposStyle2 = $addtextpos === 'right' ? 'left:-' . $addtextW . '%;' : '';
					$addtextWstyle = ' style="width:' . $addtextW . '%;' . $addtextposStyle . '"';
					$listColWstyle = ' style="width:' . $listColW . '%;' . $addtextposStyle2 . '"';
					$parentColCls = ' mb2content-col';
				}

			}




			$output .= $addtext != '' ? '<div class="mb2content-parentcols">' : '';
			$output .= $addtext != '' ? '<div class="mb2content-addtext' . $parentColCls . '"' . $addtextWstyle . '>' . format_text($addtext, FORMAT_HTML)  . '</div>' : '';
			$output .= $addtext != '' ? '<div class="mb2content-listcol' . $parentColCls . '"' . $listColWstyle . '>' : '';
			$output .= '<div class="mb2content-list mb2content-announcements' . $listCls . '"' . $sliderData . '>';


			foreach ($items as $item)
			{

				$showitem = isset($item->showitem) ? $item->showitem : true;


				if ($showitem)
				{

					$i++;
					$x++;
					$z++;


					// Some style variables
					$izotope = false;
					$rowSep = false;
					$slider = false;
					$colStyle = '';
					$showimg = true;
					$startLc = false;
					$endLc = false;
					$col = '';
					$imgCls	= '';
					$descLimit = self::mb2content_setting('desclimit',10);
					$titleLimit = self::mb2content_setting('titlelimit',6);
					$showtext = ($descLimit > 0 && $item->description !='' && $style !== 'ticker');


					// Add 'odd' and 'event' class
					$itemCls = $i%2 ? ' mb2content-odd' : ' mb2content-even';


					// Color class
					$cColor = self::mb2content_custom_color($item->id);
					$itemCls .= $cColor ? ' mb2content-ccolor' : '';


					// Style 'images and links'
					if ($style === 'imgli')
					{
						// Show or hide post image
						$showimg = ($item->imgurl && $i <= $imgnum);


						// Start last columns
						$cols = ($imgnum + 1);
						$col = round(100/$cols, 10);
						$colStyle = $showimg ? ' style="width:' . $col . '%;"' : '';
						$colCls = $showimg ? ' mb2content-col' : '';
						$imgCls	= $showimg ? ' img-item' : '';
						$startLc = ($i == ($imgnum + 1));
						$endLc = ($i == $itemCount);

						if (!$showimg)
						{
							$showtext = false;
						}
					}


					// Style 'columns'
					if ($style === 'cols')
					{
						$col = round(100/$cols, 10);
						$colStyle = ' style="width:' . $col . '%;"';
						$colCls = ' mb2content-col';
						$rowSep = (!$izotope && !$slider && $x == $cols);
					}


					// Style 'none'
					if ($style === 'none')
					{
						if (self::mb2content_setting('images', 0) == 0)
						{
							$showimg = false;
						}
					}


					// Style 'ticker'
					if ($style === 'ticker')
					{
						$showimg = false;
					}


					if (!$showimg)
					{
						$cropimg = false;
					}


					// Featured class
					$featuredArr = explode(',', str_replace(' ', '', self::mb2content_setting('featured')));
					$featured = in_array($item->id, $featuredArr) ? ' featured' : '';


					$output .= $startLc ? '<div class="mb2content-links mb2content-col" style="width:' . $col . '%;">' : '';
					$output .= '<div class="mb2content-item itemid-' . $item->id . ' item-' . $i . $featured . $colCls . $imgCls . $itemCls . '"' . $colStyle . '>';
					$output .= $wholelink ? '<a href="' . $item->link . '">' : '';
					$output .= '<div class="mb2content-item-inner">';
					$output .= '<div class="mb2content-item-a">';
					$output .= ($item->imgurl && $showimg) ? '<div class="mb2content-img"><img src="' .
					self::thumbnail_image($items,$item->imgurl,$item->id,$cropimg) . '" alt="' . $item->imgname . '"></div>' : '';
					$output .= '<div class="mb2content-content">';
					$output .= '<div class="mb2content-content2">';
					$output .= '<div class="mb2content-content3">';
					$output .= '<div class="mb2content-content4">';
					$output .= isset($item->eventdate) ? '<span class="mb2content-details">' . $item->eventdate . '</span>' : '';
					$output .= '<h4 class="mb2content-title">';
					$output .= !$wholelink ? '<a href="' . $item->link . '">' : '';
					$output .= self::mb2content_wordlimit($item->title, $titleLimit);
					$output .= !$wholelink ? '</a>' : '';
					$output .= '</h4>';
					$output .= $item->details ? '<span class="mb2content-details">' . $item->details . '</span>' : '';
					$output .= $cColor ? '<span class="ccolor-el" style="background-color:' . $cColor . ';"></span>' : '';
					$output .= '</div>';
					$output .= '</div>';
					$output .= '</div>';
					$output .= '</div>';
					$output .= '</div>';

					if ($showtext || $readmore || (isset($item->price) && $item->price))
					{
						$output .= '<div class="mb2content-item-b">';
						$output .= $showtext ? '<div class="mb2content-description">' . self::mb2content_wordlimit($item->description, $descLimit) . '</div>' : '';
						$output .= $readmore == 1 ? '<p class="mb2content-readmore"><a href="' . $item->link . '">' . $item->redmoretext . '</a></p>' : '';
						$output .= (isset($item->price) && $item->price) ? '<div class="mb2content-price">' . $item->price . '</div>' : '';
						$output .= '</div>';
					}

					$output .= '</div>';
					$output .= $wholelink ? '</a>' : '';
					$output .= '</div>';
					$output .= $rowSep ? '<div class="mb2content-rowsep clearfix"></div>' : ''; // Row separator
					$rowSep ? $x = 0 : $x = $x;	// reset x count for the nex separator
					$output .= $endLc ? '</div>' : '';



				}

			} // end foreach items

			$output .= '</div>';
			$output .= $addtext != '' ? '</div>' : ''; // end .mb2content-listcol
			$output .= $addtext != '' ? '</div>' : ''; // end .mb2content-parentcols

		}


		if($itemCount == 0 || $z == 0)
		{
			$output .= '<p class="mb2content-noitems">' . get_string('noitems', 'block_mb2content') . '</p>';
		}



		$output .= '</div>';
		$output .= self::mb2content_setting('textafter') !='' ? '<div class="mb2content-after mb2content-clr">' . format_text(self::mb2content_setting('textafter'), FORMAT_HTML) . '</div>' : '';
		$output .= '</div>';


		$this->content =  new stdClass;
		$this->content->text = $showBlock ? $output : NULL;
		$this->content->footer = '';


		return $this->content;

	}






	function mb2content_remove_thumbnails($items)
	{


		$dbImages = self::mb2content_db_images();
		$itemImages = self::mb2content_items_images($items);
		$fs = get_file_storage();

		foreach ($dbImages as $dbImage)
		{

			if (!in_array($dbImage,$itemImages) && $dbImage != '.')
			{

				$oldThumb = $fs->get_file($this->context->id,'block_mb2content','thumbnails',0,'/',$dbImage);

				if(!is_bool($oldThumb))
				{
					$oldThumb->delete();
				}

			}

		}

	}




	function mb2content_calc_img_height($image)
	{
		if ($image)
		{
			$w = self::mb2content_setting('imgw',480);
			$imageSize = getimagesize($image);
			$widthScale = round($w/$imageSize[0],30);
			return round($widthScale*$imageSize[1]);
		}

	}





	function thumbnail_image($items, $image, $id, $crop = true, $forcecanvas = false)
	{

		global $CFG;
		$fs = get_file_storage();


		if (!$image)
		{
			return false;
		}


		if (!$crop)
		{
			return $image;
		}


		$w = self::mb2content_setting('imgw',480);
		$h = self::mb2content_calc_img_height($image);


		$thumbPref = 'mb2content_' . $this->context->id . $id . $w . $h . '___';
		$thumbName = $thumbPref . basename($image);


		$isFileinfo = array(
			'contextid' => $this->context->id,
			'component' => 'block_mb2content',
			'filearea' => 'thumbnails',
			'itemid' => 0,
			'filepath' => '/',
			'filename' => $thumbName
		);

		$dbImages = self::mb2content_db_images();



		if (!in_array($thumbName,$dbImages))
		{

			if(function_exists('resize_image'))
			{
				$imgData = resize_image($image,$w,$h,$forcecanvas);
			}
			else
			{
				$imgData = generate_image_thumbnail($image,$w,$h,$forcecanvas);
			}


			if (!empty($imgData))
			{



				// Create new thumbnail
				$fs->create_file_from_string($isFileinfo, $imgData);
				$file = $fs->get_file($isFileinfo['contextid'], $isFileinfo['component'], $isFileinfo['filearea'],$isFileinfo['itemid'], $isFileinfo['filepath'], $isFileinfo['filename']);
				self::mb2content_remove_thumbnails($items);
				return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename());

			}

		}
		else
		{

			$file = $fs->get_file($isFileinfo['contextid'], $isFileinfo['component'], $isFileinfo['filearea'],$isFileinfo['itemid'], $isFileinfo['filepath'], $isFileinfo['filename']);
			return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename());

		}

	}




	function mb2content_courses ()
	{

		global $CFG,$PAGE,$USER,$DB,$OUTPUT;


		require_once($CFG->dirroot . '/course/lib.php');
        if ($CFG->version < 2018120300)
        {
        	include_once($CFG->libdir . '/coursecatlib.php');
        }


		$output = array();
		$showitem = true;
		$showitemCat = true;
		$context = $PAGE->context;
		$coursecat_canmanage = has_capability('moodle/category:manage', $context);

		$catsArr = explode(',', str_replace(' ', '', self::mb2content_setting('catids')));
		$coursesArr = explode(',', str_replace(' ', '', self::mb2content_setting('courseids')));
		$exCats = self::mb2content_setting('excats','exclude');
		$exCourses = self::mb2content_setting('excourses','exclude');

        if ($CFG->version >= 2018120300)
        {
            $categories = core_course_category::get(0)->get_children();
        }
        else
        {
            $categories = coursecat::get(0)->get_children();
        }
		$category = array_shift($categories);
        $coursesList = get_courses('all');

		$itemCount = count($coursesList);


		if ($itemCount>0)
		{
			foreach ($coursesList as $course)
			{

				// Get course category
                if ($CFG->version >= 2018120300)
                {
                    $cat = core_course_category::get($course->category, IGNORE_MISSING);
                }
                else
                {
                    $cat = coursecat::get($course->category, IGNORE_MISSING);
                }


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


				if ($course->category == 0)
				{
					$showitem = false;
				}


				if ((!isset($cat->visible) || !$cat->visible) && !$coursecat_canmanage)
				{
					$showitem = false;
				}


				$course->showitem = ($course->id == 1 || !$showitem || !$showitemCat) ? false : true;


				// Get image url
				// If attachment is empty get image from post
				$imgUrlAtt = self::mb2content_image_url(array(), false, '', $course->id);
				$imgNameAtt = self::mb2content_image_url(array(), true, '',  $course->id);

				$imgUrlPost = '';
				$imgNamePost = '';

				$course->imgurl = $imgUrlAtt !='' ? $imgUrlAtt : $imgUrlPost;
				$course->imgname = $imgNameAtt !='' ? $imgNameAtt : $imgNamePost;


				// Define item elements
				$customLink = self::mb2content_course_url($course->id);
				$course->link = $customLink ? $customLink : $CFG->wwwroot . '/course/view.php?id=' . $course->id;
				$course->title = $course->fullname;
				$course->description = $course->summary;
				$course->details = '&nbsp;';

				if ((isset($cat->visible) && !$cat->visible) && $coursecat_canmanage)
				{
					$course->details = $cat->get_formatted_name() . ' (' . get_string('hidden','block_mb2content') . ')';
				}
				elseif ((isset($cat->visible) && $cat->visible))
				{
					$course->details = $cat->get_formatted_name();
				}

				$course->redmoretext = get_string('morecourse', 'block_mb2content');
				$price = self::mb2content_course_price($course->id);

				if (self::mb2content_setting('courseprices'))
				{
					$course->price = $price ? $price : '<span class="freeprice">' . get_string('noprice','block_mb2content') . '</span>';
				}



			}

			$output = $coursesList;
		}



		return $output;


	}






	function mb2content_db_images()
	{

		global $CFG, $DB;
		$results = array();


		$query = 'SELECT * FROM ' . $CFG->prefix . 'files WHERE component=\'block_mb2content\' AND contextid=' . $this->context->id;
		$row =  $DB->get_records_sql($query);

		foreach ($row as $el)
		{
			$results[] = $el->filename;
		}

		return $results;
	}






	function mb2content_items_images($items)
	{

		global $CFG, $DB;
		$images = array();
		$w = self::mb2content_setting('imgw',480);

		foreach ($items as $item)
		{
			if ($item->imgurl)
			{

			$h = self::mb2content_calc_img_height($item->imgurl);
			$pref = 'mb2content_' . $this->context->id . $item->id . $w . $h . '___';
			$images[] = $pref . basename($item->imgurl);
			}
		}

		return $images;
	}





	function mb2content_categories ($courseid = 1)
	{

		global $CFG, $USER, $DB, $OUTPUT;

		require_once($CFG->dirroot . '/course/lib.php');
		require_once($CFG->libdir . '/coursecatlib.php');

		$output = array();


		$catids = self::mb2content_setting('catids');
		$exCats = self::mb2content_setting('excats','exclude') === 'exclude' ? ' NOT' : '';


		$query = 'SELECT * FROM ' . $CFG->prefix . 'course_categories';
		$query .= $catids !='' ? ' WHERE id' . $exCats . ' IN (' . $catids . ')' : '';
		$query .= ' ORDER BY sortorder LIMIT ' . self::mb2content_setting('limit',7) . ' OFFSET 0';
		$categories = $DB->get_records_sql($query);



		$itemCount = count($categories);


		if ($itemCount>0)
		{
			foreach ($categories as $category)
			{

				$context = context_coursecat::instance($category->id);
				$imgUrlAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'coursecat','area'=>'description','itemid'=>0),false, $category->description);
				$imgNameAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'coursecat','area'=>'description','itemid'=>0), true);
				$category->imgname = '';
				$category->imgurl = $imgUrlAtt;
				$category->imgname = $imgNameAtt;

				// Define item elements
				$category->link = $CFG->wwwroot . '/course/index.php?categoryid=' . $category->id;
				$category->title = $category->name;
				$category->description = $category->description;


				// Get course count in a category
                $coursesList = array();

                if ($category->id && $category->visible)
                {
                    if ($CFG->version >= 2018120300)
                    {
                    	$coursesList = core_course_category::get($category->id)->get_courses(array('recursive' => false));
                    }
                    else
                    {
                        $coursesList = coursecat::get($category->id)->get_courses(array('recursive' => false));
                    }
                }

				$courseCount = count($coursesList);
				$courseString = $courseCount > 1 ? get_string('courses') : get_string('course');
				$category->details = $courseCount > 0 ? $courseCount . ' ' . $courseString : get_string('nocourseincategory', 'block_mb2content');
				$category->redmoretext = get_string('morecategory', 'block_mb2content');

			}


			$output = $categories;

		}


		return $output;

	}





	function mb2content_imgfromtext($text)
	{

		$output = '';

		$matches = array();
		$str = '@@PLUGINFILE@@/';

		$isplug = preg_match('|' . $str . '|', $text);

		if ($isplug)
		{
			preg_match_all('!@@PLUGINFILE@@/[^?#]+\.(?:jpe?g|png|gif)!Ui' , $text , $matches);
		}
		else
		{
			preg_match_all('!http://[^?#]+\.(?:jpe?g|png|gif)!Ui' , $text , $matches);
		}

		foreach ($matches as $el)
		{
			$output = isset($el[0]) ? $isplug ? str_replace($str,'',$el[0]) : $el[0] : '';
		}

		return $output;

	}







	function mb2content_announcements($courseid = 1)
	{

		global $CFG;

		$output = array();


		// We'll need this
		require_once($CFG->dirroot . '/mod/forum/lib.php');


		$cid = $courseid > 1 ? $courseid : 1; // '1' = site anouncements
		if (!$forum = forum_get_course_forum($cid, 'news'))
		{
   			return '';
		}


  		$modinfo = get_fast_modinfo(get_course($cid));
 		if (empty($modinfo->instances['forum'][$forum->id]))
		{
   			return '';
   		}


   		$cm = $modinfo->instances['forum'][$forum->id];
  		if (!$cm->uservisible)
		{
       		return '';
        }

   		$context = context_module::instance($cm->id);


		// User must have perms to view discussions in that forum
		if (!has_capability('mod/forum:viewdiscussion', $context))
		{
			return '';
   		}


		// First work out whether we can post to this group and if so, include a link
   		$groupmode = groups_get_activity_groupmode($cm);
    	$currentgroup = groups_get_activity_group($cm, true);

      	if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context))
		{
   			//$output .= '<div class="mb2content-newlink"><a href="' . $CFG->wwwroot . '/mod/forum/post.php?forum=' . $forum->id . '">' . get_string('addanewtopic', 'forum').'</a></div>';
  		}


		// Get all the recent discussions we're allowed to see
		// This block displays the most recent posts in a forum in
		// descending order. The call to default sort order here will use
		// that unless the discussion that post is in has a timestart set
 		// in the future.
   		// This sort will ignore pinned posts as we want the most recent.
		!defined('FORUM_POSTS_ALL_USER_GROUPS') ? define('FORUM_POSTS_ALL_USER_GROUPS','') : '';
		$sort = 'p.modified DESC';
		if (!$discussions = forum_get_discussions($cm, $sort, true, -1, self::mb2content_setting('limit'), false, -1, 0, FORUM_POSTS_ALL_USER_GROUPS) )
		{
       		$output = array();
		}
		else
		{
			$output = $discussions;
		}


		$showDetails = self::mb2content_setting('itemdate', 0);

		if (count($discussions) > 0)
		{
			foreach ($discussions as $discussion)
			{

				$discussion->subject = $discussion->name;
				$discussion->subject = format_string($discussion->subject, true, $forum->course);


				// Get image url
				// If attachment is empty get image from post
				$imgUrlAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'mod_forum','area'=>'attachment','itemid'=>$discussion->id));
				$imgNameAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'mod_forum','area'=>'attachment','itemid'=>$discussion->id), true);

				$imgUrlPost = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'mod_forum','area'=>'post','itemid'=>$discussion->id));
				$imgNamePost = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'mod_forum','area'=>'post','itemid'=>$discussion->id), true);

				$discussion->imgurl = $imgUrlAtt !='' ? $imgUrlAtt : $imgUrlPost;
				$discussion->imgname = $imgNameAtt !='' ? $imgNameAtt : $imgNamePost;


				// Define item elements
				$discussion->id = $discussion->discussion;
				$discussion->link = $CFG->wwwroot . '/mod/forum/discuss.php?d=' . $discussion->discussion;
				$discussion->title = $discussion->subject;
				$discussion->description = $discussion->message;
				$strftimerecent = get_string('strftimerecent');
				$discussion->details = $showDetails == 1 ? userdate($discussion->modified, $strftimerecent) : '';
				$discussion->redmoretext = get_string('moreforum', 'block_mb2content');

			}
		}

		return  $output;

	}





	function mb2content_events($courseid = 1)
	{

		global $CFG, $DB, $COURSE;
		require_once($CFG->dirroot . '/calendar/lib.php');
		$output = array();
      	$filtercourse = array();

		$courseshown = $courseid;
        if (empty($this->instance))
		{
           	// Overrides: use no course at all.
		   	$courseshown = false;
        }
		else
		{

		    $courseshown = $courseid > 1 ? $courseid : 1;
            // $this->content->footer = '<div class="gotocal"><a href="'.$CFG->wwwroot.
			// '/calendar/view.php?view=upcoming&amp;course='.$courseshown.'">'.
			// get_string('gotocalendar', 'calendar').'</a>...</div>';

            $context = context_course::instance($courseshown);

			if (has_any_capability(array('moodle/calendar:manageentries', 'moodle/calendar:manageownentries'), $context))
			{
               	// $this->content->footer .= '<div class="newevent"><a href="'.$CFG->wwwroot.
                // '/calendar/event.php?action=new&amp;course='.$courseshown.'">'.
                // get_string('newevent', 'calendar').'</a>...</div>';
            }

			$filtercourse = array($courseshown => 1);

            if ($courseshown == SITEID)
			{

				// Being displayed at site level. This will cause the filter to fall back to auto-detecting
                // the list of courses it will be grabbing events from.
                $filtercourse = calendar_get_default_courses();

            }
			else
			{

				// Forcibly filter events to include only those from the particular course we are in.
                $filtercourse = array($courseshown => 1);

            }
        }


		list($courses, $group, $user) = calendar_set_filters(array());


        $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;


		if (isset($CFG->calendar_lookahead))
		{
            $defaultlookahead = intval($CFG->calendar_lookahead);
        }


        $lookahead = self::mb2content_setting('eventslookahead',90);
        $maxevents = self::mb2content_setting('limit');

		// Moodle 3.4 and later
		if ($CFG->version>=2017111300)
		{

			$display = new \stdClass;
			$display->range = $lookahead;
			$fromtime = 0;
			$now = time(); // We 'll need this later.
			$usermidnighttoday = usergetmidnight($now);


			if ($fromtime)
			{
				$display->tstart = $fromtime;
			}
			else
			{
				$display->tstart = $usermidnighttoday;
			}


			// This works correctly with respect to the user's DST, but it is accurate
			// only because $fromtime is always the exact midnight of some day!
			$display->tend = usergetmidnight($display->tstart + DAYSECS * $display->range + 3 * HOURSECS) - 1;


			// Get the events matching our criteria.
			$events = calendar_get_legacy_events($display->tstart, $display->tend, $user, $group, $courses);

		}
		// Older Moodle versions
		else
		{
			$events = calendar_get_upcoming($courses, $group, $user, $lookahead, $maxevents);
		}



		$output = $events;
        $itemCount = count($events);
		$x = 0;

		foreach ($events as $event)
		{

			$x++;
			$event->showitem = true;

			// site events: courseid = 1
			// user events: courseid = 0
			// course events: courseid > 1
			if ($event->courseid !=1)
			{
				$event->showitem = false;
			}


			// Items limit
			if ($x > $maxevents)
			{
				$event->showitem = false;
			}


			// Get image url
			$imgUrlAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'calendar','area'=>'event_description','itemid'=>$event->id));
			$imgNameAtt = self::mb2content_image_url(array('context'=>$context->id,'mod'=>'calendar','area'=>'event_description','itemid'=>$event->id), true);
			$event->imgurl = $imgUrlAtt;
			$event->imgname = $imgNameAtt;


			// Define item elements
			$link = 'view.php?view=day&amp;course=1&amp;';
			$href = calendar_get_link_href(new moodle_url(CALENDAR_URL . $link), 0, 0, 0, $event->timestart);
			$href->set_anchor('event_' . $event->id);
			$event->link = $href;
			$event->title = $event->name;
			$event->eventdate = self::mb2content_event_date($event, $now);
			$event->details = '';
			$event->content = $event->description;
			$event->redmoretext = get_string('moreevent', 'block_mb2content');


		}


		return $output;

	}




	function mb2content_event_date ($event,$now)
	{

		$shortdate = self::mb2content_setting('shortdate',1);
		$output = '';


		if ($shortdate)
		{
			$dateArr = explode(',',date('M,d',$event->timestart));


			$output .= '<span class="shortdate">';
			$output .= '<span class="m">';
			$output .= $dateArr[0];
			$output .= '</span>';
			$output .= '<span class="d">';
			$output .= $dateArr[1];
			$output .= '</span>';
			$output .= '</span>';

		}
		else
		{
			$output .= strip_tags(calendar_format_event_time($event, $now, array()));
		}



		return $output;


	}





	function mb2content_sliderdata ()
	{

		$output = '';


		$style = self::mb2content_setting('style','none');
		$iscols = self::mb2content_setting('colnum', 3);
		$isDots = self::mb2content_setting('sdots', 0);


		if ($style === 'ticker')
		{
			$iscols = 1;
			$isDots = 0;
		}


		$output .= ' data-items="' . $iscols . '"';
		$output .= ' data-margin="' . self::mb2content_setting('smargin', 30) . '"';
		$output .= ' data-loop="' . self::mb2content_setting('sloop', 0) . '"';
		$output .= ' data-nav="' . self::mb2content_setting('snav', 1) . '"';
		$output .= ' data-dots="' . $isDots . '"';
		$output .= ' data-autoplay="' . self::mb2content_setting('sautoplay', 1) . '"';
		$output .= ' data-pausetime="' . self::mb2content_setting('spausetime', 7000) . '"';
		$output .= ' data-animtime="' . self::mb2content_setting('sanimate', 600) . '"';

		return $output;

	}







	function mb2content_setting($name, $default = '', $global = '')
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






	function mb2content_wordlimit($string, $limit = 999, $end = '...')
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





	function mb2content_image_url($attribs = array(), $name = false, $desc = '', $courseId = 0)
	{


		global $CFG;

		require_once($CFG->libdir . '/filelib.php');
		$output = '';
		$namefromdesc = 0;
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


		if ($desc!='')
		{
			$urlfromdesc = self::mb2content_imgfromtext(s($desc),true);
			$namefromdesc = basename($urlfromdesc);
		}


		foreach ($files as $file)
		{

			if ($file->is_valid_image())
			{

				$isdesc = $desc ? ($namefromdesc === $file->get_filename()) : true;

				if ($isdesc)
				{

					if ($courseId || !$attribs['itemid'])
					{
						$mooUrl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename());
					}
					else
					{
						$mooUrl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
					}


					$output = $name === true ? $file->get_filename() :	$mooUrl;
				}



			}

		}

		return $output;


	}





	function mb2content_custom_color_arr ()
	{


		$colors = array();
		$defColors = self::mb2content_setting('colors');
		$colorArr1 = explode(',',str_replace(' ','',$defColors));
		$i=-1;


		foreach ($colorArr1 as $color)
		{

			if ($color)
			{
				$i++;
				$colorEl = explode(':',$color);
				$colors[$i]['id']= $colorEl[0];
				$colors[$i]['color'] = $colorEl[1];
			}

		}

		return $colors;


	}








	function mb2content_custom_color ($id)
	{

		$colors = self::mb2content_custom_color_arr();

		foreach ($colors as $color)
		{

			if ($color['id'] == $id)
			{
				return $color['color'];
			}

		}

		return false;

	}





	function mb2content_course_url ($id)
	{

		$output = '';

		$urls = self::mb2content_setting('courseurls');
		$urlsArr = explode(',',str_replace(' ','',$urls));

		foreach($urlsArr as $url)
		{
			$urlArr = explode('|',$url);

			if ($id == $urlArr[0])
			{
				$output = trim($urlArr[1]);
			}
		}

		return $output;

	}





	function mb2content_course_price ($id)
	{

		$output = '';

		$prices = self::mb2content_setting('courseprices');
		$pricesArr = explode(',',str_replace(' ','',$prices));
		$currency = self::mb2content_currency(self::mb2content_setting('currency','USD:24'));

		foreach($pricesArr as $price)
		{

			$priceArr = explode(':',$price);


			if ($id == $priceArr[0])
			{
				$output .= isset($priceArr[2]) ? '<span class="oldprice"><del>' . $currency . trim($priceArr[2]) . '</del></span>' : '';
				$output .= isset($priceArr[1]) ? '<span class="price">' . $currency . trim($priceArr[1]) . '</span>' : '';
			}

		}

		return $output;

	}




	function mb2content_currency ($currency)
	{

		$output = '';
		$is_c = '';


		// Get currency symbol
		$currencyarr = explode(':', $currency);

		$output .= '<span class="currency">';

		if (preg_match('#\\,#', $currencyarr[1]))
		{

			$curr = explode(',', $currencyarr[1]);

			foreach ($curr as $c)
			{
				$output .= '&#x' . $c;
			}
		}
		else
		{
			$output .= '&#x' . $currencyarr[1];
		}

		$output .= '</span>';



		return $output;


	}



}
