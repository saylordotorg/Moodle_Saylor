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
 * Provides the interface for overall managing of topics
 *
 * @package mod_solo
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/solo/lib.php');

use mod_solo\constants;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$solo = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

$topichelper = new \mod_solo\topichelper($cm);
$topics = $topichelper->fetch_topics();
$selectedtopics = $topichelper->fetch_selected_topics();

//mode is necessary for tabs
$mode='topics';
//Set page url before require login, so post login will return here
$PAGE->set_url('/mod/solo/topic/topics.php', array('id'=>$cm->id,'mode'=>$mode));

//require login for this page
require_login($course, false, $cm);
$context = context_module::instance($cm->id);

$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$topic_renderer = $PAGE->get_renderer(constants::M_COMPONENT,'topic');

//prepare datatable(before header printed)
$topictableid = '' . constants::M_TOPIC_TABLE . '_' . '_opts_9999';
$topic_renderer->setup_datatables($topictableid);

$PAGE->navbar->add(get_string('topics', constants::M_COMPONENT));
echo $renderer->header($solo, $cm, $mode, null, get_string('topics', constants::M_COMPONENT));


// We need view permission to be here
require_capability('mod/solo:selecttopics', $context);
if (has_capability('mod/solo:managetopics', $context)){
    echo $topic_renderer->add_edit_page_links($solo);
}


//if we have topics, show em
if($topics){
	echo $topic_renderer->show_topics_list($topics,$topictableid,$cm, $selectedtopics);
}
echo $renderer->footer();
