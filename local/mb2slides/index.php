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

//defined('MOODLE_INTERNAL') || die();

require_once( __DIR__ . '/../../config.php' );
require_once( __DIR__ . '/classes/api.php' );
require_once( __DIR__ . '/classes/helper.php' );
require_once( __DIR__ . '/lib.php' );
require_once( $CFG->libdir . '/adminlib.php' );
require_once( $CFG->libdir . '/tablelib.php' );

// Optional parameters
$deleteid = optional_param('deleteid', 0, PARAM_INT);
$hideshowid = optional_param('hideshowid', 0, PARAM_INT);
$moveup = optional_param('moveup', 0, PARAM_INT);
$movedown = optional_param('movedown', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '/local/mb2slides/index.php', PARAM_LOCALURL);

// Links
$editslide = '/local/mb2slides/edit.php';
$manageslides = '/local/mb2slides/index.php';
$deleteslide = '/local/mb2slides/delete.php';
$baseurl = new moodle_url($manageslides);
$returnurl = new moodle_url($returnurl);

// Configure the context of the page
admin_externalpage_setup('local_mb2slides_manageslides', '', null, $baseurl);
require_capability('local/mb2slides:view', context_system::instance());
$can_manage = has_capability('local/mb2slides:manageitems', context_system::instance());

// Get sorted slides
$sortorder_items = Mb2slidesApi::get_sortorder_items();

// Delete the slide
if ($can_manage && $deleteid)
{
    Mb2slidesApi::delete($deleteid);
    $message = get_string('slidedeleted', 'local_mb2slides');
}

// Switching the status of the slide
if ($can_manage && $hideshowid)
{
    Mb2slidesApi::switch_status($hideshowid);
    $message = get_string('slideupdated', 'local_mb2slides', array('title' => Mb2slidesApi::get_record($hideshowid)->title));
}

// Move up
if ($can_manage && $moveup)
{
    Mb2slidesApi::move_up($moveup);
    $message = get_string('slideupdated', 'local_mb2slides', array('title' => Mb2slidesApi::get_record($moveup)->title));
}

// Move down
if ($can_manage && $movedown)
{
    Mb2slidesApi::move_down($movedown);
    $message = get_string('slideupdated', 'local_mb2slides', array('title' => Mb2slidesApi::get_record($movedown)->title));
}

if (isset($message))
{
    redirect($returnurl, $message);
}

// Page title
$titlepage = get_string('pluginname', 'local_mb2slides');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageslides', 'local_mb2slides'));
echo $OUTPUT->single_button(new moodle_url($editslide), get_string('addslide', 'local_mb2slides'), 'get');

// Table declaration
$table = new flexible_table('mb2slides-slides-table');

// Customize the table
$table->define_columns(
    array(
        'title',
        'access',
        'createdby',
        'modifiedby',
        'language',
        'actions',
    )
);

$table->define_headers(
    array(
        get_string('name', 'moodle'),
        get_string('visibleto', 'local_mb2slides'),
        get_string('createdby', 'local_mb2slides'),
        get_string('modifiedby', 'local_mb2slides'),
        get_string('language', 'moodle'),
        get_string('actions', 'moodle'),
    )
);

$table->define_baseurl($baseurl);
$table->set_attribute('class', 'generaltable');
$table->column_class('access', 'text-center align-middle');
$table->column_class('createdby', 'text-center align-middle');
$table->column_class('modifiedby', 'text-center align-middle');
$table->column_class('language', 'text-center align-middle');
$table->column_class('actions', 'text-right align-middle');
$table->setup();

foreach ($sortorder_items as $item)
{

    $callback = Mb2slidesApi::get_record($item);

	// Filling of information columns
    $titleimg = '<img src="' . Mb2slidesHelper::get_image_url($callback->id) . '?preview=thumb" alt="" width="56"/> ';
    $titlecallback = html_writer::div(html_writer::link(new moodle_url($editslide, array('itemid' => $callback->id)), $titleimg . '<strong>' . $callback->title . '</strong>'), 'mb2slides-admin-slide-title');

    // Created and modified by
    $createduser = Mb2slidesHelper::get_user($callback->createdby);
    $createduserdate = userdate($callback->timecreated, get_string('strftimedatemonthabbr', 'local_mb2slides'));
    $modifieduserdate = userdate($callback->timemodified, get_string('strftimedatemonthabbr', 'local_mb2slides'));
    $modifieduser = Mb2slidesHelper::get_user($callback->modifiedby);
    $createdbyitem = $createduser ? '<div class="mb2slides-admin-username">' .
    $createduser->firstname . ' ' . $createduser->lastname .  '</div><div>' . $createduserdate . '</div>' : '&minus;';
    $modifiedbyitem = $modifieduser ? '<div class="mb2slides-admin-username">' .
    $modifieduser->firstname . ' ' . $modifieduser->lastname .  '</div><div>' . $modifieduserdate . '</div>' : '&minus;';

    //Visible To Everyone
    if ($callback->access == 1)
    {
        $visibletoitem = get_string('accessusers', 'local_mb2slides');
    }
    elseif ($callback->access == 2)
    {
        $visibletoitem = get_string('accessguests', 'local_mb2slides');
    }
    else
    {
        $visibletoitem = get_string('accesseveryone', 'local_mb2slides');
    }

    // Language
    $languages = Mb2slidesHelper::get_languages($callback);
    $langitem = count($languages) ? implode(', ', $languages) : '&minus;';

	// Defining slide status
    $hideshowicon = 't/show';
    $hideshowstring = get_string('enableslide', 'local_mb2slides');

    $copyicon = 't/copy';
    $copystring = get_string('duplicate', 'moodle');

    $moveupicon = 't/up';
    $movedownicon = 't/down';
    $moveupstring = get_string('moveup', 'moodle');
    $strmovedown = get_string('movedown', 'moodle');
    $previtem = Mb2slidesApi::get_record_near($callback->id, 'prev');
    $nextitem = Mb2slidesApi::get_record_near($callback->id, 'next');

    if ((bool) $callback->enable)
    {
        $hideshowicon = 't/hide';
        $hideshowstring = get_string('disableslide', 'local_mb2slides');
    }

    // Link to enable / disable the slide
    $hideshowlink = new moodle_url($manageslides, array('hideshowid' => $callback->id));
    $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($hideshowicon, $hideshowstring));

    // Link to move up
    $moveuplink = new moodle_url($manageslides, array('moveup' => $callback->id));
    $moveupitem = $previtem ? $OUTPUT->action_icon($moveuplink, new pix_icon($moveupicon, $moveupstring)) : '';

    // Link to move down
    $movedownlink = new moodle_url($manageslides, array('movedown' => $callback->id));
    $movedownitem = $nextitem ? $OUTPUT->action_icon($movedownlink, new pix_icon($movedownicon, $strmovedown)) : '';

    // Link for editing
    $editlink = new moodle_url($editslide, array('itemid' => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit', 'moodle')));

    // Link to remove
    $deletelink = new moodle_url($deleteslide, array('deleteid' => $callback->id));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', get_string('delete', 'moodle')));

    // Check if user can manage items
    $actions = $can_manage ? $hideshowitem . $moveupitem . $movedownitem . $edititem . $deleteitem : '';

	$table->add_data(array($titlecallback, $visibletoitem, $createdbyitem, $modifiedbyitem, $langitem, $actions));

}

// Display the table
$table->print_html();

echo $OUTPUT->footer();
?>
