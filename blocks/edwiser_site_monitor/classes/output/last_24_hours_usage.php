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
 * Last 24 hour usage renderable.
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Renderable for last 24 hour usage tab
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class last_24_hours_usage implements renderable, templatable {

    /** @var stdClass block instance */
    protected $instance;

    /**
     * Constructor.
     *
     * @param stdClass $instance The block instance
     */
    public function __construct($instance) {
        $this->instance = $instance;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $data = new stdClass;
        $data->dates = [];
        $maxtime = $DB->get_field_sql('SELECT max(time) FROM {block_edwiser_site_monitor}');
        $sql = 'SELECT time
                  FROM {block_edwiser_site_monitor}
                  WHERE time < ? AND time >= ?';
        $min = strtotime(date('d-m-Y', $maxtime));
        $today = strtotime(date('d-m-Y', time()));
        $limit = strtotime(date('d-m-Y', $maxtime - 24 * 60 * 60 * 7));
        if ($DB->record_exists_sql($sql, array($maxtime, $min))) {
            $data->dates[] = [
                'date' => $today == $min ? get_string('today', 'block_edwiser_site_monitor') : date('d-m-Y', $min),
                'stamp' => $min,
                "selected" => "selected"
            ];
            $max = $min;
            $min = $max - 24 * 60 * 60;
            while ($min > $limit) {
                if ($DB->record_exists_sql($sql, array($max, $min))) {
                    $data->dates[] = [
                        'date' => date('d-m-Y', $min),
                        'stamp' => $min
                    ];
                }
                $max = $min;
                $min = $max - 24 * 60 * 60;
            }
        }
        $data->hasdates = count($data->dates) > 1;

        $lastrun = $DB->get_field('task_scheduled', 'lastruntime', array(
            'component' => 'local_edwiserreports',
            'classname' => '\local_edwiserreports\task\site_access_data'
        ));
        if ($lastrun == false || $lastrun < time() - (60 * 5)) {
            $data->error = get_string(
                'crontaskwarning',
                'block_edwiser_site_monitor',
                'https://docs.moodle.org/311/en/Cron'
            );
        }

        $output = null;
        return $data;
    }

}
