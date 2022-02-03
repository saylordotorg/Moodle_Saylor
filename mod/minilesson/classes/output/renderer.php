<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_minilesson\output;

use html_writer;
use \mod_minilesson\constants;
use \mod_minilesson\utils;
use \mod_minilesson\comprehensiontest;

class renderer extends \plugin_renderer_base {

    /**
     * Returns the header for the module
     *
     * @param mod $instance
     * @param string $currenttab current tab that is shown.
     * @param int    $item id of the anything that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($moduleinstance, $cm, $currenttab = '', $itemid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = \context_module::instance($cm->id);

        /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();

        //show (or not) title
        $output .= $this->fetch_title($moduleinstance, $activityname);

        if (has_capability('mod/minilesson:evaluate', $context)) {
            //   $output .= $this->output->heading_with_help($activityname, 'overview', constants::M_COMPONENT);

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/minilesson/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        }


        return $output;
    }

    public function fetch_title($moduleinstance, $title){
        $displaytext='';
        //dont show the heading in an iframe, it will be outside this anyway
        if(!$moduleinstance->foriframe) {
            $thetitle = $this->output->heading($title, 3, 'main');
            $displaytext = \html_writer::div($thetitle, constants::M_CLASS . '_center');
        }
        return $displaytext;
    }

    /**
     * Returns the header for the module
     *
     * @param mod $instance
     * @param string $currenttab current tab that is shown.
     * @param int    $item id of the anything that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function simpleheader($moduleinstance, $cm, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = \context_module::instance($cm->id);

        /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();

        //show (or not) title
        $output .= $this->fetch_title($moduleinstance, $activityname);

        return $output;
    }

    /**
     * Return HTML to display limited header
     */
    public function notabsheader($moduleinstance){
        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        $output = $this->output->header();
        //show (or not) title
        $output .= $this->fetch_title($moduleinstance, $activityname);

        return $output;
    }
    /**
     * Return a message that something is not right
     */
    public function thatsnotright($message){

       $ret = $this->output->heading(get_string('thatsnotright',constants::M_COMPONENT),3);
       $ret .= \html_writer::div($message,constants::M_CLASS  . '_thatsnotright_message');
        return $ret;
    }

    public function backtotopbutton($courseid){

        $button = $this->output->single_button(new \moodle_url( '/course/view.php',
            array('id'=>$courseid)),get_string('backtotop',constants::M_COMPONENT));

        $ret = \html_writer::div($button ,constants::M_CLASS  . '_backtotop_cont');
        return $ret;
    }


