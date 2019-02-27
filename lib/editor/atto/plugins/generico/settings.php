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
 * PoodLL Anywhere settings.
 *
 * @package   atto_poodll
 * @copyright 2014 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_generico', new lang_string('pluginname', 'atto_generico')));

$settings = new admin_settingpage('atto_generico_settings', new lang_string('settings', 'atto_generico'));
if ($ADMIN->fulltree) {

	//A customizable editor icon for Generico
	$name = 'atto_generico/editoricon';
	$title =get_string('editoricon', 'atto_generico');
	$description = get_string('editoricon_desc', 'atto_generico');
	$settings->add(new admin_setting_configstoredfile($name, $title, $description, 'editoricon'));

}
