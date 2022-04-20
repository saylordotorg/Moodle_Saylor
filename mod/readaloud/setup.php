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
 * Setup Tab for Poodll readaloud
 *
 * @package    mod_readaloud
 * @copyright  2020 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/readaloud/mod_form.php');

use mod_readaloud\constants;
use mod_readaloud\utils;


global $DB;


// Course module ID.
$id = optional_param('id',0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // readaloud instance ID

// Course and course module data.
if ($id) {
    $cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_MODNAME, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
    $id = $cm->id;
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$modulecontext = context_module::instance($cm->id);
require_capability('mod/readaloud:manage', $modulecontext);

// Set page login data.
$PAGE->set_url(constants::M_URL . '/setup.php',array('id'=>$id));
require_login($course, true, $cm);


// Set page meta data.
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('popup');




// Render template and display page.
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);

$mform = new \mod_readaloud\setupform(null,['context'=>$modulecontext, 'cmid'=>$cm->id]);

$redirecturl = new moodle_url('/mod/readaloud/view.php', array('id'=>$cm->id));
//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}else if ($data = $mform->get_data()) {
    $data->timemodified = time();
    $data->id = $data->n;
    $data->coursemodule = $cm->id;
    $data = readaloud_process_editors($data);


    //we want to process the hashcode and lang model if it makes sense
    $oldrecord = $DB->get_record(constants::M_TABLE,array('id'=>$data->id));
    $data->passagehash = $oldrecord->passagehash;
    $newpassagehash = utils::fetch_passagehash($data->passage,$data->ttslanguage);
    if(utils::needs_lang_model($data)){
        if($newpassagehash){
            //check if it has changed, if not do not waste time processing it
            if($oldrecord->passagehash!= ($data->region . '|' . $newpassagehash)) {
                //build a lang model
                $ret = utils::fetch_lang_model($data->passage, $data->ttslanguage, $data->region);
                if ($ret && isset($ret->success) && $ret->success)  {
                    $data->passagehash = $data->region . '|' . $newpassagehash;
                }
            }
        }
    }

    //update the phonetic if it has changed
    list($thephonetic,$thepassagesegments) = utils::update_create_phonetic_segments($data,$oldrecord);
    $data->phonetic = $thephonetic;
    $data->passagesegments = $thepassagesegments;

    //we want to create a polly record and speechmarks, if (!human_modelaudio && passage) && (passage change || voice change || speed change)
    $needspeechmarks =false;
    $havettsvoice = $data->ttsvoice != constants::TTS_NONE;
    if(empty($data->modelaudiourl) && !empty($data->passage) && $newpassagehash && $havettsvoice){
        //if it has changed OR voice has changed we need to do some work
        if($oldrecord->passagehash!= ($data->region . '|' . $newpassagehash) ||
                $oldrecord->ttsvoice != $data->ttsvoice ||
                $oldrecord->ttsspeed != $data->ttsspeed
        ) {
            $needspeechmarks = true;
        }
    }

    //We create the marked up speechmarks. We do not save the modelurl, we only save that in the case of human model audio
    if($needspeechmarks) {
        $config = get_config(constants::M_COMPONENT);
        $token = utils::fetch_token($config->apiuser,$config->apisecret);
        if($token) {
            $slowpassage = utils::fetch_speech_ssml($data->passage, $data->ttsspeed);
            $speechmarks = utils::fetch_polly_speechmarks($token, $data->region,
                    $slowpassage, 'ssml', $data->ttsvoice);
            if($speechmarks) {
                $matches = utils::speechmarks_to_matches($data->passagesegments,$speechmarks, $data->ttslanguage);
                if(!empty($oldrecord->modelaudiobreaks)){
                    $breaks = utils::sync_modelaudio_breaks(json_decode($oldrecord->modelaudiobreaks,true),$matches);
                }else {
                    $breaks = utils::guess_modelaudio_breaks($data->passagesegments, $matches,$data->ttslanguage);
                }
                $data->modelaudiomatches = json_encode($matches);
                $data->modelaudiobreaks = json_encode($breaks);
            } //end of if speechmarks
        } //end of if token
    }

    //now update the db once we have saved files and stuff
    if ($DB->update_record(constants::M_TABLE, $data)) {
        // readaloud_grade_item_update($moduleinstance);
        redirect($redirecturl);
        exit;
    }
}

//if we got here we is loading up dat form
$moduleinstance = utils::prepare_file_and_json_stuff($moduleinstance,$modulecontext);

$moduleinstance->n =$moduleinstance->id;
$mform->set_data((array)$moduleinstance);

echo $renderer->header($moduleinstance, $cm, "setup");
$mform->display();
echo $renderer->footer();
