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
 * Setup Tab for Poodll wordcards
 *
 * @package    mod_wordcards
 * @copyright  2020 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/wordcards/mod_form.php');

use mod_wordcards\constants;
use mod_wordcards\utils;


global $DB;


// Course module ID.
$id = optional_param('id',0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // wordcards instance ID

// Course and course module data.
if ($id) {
    $cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_MODNAME, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
    $id = $cm->id;
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$modulecontext = context_module::instance($cm->id);
require_capability('mod/wordcards:manage', $modulecontext);

// Set page login data.
$PAGE->set_url(constants::M_URL . '/setup.php',array('id'=>$id));
require_login($course, true, $cm);


// Set page meta data.
$pagetitle = format_string($moduleinstance->name);
$PAGE->set_title($pagetitle);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

//Get an admin settings
$config = get_config(constants::M_COMPONENT);
if($config->enablesetuptab){
    $PAGE->set_pagelayout('popup');
}else{
    $PAGE->set_pagelayout('course');
}

// Render template and display page.
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);

$mform = new \mod_wordcards\setupform(null,['context'=>$modulecontext]);

$redirecturl = new moodle_url('/mod/wordcards/view.php', array('id'=>$cm->id));
//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}else if ($data = $mform->get_data()) {
    $data->timemodified = time();
    $data->id = $data->n;
    $data->coursemodule = $cm->id;

    $data->timemodified = time();
    $data->id = $data->n;

    if (empty($data->skipreview)) {
        $data->skipreview = 0;
    }

    //now update the db once we have saved files and stuff
    if ($DB->update_record(constants::M_TABLE, $data)) {
        //Process the hashcode and lang model if it makes sense
        $themod = mod_wordcards_module::get_by_modid($data->id);
        $themod->set_region_passagehash();

        redirect($redirecturl);
        exit;
    }
}

//if we got here we is loading up dat form
$moduleinstance->n =$moduleinstance->id;
$mod = mod_wordcards_module::get_by_cmid($cm->id);
$data =(array)$moduleinstance;
$data = utils::prepare_file_and_json_stuff($data,$modulecontext);
$mform->set_data($data);

echo $renderer->header($moduleinstance, $cm, "setup");
echo $renderer->heading($pagetitle);
echo $renderer->navigation($mod, "setup");
$mform->display();
echo $renderer->footer();
