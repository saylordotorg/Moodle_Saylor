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
 * Reports for readaloud
 *
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_readaloud\constants;
use \mod_readaloud\utils;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // readaloud instance ID
$action = optional_param('action', constants::M_PUSH_NONE, PARAM_INT);



if ($id) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_url(constants::M_URL . '/push.php',
	array('id' => $cm->id));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/readaloud:manage', $modulecontext);
require_capability('mod/readaloud:pushtoclones', $modulecontext);

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

switch($action){

    case constants::M_PUSH_PASSAGE:
        $updatefields = ['passage','passagesegments','passageformat','passagehash','phonetic'];
        foreach($updatefields as $thefield) {
            $DB->set_field(constants::M_TABLE, $thefield, $moduleinstance->{$thefield}, array('name' => $moduleinstance->name, 'masterinstance' => 0));
        }
        redirect($PAGE->url,get_string('pushpassage_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_TTSMODELAUDIO:
        $updatefields = ['ttslanguage','ttsvoice','ttsspeed','modelaudiourl','modelaudiobreaks','modelaudiotrans','modelaudiofulltrans','modelaudiomatches'];
        foreach($updatefields as $thefield) {
            $DB->set_field(constants::M_TABLE, $thefield, $moduleinstance->{$thefield}, array('name' => $moduleinstance->name, 'masterinstance' => 0));
        }
        redirect($PAGE->url,get_string('pushttsmodelaudio_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_QUESTIONS:
        $sql ="UPDATE {" . constants::M_QTABLE. "} qt INNER JOIN {" . constants::M_TABLE . "} rt ON rt.id=qt.readaloudid AND rt.masterinstance=0 AND rt.name= :name ";
        $sql .= " INNER JOIN {" . constants::M_QTABLE . "} qtoriginal ON qtoriginal.name = qt.name AND qtoriginal.readaloudid = :readaloudid ";
        $sql .= " SET qt.itemtext = qtoriginal.itemtext, ";
        $sql .= " qt.itemorder = qtoriginal.itemorder, ";
        $sql .= " qt.customtext1 = qtoriginal.customtext1, ";
        $sql .= " qt.customtext2 = qtoriginal.customtext2, ";
        $sql .= " qt.customtext3 = qtoriginal.customtext3, ";
        $sql .= " qt.customtext4 = qtoriginal.customtext4, ";
        $sql .= " qt.correctanswer = qtoriginal.correctanswer";

        $DB->execute($sql,array('name'=>$moduleinstance->name,'readaloudid'=>$moduleinstance->id));
        redirect($PAGE->url,get_string('pushquestions_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_ALTERNATIVES:
        $DB->set_field(constants::M_TABLE,'alternatives',$moduleinstance->alternatives,array('name'=>$moduleinstance->name,'masterinstance'=>0));
        redirect($PAGE->url,get_string('pushalternatives_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_TIMELIMIT:
        $DB->set_field(constants::M_TABLE,'timelimit',$moduleinstance->timelimit,array('name'=>$moduleinstance->name,'masterinstance'=>0));
        redirect($PAGE->url,get_string('pushtimelimit_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_TARGETWPM:
        $DB->set_field(constants::M_TABLE,'targetwpm',$moduleinstance->targetwpm,array('name'=>$moduleinstance->name,'masterinstance'=>0));
        redirect($PAGE->url,get_string('pushtargetwpm_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_MODES:
        $updatefields = ['enablepreview','enablelandr','enableshadow'];
        foreach($updatefields as $thefield) {
            $DB->set_field(constants::M_TABLE, $thefield, $moduleinstance->{$thefield}, array('name' => $moduleinstance->name, 'masterinstance' => 0));
        }
        redirect($PAGE->url,get_string('pushmodes_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_GRADESETTINGS:
        $updatefields = ['mingrade','machgrademethod','sessionscoremethod','gradeoptions'];
        foreach($updatefields as $thefield) {
            $DB->set_field(constants::M_TABLE, $thefield, $moduleinstance->{$thefield}, array('name' => $moduleinstance->name, 'masterinstance' => 0));
        }

        //this should work, but its turned off. I do not think we should mess with gradebook in this way since UI locks up if grades present
    // and it did not work for grademin grade (so not sure about point/scale/maxgrade ...)
/*
        $clones = $DB->get_records(constants::M_TABLE, array('name' => $moduleinstance->name, 'masterinstance' => 0));
        foreach($clones as $clone){
            readaloud_grade_item_update($clone);
        }
*/
        redirect($PAGE->url,get_string('pushgradesettings_done',constants::M_COMPONENT),10);
        break;

    case constants::M_PUSH_NONE:
    default:

}

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('course');
$mode = "push";

//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);


echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('pushpage', constants::M_COMPONENT));
if($moduleinstance->masterinstance){
    echo get_string('pushpage_explanation', constants::M_COMPONENT);
    echo $renderer->push_buttons_menu($cm);
}else{
    echo get_string('notmasterinstance', constants::M_COMPONENT);
}

echo $renderer->footer();
return;
