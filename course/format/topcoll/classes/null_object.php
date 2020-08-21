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
 * Collapsed Topics Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.txt' file.
 *
 * @package    format_topcoll
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2020-onwards G J Barnard in respect to modifications of Adaptable null object pattern,
 *             see below.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @link       http://docs.moodle.org/en/Collapsed_Topics_course_format
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

/**
 * For the null object pattern - https://www.wikiwand.com/en/Null_Object_pattern.
 *
 * @author    gthomas2
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_topcoll;

defined('MOODLE_INTERNAL') || die();

/**
 * Facilitates the null object pattern - https://www.wikiwand.com/en/Null_Object_pattern.
 *
 * @author    gthomas2
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait null_object {

    protected $_defaults = [];

    /**
     * Has this class been set.
     *
     * @param bool $ignoreinitialstate - if true, will consider an object with default values set by set_default as
     * not set.
     * @return bool
     */
    public function is_set($ignoreinitialstate = false) {
        $reflect = new \ReflectionClass($this);
        $props   = $reflect->getDefaultProperties();
        foreach ($props as $prop => $default) {
            if ($prop === '_defaults') {
                continue;
            }
            if (isset($this->$prop) && $this->$prop != $default) {
                if ($ignoreinitialstate) {
                    if (!isset($this->_defaults[$prop]) || $this->_defaults[$prop] !== $this->$prop) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Set and track default value
     *
     * @param string $prop
     * @param string $val
     */
    protected function set_default($prop, $val) {
        if (isset($this->_defaults[$prop])) {
            throw new \coding_exception('Default value already set for '.$prop.' - '.$this->_defaults[$prop]);
        }
        $this->$prop = $val;
        $this->_defaults[$prop] = $this->$prop;
    }
}
