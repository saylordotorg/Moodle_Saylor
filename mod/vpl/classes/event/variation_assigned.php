<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for logging of assigned variation events
 *
 * @package mod_vpl
 * @copyright 2020 onwards Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */
namespace mod_vpl\event;

defined( 'MOODLE_INTERNAL' ) || die();
require_once(dirname( __FILE__ ) . '/../../locallib.php');
class variation_assigned extends variation_base {
    protected function init() {
        parent::init();
        $this->legacyaction = 'assigned variation';
        $this->data['crud'] = 'c';
    }
    public function get_description() {
        return $this->get_description_mod( 'assigned' );
    }
    public static function log($vpl, $varid = null, $userid = null) {
        $vplinstance = $vpl->get_instance();
        $info = array (
            'objectid' => $varid,
            'contextid' => $vpl->get_context()->id,
            'relateduserid' => $userid,
            'courseid' => $vplinstance->course,
            'other' => array('vplid' => $vplinstance->id),
        );
        parent::log( $info );
    }
}
