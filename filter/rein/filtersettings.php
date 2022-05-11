<?php

/**
 * REIN Libraray Javascript
 * Copyright (C) 2008 onwards Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    filter-rein
 * @author     Remote-Learner.net Inc
 * @author     James McQuillan <james.mcquillan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008 onwards Remote Learner.net Inc http://www.remote-learner.net
 *
 */

defined('MOODLE_INTERNAL') || die;

if (!class_exists('admin_setting_reinfilterdisplaytext')) {
    /**
     * The most flexibly setting, user is typing text
     *
     * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    class admin_setting_reinfilterdisplaytext extends admin_setting {
        /**
         * Return the setting
         *
         * @return mixed returns config if successful else null
         */
        public function get_setting() {
            return '';
        }

        public function write_setting($data) {
            return true;
        }

        /**
         * Validate data before storage
         * @param string data
         * @return mixed true if ok string if error found
         */
        public function validate($data) {
            return true;
        }

        /**
         * Return an XHTML string for the setting
         * @return string Returns an XHTML string
         */
        public function output_html($data, $query='') {
            return format_admin_setting($this, $this->visiblename, $this->description,'', false, '', null, $query);
        }
    }
}

if ($ADMIN->fulltree) {

    $settings->add(
        new admin_setting_configcheckbox(
            'filter_rein/testmode',
            get_string('debug', 'filter_rein'),
            get_string('debug_desc', 'filter_rein'),
            0
        )
    );

    $settings->add(
        new admin_setting_reinfilterdisplaytext(
            'filter_rein/debuginterface',
            get_string('debuginterface', 'filter_rein'),
            get_string('debugdesc', 'filter_rein', $CFG->wwwroot.'/filter/rein/debug.php'),
            0
        )
    );
}
