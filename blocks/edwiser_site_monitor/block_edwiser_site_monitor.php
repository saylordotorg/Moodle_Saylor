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
 * Ediwser Site Monitor Block
 *
 * @package    block_edwiser_site_monitor
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die();

use block_edwiser_site_monitor\usage;
use block_edwiser_site_monitor\utility;

/**
 * Edwiser Site Monitor Block
 *
 * @package    block_edwiser_site_monitor
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_edwiser_site_monitor extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_edwiser_site_monitor');
    }

    /**
     * This function is called on your subclass right after an instance is loaded
     * Use this function to act on instance data just after it's loaded and before anything else is done
     * For instance: if your block will have different title's depending on location (site, course, blog, etc)
     *
     * @return void
     */
    public function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_edwiser_site_monitor');
    }

    /**
     * Gets the block contents.
     *
     * If we can avoid it better not check the server status here as connecting
     * to the server will slow down the whole page load.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        $usage = usage::get_instance();
        if ($this->content !== null) {
            return $this->content;
        }

        if (!has_capability('block/edwiser_site_monitor:addinstance', context_system::instance())) {
            return null;
        }

        utility::edwiser_site_monitor_log_usage();

        $renderer = $this->page->get_renderer('block_edwiser_site_monitor');

        $refreshrate = isset($this) && isset($this->config) && isset($this->config->refreshrate) ? $this->config->refreshrate : 5;

        $stringmanager = get_string_manager();
        $strings = $stringmanager->load_component_strings('block_edwiser_site_monitor', 'en');
        $this->page->requires->strings_for_js(array_keys($strings), 'block_edwiser_site_monitor');

        $this->page->requires->data_for_js('refreshrate', $refreshrate);
        $this->page->requires->data_for_js('totalmemory', $usage->get_total_memory());
        $this->page->requires->data_for_js('totalstorage', $usage->get_total_storage());

        $this->page->requires->js_call_amd('block_edwiser_site_monitor/main', 'init');

        $this->content = new stdClass();
        $this->content->footer = '';

        $data = new stdClass;

        // Render live status.
        $data->live_status = $renderer->render(new \block_edwiser_site_monitor\output\live_status());

        // Render last 24 hours usage.
        $data->last_24_hours_usage = $renderer->render(new \block_edwiser_site_monitor\output\last_24_hours_usage($this->instance));

        // Install new plugin page url.
        $data->installnewurl = (new moodle_url('/admin/tool/installaddon/index.php'))->__toString();

        // Render recommendation view.
        $data->recommendation = $renderer->render(new \block_edwiser_site_monitor\output\recommendation($this->instance));

        // Render contactus view.
        $data->contactus = $renderer->render(new \block_edwiser_site_monitor\output\contactus());

        $this->content->text = $renderer->render_from_template('block_edwiser_site_monitor/main', $data);
        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        if (defined('CLI_SCRIPT')) {
            return array('all' => true);
        }
        return array('my' => has_capability('moodle/site:config', context_system::instance()));
    }

    /**
     * Are you going to allow multiple instances of each block?
     * If yes, then it is assumed that the block WILL USE per-instance configuration
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL USE per-instance configuration.
        return false;
    }
}
