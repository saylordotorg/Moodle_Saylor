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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    block_intelliboard
 * @copyright  2017 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    https://intelliboard.net/
 */

class block_intelliboard_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $options = array(
        	'0' => get_string('s5', 'block_intelliboard'),
        	'1' => get_string('s6', 'block_intelliboard'),
            '2' => get_string('s7', 'block_intelliboard'),
            '3' => get_string('s8', 'block_intelliboard'),
        	'4' => get_string('s9', 'block_intelliboard')

        );
		$options2 = array(
        	'0' => get_string('s5', 'block_intelliboard'),
            '2' => get_string('s7', 'block_intelliboard'),
            '3' => get_string('s8', 'block_intelliboard')
        );

        $mform->addElement('checkbox', 'config_enableadmin', get_string('q1', 'block_intelliboard'));
        $mform->setDefault('config_enableadmin', 1);

        $mform->addElement('select', 'config_adminlist', get_string('s43', 'block_intelliboard'), $options, array('multiple' => 'multiple'));
        $mform->setDefault('config_adminlist', $options);

        $mform->addElement('checkbox', 'config_enablelearner', get_string('q3', 'block_intelliboard'));
        $mform->setDefault('config_enablelearner', 1);

        $mform->addElement('select', 'config_learnerlist', get_string('s43', 'block_intelliboard'), $options2, array('multiple' => 'multiple'));
        $mform->setDefault('config_learnerlist', $options2);
    }
}
