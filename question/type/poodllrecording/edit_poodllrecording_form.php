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
 * Defines the editing form for the poodllrecording question type.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use qtype_poodllrecording\constants;


/**
 * PoodLL Recording question type editing form.
 *
 * @copyright  2012 PoodLL Recording Question 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('poodllrecording');

        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', constants::M_COMP),
                array('rows' => 10), $this->editoroptions);

        //get allowed recorders from admin settings
        $allowed_recorders = get_config(constants::M_COMP, 'allowedrecorders');
        $allowed_recorders  = explode(',',$allowed_recorders);
        $recorderoptions = array();
        if(array_search(constants::RESPONSEFORMAT_AUDIO,$allowed_recorders)!==false){
            $recorderoptions[constants::RESPONSEFORMAT_AUDIO] = get_string("formataudio", constants::M_COMP);
        }
        if(array_search(constants::RESPONSEFORMAT_VIDEO,$allowed_recorders)!==false){
            $recorderoptions[constants::RESPONSEFORMAT_VIDEO] = get_string("formatvideo", constants::M_COMP);
        }
        if(array_search(constants::RESPONSEFORMAT_PICTURE,$allowed_recorders)!==false){
            $recorderoptions[constants::RESPONSEFORMAT_PICTURE] = get_string("formatpicture", constants::M_COMP);
        }
        if(count($recorderoptions)<1){
            //its not meaningful to have all recorders disabled at admin level,
            // but we need to respond to such a situation, so we show whiteboard
            $recorderoptions[constants::RESPONSEFORMAT_PICTURE] = get_string("formatpicture", constants::M_COMP);
        }
        $mform->addElement('select', 'responseformat',
                get_string('responseformat', constants::M_COMP), $recorderoptions);
        $mform->setDefault('responseformat', 'audio');

        // safesave
        $name = 'safesave';
        $label = get_string($name, constants::M_COMP);
        $text = get_string('safesave_details', constants::M_COMP);
        $mform->addElement('advcheckbox', $name, $label, $text);
        $mform->setDefault($name, 0);
				
		//Add a place to set a maximum recording time.
	   $mform->addElement('duration', 'timelimit', get_string('timelimit', constants::M_COMP));
       $mform->setDefault('timelimit',0);
	   $mform->disabledIf('timelimit', 'responseformat', 'eq', 'picture');

		// added Justin 20120814 bgimage, part of whiteboard response
		$mform->addElement('filemanager', 'qresource', get_string('qresource', constants::M_COMP), null,array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
		$mform->addElement('select', 'boardsize',
			get_string('boardsize', constants::M_COMP), $qtype->board_sizes());
			$mform->setDefault('boardsize', 'editor');
		$mform->disabledIf('boardsize', 'responseformat', 'ne', 'picture' );

    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (empty($question->options)) {
            return $question;
        }
        $question->responseformat = $question->options->responseformat;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;

		$question->boardsize=$question->options->boardsize;
		$question->timelimit=$question->options->timelimit;



	//Set backimage details, and configure a draft area to accept any uploaded pictures
	//all this and this whole method does, is to load existing files into a filearea
	//so it is not called when creating a new question, only when editing an existing one

	//best to use file_get_submitted_draft_itemid - because copying questions gets weird otherwise
	//$draftitemid =$question->options->backimage;
	$draftitemid = file_get_submitted_draft_itemid(\qtype_poodllrecording\constants::FILEAREA_QRESOURCE);

	file_prepare_draft_area($draftitemid, $this->context->id, constants::M_COMP, \qtype_poodllrecording\constants::FILEAREA_QRESOURCE,
		!empty($question->id) ? (int) $question->id : null,
		array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
	$question->qresource = $draftitemid;

        $draftid = file_get_submitted_draft_itemid(\qtype_poodllrecording\constants::FILEAREA_GRADERINFO);
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // draftid
            $this->context->id, // context
            constants::M_COMP,      // component
            \qtype_poodllrecording\constants::FILEAREA_GRADERINFO,       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        return $question;
    }


    public function qtype() {
        return 'poodllrecording';
    }
}
