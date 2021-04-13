<?php

namespace mod_minilesson\local\rsquestion;

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
 * Forms for minilesson Activity
 *
 * @package    mod_minilesson
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \mod_minilesson\constants;
use \mod_minilesson\utils;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
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
     * An array of options used in the htmleditor
     * @var array
     */
    protected $editoroptions = array();

	/**
     * An array of options used in the filemanager
     * @var array
     */
    protected $filemanageroptions = array();

    /**
     * An array of options used in the filemanager
     * @var array
     */
    protected $moduleinstance = null;


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
        global $CFG;

        $m35 = $CFG->version >= 2018051700;
        $mform = $this->_form;
        $this->editoroptions = $this->_customdata['editoroptions'];
		$this->filemanageroptions = $this->_customdata['filemanageroptions'];
        $this->moduleinstance = $this->_customdata['moduleinstance'];

	
        $mform->addElement('header', 'typeheading', get_string('createaitem', constants::M_COMPONENT, get_string($this->type, constants::M_COMPONENT)));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'type');
            $mform->setType('type', PARAM_TEXT);
			
			$mform->addElement('hidden', 'itemorder');
            $mform->setType('itemorder', PARAM_INT);

            $mform->addElement('text', 'name', get_string('itemtitle', constants::M_COMPONENT), array('size'=>70));
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', get_string('required'), 'required', null, 'client');


            if($this->moduleinstance->richtextprompt==constants::M_PROMPT_RICHTEXT) {
                $someid = \html_writer::random_id();
                $mform->addElement('editor', constants::TEXTQUESTION . '_editor',
                        get_string('itemcontents', constants::M_COMPONENT),
                        array('id' => $someid, 'wrap' => 'virtual', 'style' => 'width: 100%;', 'rows' => '5'),
                        $this->editoroptions);
                $this->_form->setDefault(constants::TEXTQUESTION . '_editor', array('text' => '', 'format' => FORMAT_HTML));
                $mform->setType(constants::TEXTQUESTION, PARAM_RAW);
            }else{
                //Question text
                $mform->addElement('textarea', constants::TEXTQUESTION, get_string('itemcontents', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
                $mform->setType(constants::TEXTQUESTION, PARAM_RAW);

                $togglearray=array();
                $togglearray[] =& $mform->createElement('advcheckbox','addmedia',get_string('addmedia',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addiframe',get_string('addiframe',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addttsaudio',get_string('addttsaudio',constants::M_COMPONENT),'');
                $mform->addGroup($togglearray, 'togglearray', '', array(' '), false);
                //in the case of page we assume they will want to use some media
                if($this->type== constants::TYPE_PAGE) {
                    $mform->setDefault('addmedia', 1);
                }

                //Question media upload
                $this->add_media_upload(constants::MEDIAQUESTION,-1,get_string('itemmedia',constants::M_COMPONENT));
                if($m35){
                    $mform->hideIf(constants::MEDIAQUESTION, 'addmedia', 'neq', 1);
                }else {
                    $mform->disabledIf(constants::MEDIAQUESTION, 'addmedia', 'neq', 1);
                }


                //Question media iframe
                $mform->addElement('text', constants::MEDIAIFRAME, get_string('itemiframe', constants::M_COMPONENT), array('size'=>100));
                $mform->setType(constants::MEDIAIFRAME, PARAM_RAW);
                if($m35){
                    $mform->hideIf( constants::MEDIAIFRAME,'addiframe','neq', 1);
                }else {
                    $mform->disabledIf( constants::MEDIAIFRAME,'addiframe','neq', 1);
                }


                //Question text to speech
                $mform->addElement('textarea', constants::TTSQUESTION, get_string('itemttsquestion', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
                $mform->setType(constants::TTSQUESTION, PARAM_RAW);
                $this->add_voiceselect(constants::TTSQUESTIONVOICE,get_string('itemttsquestionvoice',constants::M_COMPONENT));
                $this->add_voiceoptions(constants::TTSQUESTIONOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
                if($m35){
                    $mform->hideIf(constants::TTSQUESTION, 'addttsaudio', 'neq', 1);
                    $mform->hideIf(constants::TTSQUESTIONVOICE, 'addttsaudio', 'neq', 1);
                    $mform->hideIf(constants::TTSQUESTIONOPTION, 'addttsaudio', 'neq', 1);
                }else {
                    $mform->disabledIf(constants::TTSQUESTION, 'addttsaudio', 'neq', 1);
                    $mform->disabledIf(constants::TTSQUESTIONVOICE, 'addttsaudio', 'neq', 1);
                    $mform->disabledIf(constants::TTSQUESTIONOPTION, 'addttsaudio', 'neq', 1);
                }


            }

        }
		//visibility
		//$mform->addElement('selectyesno', 'visible', get_string('visible'));
        $mform->addElement('hidden', 'visible',1);
        $mform->setType('visible', PARAM_INT);

        $this->custom_definition();
		
		

		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('saveitem', constants::M_COMPONENT));

    }

    protected final function add_static_text($name, $label = null,$text='') {

        $this->_form->addElement('static', $name, $label,
                $text);

    }

    protected final function add_repeating_textboxes($name, $repeatno=5){
        global $DB;

        $additionalfields=1;
        $repeatarray = array();
        $repeatarray[] = $this->_form->createElement('text', $name, get_string($name. 'no', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('text', 'limit', get_string('limitno', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('hidden', $name . 'id', 0);
/*
        if ($this->_instance){
            $repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
            $repeatno += $additionalfields;
        }
*/

        $repeateloptions = array();
        $repeateloptions[$name]['default'] = '';
        //$repeateloptions[$name]['disabledif'] = array('limitanswers', 'eq', 0);
        //$repeateloptions[$name]['rule'] = 'numeric';
        $repeateloptions[$name]['type'] = PARAM_TEXT;

        $repeateloptions[$name]['helpbutton'] = array($name . '_help', constants::M_COMPONENT);
        $this->_form->setType($name, PARAM_CLEANHTML);

       // $this->_form->setType($name .'id', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                $repeateloptions, $name .'_repeats', $name . '_add_fields',
                $additionalfields, "add", true);
    }

    protected final function add_showtextpromptoptions($name, $label = null, $default=constants::TEXTPROMPT_DOTS) {
        $options = utils::fetch_options_textprompt();
        return $this->add_dropdown($name,$label,$options,$default);
    }

    protected final function add_dropdown($name, $label = null,$options, $default=false) {

        $this->_form->addElement('select', $name, $label, $options);
        if($default!==false) {
            $this->_form->setDefault($name, $default);
        }

    }

    protected final function add_media_upload($name, $count=-1, $label = null, $required = false) {
		if($count>-1){
			$name = $name . $count ;
		}
		
		$this->_form->addElement('filemanager',
                           $name,
                           $label,
                           null,
						   $this->filemanageroptions
                           );
		
	}

	protected final function add_media_prompt_upload($label = null, $required = false) {
		return $this->add_media_upload(constants::AUDIOPROMPT,-1,$label,$required);
	}


    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_editorarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        //edoptions = array('noclean'=>true)
        $this->_form->addElement('editor', constants::TEXTANSWER .$count. '_editor', $label, array('rows'=>'4', 'columns'=>'80'), $this->editoroptions);
        $this->_form->setDefault(constants::TEXTANSWER .$count. '_editor', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count. '_editor', get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds a ext area response
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }

        $this->_form->addElement('textarea', constants::TEXTANSWER .$count , $label,array('rows'=>'4', 'columns'=>'140', 'style'=>'width: 600px'));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textboxresponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        $this->_form->addElement('text', constants::TEXTANSWER .$count, $label, array('size'=>'60'));
        $this->_form->setType(constants::TEXTANSWER .$count, PARAM_TEXT);
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds layout hint. Width of a single answer
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_correctanswer( $label = null) {
        if ($label === null) {
            $label = get_string('correctanswer', constants::M_COMPONENT);
        }
        $options = array();
        $options['1']=1;
        $options['2']=2;
        $options['3']=3;
        $options['4']=4;
        $this->_form->addElement('select', constants::CORRECTANSWER, $label,$options);
        $this->_form->setDefault(constants::CORRECTANSWER, 1);
        $this->_form->setType(constants::CORRECTANSWER, PARAM_INT);
    }

    /**
     * Convenience function: Adds a dropdown list of voices
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceselect($name, $label = null) {
        $showall =true;
        $voiceoptions = utils::get_tts_voices($this->moduleinstance->ttslanguage,$showall);
        $this->add_dropdown($name, $label,$voiceoptions);
    }

    /**
     * Convenience function: Adds a dropdown list of voice options
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceoptions($name, $label = null) {
        $voiceoptions = utils::get_tts_options();
        $this->add_dropdown($name, $label,$voiceoptions);
    }
    /**
     * Convenience function: Adds a dropdown list of tts language
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_ttslanguage($name, $label = null) {
        $langoptions = utils::get_lang_options();
        $this->add_dropdown($name, $label,$langoptions);
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $minilesson) {
        return true;
    }
}
