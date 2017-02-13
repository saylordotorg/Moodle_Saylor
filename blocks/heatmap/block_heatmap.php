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
 * Heatmap block definition
 *
 * @package    block_heatmap
 * @copyright  2016 Michael de Raadt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Heatmap block class
 *
 * @copyright 2016 Michael de Raadt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_heatmap extends block_base {

    /**
     * Sets the block title.
     *
     * @return none
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_heatmap');
    }

    /**
     * Defines where the block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'site'        => true,
            'mod'         => false,
            'my'          => false,
        );
    }

    /**
     * Controls global configurability of block.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Controls whether multiple block instances are allowed.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Creates the block's main content
     *
     * @return string
     */
    public function get_content() {

        global $COURSE, $DB, $OUTPUT;

        if (isset($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        // Check to see user can view/use the heatmap.
        $context = context_course::instance($COURSE->id);
        if (!isloggedin() || isguestuser() || !has_capability('block/heatmap:view', $context)) {
            $this->content->text = '';
            return $this->content;
        }

        // Get global settings.
        $cachelife = get_config('block_heatmap', 'cachelife');
        if ($cachelife === false) {
            $cachelife = (5 * 60);
        }
        $activitysince = get_config('block_heatmap', 'activitysince');
        if ($activitysince === false) {
            $activitysince = 'sincestart';
        }
        $whattoshow = get_config('block_heatmap', 'whattoshow');
        if ($whattoshow === false) {
            $whattoshow = 'showboth';
        }

        // Get cached logs to avoid hitting the logs each reload.
        $cachedlogs = cache::make('block_heatmap', 'cachedlogs');
        $views = $cachedlogs->get('views'.$COURSE->id);
        $lastcached = $cachedlogs->get('time'.$COURSE->id);
        $updated = $lastcached;
        $now = time();

        // Check that the course has started.
        if ($whattoshow == 'sincestart' && $now < $COURSE->startdate) {
            $this->content->text = get_string('notstarted', 'block_heatmap');
            return $this->content;
        }

        // Check cached values are available and within set time window.
        if (empty($views) || !isset($lastcached) || $lastcached < $now - $cachelife) {

            $updated = $now;
            $useinternalreader = false; // Flag to determine if we should use the internal reader.
            $uselegacyreader = false; // Flag to determine if we should use the legacy reader.

            // Get list of readers.
            $logmanager = get_log_manager();
            $readers = $logmanager->get_readers();

            // Get preferred reader.
            if (!empty($readers)) {
                foreach ($readers as $readerpluginname => $reader) {

                    // If sql_internal_table_reader is preferred reader.
                    if ($reader instanceof \core\log\sql_internal_table_reader) {
                        $useinternalreader = true;
                        $logtable = $reader->get_internal_log_table_name();
                    }

                    // If legacy reader is preferred reader.
                    if ($readerpluginname == 'logstore_legacy') {
                        $uselegacyreader = true;
                    }

                }
            }

            // If no legacy and no internal log then don't proceed.
            if (!$uselegacyreader && !$useinternalreader) {
                $this->content->text = get_string('nologreaderenabled', 'block_heatmap');
                return $this->content;
            }

            // Get record from sql_internal_table_reader.
            if ($useinternalreader) {
                $timesince = ($activitysince == 'sincestart') ? 'AND timecreated >= :coursestart' : '';
                $sql = "SELECT contextinstanceid as cmid, COUNT('x') AS numviews, COUNT(DISTINCT userid) AS distinctusers
                          FROM {" . $logtable . "} l
                         WHERE courseid = :courseid
                           $timesince
                           AND anonymous = 0
                           AND crud = 'r'
                           AND contextlevel = :contextmodule
                      GROUP BY contextinstanceid";
                $params = array('courseid' => $COURSE->id, 'contextmodule' => CONTEXT_MODULE, 'coursestart' => $COURSE->startdate);
                $views = $DB->get_records_sql($sql, $params);

            } else if ($uselegacyreader) {
                // If using legacy log then get activity usage from old table.
                $logactionlike = $DB->sql_like('l.action', ':action');
                $timesince = ($activitysince == 'sincestart') ? 'AND l.time >= :coursestart' : '';
                $sql = "SELECT cm.id, COUNT('x') AS numviews, COUNT(DISTINCT userid) AS distinctusers
                          FROM {course_modules} cm
                          JOIN {modules} m
                            ON m.id = cm.module
                          JOIN {log} l
                            ON l.cmid = cm.id
                         WHERE cm.course = :courseid
                           $timesince
                           AND $logactionlike
                           AND m.visible = 1
                      GROUP BY cm.id";
                $params = array('courseid' => $COURSE->id, 'action' => 'view%', 'coursestart' => $COURSE->startdate);
                if (!empty($minloginternalreader)) {
                    $params['timeto'] = $minloginternalreader;
                }
                $views = $DB->get_records_sql($sql, $params);
            }

            // Cache queried values for next time window.
            $cachedlogs->set('views'.$COURSE->id, $views);
            $cachedlogs->set('time'.$COURSE->id, $now);
        }

        // Check that there were some results.
        if (empty($views)) {
            $this->content->text = get_string('nologentries', 'block_heatmap');
            return $this->content;
        }

        // Get the min, max and totals.
        $firstactivity = array_shift($views);
        $totalviews = $firstactivity->numviews;
        $totalusers = $firstactivity->distinctusers;
        $minviews = $firstactivity->numviews;
        $maxviews = $firstactivity->numviews;
        foreach ($views as $key => $activity) {
            $totalviews += $activity->numviews;
            if ($activity->numviews < $minviews) {
                $minviews = $activity->numviews;
            }
            if ($activity->numviews > $maxviews) {
                $maxviews = $activity->numviews;
            }
            $totalusers += $activity->distinctusers;
        }
        array_unshift($views, $firstactivity);

        // Block text output.
        $this->content->text .= html_writer::div(
            get_string('totalviews', 'block_heatmap', $totalviews),
            'block_heatmap_totalviews'
        );
        $this->content->text .= html_writer::div(
            get_string('distinctuserviews', 'block_heatmap', $totalusers),
            'block_heatmap_userviews'
        );
        if ($activitysince == 'sincestart') {
            $this->content->text .= html_writer::div(
                get_string('sincecoursestart', 'block_heatmap'),
                'block_heatmap_sincecoursestart'
            );
        }
        $this->content->text .= html_writer::div(
            get_string('updated', 'block_heatmap',
                userdate($updated, get_string('strftimerecentfull', 'langconfig'))
            ),
            'block_heatmap_updated'
        );
        $this->content->text .= html_writer::link(
            null,
            get_string('toggleheatmap', 'block_heatmap'),
            array('onclick' => 'M.block_heatmap.toggleHeatmap();', 'style' => 'cursor: pointer;')
        );

        // Set up JS for injecting heatmap.
        $jsmodule = array(
            'name'     => 'block_heatmap',
            'fullpath' => '/blocks/heatmap/module.js',
            'requires' => array(),
        );
        user_preference_allow_ajax_update('heatmaptogglestate', PARAM_BOOL);
        $toggledon = get_user_preferences('heatmaptogglestate', true);
        $viewsicon = $OUTPUT->pix_icon('t/hide', get_string('views', 'block_heatmap', $totalusers));
        $usersicon = $OUTPUT->pix_icon('t/user', get_string('distinctusers', 'block_heatmap', $totalusers));
        $arguments = array(
            json_encode($views),
            $minviews,
            $maxviews,
            $toggledon,
            $viewsicon,
            $usersicon,
            $whattoshow,
        );
        $this->page->requires->js_init_call('M.block_heatmap.initHeatmap', $arguments, false, $jsmodule);

        return $this->content;
    }
}
