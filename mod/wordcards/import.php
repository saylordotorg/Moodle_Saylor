<?php
/**
 * Displays the set-up phase.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - ishinekk.co.jp
 */

use \mod_wordcards\constants;
use \mod_wordcards\utils;

require_once(__DIR__ . '/../../config.php');

$cmid = required_param('id', PARAM_INT);
$leftover_rows = optional_param('leftover_rows', '', PARAM_TEXT);
$action = optional_param('action', null, PARAM_ALPHA);

$mod = mod_wordcards_module::get_by_cmid($cmid);
$course = $mod->get_course();
$cm = $mod->get_cm();

require_login($course, true, $cm);
$mod->require_manage();

$modid = $mod->get_id();
$pagetitle = format_string($mod->get_mod()->name, true, $mod->get_course());
$pagetitle .= ': ' . get_string('import', 'mod_wordcards');
$baseurl = new moodle_url('/mod/wordcards/import.php', ['id' => $cmid]);
$formurl = new moodle_url($baseurl);
$term = null;

$PAGE->set_url($baseurl);
$PAGE->navbar->add($pagetitle, $PAGE->url);
$PAGE->set_heading(format_string($course->fullname, true, [context_course::instance($course->id)]));
$PAGE->set_title($pagetitle);

//Get admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

$renderer = $PAGE->get_renderer('mod_wordcards');

$form = new mod_wordcards_form_import($formurl->out(false),['leftover_rows'=>$leftover_rows]);

if ($data = $form->get_data()) {
    if (!empty($data->importdata)) {
    	
    	//get delimiter
    	switch($data->delimiter){
    		case 'delim_comma': $delimiter = ',';break;    		
    		case 'delim_pipe': $delimiter = '|';break;
    		case 'delim_tab':
    		default: 
    			$delimiter ="\t";
    	}

    	//get array of rows
    	$rawdata =trim($data->importdata);
    	$rows = explode(PHP_EOL, $rawdata);
    	
    	//prepare results fields
    	$imported = 0;
    	$failed = array();
    	
    	foreach($rows as $row){
    		$cols = explode($delimiter,$row);
    		if(count($cols)>=2 && !empty($cols[0]) && !empty($cols[1])){
				$insertdata = new stdClass();
				$insertdata->modid = $modid;
				$insertdata->term = trim($cols[0]);
				$insertdata->definition = trim($cols[1]);
				//voices
                $voices = utils::get_tts_voices($mod->get_mod()->ttslanguage);
				if(!empty($cols[2]) && array_key_exists(trim($cols[2]),$voices) && trim($cols[2])!='auto') {
                    $insertdata->ttsvoice = trim($cols[2]);
                }else{
                    $insertdata->ttsvoice = utils::fetch_auto_voice($mod->get_mod()->ttslanguage);
                }
                if(!empty($cols[3])) {
                    $insertdata->model_sentence = trim($cols[3]);
                }
				$DB->insert_record('wordcards_terms', $insertdata);
				$imported++;
        	}else{
        		$failed[]=$row;
        	}//end of if cols ok 
        }//end of for each

        //if successful update our passagehash update flag
        if($imported > 0) {
            $DB->update_record('wordcards', array('id' => $mod->get_mod()->id, 'hashisold' => 1));
        }


        // Uncomment when migrating to 3.1.
        // redirect($PAGE->url, get_string('termadded', 'mod_wordcards', $data->term));
        $result=new stdClass();
        $result->imported=$imported;
        $result->failed=count($failed);
        $message=get_string('importresults','mod_wordcards',$result);
        
        if(count($failed)>0){
        	$leftover_rows = implode(PHP_EOL,$failed);
        	$formurl->param('leftover_rows',$leftover_rows);
        }
        
        redirect($formurl,$message);
    }
}

echo $renderer->header();
echo $renderer->heading($pagetitle);
echo $renderer->navigation($mod, 'import');
echo $renderer->box(get_string('importinstructions',constants::M_COMPONENT), 'generalbox wordcards_importintro', 'intro');

$form->display();
/*
$table = new mod_wordcards_table_terms('tblterms', $mod);
$table->define_baseurl($PAGE->url);
$table->out(25, false);
*/
echo $renderer->footer();
