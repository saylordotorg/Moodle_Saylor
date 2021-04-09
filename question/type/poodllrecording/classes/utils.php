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
 *
 *
 * @package   qtype_poodllrecording
 * @copyright 2020 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_poodllrecording;

defined('MOODLE_INTERNAL') || die();

class utils {

    public static function fetch_options_recorders() {
        $rec_options = array(
                constants::RESPONSEFORMAT_AUDIO => get_string('formataudio', constants::M_COMP),
                constants::RESPONSEFORMAT_VIDEO => get_string('formatvideo', constants::M_COMP),
                constants::RESPONSEFORMAT_PICTURE => get_string('formatpicture', constants::M_COMP),
        );
        return $rec_options;
    }


}