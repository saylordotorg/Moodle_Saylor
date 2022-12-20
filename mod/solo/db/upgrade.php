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
 * This file keeps track of upgrades to the solo module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;

/**
 * Execute solo upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_solo_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2019092400){
        $table = new xmldb_table(constants::M_AITABLE);

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('transcript', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('passage', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('jsontranscript', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('wpm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('accuracy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessionscore', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessiontime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessionerrors', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('sessionmatches', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('sessionendword', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('errorcount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table solo ai result.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for solo ai resiult.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2019092400, 'solo');
    }


    if ($oldversion < 2019100500) {
        $table = new xmldb_table(constants::M_STATSTABLE);
        $field =  new xmldb_field('aiaccuracy', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2019100500, 'solo');
    }

    if ($oldversion < 2019120900) {
        $table = new xmldb_table(constants::M_TABLE);
        $field =  new xmldb_field('postattemptedit', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2019120900, 'solo');
    }


    if ($oldversion < 2020061615) {
        // Define field feedback to be added to solo_attempts.
        $table = new xmldb_table(constants::M_ATTEMPTSTABLE);
        $field = new xmldb_field('feedback', XMLDB_TYPE_TEXT, null, null, null, null, null, 'completedsteps');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // solo savepoint reached.
        upgrade_mod_savepoint(true, 2020061615, 'solo');
    }

    if ($oldversion < 2020071501) {
        $table = new xmldb_table(constants::M_ATTEMPTSTABLE);
        $field =  new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2020071501, 'solo');
    }

    if ($oldversion < 2020082500) {
        $table = new xmldb_table(constants::M_TABLE);
        $field =  new xmldb_field('completionallsteps', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2020082500, 'solo');
    }

    if ($oldversion < 2021011001) {
        $table = new xmldb_table(constants::M_TABLE);
        $field =  new xmldb_field('gradewordgoal', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 200);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2021011001, 'solo');
    }

    // Add TTS topic  to solo table
    if ($oldversion < 2021022200) {
        $table = new xmldb_table(constants::M_TABLE);

        // Define fields itemtts and itemtts voice to be added to minilesson
        $fields=[];
        $fields[] = new xmldb_field('topicttsvoice', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED);
        $fields[] = new xmldb_field('topictts', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2021022200, 'solo');
    }

    // Add foriframe option to solo table
    if ($oldversion < 2021053100) {
        $table = new xmldb_table(constants::M_TABLE);


        // Define field foriframe to be added to solo
        $field= new xmldb_field('foriframe', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        // add richtextprompt field to minilesson table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021053100, 'solo');
    }

// Add open and close dates to the activity
    if ($oldversion < 2022020200) {
        $table = new xmldb_table(constants::M_TABLE);

        $fields=[];
        $fields[] = new xmldb_field('viewstart', XMLDB_TYPE_INTEGER, 10, XMLDB_NOTNULL, null, 0);
        $fields[] = new xmldb_field('viewend', XMLDB_TYPE_INTEGER, 10, XMLDB_NOTNULL, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022020200, 'solo');
    }

    // Add model answer and ytclip fields to solo table
    if ($oldversion < 2022020500) {
        $table = new xmldb_table(constants::M_TABLE);

        // Define YT clip /TTS voice  to be added to solo as topic and model
        $fields=[];
        $fields[]= new xmldb_field('topicytid', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[]= new xmldb_field('topicytstart', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[]= new xmldb_field('topicytend', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[]= new xmldb_field('modelttsvoice', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED);
        $fields[]= new xmldb_field('modeltts', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[]= new xmldb_field('modeliframe', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[]= new xmldb_field('modelytid', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[]= new xmldb_field('modelytstart', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[]= new xmldb_field('modelytend', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022020500, 'solo');
    }

    if ($oldversion < 2022021400) {
        $table = new xmldb_table(constants::M_ATTEMPTSTABLE);
        $field =   new xmldb_field('grammarcorrection', XMLDB_TYPE_TEXT, null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2022021400, 'solo');
    }

    // Add model answer and ytclip fields to solo table
    if ($oldversion < 2022033100) {
        $table = new xmldb_table(constants::M_TABLE);

        // Define STEPS fields to be added to Solo
        $fields=[];

        $fields[]= new xmldb_field('step2', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::M_STEP_RECORD);
        $fields[]= new xmldb_field('step3', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::M_STEP_TRANSCRIBE);
        $fields[]= new xmldb_field('step4', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::M_STEP_MODEL);
        $fields[]= new xmldb_field('step5', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::M_STEP_NONE);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        //set the value of those steps
        //set all transcriber to "guided" (before was 1) chrome:strict stt:guided or 2) stt:guided - ie all mixed up
        $DB->set_field(constants::M_TABLE,'step2',constants::M_STEP_RECORD);
        $DB->set_field(constants::M_TABLE,'step3',constants::M_STEP_TRANSCRIBE);
        $DB->set_field(constants::M_TABLE,'step4',constants::M_STEP_MODEL);
        $DB->set_field(constants::M_TABLE,'step5',constants::M_STEP_NONE);

        upgrade_mod_savepoint(true, 2022033100, 'solo');
    }

    //add missing defaults on solo
    if ($oldversion < 2022060500) {
        $table = new xmldb_table(constants::M_TABLE);



        $vfields=[];
        $vfields[] = new xmldb_field('viewstart', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,null, null, 0);
        $vfields[] = new xmldb_field('viewend', XMLDB_TYPE_INTEGER, 10,XMLDB_UNSIGNED, null, null, 0);

        // Add fields
        foreach ($vfields as $field) {
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2022060500, 'solo');
    }

    if ($oldversion < 2022061000) {
        $table = new xmldb_table(constants::M_TABLE);

        $fields=[];
        $fields[]= new xmldb_field('enablesuggestions', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED,null, null, 1);
        $fields[]= new xmldb_field('enabletts', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED,null, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022061000, 'solo');
    }

    if ($oldversion < 2022110500) {
        $table = new xmldb_table(constants::M_STATSTABLE);
        $fields=[];
        $fields[] =  new xmldb_field('cefrlevel', XMLDB_TYPE_CHAR, '4', XMLDB_UNSIGNED);
        $fields[] =  new xmldb_field('ideacount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2022110500, 'solo');
    }

    if ($oldversion < 2022121700) {
        //add relevance field to stats
        $table_s = new xmldb_table(constants::M_STATSTABLE);
        $field_s =  new xmldb_field('relevance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null);
        // Add fields
        if (!$dbman->field_exists($table_s, $field_s)) {
            $dbman->add_field($table_s, $field_s);
        }

        //add model tts embedding and idea count fields to main table
        $table_t = new xmldb_table(constants::M_TABLE);
        $fields_t=[];
        $fields_t[]= new xmldb_field('modelttsembedding', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields_t[] =  new xmldb_field('modelttsideacount', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null);
        // Add fields
        foreach ($fields_t as $field_t) {
            if (!$dbman->field_exists($table_t, $field_t)) {
                $dbman->add_field($table_t, $field_t);
            }
        }
        upgrade_mod_savepoint(true, 2022121700, 'solo');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
