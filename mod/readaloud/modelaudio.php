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
 * Model Audio for readaloud
 *
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

use \mod_readaloud\constants;
use \mod_readaloud\utils;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // readaloud instance ID
$action = optional_param('action', 'none', PARAM_TEXT);  // readaloud instance ID
$uploadaudio = optional_param('uploadaudio', 'false', PARAM_TEXT); //From model audio form (flags, to show upload)



if ($id) {
    $cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(0,'You must specify a course_module ID or an instance ID');
}

$PAGE->set_url(constants::M_URL . '/modelaudio.php',
        array('id' => $cm->id));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/readaloud:manage', $modulecontext);

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

//get token
$token = utils::fetch_token($config->apiuser,$config->apisecret);


// Trigger module viewed event.
$event = \mod_readaloud\event\course_module_viewed::create(array(
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot(constants::M_MODNAME, $moduleinstance);
$event->trigger();


//process form submission
switch ($action) {
    case 'modelaudiourl':
        $mform = new \mod_readaloud\modelaudioform();
        if ($mform->is_cancelled() || $uploadaudio!='false') {
            //both the cancel and "upload audio file" just fall through.
            // But the upload audio sets a param that is detected below to show the upload recorder
            break;
        } else {
            $data = $mform->get_data();
            $DB->update_record(constants::M_TABLE,
                    array('id' => $moduleinstance->id, 'modelaudiourl' => $data->modelaudiourl));

            //register our transcribe task
            if (utils::can_transcribe($moduleinstance)) {
                $success = utils::register_modelaudio_task($moduleinstance->id, $data->modelaudiourl, $modulecontext->id);
                if (!$success) {
                    $message = "Unable to create model audio adhoc task to fetch transcriptions";
                }
            } else {
                $success = true;
            }
            redirect($PAGE->url);
        }
        break;
    case 'modelaudiobreaks':
        $mform = new \mod_readaloud\modelaudiobreaksform();
        if ($mform->is_cancelled()) {
            break;
        } else {
            $data = $mform->get_data();
            $DB->update_record(constants::M_TABLE,
                    array('id' => $moduleinstance->id,
                            'modelaudiobreaks' => $data->modelaudiobreaks
                    ));
            redirect($PAGE->url);
        }
        break;
    case 'modelaudiobreaksgenerate':

        $ma_matches = json_decode($moduleinstance->modelaudiomatches);
        $breaks = utils::guess_modelaudio_breaks($moduleinstance->passage,$ma_matches,$moduleinstance->ttslanguage);
        $modelaudiobreaks = json_encode($breaks);
        $DB->update_record(constants::M_TABLE,
            array('id' => $moduleinstance->id, 'modelaudiobreaks' => $modelaudiobreaks));
        redirect($PAGE->url);

        break;

    case 'modelaudiobreaksclear':

          $DB->update_record(constants::M_TABLE,
                    array('id' => $moduleinstance->id, 'modelaudiobreaks' => ''));
          redirect($PAGE->url);

        break;
    case 'modelaudioclear':
            $havettsvoice = $moduleinstance->ttsvoice != constants::TTS_NONE;
            $matches=[];
            $breaks=[];
            if($havettsvoice) {
                $slowpassage = utils::fetch_speech_ssml($moduleinstance->passage, $moduleinstance->ttsspeed);
                $speechmarks = utils::fetch_polly_speechmarks($token, $moduleinstance->region,
                    $slowpassage, 'ssml', $moduleinstance->ttsvoice);
                $matches = utils::speechmarks_to_matches($moduleinstance->passagesegments, $speechmarks, $moduleinstance->ttslanguage);

                if (!empty($moduleinstance->modelaudiobreaks)) {
                    $breaks = utils::sync_modelaudio_breaks(json_decode($moduleinstance->modelaudiobreaks), $matches);
                } else {
                    $breaks = utils::guess_modelaudio_breaks($moduleinstance->passagesegments, $matches, $moduleinstance->ttslanguage);
                }
            }

            $DB->update_record(constants::M_TABLE, array('id' => $moduleinstance->id,
                            'modelaudiourl' =>'',
                            'modelaudiotrans' =>'',
                            'modelaudiofulltrans' =>'',
                            'modelaudiomatches' =>json_encode($matches),
                            'modelaudiobreaks'=>json_encode($breaks))
            );
            redirect($PAGE->url);

        break;
}

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}


//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$modelaudiorenderer = $PAGE->get_renderer(constants::M_COMPONENT, 'modelaudio');
$passagerenderer = $PAGE->get_renderer(constants::M_COMPONENT, 'passage');



//SENTENCE SPLITTER BY PAUL
/*$lines = explode("\n", $data['text']);
        $chunks = [];
        foreach ($lines as $line) {
            if (strpos($line, '"') === false) {
                $parts = preg_split("/([A-Z][^?!.]+[?.!])/", $line, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            } else {
                $parts = [$line];
            }
            foreach ($parts as $part) {
                $chunks[] = $part;
            }
        }

*/



//From here we actually display the page.
echo $renderer->header($moduleinstance, $cm,'modelaudio', null, get_string('modelaudio', constants::M_COMPONENT));


//show the recorder section
echo $modelaudiorenderer->render_sectiontop(
        get_string('modelaudio_recordtitle',constants::M_COMPONENT),
        get_string('modelaudio_recordinstructions',constants::M_COMPONENT));
echo $modelaudiorenderer->show_recorder($moduleinstance,$token,$uploadaudio!='false');
//show the recorder form
$setdata = array(
        'modelaudiourl'=>$moduleinstance->modelaudiourl,
        'action' => 'modelaudiourl',
        'n' => $moduleinstance->id);
$modelaudioform = new \mod_readaloud\modelaudioform(null, array());
$modelaudioform->set_data($setdata);
$modelaudioform->display();

echo "<hr>";

//show the player
echo $modelaudiorenderer->render_sectiontop(
        get_string('modelaudio_playertitle',constants::M_COMPONENT),
        get_string('modelaudio_playerinstructions',constants::M_COMPONENT));
if(empty($moduleinstance->modelaudiourl) || $moduleinstance->modelaudiourl=='none') {
    echo $modelaudiorenderer->render_polly_player($moduleinstance, $token);
}else{
    echo $modelaudiorenderer->render_modelaudio_player($moduleinstance,$token);
    echo $modelaudiorenderer->render_audio_clear_button($moduleinstance);

    //In debugging, we might want to view the recorded audio transcript.
    //echo $modelaudiorenderer->render_view_transcript_button();
    //echo $modelaudiorenderer->render_view_transcript();
}
echo "<hr>";

//show the clickable passage + breaks editor
echo $modelaudiorenderer->render_sectiontop(
        get_string('modelaudio_breakstitle',constants::M_COMPONENT),
        get_string('modelaudio_breaksinstructions',constants::M_COMPONENT));
echo $modelaudiorenderer->render_manualbreaktiming_checkbox();
echo $passagerenderer->render_passage($moduleinstance->passagesegments,$moduleinstance->ttslanguage);


//show the breaks form
$setdata = array(
        'modelaudiobreaks'=>$moduleinstance->modelaudiobreaks,
        'action' => 'modelaudiobreaks',
        'n' => $moduleinstance->id);
$modelaudiobreaksform = new \mod_readaloud\modelaudiobreaksform(null, array());
$modelaudiobreaksform->set_data($setdata);
$modelaudiobreaksform->display();

//clear breaks button
$clearbutton = $OUTPUT->single_button(new \moodle_url(constants::M_URL . '/modelaudio.php',
        array('n' => $moduleinstance->id, 'action' => 'modelaudiobreaksclear')), get_string('modelaudiobreaksclear', constants::M_COMPONENT),'get');
echo $clearbutton;
//auto-generate model audio breaks button
$clearbutton = $OUTPUT->single_button(new \moodle_url(constants::M_URL . '/modelaudio.php',
    array('n' => $moduleinstance->id, 'action' => 'modelaudiobreaksgenerate')), get_string('modelaudiobreaksgenerate', constants::M_COMPONENT),'get');
echo $clearbutton;

//set up the AMD js and related opts
$modelaudio_opts = Array();
$modelaudio_opts['recorderid']=constants::M_RECORDERID;
$modelaudio_opts['modelaudiobreaks']=$moduleinstance->modelaudiobreaks;
$modelaudio_opts['modelaudiomatches']=$moduleinstance->modelaudiomatches ? $moduleinstance->modelaudiomatches : false;

$jsonstring = json_encode($modelaudio_opts);
$widgetid = constants::M_RECORDERID . '_opts_9999';
$opts_html =
        \html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetid, 'type' => 'hidden', 'value' => $jsonstring));
$opts = array('widgetid' => $widgetid);

//this inits the model audio helper JS
$PAGE->requires->js_call_amd("mod_readaloud/modelaudiohelper", 'init', array($opts));
echo $opts_html;

// Finish the page
echo $renderer->footer();
return;
