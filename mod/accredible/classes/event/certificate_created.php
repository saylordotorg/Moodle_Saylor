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
 * The certificate_created event.
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_accredible\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The accredible event class.
 *
 * @since      Moodle 27
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // ... create.
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'accredible';
    }

    public static function get_name() {
        return get_string('eventcertificatecreated', 'mod_accredible');
    }

    public function get_description() {
        return "User {$this->userid} issued certificate id {$this->objectid}.";
    }
}
