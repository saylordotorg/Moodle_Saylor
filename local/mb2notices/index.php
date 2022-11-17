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
 * @package    local_mb2notices
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

//defined('MOODLE_INTERNAL') || die();

require_once( __DIR__ . '/../../config.php' );
require_once( __DIR__ . '/classes/api.php' );
require_once( __DIR__ . '/classes/helper.php' );
require_once( __DIR__ . '/lib.php' );
require_once( $CFG->libdir . '/adminlib.php' );
require_once( $CFG->libdir . '/tablelib.php' );

// Field colors
$colorcourse = 'mediumblue';
$colorfrontpage = 'blueviolet';
$colordashboard = 'crimson';
$colorcalendar = 'green';
$colorsite = 'slategray';
$colorlogin = 'coral';

// Optional parameters
$deleteid = optional_param('deleteid', 0, PARAM_INT);
$hideshowid = optional_param('hideshowid', 0, PARAM_INT);
$moveup = optional_param('moveup', 0, PARAM_INT);
$movedown = optional_param('movedown', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '/local/mb2notices/index.php', PARAM_LOCALURL);

// Links
$editnotice = '/local/mb2notices/edit.php';
$managenotices = '/local/mb2notices/index.php';
$deletenotice = '/local/mb2notices/delete.php';
$baseurl = new moodle_url($managenotices);
$returnurl = new moodle_url($returnurl);

// Configure the context of the page
admin_externalpage_setup('local_mb2notices_managenotices', '', null, $baseurl);
require_capability('local/mb2notices:view', context_system::instance());
$can_manage = has_capability('local/mb2notices:manageitems', context_system::instance());

// Get sorted notices
$sortorder_items = Mb2noticesApi::get_sortorder_items();

// Delete the notice
if ($can_manage && $deleteid)
{
    Mb2noticesApi::delete($deleteid);
    $message = get_string('noticedeleted', 'local_mb2notices');
}

// Switching the status of the notice
if ( $can_manage && $hideshowid )
{
    Mb2noticesApi::switch_status( $hideshowid );
    $message = get_string( 'noticeupdated', 'local_mb2notices', array( 'title' => Mb2noticesApi::get_record($hideshowid)->title ) );
}

// Move up
if ($can_manage && $moveup)
{
    Mb2noticesApi::move_up($moveup);
    $message = get_string('noticeupdated', 'local_mb2notices', array('title' => Mb2noticesApi::get_record($moveup)->title));
}

// Move down
if ($can_manage && $movedown)
{
    Mb2noticesApi::move_down($movedown);
    $message = get_string('noticeupdated', 'local_mb2notices', array('title' => Mb2noticesApi::get_record($movedown)->title));
}

if (isset($message))
{
    redirect($returnurl, $message);
}

// Page title
$titlepage = get_string('pluginname', 'local_mb2notices');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managenotices', 'local_mb2notices'));
echo $OUTPUT->single_button(new moodle_url($editnotice), get_string('addnotice', 'local_mb2notices'), 'get');

// Table declaration
$table = new flexible_table('mb2notices-notices-table');

// Customize the table
$table->define_columns(
    array(
        'title',
        'cansee',
        'timestart',
        'timeend',
        'language',
        'actions',
    )
);

$table->define_headers(
    array(
        get_string('name', 'moodle'),
        get_string('visibleto', 'local_mb2notices'),
        get_string('timestart', 'local_mb2notices'),
        get_string('timeend', 'local_mb2notices'),
        get_string('language', 'moodle'),
        get_string('actions', 'moodle'),
    )
);

$table->define_baseurl($baseurl);
$table->set_attribute('class', 'generaltable');
$table->column_class('timestart', 'text-center align-middle');
$table->column_class('timeend', 'text-center align-middle');
$table->column_class('language', 'text-center align-middle');
$table->column_class('actions', 'text-right align-middle');
$table->setup();

