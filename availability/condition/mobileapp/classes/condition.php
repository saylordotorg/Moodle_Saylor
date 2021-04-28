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
 * Mobile app access condition.
 *
 * @package availability_mobileapp
 * @copyright Juan Leyva <juan@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_mobileapp;

defined('MOODLE_INTERNAL') || die();

/**
 * Mobile app access condition.
 *
 * @package availability_mobileapp
 * @copyright Juan Leyva <juan@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /**  @var int Mobile app access */
    const MOBILE_APP = 1;

    /**  @var int Not mobile app access */
    const NOT_MOBILE_APP = 2;

    /** @var int Expected access type selected */
    protected $accesstype;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get expected access type.
        if (!empty($structure->e)) {
            $this->accesstype = $structure->e;
        } else {
            throw new \coding_exception('Missing or invalid ->e for access condition');
        }
    }

    public function save() {
        return (object)array('type' => 'mobileapp', 'e' => $this->accesstype);
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $accesstype Expected access type
     * @return stdClass Object representing condition
     */
    public static function get_json($accesstype) {
        return (object)array('type' => 'mobileapp', 'e' => (int)$accesstype);
    }

    protected function get_debug_string() {
        return '#' . $this->accesstype;
    }

    /**
     * Detects if the user is accesing Moodle via Web Services.
     * @return boolean True if the user is accesing via WS
     */
    protected function is_ws_access() {
        global $ME;

        // First check this global const.
        if (WS_SERVER) {
            return true;
        }

        // Check rare cases, like webservice/pluginfile.php.
        if (strpos($ME, "webservice/") !== false) {
            $token = optional_param('token', '', PARAM_ALPHANUM);
            if ($token) {
                return true;
            }
        }

        return false;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {

        $allow = false;

        // Detect if we are accesing via Webservices.
        if ($this->is_ws_access() && $this->accesstype == self::MOBILE_APP) {
            $allow = true;
        }

        if (!$this->is_ws_access() && $this->accesstype == self::NOT_MOBILE_APP) {
            $allow = true;
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {

        if ($this->accesstype == self::MOBILE_APP) {
            $str = 'requires_app';
        } else {
            $str = 'requires_notapp';
        }

        if ($not) {
            $str = ($str == 'requires_app') ? 'requires_notapp' : 'requires_app';
        }

        return get_string($str, 'availability_mobileapp');
    }

}
