<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
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
 * Upgrade scripts
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute mod_coursecertificate upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_coursecertificate_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020072201) {

        // Define index automaticsend (not unique) to be added to coursecertificate.
        $table = new xmldb_table('coursecertificate');
        $index = new xmldb_index('automaticsend', XMLDB_INDEX_NOTUNIQUE, ['automaticsend']);

        // Conditionally launch add index automaticsend.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Coursecertificate savepoint reached.
        upgrade_mod_savepoint(true, 2020072201, 'coursecertificate');
    }

    if ($oldversion < 2022020200) {

        $table = new xmldb_table('coursecertificate');

        // Rename field expires on table coursecertificate to expirydateoffset.
        $field = new xmldb_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'automaticsend');

        // Launch rename field expires.
        $dbman->rename_field($table, $field, 'expirydateoffset');

        // Define field expirydatetype to be added to coursecertificate.
        $field = new xmldb_field('expirydatetype', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'automaticsend');

        // Conditionally launch add field expirydatetype.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Set expirydatetype to 1 (Expiry date absolute) for previous records with "expires" configured.
        $DB->execute("UPDATE {coursecertificate} SET expirydatetype = 1 WHERE expirydateoffset > 0");

        // Coursecertificate savepoint reached.
        upgrade_mod_savepoint(true, 2022020200, 'coursecertificate');
    }
    return true;
}
