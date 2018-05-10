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
 * Gapfill question type upgrade code.
 *
 * @package    qtype_gapfill
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the gapfill question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_gapfill_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2018050700) {
        if (!$dbman->field_exists('question_gapfill', 'noduplicates')) {
            $field = new xmldb_field('noduplicates', XMLDB_TYPE_INTEGER, '1');
            $table = new xmldb_table('question_gapfill');
            $dbman->add_field($table, $field);
        }

        if (!$dbman->field_exists('question_gapfill', 'disableregex')) {
            $field = new xmldb_field('disableregex', XMLDB_TYPE_INTEGER, '1');
            $table = new xmldb_table('question_gapfill');
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists('question_gapfill', 'fixedgapsize')) {
            $field = new xmldb_field('fixedgapsize', XMLDB_TYPE_INTEGER, '1');
            $table = new xmldb_table('question_gapfill');
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists('question_gapfill', 'optionsaftertext')) {
            $field = new xmldb_field('optionsaftertext', XMLDB_TYPE_INTEGER, '1', null, true, null, 0, 'fixedgapsize');
            $table = new xmldb_table('question_gapfill');
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists('question_gapfill', 'letterhints')) {
            $field = new xmldb_field('letterhints', XMLDB_TYPE_INTEGER, '1', null, true, null, 0, 'optionsaftertext');
            $table = new xmldb_table('question_gapfill');
            $dbman->add_field($table, $field);
        }
        if (!$dbman->table_exists('question_gapfill_settings')) {
            $table = new xmldb_table('question_gapfill_settings');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('question', XMLDB_TYPE_CHAR, '10', null, true, null, null);
            $table->add_field('itemid', XMLDB_TYPE_CHAR, '10', null, true, null, null);
            $table->add_field('gaptext', XMLDB_TYPE_CHAR, '255', null, true, null, null);
            $table->add_field('correctfeedback', XMLDB_TYPE_TEXT, '512', null, true, null, null);
            $table->add_field('incorrectfeedback', XMLDB_TYPE_TEXT, '512', null, true, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);
        }
        // Gapfill savepoint reached.
        upgrade_plugin_savepoint(true, 2018050700, 'qtype', 'gapfill');
    }
}
