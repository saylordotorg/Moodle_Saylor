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
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_auth_mcae_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2012032914) {
        $sql = "UPDATE {config_plugins} SET plugin = 'auth_mcae' WHERE plugin = 'auth/mcae'";
        $DB->execute($sql);

        echo $OUTPUT->notification('Update plugin configugation', 'notifysuccess');
    }

    return true;
}
