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
 * This page lets admins download workflow run details in JSON.
 *
 * @package    tool_trigger
 * @copyright  Catalyst IT, 2021
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('tool/trigger:exportrundetails', $context);

$workflowid = required_param('workflow', PARAM_INT);
$runid = required_param('run', PARAM_INT);

$workflowdata = \tool_trigger\workflow_manager::export_workflow_and_run_history($workflowid, $runid);
if (!$workflowdata) {
    print_error('$workflowdata');
}

$jsonexporter = new \tool_trigger\json\json_export($workflowdata);
$jsonexporter->download_file();
