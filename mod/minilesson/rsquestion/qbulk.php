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
 * A utility for updating all lang models (speech recognition) in bulk
 *
 * @package mod_minilesson
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/minilesson/lib.php');

use \mod_minilesson\constants;
/*
 * READ ME
 * When this page runs it will seek to update all the lang models
 *
 * Use this only if change region (diff lang model server  in each region) or there are not any langmodels for some reason
 * It waits for 7s after each update so as not to crush the poor lang model server. So it could take ages
 *
 */

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('minilesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
//$minilesson = new minilesson($DB->get_record('minilesson', array('id' => $cm->instance), '*', MUST_EXIST));
$minilesson = $DB->get_record('minilesson', array('id' => $cm->instance), '*', MUST_EXIST);

//mode is necessary for tabs
$mode='rsquestions';
//Set page url before require login, so post login will return here
$PAGE->set_url('/mod/minilesson/rsquestion/rsquestions.php', array('id'=>$cm->id,'mode'=>$mode));

//require login for this page
require_login($course, false, $cm);
$context = context_module::instance($cm->id);

$mis = $DB->get_records('minilesson');
$updates = 0;
foreach($mis as $moduleinstance){
    $items = $DB->get_records(constants:: M_QTABLE,array('minilesson'=>$moduleinstance->id));
    foreach($items as $olditem) {
        $newitem = new \stdClass();
        //most of the sentences for speech recognition are in customtext1, so we use that to generate lang model
        $newitem->customtext1 = $olditem->customtext1;
        $newitem->customtext2 = $olditem->customtext2;
        $newitem->customtext3 = $olditem->customtext3;
        $newitem->customtext4 = $olditem->customtext4;
        $newitem->type = $olditem->type;
        $passagehash = \mod_minilesson\local\rsquestion\helper::update_create_langmodel($moduleinstance, $olditem, $newitem);
        if(!empty($passagehash)){
            $DB->update_record(constants::M_QTABLE,array('id'=>$olditem->id,'passagehash'=>$passagehash));
            $updates++;
            sleep(7);
        }
    }
}

$renderer = $PAGE->get_renderer('mod_minilesson');


$PAGE->navbar->add(get_string('rsquestions', 'minilesson'));
echo $renderer->header($minilesson, $cm, $mode, null, get_string('rsquestions', 'minilesson'));

echo '<h2> Updates' . $updates . '</h2>';

echo $renderer->footer();
