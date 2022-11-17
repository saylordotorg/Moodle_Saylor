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


$string['pluginname'] = 'Mb2 Content Block';
$string['mb2content'] = 'Mb2 Content';
$string['mb2content:addinstance'] = 'Add a new Mb2 Content block';
$string['mb2content:myaddinstance'] = 'Add a new Mb2 Content block to the My Moodle page';


// General options
$string['generaloptions'] = 'General options';
$string['configtitle'] = 'Title';
$string['margin'] = 'Margin';
$string['customcls'] = 'Custom css class';
$string['margin_help'] = 'Top right bottom left.';
$string['langtag'] = 'Language tag';
$string['langtag_help'] = 'Type langauge tag or comma separated multiple language tags to show different slider for different languages.';
$string['colors'] = 'Items colors';
$string['colors_help'] = 'Set custom colors for items. Use "item_id:color_value" (hex or rgb). For more items add comma separator, for example I have four items with the following ids: 1, 2, 3, 15 and I want to set custom colors for these items:<br><pre>1:#0000cc,2:#009933,3:#993333,15:#cc9933</pre>';
$string['ctype'] = 'Content type';
$string['announcement'] = 'Site announcements';
$string['blog'] = 'Site blogs';
$string['event'] = 'Site events';
$string['course'] = 'Courses';
$string['category'] = 'Courses categories';
$string['limit'] = 'Items limit';
$string['alllink'] = 'View all link';
$string['featured'] = 'Featured items ids';
$string['featured_help'] = 'Comma separated IDs of items which you want to set as featured.';
$string['catids'] = 'Category IDs';
$string['catids_help'] = 'Comma separated IDs of categories. Leave it empty to display all categories or courses from all categories.';
$string['excats'] = 'Selected categories';
$string['courseids'] = 'Course IDs';
$string['courseids_help'] = 'Comma separated IDs of courses. Leave it empty to display all courses.';
$string['excourses'] = 'Selected courses';
$string['include'] = 'Include';
$string['exclude'] = 'Exclude';
$string['desclimit'] = 'Description words limit';
$string['titlelimit'] = 'Title words limit';
$string['desclimit_help'] = 'Set \'0\' to hide description, set \'999\' to show full description.';
$string['eventslookahead'] = 'Upcoming events lookahead (days)';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['readmore'] = 'Read more link';
$string['date'] = 'Item date';
$string['showimages'] = 'Show images';
$string['textbefore'] = 'Content before block';
$string['textafter'] = 'Content after block';
$string['contentoptions'] = 'Content type options';
$string['layoutoptions'] = 'Layout options';
$string['courseprices'] = 'Courses prices';
$string['courseprices_help'] = 'Set comma separated prices for selected courses. Use "course_id:price:old_price", the "old_price" is optional.';
$string['currency'] = 'Currency';
$string['courseurls'] = 'Courses custom urls';
$string['courseurls_help'] = 'Set comma separated urls for selected courses. Use "course_id|url".';


// Style options
//$string['styleoptions'] = 'Style options';
$string['style'] = 'Style';
$string['none'] = 'None';
$string['imgli'] = 'Images and links';
$string['ticker'] = 'Ticker';
$string['imgnum'] = 'Images number';
$string['cols'] = 'Columns';
$string['slidercols'] = 'Slider columns';
$string['colnum'] = 'Columns number';
$string['addtext'] = 'Additional text';
$string['addtextw'] = 'Additional text width (%)';
$string['addtextpos'] = 'Additional text position';
$string['left'] = 'Left';
$string['right'] = 'Right';
$string['small'] = 'Small';
$string['default'] = 'Default';
$string['gutter'] = 'Gutter width';
$string['wholelink'] = 'Whole item link';
$string['shortdate'] = 'Short date';


// Slider options
$string['gslideroptions'] = 'Slider options';
$string['sloop'] = 'Loop animation';
$string['smargin'] = 'Items horizontal margin';
$string['snav'] = 'Prev next navigation';
$string['sdots'] = 'Dots navigation';
$string['sautoplay'] = 'Autoplay';
$string['spausetime'] = 'Pause time (ms)';
$string['sanimate'] = 'Animation speed (ms)';


// Imgaes options
//$string['imgoptions'] = 'Images options';
//$string['cropimg'] = 'Crop images';
//$string['imgw'] = 'Images width (px)';


// Front end strings
$string['noitems'] = 'No items found.';
$string['morecategory'] = 'View courses';
$string['morecourse'] = 'Enter this course';
$string['moreforum'] = 'Read more';
$string['moreevent'] = 'View details';
$string['nocourseincategory'] = 'No courses';
$string['viewall'] = 'View all';
$string['noprice'] = 'Free';
$string['hidden'] = 'Hidden';