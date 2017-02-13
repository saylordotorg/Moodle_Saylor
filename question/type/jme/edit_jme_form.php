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
 * Defines the editing form for the jme question type.
 *
 * @package    qtype_jme
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');

class qtype_jme_edit_form extends qtype_shortanswer_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG;

        $PAGE->requires->js('/question/type/jme/jme_script.js');
        $PAGE->requires->css('/question/type/jme/styles.css');

        $mform->addElement('html', html_writer::tag('div', get_string('enablejavascript', 'qtype_jme'),
                array('class' => 'jme_applet', 'code' => 'JME.class', 'id' => 'jme1', 'name' => 'JME1',
                'archive' => 'JME.jar', 'width' => '360', 'height' => '315')));
        $mform->addElement('hidden', 'usecase', 1);
        $mform->setType('usecase', PARAM_INT);
        $optionscript = 'onClick = "setJSMEoptions()"';
        $label = get_string('jmeoptions', 'qtype_jme');
        $editoroptions[] = $mform->createElement('text', 'jmeoptions', '', array('size' => 50));
        $editoroptions[] = $mform->createElement('button', 'setoptions', get_string('setoptions', 'qtype_jme'), $optionscript);
        $mform->addElement('group', 'editoroptions',
                 $label, $editoroptions, null, false);
        $mform->setDefault('jmeoptions', $CFG->qtype_jme_options);
        $mform->setType('jmeoptions', PARAM_RAW);
        $mform->addHelpButton('editoroptions', 'jmeoptions', 'qtype_jme');
        $mform->addElement('text', 'width', get_string('width', 'qtype_jme'), array('size' => 6));
        $mform->setDefault('width', QTYPE_JME_APPLET_WIDTH);
        $mform->setType('width', PARAM_INT);
        $mform->addElement('text', 'height', get_string('height', 'qtype_jme'), array('size' => 6));
                $mform->setDefault('height', QTYPE_JME_APPLET_HEIGHT);
        $mform->setType('height', PARAM_INT);
        $mform->addElement('static', 'answersinstruct',
                get_string('correctanswers', 'qtype_jme'),
                get_string('filloutoneanswer', 'qtype_jme'));
        $mform->closeHeaderBefore('answersinstruct');

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_jme', '{no}'),
                question_bank::fraction_options());

        $this->add_interactive_settings();

        // Include JSME loader script as an html tag.
        $jsmescript = $CFG->wwwroot.'/question/type/jme/jsme/jsme.nocache.js';
        $mform->addElement('html', html_writer::tag('script', '', array('src' => $jsmescript)));
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $answeroptions = array();
        $answeroptions[] = $mform->createElement('text', 'answer',
                $label, array('size' => 40));
        // Construct the insert button.
        $scriptattrs = 'onClick = "getSmilesEdit(this.name)"';
        $answeroptions[] = $mform->createElement('button', 'insert', get_string('insertfromeditor', 'qtype_jme'), $scriptattrs);
        $answeroptions[] = $mform->createElement('select', 'fraction',
                get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('group', 'answeroptions',
                 $label, $answeroptions, null, false);
        $repeated[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        return $question;
    }

    public function qtype() {
        return 'jme';
    }
}
