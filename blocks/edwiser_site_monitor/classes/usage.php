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
 * Class to get Site usage
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor;

defined('MOODLE_INTERNAL') || die;

/**
 * Get usage of site using this class.
 *
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usage {

    /**
     * Static $instance to implement singletone class
     * @var block_edwiser_site_monitor_usage
     */
    public static $instance = null;

    /**
     * $disabled list of disabled usage. cpu, memory for now
     * @var array
     */
    public static $disabled = [];

    /**
     * Private constructor for singletone class
     */
    private function __construct() {
        if (stripos(PHP_OS, 'linux') !== false) {
            if (empty(sys_getloadavg())) {
                self::$disabled['cpu'] = true;
            }
            if (shell_exec("free") == "") {
                self::$disabled['memory'] = true;
            }
        }
    }

    /**
     * Get instance of class using this method
     *
     * @return block_edwiser_site_monitor_usage
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new usage();
        }
        return self::$instance;
    }
    /**
     * Return server's cpu usage
     *
     * @return float cpu usage
     */
    public function get_cpu_usage() {
        $load = 0;
        if (stripos(PHP_OS, 'win') !== false) {
            $load = shell_exec("wmic cpu get loadpercentage");
            $load = (int)filter_var($load, FILTER_SANITIZE_NUMBER_INT);
        } else if (stripos(PHP_OS, 'linux') !== false) {
            if (isset(self::$disabled['cpu'])) {
                return $load;
            }
            $loads    = sys_getloadavg();
            $corenums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
            if (!empty($corenums)) {
                $load     = round($loads[0] / ($corenums + 1) * 100, 2);
            } else {
                $load = round($loads[0] * 100, 2);
            }
        }
        if ($load > 100) {
            return 100;
        }
        return $load;
    }

    /**
     * Return server's memory usage
     *
     * @return float memory usage
     */
    public function get_memory_usage() {
        $usage = 0;
        if (stripos(PHP_OS, 'win') !== false) {
            $max   = shell_exec("wmic OS get TotalVisibleMemorySize");
            $max   = (int)filter_var($max, FILTER_SANITIZE_NUMBER_INT);
            $free  = shell_exec("wmic OS get FreePhysicalMemory");
            $free  = (int)filter_var($free, FILTER_SANITIZE_NUMBER_INT);
            $usage = round(($max - $free) / $max * 100, 2);
        } else if (stripos(PHP_OS, 'linux') !== false) {
            if (isset(self::$disabled['memory'])) {
                return $usage;
            }
            $memory = shell_exec('free -k');
            if ($memory != "") {
                $memory    = explode("\n", $memory);
                $memory    = explode(' ', preg_replace('!\s+!', ' ', $memory[1]));
                $maxmemory = round($memory[1] / 1024 / 1024, 2);
                $usage     = round($memory[2] / 1024 / 1024, 2);
                $usage     = round($usage / $maxmemory * 100, 2);
            }
        }
        if ($usage > 100) {
            return 100;
        }
        return $usage;
    }

    /**
     * Return server's max memory
     *
     * @return float memory
     */
    public function get_total_memory() {
        $max = 0;
        if (stripos(PHP_OS, 'win') !== false) {
            $max = shell_exec("wmic OS get TotalVisibleMemorySize");
            $max = (int)filter_var($max, FILTER_SANITIZE_NUMBER_INT);
        } else if (stripos(PHP_OS, 'linux') !== false) {
            if (isset(self::$disabled['memory'])) {
                return $max;
            }
            $memory = shell_exec('free');
            if ($memory != "") {
                $memory = explode("\n", $memory)[1];
                $memory = array_merge(array_filter(explode(" ", $memory)));
                $max    = $memory[1];
            }
        }
        return $max / 1024 / 1024;
    }

    /**
     * Return server's storage usage
     *
     * @return float storage usage
     */
    public function get_storage_usage() {
        global $CFG;
        $usage = 0;
        if (stripos(PHP_OS, 'linux') !== false) {
            $storage = shell_exec('df -m ' . $CFG->dirroot);
            if ($storage != "") {
                $storage = explode("\n", $storage)[1];
                $storage = array_merge(array_filter(explode(" ", $storage)));
                $usage   = round($storage[2] / $storage[1] * 100, 2);
            } else {
                $free  = disk_free_space($CFG->dirroot);
                $all   = disk_total_space($CFG->dirroot);
                $usage = round(($all - $free) / $all * 100, 2);
            }
        } else if (stripos(PHP_OS, 'win') !== false) {
            $free  = disk_free_space($CFG->dirroot);
            $all   = disk_total_space($CFG->dirroot);
            $usage = round(($all - $free) / $all * 100, 2);
        }
        if ($usage > 100) {
            return 100;
        }
        return $usage;
    }

    /**
     * Return server's storage
     *
     * @return float storage
     */
    public function get_total_storage() {
        global $CFG;
        $storage = disk_total_space($CFG->dirroot) / 1024 / 1024 / 1024;
        return $storage;
    }

    /**
     * Return live users count
     *
     * @return int live users count
     */
    public function get_live_users() {
        global $DB, $CFG;
        $timetosee = 300;
        if (isset($CFG->block_online_users_timetosee)) {
            $timetosee = $CFG->block_online_users_timetosee * 60;
        }
        $live = $DB->get_field_sql('SELECT count(id) live FROM {user} WHERE lastaccess > ?', array(time() - $timetosee));
        return $live == 0 ? 1 : $live;
    }


    /**
     * Return total users, active users, suspended users, and deleted users
     *
     * @return array [active, suspended, deleted]
     */
    public function get_all_users() {
        global $DB;
        $users = $DB->get_record_sql(
            'SELECT count(id) total,
                    count(CASE suspended WHEN 1 THEN 1 ELSE NULL END) suspended,
                    count(CASE deleted WHEN 1 THEN 1 ELSE NULL END) deleted
               FROM {user}
               WHERE username <> ?',
            array("guest")
        );
        return [
            'total'               => $users->total,
            'active'              => $users->total - $users->suspended - $users->deleted,
            'suspended'           => $users->suspended,
            'deleted'             => $users->deleted,
            'activepercentage'    => ($users->total - $users->suspended - $users->deleted) / $users->total * 100,
            'suspendedpercentage' => $users->suspended / $users->total * 100,
            'deletedpercentage'   => $users->deleted / $users->total * 100,
        ];
    }
}
