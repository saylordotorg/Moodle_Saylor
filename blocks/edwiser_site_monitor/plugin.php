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
 * Local class of edwiser_site_monitor
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

use core\update\remote_info;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

$installupdate = required_param('installupdate', PARAM_COMPONENT); // Install given available update.
$installupdateversion = required_param('installupdateversion', PARAM_INT); // Version of the available update to.
$confirminstallupdate = optional_param('confirminstallupdate', 0, PARAM_INT);
$sesskey = optional_param('sesskey', 0, PARAM_RAW);

require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:config', $syscontext);

$pageparams = array('installupdate' => $installupdate, 'installupdateversion' => $installupdateversion, 'sesskey' => $sesskey);
$pageurl = new moodle_url('/blocks/edwiser_site_monitor/plugin.php', $pageparams);

$edwiserpluginman = new block_edwiser_site_monitor\plugins();
$update = $edwiserpluginman->prepare_edwiser_plugins_update($installupdate);
if ($update !== true) {
    $output = $PAGE->get_renderer('core', 'admin');
    echo $output->header();
    throw new moodle_exception($update);
    echo $output->footer();
    die;
}

$pluginman = core_plugin_manager::instance();
require_once($CFG->libdir.'/upgradelib.php');
require_sesskey();

$PAGE->set_url($pageurl);
$PAGE->set_context($syscontext);
$PAGE->set_pagelayout('maintenance');
$PAGE->set_popup_notification_allowed(false);

$type = explode('_', $installupdate)[0];
$pluginname = str_replace($type.'_', '', $installupdate);
$installable = new remote_info;
$installable->name = get_string('pluginname', $installupdate);
$installable->component = $installupdate;
$installable->version = $edwiserpluginman->get_edwiser_plugin($type, $pluginname);
$edwiserpluginman->upgrade_install_plugin(
    $installable,
    $confirminstallupdate,
    get_string('updateavailableinstallallhead', 'core_admin'),
    new moodle_url(
        $PAGE->url,
        array(
            'installupdate' => $installupdate,
            'installupdateversion' => $installupdateversion,
            'confirminstallupdate' => 1
        )
    ),
    new moodle_url('/my')
);
