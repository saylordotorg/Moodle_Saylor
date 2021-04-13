<?php
/**
 * Term form.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use \mod_wordcards\utils;
use \mod_wordcards\constants;


/**
 * Term form class.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */
class mod_wordcards_form_term extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $termid = $this->_customdata['termid'];
        $ttslanguage = $this->_customdata['ttslanguage'];
        $bigwidth = array('style'=>'width: 100%');

        $mform->addElement('hidden', 'termid');
        $mform->setType('termid', PARAM_INT);
        $mform->setConstant('termid', $termid);

        $mform->addElement('text', 'term', get_string('term', constants::M_COMPONENT),$bigwidth);
        $mform->setType('term', PARAM_NOTAGS);
        $mform->addHelpButton('term', 'term', constants::M_COMPONENT);
        $mform->addRule('term', null, 'required', null, 'client');
        $mform->addRule('term', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
    
        $mform->addElement('textarea', 'definition', get_string('definition', constants::M_COMPONENT),$bigwidth);
        $mform->setType('definition', PARAM_NOTAGS);
        $mform->addHelpButton('definition', 'definition', constants::M_COMPONENT);
        $mform->addRule('definition', null, 'required', null, 'client');
    
        $mform->addElement('text', 'model_sentence', get_string('model_sentence', constants::M_COMPONENT),$bigwidth);
        $mform->setType('model_sentence', PARAM_NOTAGS);
        $mform->addHelpButton('model_sentence', 'model_sentence', constants::M_COMPONENT);
        $mform->addRule('model_sentence', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
 
        $mform->addElement('textarea', 'alternates', get_string('alternates', constants::M_COMPONENT),$bigwidth);
        $mform->addHelpButton('alternates', 'alternates', constants::M_COMPONENT);
        $mform->setType('alternates', PARAM_NOTAGS);

        $voices=utils::get_tts_voices($ttslanguage);
        $mform->addElement('select', 'ttsvoice', get_string('ttsvoice', constants::M_COMPONENT),
                $voices);
        $mform->addHelpButton('ttsvoice', 'ttsvoice', constants::M_COMPONENT);

        $mform->addElement('header', 'audioandimages', get_string('audioandimages', constants::M_COMPONENT));
        $mform->setExpanded('audioandimages', false);

        $filemanageropts = utils::fetch_filemanager_opts('audio');
        $mform->addElement('filemanager', 'audio_filemanager', get_string('audiofile', constants::M_COMPONENT), null,
                $filemanageropts);
        $mform->addHelpButton('audio_filemanager', 'audiofile', constants::M_COMPONENT);
        
        $filemanageropts = utils::fetch_filemanager_opts('audio');
        $mform->addElement('filemanager', 'model_sentence_audio_filemanager', get_string('model_sentence_audio', constants::M_COMPONENT), null,
                $filemanageropts);

        $mform->addHelpButton('audio_filemanager', 'audiofile', constants::M_COMPONENT);

        $filemanageropts = utils::fetch_filemanager_opts('image');
        $mform->addElement('filemanager', 'image_filemanager', get_string('imagefile', constants::M_COMPONENT), null,
                $filemanageropts);
        $mform->addHelpButton('image_filemanager', 'imagefile', constants::M_COMPONENT);

        $this->add_action_buttons(false);
    }

}