foreach ($sortorder_items as $item)
{

    $callback = Mb2noticesApi::get_record($item);

	// Filling of information columns
    $titlebadge = '';
    $datestatus = Mb2noticesHelper::date_status( $callback );
    $attribs = json_decode( $callback->attribs );

    if ( $datestatus == -1 )
    {
        $datestatusstr = get_string( 'hiddenwait', 'local_mb2notices' );
        $datebadgecls = ' badge-info';
    }
    elseif ( $datestatus == -2 )
    {
        $datestatusstr = get_string( 'hiddenfinished', 'local_mb2notices' );
        $datebadgecls = ' badge-danger';
    }

    if ( $datestatus < 0 && $callback->enable )
    {
        $titlebadge = ' <span class="badge' . $datebadgecls . '">' . $datestatusstr . '</span>';
    }

    $itemcolor = $colorsite;

    if ( $attribs->showon == 1 )
    {
        $itemcolor = $colorfrontpage;
    }
    elseif ( $attribs->showon == 2 )
    {
        $itemcolor = $colorcourse;
    }
    elseif ( $attribs->showon == 3 )
    {
        $itemcolor = $colordashboard;
    }
    elseif ( $attribs->showon == 4 )
    {
        $itemcolor = $colorlogin;
    }
    elseif ( $attribs->showon == 5 )
    {
        $itemcolor = $colorcalendar;
    }

    $titlecallback = '<div class="mb2notices-admin-notice-title" title="' . $callback->title . '"><a href="' .
    new moodle_url( $editnotice, array( 'itemid' => $callback->id ) ) . '" style="color:' . $itemcolor . ';"><strong>' . $callback->title . '</strong></a>' . $titlebadge . '</div>';

    // Visible to columns
    $visibletotext = get_string( 'accesseveryone', 'local_mb2notices' );

    if ( $attribs->cansee == 1 )
    {
        $visibletotext = get_string( 'accessusers', 'local_mb2notices' );
    }
    elseif ( $attribs->cansee == 2 )
    {
        $visibletotext = get_string( 'accessguests', 'local_mb2notices' );
    }
    elseif ( $attribs->cansee == 3 )
    {
        $visibletotext = get_string( 'accesstudents', 'local_mb2notices' );
    }
    elseif ( $attribs->cansee == 4 )
    {
        $visibletotext = get_string( 'accesteachers', 'local_mb2notices' );
    }
    elseif ( $attribs->cansee == 5 )
    {
        $visibletotext = get_string('rolecustom','local_mb2notices', array( 'num'=> 1 ) );
    }
    elseif ( $attribs->cansee == 6 )
    {
        $visibletotext = get_string('rolecustom','local_mb2notices', array( 'num'=> 2 ) );
    }
    elseif ( $attribs->cansee == 7 )
    {
        $visibletotext = get_string('rolecustom','local_mb2notices', array( 'num'=> 3 ) );
    }

    $visibletoitem = $visibletotext;

    // Time start and time end
    $timestartdate = userdate( $callback->timestart, get_string( 'strftimedatetimeshort', 'local_mb2notices' ) );
    $timeenddate = userdate( $callback->timeend, get_string( 'strftimedatetimeshort', 'local_mb2notices' ) );
    $timestartitem = $callback->timestart ? '<div class="mb2notices-admin-time">' . $timestartdate . '</div>' : '&minus;';
    $timeenditem = $callback->timeend ? '<div class="mb2notices-admin-time">' . $timeenddate . '</div>' : '&minus;';

    // Language
    $languages = Mb2noticesHelper::get_languages($callback);
    $langitem = count($languages) ? implode(', ', $languages) : '&minus;';

	// Defining notice status
    $hideshowicon = 't/show';
    $hideshowstring = get_string('enablenotice', 'local_mb2notices');

    $copyicon = 't/copy';
    $copystring = get_string('duplicate', 'moodle');

    $moveupicon = 't/up';
    $movedownicon = 't/down';
    $moveupstring = get_string('moveup', 'moodle');
    $strmovedown = get_string('movedown', 'moodle');
    $previtem = Mb2noticesApi::get_record_near($callback->id, 'prev');
    $nextitem = Mb2noticesApi::get_record_near($callback->id, 'next');

    if ((bool) $callback->enable)
    {
        $hideshowicon = 't/hide';
        $hideshowstring = get_string('disablenotice', 'local_mb2notices');
    }

    // Link to enable / disable the notice
    $hideshowlink = new moodle_url($managenotices, array('hideshowid' => $callback->id));
    $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($hideshowicon, $hideshowstring));

    // Link to move up
    $moveuplink = new moodle_url($managenotices, array('moveup' => $callback->id));
    $moveupitem = $previtem ? $OUTPUT->action_icon($moveuplink, new pix_icon($moveupicon, $moveupstring)) : '';

    // Link to move down
    $movedownlink = new moodle_url($managenotices, array('movedown' => $callback->id));
    $movedownitem = $nextitem ? $OUTPUT->action_icon($movedownlink, new pix_icon($movedownicon, $strmovedown)) : '';

    // Link for editing
    $editlink = new moodle_url($editnotice, array('itemid' => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit', 'moodle')));

    // Link to remove
    $deletelink = new moodle_url($deletenotice, array('deleteid' => $callback->id));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', get_string('delete', 'moodle')));

    // Check if user can manage items
    $actions = $can_manage ? $hideshowitem . $moveupitem . $movedownitem . $edititem . $deleteitem : '';

	$table->add_data( array( $titlecallback, $visibletoitem, $timestartitem, $timeenditem, $langitem, $actions ) );

}

// Display the table
$table->print_html();

$legend = '<div class="mb2notices-admin-legend">';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colorsite . ';">' .
get_string( 'showoneverywhere', 'local_mb2notices' ) . '</span>';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colorfrontpage . ';">' .
get_string( 'showonfrontpage', 'local_mb2notices' ) . '</span>';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colordashboard . ';">' .
get_string( 'showondashboard', 'local_mb2notices' ) . '</span>';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colorcourse . ';">' .
get_string( 'showoncourse', 'local_mb2notices' ) . '</span>';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colorcalendar . ';">' .
get_string( 'showoncalendarpage', 'local_mb2notices' ) . '</span>';
$legend .= '<span class="mb2notices-admin-legend-item" style="background-color:' . $colorlogin . ';">' .
get_string( 'showonloginpage', 'local_mb2notices' ) . '</span>';
$legend .= '</div>';
echo $legend;

echo $OUTPUT->footer();
?>
