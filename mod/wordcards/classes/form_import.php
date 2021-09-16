<?php
/**
 * Helper.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - ishinekk.co.jp
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Helper class.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - ishinekk.co.jp
 */
class mod_wordcards_form_import extends moodleform {

   public function definition() {
        $mform = $this->_form;
        $leftover_rows = $this->_customdata['leftover_rows'];
        
        $delimiter_options=array('delim_tab'=>get_string('delim_tab','mod_wordcards'),
         	'delim_comma'=>get_string('delim_comma','mod_wordcards'),
         	'delim_pipe'=>get_string('delim_pipe','mod_wordcards')
         );
        $mform->addElement('select', 'delimiter', get_string('delimiter', 'mod_wordcards'),$delimiter_options);
        $mform->setType('delimiter', PARAM_NOTAGS);
        $mform->setDefault('delimiter', 'delim_comma');
        $mform->addRule('delimiter', null, 'required', null, 'client');

        
        $mform->addElement('textarea', 'importdata', get_string('importdata', 'mod_wordcards'), array('style'=>'width: 100%; max-width: 1200px;'));
        $mform->setDefault('importdata', $leftover_rows);
        $mform->setType('importdata', PARAM_NOTAGS);
        $mform->addRule('importdata', null, 'required', null, 'client');
        $this->add_action_buttons(false);
    }

}
