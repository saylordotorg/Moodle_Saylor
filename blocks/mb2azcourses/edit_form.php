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
 * @package		Mb2 A-Z Courses
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2018 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/

defined('MOODLE_INTERNAL') || die;
 

 
class block_mb2azcourses_edit_form extends block_edit_form 
{
 
 
 
 
	protected function specific_definition($mform) 
	{
 
       
	   	global $CFG, $PAGE;
		
		
		$PAGE->requires->jquery();
		//$PAGE->requires->css('/blocks/mb2azcourses/assets/spectrum/spectrum.css');
	   	//$PAGE->requires->js('/blocks/mb2azcourses/assets/spectrum/spectrum.js');
	   	$PAGE->requires->js('/blocks/mb2azcourses/scripts/admin.js'); 
		
		
		$layoutArr = array(
			'list' => get_string('layoutlist','block_mb2azcourses')
		);
		
		$exclCatArr = array(
			'exclude' => get_string('exclude','block_mb2content'),
			'include' => get_string('include','block_mb2content')
		);
		
		
		// General options
		$mform->addElement('header', 'config_generaloptions', get_string('generaloptions', 'block_mb2azcourses'));
		
		
		$mform->addElement('text', 'config_title', get_string('configtitle', 'block_mb2azcourses'));
		$mform->setType('config_title', PARAM_TEXT);		
				
		
		$mform->addElement('text', 'config_customcls', get_string('customcls','block_mb2azcourses'));
        $mform->setType('config_customcls', PARAM_TEXT);
		
		
		$mform->addElement('text', 'config_margin', get_string('margin','block_mb2azcourses'));
		$mform->addHelpButton('config_margin', 'margin', 'block_mb2azcourses');
        $mform->setType('config_margin', PARAM_TEXT);
		
		
		$mform->addElement('text', 'config_langtag', get_string('langtag','block_mb2azcourses'));
		$mform->addHelpButton('config_langtag', 'langtag', 'block_mb2azcourses');
        $mform->setType('config_langtag', PARAM_TEXT);
		
		
		$mform->addElement('textarea', 'config_textbefore', get_string('textbefore','block_mb2azcourses'));
		$mform->setType('config_textbefore', PARAM_TEXT);	
		
		
		$mform->addElement('textarea', 'config_textafter', get_string('textafter','block_mb2azcourses'));
		$mform->setType('config_textafter', PARAM_TEXT);
		
					
		$mform->addElement('textarea', 'config_alphabet', get_string('alphabet','block_mb2azcourses'));
		$mform->addHelpButton('config_alphabet', 'alphabet', 'block_mb2azcourses');
		$mform->setType('config_alphabet', PARAM_TEXT);
		
		
		$mform->addElement('textarea', 'config_catids', get_string('catids','block_mb2azcourses'));
		$mform->addHelpButton('config_catids', 'catids', 'block_mb2azcourses');
		$mform->setType('config_catids', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_excats', get_string('excats', 'block_mb2azcourses'), $exclCatArr);
		$mform->setDefault('config_excats', 'exclude');	
		
			
		$mform->addElement('textarea', 'config_courseids', get_string('courseids','block_mb2azcourses'));
		$mform->addHelpButton('config_courseids', 'courseids', 'block_mb2azcourses');
		$mform->setType('config_courseids', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_excourses', get_string('excourses', 'block_mb2azcourses'), $exclCatArr);
		$mform->setDefault('config_excourses', 'exclude');
		
		
		
		$mform->addElement('select', 'config_layout', get_string('layout', 'block_mb2azcourses'), $layoutArr);
				
		
		$mform->addElement('text', 'config_plus', get_string('plus','block_mb2azcourses'));
		$mform->setDefault('config_plus',0);
        $mform->setType('config_plus', PARAM_INT);
		
					
		$mform->addElement('text', 'config_plusttop', get_string('plusttop','block_mb2azcourses'));
		$mform->setDefault('config_plusttop',70);
        $mform->setType('config_plusttop', PARAM_INT);
				
		
		
	}
	
	
	
	function set_data($defaults) {

        
		$slidesCount = 7;
		
		if (empty($entry->id)) 
		{
           
		    $entry = new stdClass;
            $entry->id = null;
			
        }

        $draftitemid = file_get_submitted_draft_itemid('config_images');

       	file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_mb2azcourses', 'content', 0, array('subdirs'=>true));

      	$entry->attachments = $draftitemid;	
		
		
			
		

        parent::set_data($defaults);		
		
		
        if ($data = parent::get_data()) 		
		{
           
		   
			//file_save_draft_area_files($data->config_images, $this->block->context->id, 'block_mb2azcourses', 'content', 0, array('subdirs' => true));
			
			
        }
		
		
    }
	
	
}