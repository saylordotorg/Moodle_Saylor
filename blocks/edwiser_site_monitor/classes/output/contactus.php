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
 * Plugin contactus renderable.
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edwiser_site_monitor/classes/utility.php');

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Renderable for contact us form
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contactus implements renderable, templatable {

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $USER;
        $data = new stdClass;
        $data->firstname = $USER->firstname;
        $data->lastname = $USER->lastname;
        $data->email = $USER->email;
        $data->policy = get_string('policy', 'block_edwiser_site_monitor', ESM_PRIVACY_POLICY_LINK);
        $output = null;
        return $data;
    }

}
