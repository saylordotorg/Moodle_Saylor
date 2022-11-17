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

require_once( __DIR__ . '/../../config.php' );
require_once( __DIR__ . '/notice_form.php');
require_once( __DIR__ . '/classes/api.php' );
require_once( __DIR__ . '/classes/helper.php' );
require_once( __DIR__ . '/lib.php' );
require_once( $CFG->libdir . '/adminlib.php' );

// Optional parameters
$itemid = optional_param('itemid', 0, PARAM_INT);
$returnurl = optional_param( 'returnurl', '/local/mb2notices/index.php', PARAM_LOCALURL );

// Link generation
$urlparameters = array( 'itemid' => $itemid, 'returnurl' => $returnurl );
$baseurl = new moodle_url( '/local/mb2notices/edit.php', $urlparameters );
$returnurl = new moodle_url( $returnurl );

// Configure the context of the page
admin_externalpage_setup( 'local_mb2notices_managenotices', '', null, $baseurl );
require_capability( 'local/mb2notices:manageitems', context_system::instance() );

// Get existing items
$items = Mb2noticesApi::get_list_records();

// Create an editing form
$mform = new service_edit_form($PAGE->url);

// Cancel processing
if ($mform->is_cancelled())
{
    $message = '';
}

// Getting the data
$noticerecord = new stdClass();
$data = Mb2noticesApi::get_form_data($mform, $itemid);

// Processing of received data
if (!empty($data))
{
    if ($itemid)
    {
        Mb2noticesApi::update_record_data($data, true);
        $message = get_string('noticeupdated', 'local_mb2notices', array('title' => Mb2noticesApi::get_record($itemid)->title));
    }
    else
    {
        Mb2noticesApi::add_record($data);
        $message = get_string('noticecreated', 'local_mb2notices');
    }
}

if (isset($message))
{
    redirect($returnurl, $message);
}

// The page title
$titlepage = get_string('editnotice', 'local_mb2notices');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
echo $OUTPUT->heading($titlepage);

// Displays the form
$mform->display();

echo $OUTPUT->footer();
?>
