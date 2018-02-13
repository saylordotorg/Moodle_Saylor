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
 * @package    qtype_algebra
 * @copyright  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Default evaluation method.
    $settings->add(new admin_setting_configselect('qtype_algebra_method',
            new lang_string('defaultmethod', 'qtype_algebra'),
            new lang_string('compareby', 'qtype_algebra'), 'eval',
            array('sage' => new lang_string('comparesage', 'qtype_algebra'),
                  'eval' => new lang_string('compareeval', 'qtype_algebra'),
                  'equiv'    => new lang_string('compareequiv', 'qtype_algebra')
            )));
    // SAGE server connection host.
    $settings->add(new admin_setting_configtext('qtype_algebra_host',
            get_string('host', 'qtype_algebra'), '', 'localhost', PARAM_TEXT));
    // SAGE server connection port.
    $settings->add(new admin_setting_configtext('qtype_algebra_port',
            get_string('port', 'qtype_algebra'), '', 7777, PARAM_INT));
    // SAGE server connection uri.
    $settings->add(new admin_setting_configtext('qtype_algebra_uri',
            get_string('uri', 'qtype_algebra'), '', '', PARAM_TEXT));
    // TeX expressions delimiter.
    $settings->add(new admin_setting_configselect('qtype_algebra_texdelimiters',
            new lang_string('texdelimiters', 'qtype_algebra'),
            '', 'old',
            array('old' => new lang_string('dollars', 'qtype_algebra'),
                  'simple' => new lang_string('dollar', 'qtype_algebra'),
                  'new' => new lang_string('brackets', 'qtype_algebra'),
                  'inline' => new lang_string('braces', 'qtype_algebra'),
            )));
    // TeX operator for multiplication.
    $settings->add(new admin_setting_configselect('qtype_algebra/multiplyoperator',
            new lang_string('multiplyoperator', 'qtype_algebra'),
            '', 'times',
            array('times' => new lang_string('times', 'qtype_algebra'),
                  'cdot' => new lang_string('cdot', 'qtype_algebra')
            )));
    // Method to diplay TeX formatted answer formula.
    $settings->add(new admin_setting_configselect('qtype_algebra/formuladisplay',
            new lang_string('formuladisplay', 'qtype_algebra'),
            '', 'iframe',
            array('iframe' => new lang_string('iframe', 'qtype_algebra'),
                  'dynamic' => new lang_string('dynamic', 'qtype_algebra')
            )));
}
