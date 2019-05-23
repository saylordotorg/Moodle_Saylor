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
 * Algebra question type upgrade code.
 *
 * @package    qtype_algebra
 * @copyright  Roger Moore
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_algebra_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();

    // Add the field to store the string which is placed in front of the answer
    // box when the question is displayed.
    if ($oldversion < 2008061500) {
        $table = new xmldb_table('question_algebra');
        $field = new xmldb_field('answerprefix', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, '', 'allowedfuncs');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2008061500, 'qtype', 'algebra');
    }

    // Drop the answers and variables fields wich are totally redundant.
    if ($oldversion < 2011072800) {
        $table = new xmldb_table('question_algebra');
        $field = new xmldb_field('answers');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('variables');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2011072800, 'qtype', 'algebra');
    }

    // Change tables names according to new standards for plugins.
    if ($oldversion < 2012061701) {
        // Renaming old tables.
        $table = new xmldb_table('question_algebra');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'qtype_algebra');
        }
        $table = new xmldb_table('question_algebra_variables');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'qtype_algebra_variables');
        }
        upgrade_plugin_savepoint(true, 2012061701, 'qtype', 'algebra');
    }

    // Change table name one more time.
    if ($oldversion < 2012061702) {
        // Renaming old table.
        $table = new xmldb_table('qtype_algebra');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'qtype_algebra_options');
        }
        upgrade_plugin_savepoint(true, 2012061702, 'qtype', 'algebra');
    }

    if ($oldversion < 2019042705) {

        // Define key question (foreign) to be dropped form qtype_algebra_variables.
        $table = new xmldb_table('qtype_algebra_variables');
        $key = new xmldb_key('question', XMLDB_KEY_FOREIGN, array('question'), 'question', array('id'));

        // Launch drop key question.
        $dbman->drop_key($table, $key);

        // Record that qtype_algebra savepoint was reached.
        upgrade_plugin_savepoint(true, 2019042705, 'qtype', 'algebra');
    }

    if ($oldversion < 2019042706) {

        // Rename field question on table qtype_algebra_variables to questionid.
        $table = new xmldb_table('qtype_algebra_variables');
        $field = new xmldb_field('question', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        // Launch rename field question.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'questionid');
        }

        // Record that qtype_algebra savepoint was reached.
        upgrade_plugin_savepoint(true, 2019042706, 'qtype', 'algebra');
    }

    if ($oldversion < 2019042900) {

        // Define key questionid (foreign) to be added to qtype_algebra_variables.
        $table = new xmldb_table('qtype_algebra_variables');
        $key = new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', array('id'));

        // Launch add key questionid.
        $dbman->add_key($table, $key);

        // Record that qtype_algebra savepoint was reached.
        upgrade_plugin_savepoint(true, 2019042900, 'qtype', 'algebra');
    }

    return true;
}

