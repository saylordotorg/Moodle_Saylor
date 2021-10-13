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
 * Defines {@link \availability_mobileapp\privacy\provider} class.
 *
 * @package     availability_mobileapp
 * @category    privacy
 * @copyright   2018 Sara Arjona <sara@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_mobileapp\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy API implementation for the Restriction by Mobile app access plugin.
 *
 * @copyright  2018 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns stringid of a text explaining that this plugin stores no personal data.
     *
     * @return string
     */
    public static function _get_reason() {
        return 'privacy:metadata';
    }
}