    /**
     *
     */
    public function reattemptbutton($moduleinstance){

        $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/view.php',
            array('n'=>$moduleinstance->id,'retake'=>1)),get_string('reattempt',constants::M_COMPONENT));

        $ret = \html_writer::div($button ,constants::M_CLASS  . '_afterattempt_cont');
        return $ret;

    }

    /**
     *
     */
    public function show_wheretonext($moduleinstance){

        $nextactivity = utils::fetch_next_activity($moduleinstance->activitylink);
        //show activity link if we are up to it
        if ($nextactivity->url) {
            $button= $this->output->single_button($nextactivity->url,$nextactivity->label);
        //else lets show a back to top link
        }else {
            $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/view.php',
                array('n' => $moduleinstance->id)), get_string('backtotop', constants::M_COMPONENT));
        }
        $ret = \html_writer::div($button ,constants::M_WHERETONEXT_CONTAINER);
        return $ret;

    }



    /**
     *
     */
    public function exceededattempts($moduleinstance){
        $message = get_string("exceededattempts",constants::M_COMPONENT,$moduleinstance->maxattempts);
        $ret = \html_writer::div($message ,constants::M_CLASS  . '_afterattempt_cont');
        return $ret;

    }

    public function show_ungradedyet(){
        $message = get_string("notgradedyet",constants::M_COMPONENT);
        $ret = \html_writer::div($message ,constants::M_CLASS  . '_ungraded_cont');
        return $ret;
    }


    /**
     *  Show instructions/welcome
     */
    public function show_welcome($showtext, $showtitle) {
        $thetitle =  $this->output->heading($showtitle, 3, 'main');
        $displaytext =  \html_writer::div($thetitle ,constants::M_CLASS  . '_center');
        $displaytext .= $this->output->box_start();
        $displaytext .= \html_writer::div($showtext ,constants::M_CLASS  . '_center');
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_INSTRUCTIONS_CONTAINER,array('id'=>constants::M_INSTRUCTIONS_CONTAINER));
        return $ret;
    }

    /**
     * Show the introduction text is as set in the activity description
     */
    public function show_intro($minilesson,$cm) {
        $ret = "";
        if (trim(strip_tags($minilesson->intro))) {
            $ret .= $this->output->box_start('mod_introbox');
            $ret .= format_module_intro('minilesson', $minilesson, $cm->id);
            $ret .= $this->output->box_end();
        }
        return $ret;
    }

    /**
     * Show error (but when?)
     */
    public function show_no_items($cm,$showadditemlinks) {
        $displaytext = $this->output->box_start();
        $displaytext .= $this->output->heading(get_string('noitems', constants::M_COMPONENT), 3, 'main');
        if ($showadditemlinks) {
            $displaytext .= \html_writer::div(get_string('letsadditems', constants::M_COMPONENT), '', array());
            $displaytext .= $this->output->single_button(new \moodle_url(constants::M_URL . '/rsquestion/rsquestions.php',
                array('id' => $cm->id)), get_string('additems', constants::M_COMPONENT));
        }
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_NOITEMS_MSG,array('id'=>constants::M_NOITEMS_MSG));
        return $ret;
    }

    /**
     *  Finished View
     */
    public function show_finished_results($comp_test, $latestattempt, $canattempt){
        global $CFG;

        //quiz data
        $quizdata = $comp_test->fetch_test_data_for_js();
        //config
        $config = get_config(constants::M_COMPONENT);

        //steps data
        $steps = json_decode($latestattempt->sessiondata)->steps;

        //prepare results fopr display
        $results = array_filter($steps, function($step){return $step->hasgrade;});
        $useresults=[];
        foreach($results as $result){
            if(isset($quizdata[$result->index]->title)) {
                $result->title = $quizdata[$result->index]->title;
            }else{
                $result->title = get_string($quizdata[$result->index]->type,constants::M_COMPONENT);
            }
            $result->index++;
            $useresults[]=$result;
        }

        //output results and back to course button
        $tdata=new \stdClass();
        $tdata->total = $latestattempt->sessionscore;
        $tdata->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $latestattempt->courseid;
        $tdata->results=$useresults;
        //output reattempt button
        if($canattempt){
            $reattempturl = new \moodle_url( constants::M_URL . '/view.php',
                    array('n'=>$latestattempt->moduleid, 'retake'=>1));
            $tdata->reattempturl = $reattempturl->out();
        }
        //show back to course button if we are not in a tab
        if(!$config->enablesetuptab) {
            $tdata->backtocourse = true;
        }

        $finishedcontents = $this->render_from_template(constants::M_COMPONENT . '/quizfinished', $tdata);

        //put it all in a div and return it
        $finisheddiv = \html_writer::div($finishedcontents ,constants::M_QUIZ_FINISHED,
                array('id'=>constants::M_QUIZ_FINISHED,'style'=>'display: block'));


        return  $finisheddiv;
    }

    /**
     *  Show quiz container
     */
    public function show_quiz($comp_test){

        //quiz data
        $quizdata = $comp_test->fetch_test_data_for_js();
        $itemshtml=[];
        foreach($quizdata as $item){
           $itemshtml[] = $this->render_from_template(constants::M_COMPONENT . '/' . $item->type, $item);
           // $this->page->requires->js_call_amd(constants::M_COMPONENT . '/' . $item->type, 'init', array($item));
        }

        $finisheddiv = \html_writer::div("" ,constants::M_QUIZ_FINISHED,
            array('id'=>constants::M_QUIZ_FINISHED));
      
        $quizdiv = \html_writer::div($finisheddiv.implode('',$itemshtml) ,constants::M_QUIZ_CONTAINER,
            array('id'=>constants::M_QUIZ_CONTAINER));
      
        $ret = $quizdiv;
        return $ret;
    }

    /**
     *  Show quiz container
     */
    public function show_quiz_preview($comp_test, $qid){

        //quiz data
        $quizdata = $comp_test->fetch_test_data_for_js();
        $itemshtml=[];
        foreach($quizdata as $item) {
            if ($item->id == $qid) {
                $itemshtml[] = $this->render_from_template(constants::M_COMPONENT . '/' . $item->type, $item);
            }
        }

        $quizdiv = \html_writer::div(implode('',$itemshtml) ,constants::M_QUIZ_CONTAINER,
                array('id'=>constants::M_QUIZ_CONTAINER));

        $ret = $quizdiv;
        return $ret;
    }

    /**
     *  Show a progress circle overlay while uploading
     */
    public function show_progress($minilesson,$cm){
        $hider =  \html_writer::div('',constants::M_HIDER,array('id'=>constants::M_HIDER));
        $message =  \html_writer::tag('h4',get_string('processing',constants::M_COMPONENT),array());
        $spinner =  \html_writer::tag('i','',array('class'=>'fa fa-spinner fa-5x fa-spin'));
        $progressdiv = \html_writer::div($message . $spinner ,constants::M_PROGRESS_CONTAINER,
            array('id'=>constants::M_PROGRESS_CONTAINER));
        $ret = $hider . $progressdiv;
        return $ret;
    }

    /**
     * Show the feedback set in the activity settings
     */
    public function show_feedback($minilesson,$showtitle){
        $thetitle =  $this->output->heading($showtitle, 3, 'main');
        $displaytext =  \html_writer::div($thetitle ,constants::M_CLASS  . '_center');
        $displaytext .= $this->output->box_start();
        $displaytext .=  \html_writer::div($minilesson->feedback,constants::M_CLASS  . '_center');
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_FEEDBACK_CONTAINER,array('id'=>constants::M_FEEDBACK_CONTAINER));
        return $ret;
    }

    /**
     * Show the feedback set in the activity settings
     */
    public function show_title_postattempt($minilesson,$showtitle){
        $thetitle =  $this->output->heading($showtitle, 3, 'main');
        $displaytext =  \html_writer::div($thetitle ,constants::M_CLASS  . '_center');
        $ret= \html_writer::div($displaytext,constants::M_FEEDBACK_CONTAINER . ' ' . constants::M_POSTATTEMPT,array('id'=>constants::M_FEEDBACK_CONTAINER));
        return $ret;
    }

    /**
     * Show error (but when?)
     */
    public function show_error($minilesson,$cm){
        $displaytext = $this->output->box_start();
        $displaytext .= $this->output->heading(get_string('errorheader',constants::M_COMPONENT), 3, 'main');
        $displaytext .=  \html_writer::div(get_string('uploadconverterror',constants::M_COMPONENT),'',array());
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_ERROR_CONTAINER,array('id'=>constants::M_ERROR_CONTAINER));
        return $ret;
    }


    function fetch_activity_amd($cm, $moduleinstance,$previewquestionid=0,$canreattempt=false){
        global $CFG, $USER;
        //any html we want to return to be sent to the page
        $ret_html = '';

        //here we set up any info we need to pass into javascript

        $recopts =Array();
        //recorder html ids
        $recopts['recorderid'] = constants::M_RECORDERID;
        $recopts['recordingcontainer'] = constants::M_RECORDING_CONTAINER;
        $recopts['recordercontainer'] = constants::M_RECORDER_CONTAINER;

        //activity html ids
        $recopts['passagecontainer'] = constants::M_PASSAGE_CONTAINER;
        $recopts['instructionscontainer'] = constants::M_INSTRUCTIONS_CONTAINER;
        $recopts['recordbuttoncontainer'] =constants::M_RECORD_BUTTON_CONTAINER;
        $recopts['startbuttoncontainer'] =constants::M_START_BUTTON_CONTAINER;
        $recopts['hider']=constants::M_HIDER;
        $recopts['progresscontainer'] = constants::M_PROGRESS_CONTAINER;
        $recopts['feedbackcontainer'] = constants::M_FEEDBACK_CONTAINER;
        $recopts['wheretonextcontainer'] = constants::M_WHERETONEXT_CONTAINER;
        $recopts['quizcontainer'] = constants::M_QUIZ_CONTAINER;
        $recopts['errorcontainer'] = constants::M_ERROR_CONTAINER;

        //first confirm we are authorised before we try to get the token
        $config = get_config(constants::M_COMPONENT);
        if(empty($config->apiuser) || empty($config->apisecret)){
            $errormessage = get_string('nocredentials',constants::M_COMPONENT,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return $this->show_problembox($errormessage);
        }else {
            //fetch token
            $token = utils::fetch_token($config->apiuser,$config->apisecret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                return $this->show_problembox($errormessage);
            }
        }
        $recopts['token']=$token;
        $recopts['owner']=hash('md5',$USER->username);
        $recopts['region']=$moduleinstance->region;
        $recopts['ttslanguage']=$moduleinstance->ttslanguage;
        $recopts['ds_only']=$moduleinstance->transcriber==constants::TRANSCRIBER_POODLL;


        $recopts['courseurl']=$CFG->wwwroot . '/course/view.php?id=' . $moduleinstance->course ;
        $recopts['reattempturl']='';
        if($canreattempt) {
            $reattempturl = new \moodle_url(constants::M_URL . '/view.php',
                    array('n' => $moduleinstance->id, 'retake' => 1));
            $recopts['reattempturl']=$reattempturl->out();
        }
        //show back to course button if we are not in an iframe
        if(!$config->enablesetuptab) {
            $recopts['backtocourse'] = true;
        }else{
            $recopts['backtocourse'] = '';
        }



        //quiz data
        $comp_test =  new comprehensiontest($cm);
        $quizdata =$comp_test->fetch_test_data_for_js();
        if($previewquestionid){
           foreach($quizdata as $item){
               if($item->id==$previewquestionid){
                   $item->preview=true;
                   $recopts['quizdata'] = [$item];
                   break;
               }
           }
        }else {
            $recopts['quizdata'] = $quizdata;
        }



        //this inits the M.mod_minilesson thingy, after the page has loaded.
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        //convert opts to json
        $jsonstring = json_encode($recopts);
        $widgetid = constants::M_RECORDERID . '_opts_9999';
        $opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetid, 'type' => 'hidden', 'value' => $jsonstring));

        //the recorder div
        $ret_html = $ret_html . $opts_html;

        $opts=array('cmid'=>$cm->id,'widgetid'=>$widgetid);
        $this->page->requires->js_call_amd("mod_minilesson/activitycontroller", 'init', array($opts));


        //these need to be returned and echo'ed to the page
        return $ret_html;
    }

    /**
     * Return HTML to display message about problem
     */
    public function show_problembox($msg) {
        $output = '';
        $output .= $this->output->box_start(constants::M_COMPONENT . '_problembox');
        $output .= $this->notification($msg, 'warning');
        $output .= $this->output->box_end();
        return $output;
    }

}
