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
 * @package    local_mb2slides
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mb2 Slides';

// Roles
$string['mb2slides:manageitems'] = 'Manage slides';
$string['mb2slides:view'] = 'View slides';

// Global strings
$string['none'] = 'None';
$string['left'] = 'Left';
$string['right'] = 'Right';
$string['center'] = 'Center';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['all'] = 'All';
$string['show'] = 'Show';
$string['hide'] = 'Hide';

// Moodle menu
$string['manageslides'] = 'Manage slides';
$string['editslide'] = 'Edit slide';
$string['enableslide'] = 'Show slide';
$string['disableslide'] = 'Hide slide';
$string['deleteslide'] = 'Delete slide';
$string['addslide'] = "Add slide";
$string['slideupdated'] = 'Slide <strong>{$a->title}</strong> updated.';
$string['slidecreated'] = 'Slide created.';
$string['slidedeleted'] = 'Slide deleted.';
$string['confirmdeleteslide'] = 'Do you really want to delete slide: <strong>{$a->title}</strong>?';
$string['access'] = 'Access';

// Slide edit form
$string['title'] = 'Title';
$string['enable'] = 'Slide visibility';
$string['timestart'] = 'Start publishing';
$string['timeend'] = 'End publishing';
$string['access'] = 'Who can see this slide?';
$string['accesseveryone'] = 'Everyone';
$string['accessusers'] = 'Logged in users';
$string['accessguests'] = 'Guests';
$string['guestscansee'] = 'Slide is visible only for guests.';
$string['userscansee'] = 'Slide is visible only for logged in users.';
$string['image'] = 'Image';
$string['showtitle'] = 'Display title';
$string['userids'] = 'User IDs';
$string['userids_help'] = 'Leave this field empty if you want to show slide for all users.<br/><br/>If you want to show slide only for specific user or users type user ID or comma separated IDs, for example: 4,24,32<br/><br/>If you want to show slide for all users except for specific user or users type user ID or comma separated IDs with minus ("-") character, for example:-7,-18,-45';

// Slides list table
$string['createdby'] = 'Created by';
$string['modifiedby'] = 'Modified by';
$string['visibleto'] = 'Visible to';
$string['strftimedatemonthabbr'] = '%d %b %Y';

// Options
$string['slideroptions'] = 'Slider options';
$string['slidermargin'] = 'Margin';
$string['slidermargindesc'] = 'Margin top right bottom left, for example: <br><pre>30px 30px 0 30px</pre>';
$string['appearance'] = 'Appearance';
$string['options'] = 'Global options';
$string['animspeed'] = 'Animation speed (ms)';
$string['animtype'] = 'Animation type';
$string['captionanimtype'] = 'Description animation type';
$string['captionanimtype1'] = 'Fade slide to top';
$string['animtypeslide'] = 'Slide';
$string['animtypefade'] = 'Fade';
$string['animauto'] = 'Auto animation';
$string['animpause'] = 'Pause time (ms)';
$string['animloop'] = 'Loop animation';
$string['navdir'] = 'Prev/Next navigation';
$string['navpager'] = 'Pager navigation';
$string['cvalign'] = 'Description vertical alignment';
$string['chalign'] = 'Description horizontal alignment';
$string['captionw'] = 'Description width (px)';
$string['cstylepre'] = 'Description predefined style';
$string['circle'] = 'Circle';
$string['gradient'] = 'Gradient';
$string['striplight'] = 'Strips light';
$string['stripdark'] = 'Strips dark';
$string['fullwidth'] = 'Full width';
$string['border'] = 'Border';
$string['fromtheme'] = 'Get style from theme if exists';
$string['custom'] = 'Custom';
$string['contentwidth'] = 'Slider content container width (px)';
$string['captionnavdir'] = 'Navigation in description';
$string['cbgcolor'] = 'Description background color';
$string['imagecolor'] = 'Image overlay color';
$string['titlecolor'] = 'Title color';
$string['desccolor'] = 'Description color';
$string['titlefs'] = 'Title font size (rem)';
$string['descfs'] = 'Description font size (rem)';
$string['link'] = 'Link';
$string['linkbtn'] = 'Button link';
$string['linkbtncls'] = 'Button link css class';
$string['linkbtntext'] = 'Button link text';
$string['btncolor'] = 'Button color';
$string['cbordercolor'] = 'Description border color';
$string['cshadow'] = 'Description shadow';
$string['linktarget'] = 'Open link in a new window';
$string['useglobal'] = 'Use global';
$string['useglobal_help'] = 'Leave this option empty to use global value.';

// Front end
$string['readmore'] = 'Read more';
