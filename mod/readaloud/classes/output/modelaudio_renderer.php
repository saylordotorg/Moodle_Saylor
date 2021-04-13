<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_readaloud\output;

use \mod_readaloud\utils;
use \mod_readaloud\constants;

class modelaudio_renderer extends \plugin_renderer_base {

    public function render_sectiontop($title,$instructions){
        $heading = $this->output->heading($title, 4);
        $body = \html_writer::div($instructions, 'modelaudio_instructions');

        return $heading . $body;
    }

    public function render_manualbreaktiming_checkbox(){
        $manualcheckbox= \html_writer::checkbox('manualbreaktiming',0,false,
                get_string('manualbreaktiming',constants::M_COMPONENT),
                array('class'=>constants::M_COMPONENT . '_manualbreaktiming'));
        return $manualcheckbox;
     }

    public function render_polly_url($moduleinstance,$token) {
        $slowpassage = utils::fetch_speech_ssml($moduleinstance->passage,$moduleinstance->ttsspeed);
        $ret = utils::fetch_polly_url($token,$moduleinstance->region,
                $slowpassage,'ssml',$moduleinstance->ttsvoice);
        return $ret;
    }

    public function render_polly_player($moduleinstance,$token) {
        $slowpassage = utils::fetch_speech_ssml($moduleinstance->passage,$moduleinstance->ttsspeed);
        $url = utils::fetch_polly_url($token,$moduleinstance->region,
                $slowpassage,'ssml',$moduleinstance->ttsvoice);
        $playerclass = constants::M_MODELAUDIO_PLAYER;
        $ret = "<audio id='$playerclass' controls src='$url'></audio>";
        return $ret;
    }


    public function render_modelaudio_player($moduleinstance, $token, $visible=true){
        $modelaudiourl = $moduleinstance->modelaudiourl;
        if(empty($modelaudiourl) || $modelaudiourl=='none') {
            $modelaudiourl= $this->render_polly_url($moduleinstance, $token);
        }
        echo $this->render_modelaudio_player_from_url($modelaudiourl,$visible);
    }

    /**
     * The modelaudio player
     */
    public function render_modelaudio_player_from_url($modelaudiourl, $visible=true) {
        $playerclass = constants::M_MODELAUDIO_PLAYER;
        $displaystyle = $visible ? 'block' : 'none';
        $ret = "<audio id='$playerclass' class='$playerclass' controls src='$modelaudiourl' style='display: $displaystyle'></audio>";
        return $ret;
    }

    /**
     * The html part of the recorder (js is in the modelaudio.js)
     */
    public function show_recorder($moduleinstance, $token, $uploadaudio = false) {
        global $CFG,$USER;
        $recorderdiv = \html_writer::div('','',
                array('id' => constants::M_RECORDERID,
                        'data-id' => constants::M_RECORDERID,
                        'data-parent' => $CFG->wwwroot,
                        'data-localloading' => 'auto',
                        'data-localloader' => '/mod/readaloud/poodllloader.html',
                        'data-media' => "audio",
                        'data-appid' => constants::M_COMPONENT,
                        'data-owner' => hash('md5',$USER->username),
                        'data-type' => $uploadaudio ? "upload" : "onetwothree",
                        'data-width' => $uploadaudio ? "350" : "360",
                        'data-height' => $uploadaudio ? "150" : "220",
                    //'data-iframeclass'=>"letsberesponsive",
                        'data-updatecontrol' => constants::M_MODELAUDIO_FORM_URLFIELD,
                        'data-timelimit' => 0,
                        'data-transcode' => "1",
                        'data-transcribe' => "1",
                        'data-language'=> $moduleinstance->ttslanguage,
                        'data-expiredays' => "9999",
                        'data-region' => $moduleinstance->region,
                        'data-fallback' => 'warning',
                        'data-token' => $token
                )
        );
        $recordingdiv = \html_writer::div($recorderdiv, constants::M_RECORDER_CONTAINER,
                array('id' => constants::M_RECORDER_CONTAINER));


        //prepare output
        $ret = "";
        $ret .= $recordingdiv;
        //return it
        return $ret;
    }

    public function render_audio_clear_button($moduleinstance){
        $url=new \moodle_url(constants::M_URL . '/modelaudio.php',
                array('n' => $moduleinstance->id, 'action'=>'modelaudioclear'));
        $btn = new \single_button($url, get_string('modelaudioclear', constants::M_COMPONENT), 'post');
        $button = $this->output->render($btn);
        return $button;

    }

    public function render_view_transcript_button(){
        $button =  \html_writer::link('#', get_string("viewmodeltranscript", constants::M_COMPONENT),
                array('class'=>constants::M_CLASS . '_center btn btn-secondary ' . constants::M_VIEWMODELTRANSCRIPT,
                        'id'=>constants::M_VIEWMODELTRANSCRIPT, 'style'=>'display: none'));

        return $button;

    }
    public function render_view_transcript(){
        $transcriptdiv = \html_writer::div('', constants::M_MODELTRANSCRIPT,
                array('id' => constants::M_MODELTRANSCRIPT,'style'=>'display: none'));

        return $transcriptdiv;
    }



}