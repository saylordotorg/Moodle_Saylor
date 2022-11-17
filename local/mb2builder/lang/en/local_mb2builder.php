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
 *
 * @package    local_mb2builder
 * @copyright  2018 - 2020 Mariusz Boloz (https://mb2themes.com/)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mb2 Builder';

// Settings menu
$string['builder'] = 'Builder';
$string['builderfp'] = 'Front page builder';
$string['builderfooter'] = 'Footer builder';
$string['builderarea'] = 'Builder area {$a->id}';
$string['images'] = 'Media';
$string['options'] = 'Options';
$string['theme'] = 'Theme';
$string['mb2builder:view'] = "Mb2 front page builder";


// Page builder
$string['addsection'] = 'Add section';
$string['addrow'] = 'Add row';
$string['remove'] = 'Remove';
$string['settings'] = 'Settings';
$string['section'] = 'Section';
$string['row'] = 'Row';
$string['col'] = 'Column';
$string['duplicate'] = 'Duplicate';
$string['columns'] = 'Columns';
$string['addelement'] = 'Add element';
$string['element'] = 'Element';
$string['copy'] = 'Copy';
$string['item'] = 'item';
$string['close'] = 'Close';
$string['selecticon'] = 'Select icon';
$string['downloadcontent1'] = 'Download front page content';
$string['downloadcontent2'] = 'Download footer content';
$string['importexport'] = 'Import/Export';
$string['import'] = 'Import';
$string['export'] = 'Export';
$string['importlabel'] = 'Insert valid JSON string';
$string['importtextempty'] = 'Import field is empty.';
$string['importtextnotvalidjson'] = 'Imported content is not a valid JSON object.';
$string['importsuccess'] = 'Import done. Click \'Save changes\' button to keep changes.';
$string['icons'] = 'Icons';


// Settings
$string['generaltab'] = 'General';
$string['styletab'] = 'Style';
$string['adminlabellabel'] = 'Admin label';
$string['adminlabeldesc'] = 'Enter admin label of this element for easy identification.';
$string['customclasslabel'] = 'Custom css class';
$string['customclassdesc'] = 'Use this field to add a custom css class and then refer to it in your css style.';
$string['rowheader'] = 'Row header';
$string['rowicon'] = 'Row icon';
$string['rowtext'] = 'Row text';
$string['iconposh'] = 'Icon horizontal position';
$string['iconposv'] = 'Icon vertical position';
$string['textcolor'] = 'Text color';
$string['bgcolor'] = 'Background color';
$string['rowtextsize'] = 'Text size (rem)';
$string['yes'] = 'Yes';
$string['no'] = "No";
$string['rowheadercontent'] = 'Row header text';
$string['rowheaderbig'] = 'Big header';
$string['columns'] = 'Columns';
$string['marginlabel'] = 'Margin';
$string['margindesc'] = 'Margin top right bottom left, for example: 10px 15px 30px 15px';
$string['title'] = 'Title';
$string['subtitle'] = 'Subtitle';
$string['text'] = 'Text';
$string['ptlabel'] = 'Padding top (px)';
$string['pblabel'] = 'Padding bottom (px)';
$string['ptlabelrem'] = 'Padding top (rem)';
$string['pblabelrem'] = 'Padding bottom (rem)';
$string['link'] = 'Link';
$string['linktarget'] = 'Open link';
$string['linktargetblank'] = 'In a new window';
$string['linktargetself'] = 'In the same window';
$string['readmore'] = 'Read more text';
$string['icon'] = 'Icon';
$string['selecticon'] = 'Select icon';
$string['videoidlabel'] = 'Web video URL';
$string['videoiddesc'] = 'Paste URL of video which can be embeded, for example Youtube or Vimeo.';
$string['customvideolabel'] = 'Custom video';
$string['widthlabel'] = 'Width (px)';
$string['bgimagelabel'] = 'Background image';
$string['elheading'] = 'Heading';
$string['sizelabel'] = 'Size';
$string['line'] = 'Line';
$string['default'] = 'Default';
$string['paragraph'] = 'Paragraph';
$string['htmltag'] = 'HTML tag';
$string['normal'] = 'Normal';
$string['small'] = 'Small';
$string['alignlabel'] = 'Align';
$string['left'] = 'Left';
$string['right'] = 'Right';
$string['center'] = 'Center';
$string['color'] = 'Color';
$string['accordionparent'] = 'Close other panels when current panel is open';
$string['accordionopen'] = 'Show panel #';
$string['content'] = 'Content';
$string['itemsperpage'] = 'Items per page';
$string['catidslabel'] = 'Categories IDs';
$string['catidsdesc'] = 'Comma separated categories IDs.';
$string['cids'] = 'Courses IDs';
$string['cidsdesc'] = 'Comma separated courses IDs.';
$string['courses'] = 'Courses';
$string['none'] = 'None';
$string['exclude'] = 'Exclude';
$string['include'] = 'Include';
$string['carouseltab'] = 'Carousel options';
$string['layouttab'] = 'Layout';
$string['slidercolumns'] = 'Carousel columns';
$string['pagernav'] = 'Pager navigation';
$string['loop'] = 'Animation loop';
$string['dirnav'] = 'Prev next navigation';
$string['spausetime'] = 'Pause time (ms)';
$string['sanimate'] = 'Animation time (ms)';
$string['autoplay'] = 'Auto play';
$string['desclimit'] = 'Description words limit';
$string['titlelimit'] = 'Title words limit';
$string['readmorebtn'] = 'Read more button';
$string['readmoretext'] = 'Read more text';
$string['thin'] = 'Thin';
$string['normal'] = 'Normal';
$string['wholeitemlink'] = 'Whole item link';
$string['showall'] = 'Show all';
$string['grdwidth'] = 'Grid width';
$string['prestyle'] = 'Predefined style';
$string['colors'] = 'Custom colors';
$string['colorsdesc'] = 'Set custom colors for items. Use "item_id:color_value" (hex or rgb). For more items add comma separator, for example I have four items with the following ids: 1, 2, 3, 15 and I want to set custom colors for these items:<br><pre>1:#0000cc,2:#009933,3:#993333,15:#cc9933</pre>';
$string['bgimage'] = 'Background image';
$string['strip1'] = 'Strip left light';
$string['strip2'] = 'Strip left dark';
$string['strip3'] = 'Strip vertical dark';
$string['gradient20'] = 'Gradient 20';
$string['gradient40'] = 'Gradient 40';
$string['scheme'] = 'Color scheme';
$string['light'] = 'Light';
$string['dark'] = 'Dark';
$string['dark_striped'] = 'Dark striped';
$string['light_striped'] = 'Light striped';
$string['pricetab'] = 'Prices';
$string['courseprices'] = 'Courses prices';
$string['coursepricesdesc'] = 'Set comma separated prices for selected courses. Use "course_id:normal_price:old_price", the "old_price" is optional.';
$string['currency'] = 'Currency';
$string['subtext'] = 'Subtext';
$string['type'] = 'Type';
$string['primary'] = 'Primary';
$string['gray'] = 'Gray';
$string['secondary'] = 'Secondary';
$string['success'] = 'Success';
$string['warning'] = 'Warning';
$string['info'] = 'Info';
$string['danger'] = 'Danger';
$string['inverse'] = 'Inverse';
$string['large'] = 'Large';
$string['xlarge'] = 'Extra large';
$string['xsmall'] = 'Extra small';
$string['fullwidth'] = 'Full width';
$string['rounded'] = 'Rounded';
$string['border'] = 'Border';
$string['image'] = 'Image';
$string['tabpos'] = 'Tabs position';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';
$string['height'] = 'Height (px)';
$string['smallscreen'] = 'Show on small screen';
$string['spin'] = 'Spin';
$string['rotate'] = 'Rotate {$a->rotate}';
$string['flip_hor'] = 'Flip horizontal';
$string['flip_ver'] = 'Flip vertical';
$string['alttext'] = 'Alternative text';
$string['custom'] = 'Custom';
$string['solid'] = 'Solid';
$string['dotted'] = 'Dotted';
$string['dashed'] = 'Dashed';
$string['double'] = 'Double';
$string['square'] = 'Square';
$string['circle'] = 'Circle';
$string['disc'] = 'Disc';
$string['horizontal'] = 'Horizontal';
$string['number'] = 'Number';
$string['itemdate'] = 'Show item date';
$string['titleandimage'] = 'Title and image';
$string['closebtn'] = 'Close button';
$string['hidden'] = 'Hidden';
$string['onlyfortype'] = 'Only for type {$a->type}';
$string['languagedesc'] = 'Type a language code or comma-separated list of codes for displaying the section/row to users of the specified language only.';
$string['numsize'] = 'Number size (rem)';
$string['iconsize'] = 'Icon size (rem)';
$string['numcolor'] = 'Number color';
$string['iconcolor'] = 'Icon color';
$string['titlecolor'] = 'Title color';
$string['subtitlecolor'] = 'Subtitle color';
$string['elaccess'] = 'Who can see this section/row?';
$string['elaccessall'] = 'Everyone';
$string['elaccessusers'] = 'Only logged in users';
$string['elaccesguests'] = 'Only guests';



// Settings editor
$string['center'] = 'Center';
$string['imgdescription'] = 'Image description';
$string['selectimage'] = 'Select media';
$string['uploadimages'] = 'Upload media';
$string['removeformat'] = 'Remove formatting';
$string['accordion'] = 'Accordion';


// Elements
$string['elcode'] = 'Code';
$string['animnum'] = 'Animated number';
$string['list'] = 'List';
$string['eltext'] = 'Text';
$string['elboxesicon'] = 'Boxes icon';
$string['elboxesimg'] = 'Boxes image';
$string['elvideo'] = 'Video';
$string['categories'] = 'Categories';
$string['title'] = 'Title';
$string['button'] = 'Button';
$string['tabs'] = 'Tabs';
$string['carousel'] = 'Carousel';
$string['elboxescontent'] = 'Boxes content';
$string['gap'] = 'Gap';
$string['header'] = 'Header';
$string['announcements'] = 'Site announcements';
$string['alert'] = 'Alert';


// Front end
$string['imgnoticespace'] = 'Image name \'{$a->img}\' contains space.';
