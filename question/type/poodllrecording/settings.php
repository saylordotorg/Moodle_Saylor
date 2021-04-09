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
 * This file defines the admin settings for this plugin
 *
 * @package   qtype_poodllrecording
 * @copyright 2020 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_poodllrecording\constants;
use qtype_poodllrecording\utils;

if ($ADMIN->fulltree) {
    $plugin = constants::M_COMP;


    //Recorders
    $rec_options = utils::fetch_options_recorders();
    $rec_defaults = array(constants::RESPONSEFORMAT_AUDIO  => 1, constants::RESPONSEFORMAT_VIDEO => 1 , constants::RESPONSEFORMAT_PICTURE => 1);
    $settings->add(new admin_setting_configmulticheckbox(constants::M_COMP . '/allowedrecorders',
            get_string('allowedrecorders', constants::M_COMP),
            get_string('allowedrecordersdetails', constants::M_COMP), $rec_defaults,$rec_options));


}