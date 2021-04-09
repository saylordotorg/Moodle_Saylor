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
 * Provides the main page for solo
 *
 * @package mod_solo
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/solo/lib.php');

use mod_solo\constants;
use mod_solo\utils;

$id = optional_param('id',0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // solo instance ID
$reattempt = optional_param('reattempt',0, PARAM_INT);

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

$attempthelper = new \mod_solo\attempthelper($cm);
$attempts = $attempthelper->fetch_attempts();

//mode is necessary for tabs
$mode='attempts';
//Set page url before require login, so post login will return here
$PAGE->set_url(constants::M_URL . '/view.php', array('id'=>$cm->id,'mode'=>$mode));
$PAGE->force_settings_menu(true);


//require login for this page
require_login($course, false, $cm);
$context = context_module::instance($cm->id);


$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$attempt_renderer = $PAGE->get_renderer(constants::M_COMPONENT,'attempt');


// We need view permission to be here
require_capability('mod/solo:view', $context);

//Get an admin settings
$config = get_config(constants::M_COMPONENT);

if($config->enablesetuptab && empty($moduleinstance->speakingtopic)){
    echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('attempts', constants::M_COMPONENT));
    if (has_capability('mod/solo:manage', $context)) {
        echo $renderer->show_no_content($cm,true);
    }else{
        echo $renderer->show_no_content($cm,false);
    }
    echo $renderer->footer();
    return;
}

//Do we do continue an attempt or start a new one
$start_or_continue=false;
if(count($attempts)==0){
    $start_or_continue=true;
    $nextstep = constants::STEP_USERSELECTIONS;
    $attemptid = 0;
} elseif($reattempt==1){
    $start_or_continue=true;
    $nextstep = constants::STEP_USERSELECTIONS;
    $attemptid = 0;
}else{
    $latestattempt = utils::fetch_latest_attempt($moduleinstance);
    if ($latestattempt && $latestattempt->completedsteps < constants::STEP_SELFTRANSCRIBE){
        $start_or_continue=true;
        $nextstep=$latestattempt->completedsteps+1;
        $attemptid=$latestattempt->id;
    }
}

//either redirect to a form handler for the attempt step, or show our attempt summary
if($start_or_continue) {
    $redirecturl = new moodle_url(constants::M_URL . '/attempt/manageattempts.php',
            array('id'=>$cm->id, 'attemptid' => $attemptid, 'type' => $nextstep));
    redirect($redirecturl);


}else{

    //if we need datatables we need to set that up before calling $renderer->header
    $tableid = '' . constants::M_CLASS_ITEMTABLE . '_' . '_opts_9999';
    $attempt_renderer->setup_datatables($tableid);

    $PAGE->navbar->add(get_string('attempts', constants::M_COMPONENT));

    echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('attempts', constants::M_COMPONENT));


    $attempt = utils::fetch_latest_finishedattempt($moduleinstance);
    $stats=false;
    if($attempt) {
        $requiresgrading = ($attempt->grade==0 && $attempt->manualgraded==0);
        //try to pull transcripts if we have none. Why wait for cron?
        $hastranscripts = !empty($attempt->jsontranscript);
        if(!$hastranscripts){
            $attempt_with_transcripts = utils::retrieve_transcripts_from_s3($attempt);
            $hastranscripts = $attempt_with_transcripts !==false;
            if($hastranscripts && !empty($attempt->selftranscript)){
                $autotranscript=$attempt_with_transcripts->transcript;
                $aitranscript = new \mod_solo\aitranscript($attempt_with_transcripts->id,
                        $context->id, $attempt->selftranscript,
                        $attempt_with_transcripts->transcript,
                        $attempt_with_transcripts->jsontranscript);
                $attempt = utils::fetch_latest_finishedattempt($moduleinstance);
            }
        }

        $stats=utils::fetch_stats($attempt,$moduleinstance);
        //if we have an ungraded activity, lets grade it
        if($requiresgrading) {
            utils::autograde_attempt($attempt->id, $stats);
        }


        $aidata = $DB->get_record(constants::M_AITABLE,array('attemptid'=>$attempt->id));


        echo $attempt_renderer->show_summary($moduleinstance,$attempt,$aidata, $stats);

        //open the summary results div
        echo html_writer::start_div('mod_solo_summaryresults');

        //show evaluation (auto or teacher)
        //necessary for M3.3
        require_once($CFG->libdir.'/gradelib.php');
        $gradinginfo = grade_get_grades($moduleinstance->course, 'mod', 'solo', $moduleinstance->id, $USER->id);
        if($attempt && !empty($gradinginfo ) && $attempt->grade !=null) {
            $feedback=$attempt->feedback;
            $starrating=true;
            if($attempt->manualgraded){
                $evaluator = get_string("teachereval", constants::M_COMPONENT);
                $rubricresults= utils::display_studentgrade($context,$moduleinstance,$attempt,$gradinginfo,$starrating);
            }else{
                $evaluator = get_string("autoeval", constants::M_COMPONENT);
                $rubricresults= utils::display_studentgrade($context,$moduleinstance,$attempt,$gradinginfo,$starrating );
            }
            echo $attempt_renderer->show_teachereval($rubricresults,$feedback,$evaluator);
            $autotranscriptready=true;
            echo $attempt_renderer->show_summarypassageandstats($attempt,$aidata, $stats,$autotranscriptready);

        }else if($attempt){
            echo $attempt_renderer->show_placeholdereval($attempt->id);
            $autotranscriptready=false;
            //we decided to make it real obvious if the reslt was not ready yet
            //echo $attempt_renderer->show_summarypassageandstats($attempt,$aidata, $stats,$autotranscriptready);
        }



        //myreports
       // echo $attempt_renderer->show_myreports($moduleinstance,$cm);

        //close the summary results div
        echo '</div>';
    }

    //all attempts by user table [good for debugging]
    // echo $attempt_renderer->show_attempts_list($attempts,$tableid,$cm);
    $tdata=new \stdClass();
    if((!$attempt->manualgraded && $moduleinstance->multiattempts) || has_capability('mod/solo:manageattempts', $context)){
        $reattempturl = new \moodle_url(constants::M_URL . '/view.php',
                array('id'=>$cm->id,'reattempt'=>1));
        $tdata->reattempturl=$reattempturl->out();
    }
    if($attempt) {

        if ($moduleinstance->postattemptedit || has_capability('mod/solo:manageattempts', $context)) {
            $postattemptediturl = new \moodle_url('/mod/solo/attempt/manageattempts.php',
                    array('id' => $cm->id, 'attemptid' => $attempt->id, 'type' => constants::STEP_SELFTRANSCRIBE));
            $tdata->postattemptediturl=$postattemptediturl->out();
        }
    }
    echo $renderer->render_from_template(constants::M_COMPONENT . '/postattemptbuttons', $tdata);
}
echo $renderer->footer();