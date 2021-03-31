<?php

// This file is part of the Accredible Certificate module for Moodle - http://moodle.org/
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
 * This file keeps track of upgrades to the certificate module
 *
 * @package    mod
 * @subpackage accredible
 * @copyright  Accredible <dev@accredible.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_accredible_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager();

    $result = true;

    if ($oldversion < 2014111800) {

        // Changing type of field description on table accredible to text.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'achievementid');

        // Launch change of type for field description.
        $dbman->change_field_type($table, $field);

        // Accredible savepoint reached.
        upgrade_mod_savepoint(true, 2014111800, 'accredible');
    }

    if ($oldversion < 2014112600) {

        // Define field completionactivities to be added to accredible.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('completionactivities', XMLDB_TYPE_TEXT, null, null, null, null, null, 'passinggrade');

        // Conditionally launch add field completionactivities.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Accredible savepoint reached.
        upgrade_mod_savepoint(true, 2014112600, 'accredible');
    }

    if ($oldversion < 2014121800) {

        // Define field certificatename to be added to accredible.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('certificatename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'timecreated');

        // Conditionally launch add field certificatename.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Set the certificate names to equal the Activity name
        if ($accredible_activities = $DB->get_records('accredible')) {
            foreach ($accredible_activities as $activity) {
                $activity->certificatename = $activity->name;
                $DB->update_record('accredible', $activity);
            }
        }

        // Accredible savepoint reached.
        upgrade_mod_savepoint(true, 2014121800, 'accredible');
    }

    if ($oldversion < 2016111000) {

        // Define field groupid to be added to accredible.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'certificatename');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Changing nullability of field name on table accredible to null.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'id');

        // Launch change of nullability for field name.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field course on table accredible to null.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'name');

        // Launch change of nullability for field course.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field achievementid on table accredible to null.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('achievementid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'course');

        // Launch change of nullability for field achievementid.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field description on table accredible to null.
        $table = new xmldb_table('accredible');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'achievementid');

        // Launch change of nullability for field description.
        $dbman->change_field_notnull($table, $field);

        // Accredible savepoint reached.
        upgrade_mod_savepoint(true, 2016111000, 'accredible');

    }

    return true;
}
