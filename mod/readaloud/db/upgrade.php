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
 * This file keeps track of upgrades to the readaloud module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\utils;
use \mod_readaloud\constants;

/**
 * Execute readaloud upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_readaloud_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // Add allowearlyexit field
    if ($oldversion < 2015071501) {

        // Define field introformat to be added to readaloud
        $table = new xmldb_table('readaloud');
        $field = new xmldb_field('allowearlyexit', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2015071501, 'readaloud');
    }

    // Add allowearlyexit field
    if ($oldversion < 2015071502) {

        // Define field grade to be added to readaloud
        $table = new xmldb_table('readaloud');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2015071502, 'readaloud');
    }

    // Add wcpm field
    if ($oldversion < 2015072201) {

        // Define field wpcm to be added to readaloud_attempt
        $table = new xmldb_table('readaloud_attempt');
        $field = new xmldb_field('wpm', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2015072201, 'readaloud');
    }

    // Add accuracy and targetwpm fields
    if ($oldversion < 2015072701) {

        // Define field wpcm to be added to readaloud_attempt
        $table = new xmldb_table('readaloud_attempt');
        $field = new xmldb_field('accuracy', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field wpcm to be added to readaloud_attempt
        $table = new xmldb_table('readaloud');
        $field = new xmldb_field('targetwpm', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '100');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2015072701, 'readaloud');
    }
    // Rename fedbackformat to feedbackformat
    if ($oldversion < 2016022102) {

        // Define field wpcm to be added to readaloud_attempt
        $table = new xmldb_table('readaloud');
        $field = new xmldb_field('fedbackformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Rename field to feedbackformat
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'feedbackformat');
        }

        upgrade_mod_savepoint(true, 2016022102, 'readaloud');
    }

    if ($oldversion < 2018060900) {
        $table = new xmldb_table('readaloud_ai_result');

        // Adding fields to table tool_dataprivacy_contextlist.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('readaloudid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('transcript', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('fulltranscript', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('wpm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('accuracy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessionscore', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessiontime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessionerrors', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('sessionendword', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table tool_dataprivacy_contextlist.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tool_dataprivacy_contextlist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('readaloud');
        $field = new xmldb_field('enableai', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2018060900, 'readaloud');
    }

    // Add expiredays and region to readaloud table
    if ($oldversion < 2018060902) {
        $table = new xmldb_table('readaloud');

        // Define field expiredays to be added to readaloud
        $field = new xmldb_field('expiredays', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '365');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field expiredays to be added to readaloud
        $field = new xmldb_field('region', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 'useast1');
        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018060902, 'readaloud');
    }

    // Add accadjust to readaloud table
    if ($oldversion < 2018071300) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('accadjust', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018071300, 'readaloud');
    }

    // Add session matches to databaase
    if ($oldversion < 2018073101) {
        $table = new xmldb_table('readaloud_ai_result');

        //records the matched words in the passage and their transcript location. For debugging, passage tweaking, and audio location
        $field = new xmldb_field('sessionmatches', XMLDB_TYPE_TEXT, null, null, null, null);

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018073101, 'readaloud');
    }

    // Add accadjustmethod to readaloud table
    if ($oldversion < 2018082400) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('accadjustmethod', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018082400, 'readaloud');
    }

    // Add accadjustmethod to readaloud table
    if ($oldversion < 2018082402) {
        $table = new xmldb_table('readaloud_ai_result');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('errorcount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to AI table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //add field to attempts table
        $table = new xmldb_table('readaloud_attempt');
        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018082402, 'readaloud');
    }
    // Add humanpostattempt and machinepostattempt to readaloud table
    if ($oldversion < 2018082403) {
        $table = new xmldb_table('readaloud');

        //This adds the post attempt display options for each of the evaluation methods (machine and human)
        $field1 = new xmldb_field('humanpostattempt', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '2');
        $field2 = new xmldb_field('machinepostattempt', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // add fields to readaloud table
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_mod_savepoint(true, 2018082403, 'readaloud');
    }
    // Add "alternatives" to readaloud table
    if ($oldversion < 2018082404) {
        $table = new xmldb_table('readaloud');

        //This adds the post attempt display options for each of the evaluation methods (machine and human)
        $field = new xmldb_field('alternatives', XMLDB_TYPE_TEXT, null, null, null, null);

        // add fields to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018082404, 'readaloud');
    }

    // Add accadjustmethod to readaloud table
    if ($oldversion < 2018090700) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('machgrademethod', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018090700, 'readaloud');
    }

    // Add accadjustmethod to readaloud table
    if ($oldversion < 2018101200) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('activitylink', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018101200, 'readaloud');
    }

    // Add transcriber to readaloud table
    if ($oldversion < 2019052000) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to compensate for a certain no. of errors to compensate for machine transcription errors
        $field = new xmldb_field('transcriber', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2019052000, 'readaloud');
    }

    // Add submitrawaudio to readaloud table
    if ($oldversion < 2019101400) {
        $table = new xmldb_table('readaloud');

        //This allows the activity admin to submit raw audio (as opposed to recording). Usually for some sort of disaster recovery
        $field = new xmldb_field('submitrawaudio', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2019101400, 'readaloud');
    }

    // Add sessionscoremethod to readaloud table.
    if ($oldversion < 2020021800) {
        $table = new xmldb_table('readaloud');

        //
        $field = new xmldb_field('sessionscoremethod', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020021800, 'readaloud');
    }

    // Add sessionscoremethod to readaloud table.
    if ($oldversion < 2020022000) {
        $table = new xmldb_table('readaloud');

        $fields=array();
        //Model Audio URL
        $fields[] = new xmldb_field('modelaudiourl', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, null, null, null);
        //Model audio breaks (JSON)
        $fields[] = new xmldb_field('modelaudiobreaks', XMLDB_TYPE_TEXT, null, null, null, null);
        //TTS Voice
        $fields[] = new xmldb_field('ttsvoice', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 'Amy');
        //Enable preview mode
        $fields[] = new xmldb_field('enablepreview', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        //Enable shadow mode
        $fields[] = new xmldb_field('enableshadow', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add fields to readaloud table
        foreach($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2020022000, 'readaloud');
    }

    // Add TTS Speed to readaloud table.
    if ($oldversion < 2020022900) {
        $table = new xmldb_table('readaloud');

        //
        $field = new xmldb_field('ttsspeed', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020022900, 'readaloud');
    }

    // Add recorder to readaloud table
    if ($oldversion < 2020060100) {
        $table = new xmldb_table('readaloud');

        // Define field recorder to be added to readaloud
        $field = new xmldb_field('recorder', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 'readaloud');
        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020060100, 'readaloud');
    }

    // Add passage hashcode to readaloud table
    if ($oldversion < 2020062800) {
        $table = new xmldb_table('readaloud');

        // Define field expiredays to be added to readaloud
        $field = new xmldb_field('passagehash', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, null, null);
        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020062800, 'readaloud');
    }

    // Add enable listen and repeat to readaloud table
    if ($oldversion < 2020070500) {
        $table = new xmldb_table('readaloud');

        // Define field enablelandr to be added to readaloud
        $field = new xmldb_field('enablelandr', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020070500, 'readaloud');
    }

    // Make sure language models are saved on langservices server.
    if ($oldversion < 2020111700) {
        $mods = $DB->get_records('readaloud',array());
        foreach ($mods as $themod) {
            utils::fetch_lang_model($themod->passage,$themod->ttslanguage,$themod->region);
        }
        upgrade_mod_savepoint(true, 2020111700, 'readaloud');
    }

    // Add stricttranscribe to readaloud table
    if ($oldversion < 2020121400) {
        $table = new xmldb_table('readaloud');

        //This forces the activity to use AWS transcription (not Poodll transcription). It results in slower but stricter transcription, ie more errors
        $field = new xmldb_field('stricttranscribe', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // add field to readaloud table
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2020121400, 'readaloud');
    }

    if($oldversion<2021032600){

        $table = new xmldb_table('readaloud');

        // Adding fields to table tool_dataprivacy_contextlist.
        $fields=array();
        $fields[] = new xmldb_field('modelaudiotrans', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[] = new xmldb_field('modelaudiofulltrans', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[] = new xmldb_field('modelaudiomatches', XMLDB_TYPE_TEXT, null, null, null, null);

        // add fields to readaloud table
        foreach($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2021032600, 'readaloud');

    }

    if($oldversion<2021032902){
        //loop through records looking for newly available neural voices and generate speechmarks and breaks
        //neural voices will be available right away, but the timing of words in the speech will change. So we need to do this
        //so we regenerate when required to do so
        $config = get_config(constants::M_COMPONENT);
        $token = utils::fetch_token($config->apiuser,$config->apisecret);
        $readalouds=$DB->get_records(constants::M_TABLE);

        foreach($readalouds as $readaloud){
            if($token && !empty($readaloud->passage)
                    && !empty($readaloud->modelaudiobreaks)
                    && empty($readaloud->modelaudiourl)
            ){
                //if it is not a neural voice, there is no need to resync
                if(!in_array($readaloud->ttsvoice,constants::M_NEURALVOICES)){continue;}

                //fetch SSML , speechmarks
                $slowpassage = utils::fetch_speech_ssml($readaloud->passage, $readaloud->ttsspeed);
                $speechmarks = utils::fetch_polly_speechmarks($token, $readaloud->region,
                        $slowpassage, 'ssml', $readaloud->ttsvoice);

                //if successful create a set of 'matches' (internal doc matching audio/passage/transcript positions)
                if($speechmarks) {
                    $matches = utils::speechmarks_to_matches($readaloud->passage,$speechmarks,$readaloud->ttslanguage);
                    //from matches create or sync an existing phrase breaks array with audio/word locations
                    if(!empty($readaloud->modelaudiobreaks)){
                        $breaks = utils::sync_modelaudio_breaks(json_decode($readaloud->modelaudiobreaks,true),$matches);
                    }else {
                        $breaks = utils::guess_modelaudio_breaks($readaloud->passage, $matches,$readaloud->ttslanguage);
                    }
                    //save it
                    $updatereadaloud = new stdClass();
                    $updatereadaloud->id = $readaloud->id;
                    $updatereadaloud->modelaudiomatches = json_encode($matches);
                    $updatereadaloud->modelaudiobreaks = json_encode($breaks);
                    $DB->update_record(constants::M_TABLE,$updatereadaloud);
                } //end of if speechmarks
            } //end of if should regenerate speechmarks/breaks
        } //end of for each

        upgrade_mod_savepoint(true, 2021032902, 'readaloud');
    }

    //a bug means that some attempts might not have been graded
    if($oldversion <2021033000){
        if($oldversion>=2021032900){
            $sql = "SELECT * from {" . constants::M_AITABLE . "} WHERE timecreated < 1617092420 AND timecreated > 1616943600 ";
            $ai_evals = $DB->get_records_sql($sql);
            if ($ai_evals) {
                foreach ($ai_evals as $eval) {
                    $thecm = get_coursemodule_from_instance(constants::M_TABLE, $eval->readaloudid, $eval->courseid, false);
                    if($thecm) {
                        $modulecontext =context_module::instance($thecm->id);
                        $aigrade = new \mod_readaloud\aigrade($eval->attemptid, $modulecontext->id);
                        if ($aigrade->has_transcripts()) {
                            $aigrade->do_diff();
                        }
                    }
                }
            }
        }
        upgrade_mod_savepoint(true, 2021033000, 'readaloud');
    }

    // Add foriframe option to readaloud table
    if ($oldversion < 2021053100) {
        $table = new xmldb_table(constants::M_TABLE);


        // Define field forframe to be added to readaloud
        $field= new xmldb_field('foriframe', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021053100, 'readaloud');
    }


    if ($oldversion < 2021061100) {
        $table = new xmldb_table('readaloud_rsquestions');

        // Adding fields to table rsquestions
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('readaloudid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, null, null);
        $table->add_field('itemorder', XMLDB_TYPE_INTEGER, '10', null, false, null, '0');
        $table->add_field('type', XMLDB_TYPE_CHAR, '20', XMLDB_UNSIGNED, null, null);
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '2', null, false, null, '1');
        $table->add_field('itemtext', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('itemtextformat', XMLDB_TYPE_INTEGER, '2', null, false, null, '0');
        $table->add_field('itemaudiofname', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('customtext1', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('customtext1format', XMLDB_TYPE_INTEGER, '2', null, false, null);
        $table->add_field('customtext2', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('customtext2format', XMLDB_TYPE_INTEGER, '2', null, false, null);
        $table->add_field('customtext3', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('customtext3format', XMLDB_TYPE_INTEGER, '2', null, false, null);
        $table->add_field('customtext4', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('customtext4format', XMLDB_TYPE_INTEGER, '2', null, false, null);
        $table->add_field('correctanswer', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rsquestionkey', XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null);

        // Adding keys to table rsquestions
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tool_dataprivacy_contextlist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2021061100, 'readaloud');
    }

    // Add Phonetic  to ReadAloud table
    if ($oldversion < 2021090100) {
        $table = new xmldb_table(constants::M_TABLE);

        $fields=[];
        $fields[] = new xmldb_field('phonetic', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2021090100, 'readaloud');
    }

    // Add Passage Segments  to ReadAloud table
    if ($oldversion < 2021090300) {
        $table = new xmldb_table(constants::M_TABLE);

        // Define field to be added to readaloud
        $fields=[];
        $fields[] = new xmldb_field('passagesegments', XMLDB_TYPE_TEXT, null, null, null, null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        //create a phonetic for each readaloud instance
        //this would blow up a site with lots of readalouds because of the time taken. So we added a place to do this on activity load in view.php
        //utils::update_all_phonetic_segments();

        $table = new xmldb_table(constants::M_TABLE);

        $field= new xmldb_field('masterinstance', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021093000, 'readaloud');
    }

    if($oldversion < 2021112100){


        $table = new xmldb_table(constants::M_USERTABLE);

        $field= new xmldb_field('dontgrade', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021112100, 'readaloud');
    }

    // Add open and close dates to the activity
    if ($oldversion < 2022020100) {
        $table = new xmldb_table(constants::M_TABLE);

        $fields=[];
        $fields[] = new xmldb_field('viewstart', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,XMLDB_NOTNULL, null, 0);
        $fields[] = new xmldb_field('viewend', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,XMLDB_NOTNULL, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022020100, 'readaloud');
    }

    // Add defaults to the viewstart and viewend fields (if they were not already set)
    if ($oldversion < 2022040200) {
        $table = new xmldb_table(constants::M_TABLE);

        $fields=[];
        $fields[] = new xmldb_field('viewstart', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,null, null, 0);
        $fields[] = new xmldb_field('viewend', XMLDB_TYPE_INTEGER, 10,XMLDB_UNSIGNED, null, null, 0);

        // Add fields
        foreach ($fields as $field) {
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2022040200, 'readaloud');
    }

    if ($oldversion < 2022041605){
        $table = new xmldb_table(constants::M_AITABLE);

        // Adding fields to ai results table.
        $fields= array();
        $fields[] = new xmldb_field('selfcorrections', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[] = new xmldb_field('sccount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Add fields
        foreach($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table(constants::M_USERTABLE);

        // Adding fields to attempt table.
        $fields= array();
        $fields[] = new xmldb_field('selfcorrections', XMLDB_TYPE_TEXT, null, null, null, null);
        $fields[] = new xmldb_field('sccount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $fields[]=  new xmldb_field('qscore', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0);
        $fields[] = new xmldb_field('qdetails', XMLDB_TYPE_TEXT, 8192, null, null, null);
        // Add fields
        foreach($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table(constants::M_TABLE);
        $fields=[];
        $fields[] = new xmldb_field('usecorpus', XMLDB_TYPE_INTEGER, 2, null,XMLDB_NOTNULL, null, constants::GUIDEDTRANS_PASSAGE);
        $fields[] =new xmldb_field('corpushash', XMLDB_TYPE_CHAR, '255', null, null, null );
        $fields[] =new xmldb_field('passagekey', XMLDB_TYPE_CHAR, '255', null, null, null );
        $fields[]=  new xmldb_field('corpusrange', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, constants::CORPUSRANGE_SITE);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        //set all transcriber to "guided" (before was 1) chrome:strict stt:guided or 2) stt:guided - ie all mixed up
        $DB->set_field(constants::M_TABLE,'transcriber',constants::TRANSCRIBER_GUIDED);
        set_config('transcriber', constants::TRANSCRIBER_GUIDED,constants::M_COMPONENT).

        upgrade_mod_savepoint(true, 2022041605, 'readaloud');
    }

    // Add customfont  to readaloud table
    if ($oldversion < 2022053100) {
        $table = new xmldb_table(constants::M_TABLE);

        // Define fields customfont,to be added to readaloud
        $fields=[];
        $fields[] = new xmldb_field('customfont', XMLDB_TYPE_CHAR, '255', null, null, null,null);

        // Add fields
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2022053100, 'readaloud');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
