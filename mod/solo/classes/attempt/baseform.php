<?php

namespace mod_solo\attempt;

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Forms for solo Activity
 *
 * @package    mod_solo
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \mod_solo\constants;
use \mod_solo\utils;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2019 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class baseform extends \moodleform {

    /**
     * This is used to identify this itemtype.
     * @var string
     */
    public $type;

    /**
     * The simple string that describes the item type e.g. audioitem, textitem
     * @var string
     */
    public $typestring;


    /**
     * The module instance
     * @var array
     */
    protected $moduleinstance = null;

    /**
     * The cloudppoodll token
     * @var array
     */
    protected $token = 'notoken';


    /**
     * True if this is a standard item of false if it does something special.
     * items are standard items
     * @var bool
     */
    protected $standard = true;

    /**
     * Each item type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Item types can override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition_after_data() {}

    /**
     * Used to determine if this is a standard item or a special item
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;

        $this->moduleinstance = $this->_customdata['moduleinstance'];
        $this->cm = $this->_customdata['moduleinstance'];


        // $mform->addElement('header', 'typeheading', get_string('createattempt', constants::M_COMPONENT, get_string($this->typestring, constants::M_COMPONENT)));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'attemptid');
        $mform->setType('attemptid', PARAM_INT);

        //visibility
        $mform->addElement('hidden', 'visible', true);
        $mform->setType('visible', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'type');
            $mform->setType('type', PARAM_INT);

        }

        $this->custom_definition();

        $savebutton_text = $this->get_savebutton_text();

        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), $savebutton_text);

    }

    public final function definition_after_data() {
        parent::definition_after_data();
        $this->custom_definition_after_data();
    }
    public function get_savebutton_text(){
        return get_string('saveitem', constants::M_COMPONENT);
    }

    protected final function add_fontawesomecombo_field($name, $label) {
        global $PAGE, $OUTPUT;

        $radios = array();
        $this->_form->addElement('hidden', $name);
        $this->_form->setType($name,PARAM_TEXT);

        if($this->topics && count($this->topics)) {
            foreach ($this->topics as $topic) {
                $oneradio = array();
                $oneradio['name'] = $name;
                $oneradio['fontcode'] = $topic->fonticon;
                $oneradio['topicname'] = $topic->name;
                $oneradio['value'] = $topic->id;
                $radios[] = $oneradio;
            }
            $staticcontent =
                    $OUTPUT->render_from_template(constants::M_COMPONENT . '/fontawesomecombo', array('radios' => $radios));
        }else{
            $staticcontent = get_string('notopicsavailable',constants::M_COMPONENT);
        }

        $this->_form->addElement('static', 'combo_' . $name, $label, $staticcontent);

        $opts =Array();
        $opts['container']='fonttogglegroup';
        $opts['item']='fonttoggleitem';
        $opts['updatecontrol']=$name;
        $opts['mode']='radio';
        $opts['maxchecks']=1;
        $PAGE->requires->js_call_amd("mod_solo/toggleselected", 'init', array($opts));
    }

    protected final function add_targetwords_fields() {
        global $PAGE;
        $this->_form->addElement('hidden','targetwords');
        $this->_form->setType('targetwords',PARAM_TEXT);

        //display target words in a "tag" like way.
        $this->add_targetwords_display($this->moduleinstance->targetwords);
        //we no longer use "my words"
        $this->_form->addElement('hidden','mywords', '');
        $this->_form->setType('mywords',PARAM_TEXT);
/*
        $this->_form->addElement('textarea','mywords',get_string('mywords', constants::M_COMPONENT),'wrap="virtual" rows="5" cols="50"');
        $this->_form->setType('mywords',PARAM_TEXT);
        $this->_form->addElement('static','targetwordsexplanation','',
                get_string('targetwordsexplanation',constants::M_COMPONENT));
*/

    }

    //add a field to display target words in a "tag" like way.
    //called from audiorecorder and userselections forms.
    protected final function add_targetwords_display($targetwords) {
        global $OUTPUT;

        //we have to work quite hard to get target words displayed like tags.
        if(empty($targetwords) || trim($targetwords)==''){
            $targetwordcontent = '';
        }else {
            $tdata = array('targetwords' => explode(PHP_EOL, $targetwords));
            $targetwordcontent = $OUTPUT->render_from_template(constants::M_COMPONENT . '/targetwords', $tdata);
        }
        $this->_form->addElement('static','targetwordsdisplay',
                get_string('targetwords', constants::M_COMPONENT),
                "<div id='" . constants::C_TARGETWORDSDISPLAY. "'>$targetwordcontent</div>");
    }

    //add a field to display the  target spoken words
    protected final function add_totalwordsgoal() {
        global $OUTPUT;

        $this->_form->addElement('static','totalwordsgoaldisplay',
                get_string('gradewordgoal', constants::M_COMPONENT),
                get_string('displaygradewordgoal', constants::M_COMPONENT,$this->moduleinstance->gradewordgoal));
    }

    //add a field to display the  upload warning
    protected final function add_upload_warning() {

        $this->_form->addElement('static','uploadwarning',
                '',
                '<div class="mod_solo_uploadwarning" style="display: none">' .
                get_string('uploading', constants::M_COMPONENT) . '<br>' .
                '<i class="fa fa-spinner fa-spin fa-3x"></i></div>');
    }

    protected final function add_title($title) {
        $titlediv = \html_writer::div($title,'mod_solo_formtitle');
        $this->_form->addElement('static','title','', $titlediv);
    }

    protected final function add_instructions($instructions) {
        $instructionsdiv = \html_writer::div($instructions,'mod_solo_forminstructions');
        $this->_form->addElement('static','title','', $instructionsdiv);
    }

    protected final function add_activitycontent() {
        global $OUTPUT;

        //contentitem
        $contentitem = [];
        $context = \context_module::instance($this->cm->id);

        //Prepare speaking topic text
        $contentitem['itemtext']=$this->moduleinstance->speakingtopic;

        //Prepare IFrame
        if(!empty(trim($this->moduleinstance->topiciframe))){
            $contentitem['itemiframe']=$this->moduleinstance->topiciframe;
        }

        //Prepare TTS prompt
        if(!empty(trim($this->moduleinstance->topictts))){
            $contentitem['itemtts']=utils::fetch_polly_url($this->token,$this->moduleinstance->region,$this->moduleinstance->topictts,'text',$this->moduleinstance->topicttsvoice);
        }

        //media items
        $itemid=0;
        $filearea='topicmedia';
        $mediaurls = utils::fetch_media_urls($context->id,$filearea,$itemid);
        if($mediaurls && count($mediaurls)>0){
            foreach($mediaurls as $mediaurl){
                $file_parts = pathinfo(strtolower($mediaurl));
                switch($file_parts['extension'])
                {
                    case "jpg":
                    case "png":
                    case "gif":
                    case "bmp":
                    case "svg":
                        $contentitem['itemimage'] = $mediaurl;
                        break;

                    case "mp4":
                    case "mov":
                    case "webm":
                    case "ogv":
                        $contentitem['itemvideo'] = $mediaurl;
                        break;

                    case "mp3":
                    case "ogg":
                    case "wav":
                        $contentitem['itemaudio'] = $mediaurl;
                        break;

                    default:
                        //do nothing
                }//end of extension switch
            }//end of for each
        }//end of if mediaurls
        $staticcontent = $OUTPUT->render_from_template(constants::M_COMPONENT . '/activitycontent', $contentitem);
        $contentdiv = \html_writer::div($staticcontent,'mod_solo_forminstructions');
        $this->_form->addElement('static','title',get_string('speakingtopic', constants::M_COMPONENT), $contentdiv);
    }

    protected final function add_tips_field() {
        $this->_form->addElement('static','tips',get_string('tips', constants::M_COMPONENT),
                $this->moduleinstance->tips ,array("class"=>'mod_solo_bordered mod_solo_readonly'));

    }

    protected final function add_targettime_field() {
        $this->_form->addElement('static','targettime',get_string('targettime', constants::M_COMPONENT),
                get_string('xminutes',constants::M_COMPONENT ,$this->moduleinstance->convlength) ,array("class"=>'mod_solo_bordered mod_solo_readonly'));

    }

    protected final function add_speakingtopic() {

        $this->_form->addElement('static','speakingtopic',get_string('speakingtopic', constants::M_COMPONENT),
                $this->moduleinstance->speakingtopic ,array("class"=>'mod_solo_speakingtopic_readonly'));
    }

    protected final function set_targetwords() {
        global $OUTPUT;

        $targetwordselement =& $this->_form->getElement('targetwords');
        if ($targetwordselement) {
            $targetwordselement->setValue($this->moduleinstance->targetwords);
        }
    }

    protected final function add_usercombo_field($name, $label) {
        global $CFG, $PAGE, $OUTPUT;
        require_once("$CFG->libdir/outputcomponents.php");

        $checks = array();
        $this->_form->addElement('hidden', $name);
        $this->_form->setType($name,PARAM_TEXT);

        foreach ($this->users as $user){
            $user_picture=new \user_picture($user);
            $picurl = $user_picture->get_url($PAGE);

            $onecheck=array();
            $onecheck['name']=$name;
            $onecheck['userpic']=$picurl;
            $onecheck['username']=fullname($user);
            $onecheck['value']=$user->id;
            $checks[] = $onecheck;
        }
        $staticcontent = $OUTPUT->render_from_template(constants::M_COMPONENT . '/usercombo', array('checks'=>$checks));
        $this->_form->addElement('static', 'combo_' . $name, $label, $staticcontent);


        $opts =Array();
        $opts['container']='usertogglegroup';
        $opts['item']='usertoggleitem';
        $opts['updatecontrol']=$name;
        $opts['mode']='checkbox';
        $opts['maxchecks']=4;
        $PAGE->requires->js_call_amd("mod_solo/toggleselected", 'init', array($opts));
    }

    protected final function add_userselector_field($name,$label){
        $options = [
                'multiple'=>true
        ];

        $selectusers=array();
        foreach ($this->users as $user){
            $selectusers[$user->id] =  fullname($user);
        }
        $this->_form->addElement('autocomplete', $name, $label,$selectusers, $options);
        $this->_form->addRule($name, null, 'required', null, 'client');
    }


    protected final function add_transcript_editor($name, $label){
        global $OUTPUT, $PAGE, $CFG;

        $tdata=array();
        $tdata['audiofilename']=$this->filename;

        $transcripteditoraudio = $OUTPUT->render_from_template( constants::M_COMPONENT . '/transcripteditor', $tdata);
        $staticcontent = \html_writer::div($transcripteditoraudio,constants::M_C_CONVERSATION,array());
        $this->_form->addElement('static', 'control_' . $name, $label, $staticcontent);

        $opts= array('rows'=>'5', 'cols'=>'80');
        $this->_form->addElement('textarea', $name, '', $opts);
        $this->_form->setType($name, PARAM_TEXT);
    }

    protected final function fetch_markedpassage() {
        $markedpassage = \mod_solo\aitranscriptutils::render_passage($this->selftranscript);
        $js_opts_html = \mod_solo\aitranscriptutils::prepare_passage_amd($this->attempt, $this->aidata);
        return ($markedpassage . $js_opts_html);
    }

    protected final function add_markedpassage_field($name, $label) {
        global $PAGE;
        $markedpassage = $this->fetch_markedpassage();
        $this->_form->addElement('static', 'combo_' . $name, $label, $markedpassage);
    }

    protected final function fetch_stats() {
        global $OUTPUT;
        $stats = $OUTPUT->render_from_template( constants::M_COMPONENT . '/summarystats', array('s'=>$this->stats));
        return $stats;
    }

    protected final function add_stats_field($name, $label) {
        global $OUTPUT;
        $stats = $this->fetch_stats();
        $this->_form->addElement('static', 'combo_' . $name, $label, $stats);

    }


    protected final function add_recordingurl_field() {
        $this->_form->addElement('hidden', constants::RECORDINGURLFIELD,null,array('id'=>constants::M_WIDGETID . constants::RECORDINGURLFIELD));
        $this->_form->setType(constants::RECORDINGURLFIELD, PARAM_TEXT);
        $this->_form->addElement('hidden', constants::STREAMINGTRANSCRIPTFIELD,null,array('id'=>constants::M_WIDGETID . constants::STREAMINGTRANSCRIPTFIELD));
        $this->_form->setType(constants::STREAMINGTRANSCRIPTFIELD, PARAM_TEXT);
        $this->_form->addElement('static', constants::RECORDERORPLAYERFIELD, get_string('audiorecording', constants::M_COMPONENT), '','class="mod_solo_audiorecordercont');

    }

    protected final function add_audio_upload($name, $label = null, $required = false)
    {
        $recordingurlfield =& $this->_form->getElement(constants::RECORDINGURLFIELD);
        $recordingorplayerfield =& $this->_form->getElement(constants::RECORDERORPLAYERFIELD);
        if ($recordingurlfield) {
            $recordingurl = $this->_form->getElementValue(constants::RECORDINGURLFIELD);
        }else{
            $recordingurl=false;
        }

        if($recordingurl && !empty($recordingurl)){
            $player_html = "<audio src='" . $recordingurl . "' controls></audio>";
            $recordingorplayerfield->setValue($player_html);

        }else{
            $width=360;
            $height=210;
            $recorder_html = $this->fetch_recorder_html($this->moduleinstance,'audio','upload',$this->token, $width,$height);
            $recordingorplayerfield->setValue($recorder_html);
        }

    }

    protected final function add_audio_player($name, $label = null, $required = false) {
        $player_html = "<audio src='" . $this->filename . "' controls></audio>";
        $this->_form->addElement('static', $name, $label, $player_html, '');

    }


    protected final function add_audio_recording($name, $label = null, $required = false) {
        $recordingurlfield =& $this->_form->getElement(constants::RECORDINGURLFIELD);
        $recordingorplayerfield =& $this->_form->getElement(constants::RECORDERORPLAYERFIELD);
        if ($recordingurlfield) {
            $recordingurl = $this->_form->getElementValue(constants::RECORDINGURLFIELD);
        }else{
            $recordingurl=false;
        }

        $player_html ='';
        if($recordingurl && !empty($recordingurl)){
            $player_html = "<audio src='" . $recordingurl . "' controls></audio>";
            $recordingorplayerfield->setValue($player_html);
        }

        $width=450;
        $height=280;
        $timelimit = $this->moduleinstance->maxconvlength * 60;

        $error_message = utils::fetch_token_error($this->token);
        $recorderskin = $this->moduleinstance->recorderskin;
        $recordertype = $this->moduleinstance->recordertype;
        if(empty($error_message)) {
            $recorder_html =
                    $this->fetch_recorder($this->moduleinstance, $recordertype, $recorderskin, $timelimit, $this->token, $width, $height);
        }else{
            $recorder_html = $error_message;
        }
        $recordingorplayerfield->setValue($player_html . $recorder_html);

    }

    /**
     * The html part of the recorder (js is in the fetch_activity_amd)
     * PARAM $media one of audio, video
     * PARAM $recordertype something like "upload" or "fresh" or "bmr"
     */
    public function fetch_recorder($moduleinstance, $media, $recordertype,$timelimit, $token,$width,$height){
        global $CFG, $PAGE, $USER;

        $recorderdiv_domid = constants::M_WIDGETID;
        //we never need more than a recorder on the page of this mod
        //but this is how to do it, and we need to update JS to use this too
        //\html_writer::random_id(constants::M_WIDGETID);



        ///recorder
        //=======================================
        $can_transcribe = \mod_solo\utils::can_transcribe($moduleinstance);
        $hints = new \stdClass();

        //if they choose streaming transcription we also transcribe on server(just in case)
        //we will turn this off after streaming has proved stable 03/2020
        switch ($moduleinstance->transcriber){
            case constants::TRANSCRIBER_AMAZONSTREAMING :
                $transcribe = $can_transcribe ? constants::TRANSCRIBER_AMAZONTRANSCRIBE : "0";
                $hints->streamingtranscriber = 'aws';
                $speechevents = '1';
                break;
            case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
            case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
            case constants::TRANSCRIBER_NONE:
            default:
                $transcribe = $can_transcribe ? $moduleinstance->transcriber : "0";
                $speechevents="0";
        }

        //we encode any hints
        $string_hints = base64_encode(json_encode($hints));


        $recorderdiv= \html_writer::div('', constants::M_CLASS  . '_center',
                array('id'=>$recorderdiv_domid,
                        'data-id'=>'therecorder',
                        'data-parent'=>$CFG->wwwroot,
                        'data-owner'=>hash('md5',$USER->username),
                        'data-localloading'=>'auto',
                        'data-localloader'=> constants::M_URL . '/poodllloader.html',
                        'data-media'=>$media,
                        'data-appid'=>constants::M_COMPONENT,
                        'data-type'=>$recordertype,
                        'data-width'=>$width,
                        'data-height'=>$height,
                        'data-updatecontrol'=>constants::M_WIDGETID . constants::RECORDINGURLFIELD,
                        'data-timelimit'=> $timelimit,
                        'data-transcode'=>"1",
                        'data-transcribe'=>$transcribe,
                        'data-subtitle'=>$transcribe,
                        'data-language'=>$moduleinstance->ttslanguage,
                        'data-expiredays'=>$moduleinstance->expiredays,
                        'data-region'=>$moduleinstance->region,
                        'data-speechevents' => $speechevents,
                        'data-hints' => $string_hints,
                        'data-fallback'=>'warning',
                        'data-token'=>$token
                    //'data-hints'=>$string_hints,
                )
        );
        $containerdiv= \html_writer::div($recorderdiv,constants::M_CLASS . '_recordercontainer'  . " " . constants::M_CLASS  . '_center',
                array('id'=>$recorderdiv_domid . '_recordercontainer'));
        //=======================================


        $recordingdiv = \html_writer::div($containerdiv ,constants::M_CLASS . '_recordingcontainer');

        //prepare output
        $ret_html = "";
        $ret_html .=$recordingdiv;

        //here we set up any info we need to pass into javascript
        //importantly we tell it the div id of the recorder
        $recopts =Array();
        $recopts['recorderid']=$recorderdiv_domid;

        //streaming transcriber
        //if not available we switch to amazon transcribe
        if($moduleinstance->transcriber == constants::TRANSCRIBER_AMAZONSTREAMING &&
                !utils::can_streaming_transcribe($moduleinstance)){
            $moduleinstance->transcriber=constants::TRANSCRIBER_AMAZONTRANSCRIBE;
        }
        $recopts['transcriber']=$moduleinstance->transcriber;
        $recopts['language']=$moduleinstance->ttslanguage;
        $recopts['region']= $moduleinstance->region;
        $recopts['token']=$token;
        $recopts['parent']=$CFG->wwwroot;
        $recopts['owner']=hash('md5',$USER->username);
        $recopts['appid']=constants::M_COMPONENT;
        $recopts['expiretime']=300;//max expire time is 300 seconds



        //this inits the M.mod_solo recoorder controller, after the page has loaded.
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        $jsonstring = json_encode($recopts);
        $opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $recorderdiv_domid, 'type' => 'hidden', 'value' => $jsonstring));

        //the recorder div
        $ret_html .= $opts_html;

        $opts=array('cmid'=>$this->cm->id,'widgetid'=>$recorderdiv_domid);
        $PAGE->requires->js_call_amd("mod_solo/recordercontroller", 'init', array($opts));

        //these need to be returned and echo'ed to the page
        return $ret_html;

    }


    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $solo) {
        return true;
    }
}