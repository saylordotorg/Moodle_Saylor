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
 * This file keeps track of upgrades to the minilesson module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_minilesson
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_minilesson\constants;

/**
 * Execute minilesson upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_minilesson_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes



    // Add question titles to minilesson table
    if ($oldversion < 2020090700) {
        $activitytable = new xmldb_table(constants::M_TABLE);


        // Define field showqtitles to be added to minilesson\
        $showqtitles= new xmldb_field('showqtitles', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // add showqtitles field to minilesson table
        if (!$dbman->field_exists($activitytable, $showqtitles)) {
            $dbman->add_field($activitytable, $showqtitles);
        }
        upgrade_mod_savepoint(true, 2020090700, 'minilesson');
    }

    // Add passagehash to questions table
    if ($oldversion < 2020100200) {
        $qtable = new xmldb_table(constants::M_QTABLE);


        // Define field showqtitles to be added to minilesson\
        $field = new xmldb_field('passagehash', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);

        // add showqtitles field to minilesson table
        if (!$dbman->field_exists($qtable, $field)) {
            $dbman->add_field($qtable, $field);
        }
        upgrade_mod_savepoint(true, 2020100200, 'minilesson');
    }

    // Add rich text prompt flag to minilesson table
    if ($oldversion < 2020122300) {
        $activitytable = new xmldb_table(constants::M_TABLE);


        // Define field richtextprompt to be added to minilesson
        $richtextprompt= new xmldb_field('richtextprompt', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::M_PROMPT_RICHTEXT);

        // add richtextprompt field to minilesson table
        if (!$dbman->field_exists($activitytable, $richtextprompt)) {
            $dbman->add_field($activitytable, $richtextprompt);
        }
        upgrade_mod_savepoint(true, 2020122300, 'minilesson');
    }

    // Add TTS item  to minilesson table
    if ($oldversion < 2021021800) {
        $table = new xmldb_table(constants::M_QTABLE);

        // Define fields itemtts and itemtts voice to be added to minilesson
        $fields=[];
        $fields[] = new xmldb_field('itemttsvoice', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED);
        $fields[] = new xmldb_field('itemtts', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2021021800, 'minilesson');
    }

    // Add TTS option to minilesson table
    if ($oldversion < 2021031500) {
        $table = new xmldb_table(constants::M_QTABLE);


        // Define field itemtts to be added to minilesson
        $itemttsoption= new xmldb_field('itemttsoption', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::TTS_NORMAL);

        // add richtextprompt field to minilesson table
        if (!$dbman->field_exists($table, $itemttsoption)) {
            $dbman->add_field($table, $itemttsoption);
        }
        upgrade_mod_savepoint(true, 2021031500, 'minilesson');
    }
    // Add Question TextArea item  to minilesson table
    if ($oldversion < 2021052200) {
        $table = new xmldb_table(constants::M_QTABLE);

        // Define fields itemtts and itemtts voice to be added to minilesson
        //Item text area was added in 2021041500, but it was missed from install.xml ... so we double check it in this version
        $fields=[];
        $fields[] = new xmldb_field('itemtextarea', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

       //Some passage hashs seem to be empty. This script will search for empty (and wrong) ones and update them
        $instances = $DB->get_records(constants::M_TABLE);
        if($instances){
            foreach ($instances as $moduleinstance){
                \mod_minilesson\local\rsquestion\helper::update_all_langmodels($moduleinstance);
            }
        }
        upgrade_mod_savepoint(true, 2021052200, 'minilesson');
    }

    // Add foriframe option to minilesson table
    if ($oldversion < 2021053100) {
        $table = new xmldb_table(constants::M_TABLE);


        // Define field itemtts to be added to minilesson
        $field= new xmldb_field('foriframe', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        // add richtextprompt field to minilesson table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021053100, 'minilesson');
    }

    // Add alternatives  to minilesson table
    if ($oldversion < 2021081801) {
        $table = new xmldb_table(constants::M_QTABLE);

        // Define alternatives field to be added to minilesson
        $fields=[];
        $fields[] = new xmldb_field('alternatives', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[] = new xmldb_field('phonetic', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2021081801, 'minilesson');
    }

    // Update all the phonetic fields in minilesson
    if ($oldversion < 2021082701) {

        //this will add phonetic info for speechy items that have none currently
        $instances = $DB->get_records(constants::M_TABLE);
        if($instances){
            foreach ($instances as $moduleinstance){
                \mod_minilesson\local\rsquestion\helper::update_all_phonetic($moduleinstance);
            }
        }

        upgrade_mod_savepoint(true, 2021082701, 'minilesson');
    }

    // Add TTS autoplay to minilesson q table
    if ($oldversion < 2022012001) {
        $table = new xmldb_table(constants::M_QTABLE);


        // Define fields itemttsautoplay and layout to be added to minilesson q table
        $fields=[];
        $fields[]= new xmldb_field('itemttsautoplay', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[]= new xmldb_field('layout', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, constants::LAYOUT_AUTO);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022012001, 'minilesson');
    }

    // Add YT Video Clip to minilesson question table
    if ($oldversion < 2022020300) {
        $table = new xmldb_table(constants::M_QTABLE);

        // Define YT clip fields to be added to minilesson
        $fields=[];
        $fields[] = new xmldb_field('itemytid', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[]= new xmldb_field('itemytstart', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[]= new xmldb_field('itemytend', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022020300, 'minilesson');
    }


 // Add open and close dates to the activity
    if ($oldversion < 2022020800) {
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

        upgrade_mod_savepoint(true, 2022020800, 'minilesson');
    }
    //redo the prompt/response =>
    if ($oldversion < 2022021400) {
        $questions = $DB->get_records(constants::M_QTABLE);
        foreach($questions as $question){
            $sentences = explode(PHP_EOL, $question->customtext1);
            $updaterequired=false;
            $newsentences=[];
            foreach($sentences as $sentence){
                $sentencebits = explode('|', $sentence);
                if (count($sentencebits) > 1) {
                    $updaterequired=true;
                    $audioprompt = trim($sentencebits[1]);
                    $correctresponse= trim($sentencebits[0]);
                    $textprompt = $correctresponse;
                    $newsentences[]=$audioprompt . '|' . $correctresponse .'|' . $textprompt;
                }else{
                    $newsentences[]=$sentence;
                }//end of if count
            }//end of for sentences
            if($updaterequired){
                $updatetext=implode(PHP_EOL,$newsentences);
                $DB->update_record(constants::M_QTABLE,array('id'=>$question->id,'customtext1'=>$updatetext));
            }
        }

        upgrade_mod_savepoint(true, 2022021400, 'minilesson');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
