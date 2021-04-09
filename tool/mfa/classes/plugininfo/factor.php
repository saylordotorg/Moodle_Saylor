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
 * Subplugin info class.
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_mfa\plugininfo;

defined('MOODLE_INTERNAL') || die();

class factor extends \core\plugininfo\base {

    const STATE_UNKNOWN = 'unknown';
    const STATE_PASS = 'pass';
    const STATE_FAIL = 'fail';
    const STATE_NEUTRAL = 'neutral';
    // Locked state is identical to neutral, but can't be overridden.
    const STATE_LOCKED = 'locked';

    /**
     * Finds all MFA factors.
     *
     * @return array of factor objects.
     */
    public static function get_factors() {
        $return = array();
        $factors = \core_plugin_manager::instance()->get_plugins_of_type('factor');

        foreach ($factors as $factor) {
            $classname = '\\factor_'.$factor->name.'\\factor';
            if (class_exists($classname)) {
                $return[] = new $classname($factor->name);
            }
        }
        return self::sort_factors_by_order($return);
    }

    /**
     * Sorts factors by configured order.
     *
     * @param array of factor objects
     *
     * @return array of factor objects
     * @throws \dml_exception
     */
    public static function sort_factors_by_order($unsorted) {
        $sorted = array();
        $orderarray = explode(',', get_config('tool_mfa', 'factor_order'));

        foreach ($orderarray as $order => $factorname) {
            foreach ($unsorted as $key => $factor) {
                if ($factor->name == $factorname) {
                    $sorted[] = $factor;
                    unset($unsorted[$key]);
                }
            }
        }

        $sorted = array_merge($sorted, $unsorted);
        return $sorted;
    }

    /**
     * Finds factor by its name.
     *
     * @param string $name
     *
     * @return mixed factor object or false if factor not found.
     */
    public static function get_factor($name) {
        $factors = \core_plugin_manager::instance()->get_plugins_of_type('factor');

        foreach ($factors as $factor) {
            if ($name == $factor->name) {
                $classname = '\\factor_'.$factor->name.'\\factor';
                if (class_exists($classname)) {
                    return new $classname($factor->name);
                }
            }
        }

        return false;
    }

    /**
     * Finds all enabled factors.
     *
     * @return array of factor objects
     */
    public static function get_enabled_factors() {
        $return = array();
        $factors = self::get_factors();

        foreach ($factors as $factor) {
            if ($factor->is_enabled()) {
                $return[] = $factor;
            }
        }

        return $return;
    }

    /**
     * Finds active factors for current user.
     *
     * @return array of factor objects.
     */
    public static function get_active_user_factor_types() {
        global $USER;
        $return = array();
        $factors = self::get_enabled_factors();

        foreach ($factors as $factor) {
            $userfactors = $factor->get_active_user_factors($USER);
            if (count($userfactors) > 0) {
                $return[] = $factor;
            }
        }

        return $return;
    }

    /**
     * Finds active factors for given user.
     *
     * @param stdClass $user the user to get types for.
     * @return array of factor objects.
     */
    public static function get_active_other_user_factor_types($user) {
        $return = array();
        $factors = self::get_enabled_factors();

        foreach ($factors as $factor) {
            $userfactors = $factor->get_active_user_factors($user);
            if (count($userfactors) > 0) {
                $return[] = $factor;
            }
        }

        return $return;
    }

    /**
     * Returns next factor to authenticate user.
     *
     * @return mixed factor object the next factor to be authenticated or false.
     */
    public static function get_next_user_factor() {
        $factors = self::get_active_user_factor_types();

        foreach ($factors as $factor) {
            if (!$factor->has_input()) {
                continue;
            }

            if ($factor->get_state() == self::STATE_UNKNOWN) {
                return $factor;
            }
        }

        return new \tool_mfa\local\factor\fallback();
    }

    /**
     * Returns the list of available actions with factor.
     *
     * @return array
     */
    public static function get_factor_actions() {
        $actions = array();
        $actions[] = 'setup';
        $actions[] = 'revoke';
        $actions[] = 'enable';
        $actions[] = 'revoke';
        $actions[] = 'disable';
        $actions[] = 'up';
        $actions[] = 'down';

        return $actions;
    }

    /**
     * Returns the information about plugin availability
     *
     * True means that the plugin is enabled. False means that the plugin is
     * disabled. Null means that the information is not available, or the
     * plugin does not support configurable availability or the availability
     * can not be changed.
     *
     * @return null|bool
     */
    public function is_enabled() {
        if (!$this->rootdir) {
            // Plugin missing.
            return false;
        }

        $factor = $this->get_factor($this->name);

        if ($factor) {
            return $factor->is_enabled();
        }

        return false;
    }

    /**
     * Returns section name for settings.
     *
     * @return string
     */
    public function get_settings_section_name() {
        return $this->type . '_' . $this->name;
    }

    /**
     * Loads factor settings to the settings tree
     *
     * This function usually includes settings.php file in plugins folder.
     * Alternatively it can create a link to some settings page (instance of admin_externalpage)
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);

        if ($adminroot->fulltree) {
            include($this->full_path('settings.php'));
        }

        $adminroot->add($parentnodename, $settings);
    }

    /**
     * Checks that given factor exists.
     *
     * @param string $factorname
     *
     * @return bool
     */
    public static function factor_exists($factorname) {
        $factor = self::get_factor($factorname);
        return !$factor ? false : true;
    }

    /**
     * Returns instance of any factor from the factorid.
     *
     * @param int $factorid
     *
     * @return stdClass|null Factor instance or nothing if not found.
     */
    public static function get_instance_from_id($factorid) {
        global $DB;
        return $DB->get_record('tool_mfa', array('id' => $factorid));
    }
}
