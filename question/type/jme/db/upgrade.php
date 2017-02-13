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
 * JME question type upgrade code.
 *
 * @package    qtype_jme
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the jme question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_jme_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Moodle v2.4.0 release upgrade line
    // Put any upgrade step following this.

    if ($oldversion < 2013011800) {

        // Define table question_jme to be dropped from question_jme.
        $table = new xmldb_table('question_jme');

        // Conditionally launch drop field answers.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Jme savepoint reached.
        upgrade_plugin_savepoint(true, 2013011800, 'qtype', 'jme');
    }

    // Moodle v2.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2013070100) {
         // Define table qtype_jme_options to be created.
        $table = new xmldb_table('qtype_jme_options');

        // Adding fields to table qtype_jme_options.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('jmeoptions', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
        $table->add_field('width', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '420');
        $table->add_field('height', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '315');

        // Adding keys to table qtype_jme_options.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN_UNIQUE, array('questionid'), 'question', array('id'));

        // Conditionally launch create table for qtype_jme_options.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Jme savepoint reached.
        upgrade_plugin_savepoint(true, 2013070100, 'qtype', 'jme');
    }

    if ($oldversion < 2013070102) {
        // Insert a row into the qtype_jme_options table for each existing jme question.
        $DB->execute("
                INSERT INTO {qtype_jme_options} (questionid, jmeoptions, width, height)
                SELECT q.id, '" . $CFG->qtype_jme_options . "', 420, 315
                FROM {question} q
                WHERE q.qtype = 'jme'
                AND NOT EXISTS (
                    SELECT 'x'
                    FROM {qtype_jme_options} qeo
                    WHERE qeo.questionid = q.id)");

        // Jme savepoint reached.
        upgrade_plugin_savepoint(true, 2013070102, 'qtype', 'jme');
    }

    if ($oldversion < 2014080800) {
        // Changing the default of field width on table qtype_jme_options to 360.
        $table = new xmldb_table('qtype_jme_options');
        $field = new xmldb_field('width', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '360');

        // Launch change of default for field width.
        $dbman->change_field_default($table, $field);

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2014080800, 'qtype', 'jme');
    }

    if ($oldversion < 2015050600) {
        // Insert a row into the qtype_jme_options table for each existing jme question.
        // This is done to correct broken jme questions after a restore from old version
        $DB->execute("
                INSERT INTO {qtype_jme_options} (questionid, jmeoptions, width, height)
                SELECT q.id, '" . $CFG->qtype_jme_options . "', 360, 315
                FROM {question} q
                WHERE q.qtype = 'jme'
                AND NOT EXISTS (
                    SELECT 'x'
                    FROM {qtype_jme_options} qeo
                    WHERE qeo.questionid = q.id)");


        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2015050600, 'qtype', 'jme');
    }
    return true;
}
