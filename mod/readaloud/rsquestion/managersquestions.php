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
 * Action for adding/editing a rsquestion.
 *
 * @package mod_readaloud
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

use \mod_readaloud\constants;
use \mod_readaloud\local\rsquestion\helper;
use \mod_readaloud\local\rsquestion\textpromptaudioform;
use \mod_readaloud\local\rsquestion\textpromptlongform;
use \mod_readaloud\local\rsquestion\textpromptshortform;

require_once("../../../config.php");
require_once($CFG->dirroot.'/mod/readaloud/lib.php');


global $USER,$DB;

// first get the nfo passed in to set up the page
$itemid = optional_param('itemid',0 ,PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Course Module ID
$type  = optional_param('type', constants::NONE, PARAM_INT);
$action = optional_param('action','edit',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/readaloud:itemedit', $context);

//set up the page object
$PAGE->set_url('/mod/readaloud/rsquestion/managersquestions.php', array('itemid'=>$itemid, 'id'=>$id, 'type'=>$type));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

//are we in new or edit mode?
if ($itemid) {
    $item = $DB->get_record(constants::M_QTABLE, array('id'=>$itemid,constants::M_MODNAME . 'id' => $cm->instance), '*', MUST_EXIST);
	if(!$item){
		print_error('could not find item of id:' . $itemid);
	}
    $type = $item->type;
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the readaloud items page
$redirecturl = new moodle_url('/mod/readaloud/rsquestion/rsquestions.php', array('id'=>$cm->id));

	//handle delete actions
    if($action == 'confirmdelete'){
        //TODO more intelligent detection of question usage
    	$usecount = $DB->count_records(constants::M_USERTABLE,array(constants::M_MODNAME .'id'=>$cm->instance));
    	if($usecount>0){
    		redirect($redirecturl,get_string('iteminuse',constants::M_COMPONENT),10);
    	}

		$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
		$rsquestion_renderer = $PAGE->get_renderer(constants::M_COMPONENT,'rsquestion');
		echo $renderer->header($moduleinstance, $cm, 'rsquestions', null, get_string('confirmitemdeletetitle', constants::M_COMPONENT));
		echo $rsquestion_renderer->confirm(get_string("confirmitemdelete",constants::M_COMPONENT,$item->name),
			new moodle_url('/mod/readaloud/rsquestion/managersquestions.php', array('action'=>'delete','id'=>$cm->id,'itemid'=>$itemid)),
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete item NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		$success = helper::delete_item($moduleinstance,$itemid,$context);
        redirect($redirecturl);
    }elseif($action=="moveup" || $action=="movedown"){
        helper::move_item($moduleinstance,$itemid,$action);
        redirect($redirecturl);
    }



//get filechooser and html editor options
//get filechooser and html editor options
$editoroptions = helper::fetch_editor_options($course, $context);
$filemanageroptions = helper::fetch_filemanager_options($course,1);


//get the mform for our item
switch($type){

	case constants::TYPE_TEXTPROMPT_LONG:
		$mform = new textpromptlongform(null,
			array('editoroptions'=>$editoroptions,
			'filemanageroptions'=>$filemanageroptions)
		);
		break;

    case constants::TYPE_TEXTPROMPT_SHORT:
        $mform = new textpromptshortform(null,
            array('editoroptions'=>$editoroptions,
                'filemanageroptions'=>$filemanageroptions)
        );
        break;

    case constants::TYPE_TEXTPROMPT_AUDIO:
        $mform = new textpromptaudioform(null,
            array('editoroptions'=>$editoroptions,
                'filemanageroptions'=>$filemanageroptions)
        );
        break;

	case constants::NONE:
	default:
		print_error('No item type specifified');

}

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$theitem = new stdClass;
        $theitem->readaloudid = $moduleinstance->id;
        $theitem->id = $data->itemid;
		$theitem->visible = $data->visible;
		$theitem->itemorder = $data->itemorder;
		$theitem->type = $data->type;
		$theitem->name = $data->name;
		$theitem->modifiedby=$USER->id;
		$theitem->timemodified=time();
		
		//first insert a new item if we need to
		//that will give us a itemid, we need that for saving files
		if(!$edit){
			
			$theitem->{constants::TEXTQUESTION} = '';
			$theitem->timecreated=time();			
			$theitem->createdby=$USER->id;

			//get itemorder
            $comprehensiontest = new \mod_readaloud\comprehensiontest($cm);
            $currentitems = $comprehensiontest->fetch_items();
            if(count($currentitems)>0){
                $lastitem = array_pop($currentitems);
                $itemorder = $lastitem->itemorder +1;
            } else{
                $itemorder=1;
            }
            $theitem->itemorder=$itemorder;

			//create a rsquestionkey
			$theitem->rsquestionkey = helper::create_rsquestionkey();
			
			//try to insert it
			if (!$theitem->id = $DB->insert_record(constants::M_QTABLE,$theitem)){
					error("Could not insert readaloud item!");
					redirect($redirecturl);
			}
		}			
		
		//handle all the text questions
		$theitem->{constants::TEXTQUESTION} = $data->{constants::TEXTQUESTION} ;


	//save correct answer if we have one
    if(property_exists($data,constants::CORRECTANSWER)){
        $theitem->{constants::CORRECTANSWER} = $data->{constants::CORRECTANSWER} ;
    }

    //save text answers
    for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++){
        //if its an editor field, do this
        if(property_exists($data,constants::TEXTANSWER . $anumber . '_editor')) {
            $data = file_postupdate_standard_editor($data, constants::TEXTANSWER . $anumber, $editoroptions, $context,
                constants::M_COMPONENT, constants::TEXTANSWER_FILEAREA . $anumber, $theitem->id);
            $theitem->{constants::TEXTANSWER . $anumber} = $data->{constants::TEXTANSWER . $anumber};
            $theitem->{constants::TEXTANSWER . $anumber . 'format'} = $data->{constants::TEXTANSWER . $anumber . 'format'};
            //if its a text field, do this
        }elseif(property_exists($data,constants::TEXTANSWER . $anumber)){
            $theitem->{constants::TEXTANSWER . $anumber} = $data->{constants::TEXTANSWER. $anumber} ;
        }
    }



		//now update the db once we have saved files and stuff
		if (!$DB->update_record(constants::M_QTABLE,$theitem)){
				print_error("Could not update readaloud item!");
				redirect($redirecturl);
		}

		//go back to edit quiz page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the item into a data object
if ($edit) {
	$data = $item;		
	$data->itemid = $item->id;
}else{
	$data=new stdClass;
	$data->itemid = null;
	$data->visible = 1;
	$data->type=$type;
}
		
	//init our item, we move the id fields around a little 
    $data->id = $cm->id;
		

	//Set up the item type specific parts of the form data
	switch($type){
		case constants::TYPE_TEXTPROMPT_LONG:
			//prepare answer areas
            //save text answers
            for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
                $data = file_prepare_standard_editor($data, constants::TEXTANSWER . $anumber, $editoroptions, $context,
                    constants::M_COMPONENT, constants::TEXTANSWER_FILEAREA. $anumber , $data->itemid);
            }
            break;
        case constants::TYPE_TEXTPROMPT_SHORT:
        case constants::TYPE_TEXTPROMPT_AUDIO:
		default:
	}
    $mform->set_data($data);
    $PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/readaloud/rsquestion/rsquestions.php', array('id'=>$id)));
    $PAGE->navbar->add(get_string('editingitem', constants::M_COMPONENT, get_string($mform->typestring, constants::M_COMPONENT)));
	$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
	$mode='rsquestions';
	echo $renderer->header($moduleinstance, $cm,$mode, null, get_string('edit', constants::M_COMPONENT));
	$mform->display();
	echo $renderer->footer();