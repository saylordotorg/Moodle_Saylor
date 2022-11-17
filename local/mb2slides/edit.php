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

require_once( __DIR__ . '/../../config.php' );
require_once( __DIR__ . '/slide_form.php');
require_once( __DIR__ . '/classes/api.php' );
require_once( __DIR__ . '/classes/helper.php' );
require_once( __DIR__ . '/lib.php' );
require_once( $CFG->libdir . '/adminlib.php' );

// Optional parameters
$itemid = optional_param('itemid', 0, PARAM_INT);
$returnurl = optional_param( 'returnurl', '/local/mb2slides/index.php', PARAM_LOCALURL );

// Link generation
$urlparameters = array( 'itemid' => $itemid, 'returnurl' => $returnurl );
$baseurl = new moodle_url( '/local/mb2slides/edit.php', $urlparameters );
$returnurl = new moodle_url( $returnurl );

// Configure the context of the page
admin_externalpage_setup( 'local_mb2slides_manageslides', '', null, $baseurl );
require_capability( 'local/mb2slides:manageitems', context_system::instance() );

// Get existing items
$items = Mb2slidesApi::get_list_records();

// Create an editing form
$mform = new service_edit_form($PAGE->url);

// Cancel processing
if ($mform->is_cancelled())
{
    $message = '';
}

// Getting the data
$sliderecord = new stdClass();
$data = Mb2slidesApi::get_form_data($mform, $itemid);

// Processing of received data
if (!empty($data))
{
    if ($itemid)
    {
        Mb2slidesApi::update_record_data($data, true);
        $message = get_string('slideupdated', 'local_mb2slides', array('title' => Mb2slidesApi::get_record($itemid)->title));
    }
    else
    {
        Mb2slidesApi::add_record($data);
        $message = get_string('slidecreated', 'local_mb2slides');
    }
}

if (isset($message))
{
    redirect($returnurl, $message);
}

// The page title
$titlepage = get_string('editslide', 'local_mb2slides');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
echo $OUTPUT->heading($titlepage);

// Displays the form
$mform->display();

echo $OUTPUT->footer();
?>
