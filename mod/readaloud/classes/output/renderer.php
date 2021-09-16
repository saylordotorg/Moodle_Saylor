<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_readaloud\output;

use \mod_readaloud\constants;
use \mod_readaloud\utils;

class renderer extends \plugin_renderer_base {

    /**
     * Returns the header for the module
     *
     * @param mod $instance
     * @param string $currenttab current tab that is shown.
     * @param int $item id of the anything that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($moduleinstance, $cm, $currenttab = '', $itemid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname . ": " . $activityname;
        } else {
            $title = $this->page->course->shortname . ": " . $activityname . ": " . $extrapagetitle;
        }

        // Build the buttons
        $context = \context_module::instance($cm->id);

        /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();
        if(!$moduleinstance->foriframe) {
            $thetitle = $this->output->heading($activityname, 3, 'main');
            $displaytext = \html_writer::div($thetitle, constants::M_CLASS . '_center');
            $output .= $displaytext;

        }


        if (has_capability('mod/readaloud:viewreports', $context)) {
            //   $output .= $this->output->heading_with_help($activityname, 'overview', constants::M_COMPONENT);

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot . '/mod/readaloud/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        }

        return $output;
    }


    public function show_no_content($cm, $showsetup){
        $displaytext = $this->output->box_start();
        $displaytext .= $this->output->heading(get_string('nopassage', constants::M_COMPONENT), 3, 'main');
        if ($showsetup) {
            $displaytext .= \html_writer::div(get_string('letsaddpassage', constants::M_COMPONENT), '', array());
            $displaytext .= $this->output->single_button(new \moodle_url(constants::M_URL . '/setup.php',
                    array('id' => $cm->id)), get_string('addpassage', constants::M_COMPONENT));
        }else{
            $displaytext .= \html_writer::div(get_string('waitforpassage', constants::M_COMPONENT), '', array());
        }
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_CLASS . '_nopassage_msg',array('id'=>constants::M_CLASS . '_nopassage_msg'));
        return $ret;

    }

    public function show_attempt_summary($attemptsummary,$showgrades){

        //set up our table
        $tableattributes = array('class' => 'generaltable ' . constants::M_CLASS . '_table');

        $htmltable = new \html_table();
        $tableid = \html_writer::random_id(constants::M_COMPONENT);
        $htmltable->id = $tableid;
        $htmltable->attributes = $tableattributes;

        $head = array('');
        $head[]= get_string('wpm', constants::M_COMPONENT);
        $head[]= get_string('accuracy_p', constants::M_COMPONENT);
        if($showgrades) {
            $head[] = get_string('grade_p', constants::M_COMPONENT);
        }

        $htmltable->head = $head;
        $htr = new \html_table_row();
        $cell = new \html_table_cell(get_string('averages', constants::M_COMPONENT));
        // $cell->attributes = array('class' => constants::M_CLASS . '_cell_passageindex');
        $htr->cells[] = $cell;

        $cell = new \html_table_cell( $attemptsummary->av_wpm);
       // $cell->attributes = array('class' => constants::M_CLASS . '_cell_passageindex');
        $htr->cells[] = $cell;

        $cell = new \html_table_cell( $attemptsummary->av_accuracy);
        $htr->cells[] = $cell;

        if($showgrades) {
            $cell = new \html_table_cell($attemptsummary->av_sessionscore);
            $htr->cells[] = $cell;
        }

        $htmltable->data[] = $htr;

        $htr = new \html_table_row();
        $cell = new \html_table_cell(get_string('highest', constants::M_COMPONENT));
        $htr->cells[] = $cell;
        $cell = new \html_table_cell( $attemptsummary->h_wpm);
        // $cell->attributes = array('class' => constants::M_CLASS . '_cell_passageindex');
        $htr->cells[] = $cell;

        $cell = new \html_table_cell( $attemptsummary->h_accuracy);
        $htr->cells[] = $cell;

        if($showgrades) {
            $cell = new \html_table_cell($attemptsummary->h_sessionscore);
            $htr->cells[] = $cell;
        }

        $htmltable->data[] = $htr;


        $tabletitle = get_string("myattemptssummary", constants::M_COMPONENT, $attemptsummary->totalattempts);
        $htmltitle = $this->output->heading($tabletitle, 5);
        $html = \html_writer::div($htmltitle, constants::M_CLASS . '_center');
        $html .= \html_writer::div(get_string("summaryexplainer", constants::M_COMPONENT),
                constants::M_CLASS . '_center');
        $thetable = \html_writer::table($htmltable);
        $html .= \html_writer::div($thetable, constants::M_CLASS . '_attemptsummarytable');

        return  \html_writer::div($html, constants::M_CLASS . '_attemptsummary');


    }

    public function show_progress_chart($chartdata,$showgrades){
        global $CFG;
        //if no chart data or lower than Moodle 3.2 we do not shopw the chart
        if(!$chartdata || $CFG->version < 2016120500 ){return '';}

        $chart = new \core\chart_line();
        $chart->add_series($chartdata->wpmseries);
        $chart->add_series($chartdata->accuracyseries);
        if($showgrades) {
            $chart->add_series($chartdata->sessionscoreseries);
        }
        $chart->set_labels($chartdata->labelsdata);
        $renderedchart= $this->output->render($chart);


        $htmltitle = $this->output->heading(get_string("progresschart", constants::M_COMPONENT), 5);
        $html = \html_writer::div($htmltitle, constants::M_CLASS . '_center ' . constants::M_CLASS . '_progressheader');
        $html .= \html_writer::div(get_string("chartexplainer", constants::M_COMPONENT),
                constants::M_CLASS . '_center');
        $html .= \html_writer::div($renderedchart,
                constants::M_CLASS . '_center ' . constants::M_CLASS . '_progresschart');


        return $html;
    }
  
    public function show_stopandplay($moduleinstance){
      $ret = "<div id='".constants::M_STOPANDPLAY."'>";
      $ret .= "<button id='".constants::M_PLAY_BTN."' style='margin:10px;width:40%;float:left;' class='btn btn-secondary'><i class='fa fa-play'></i> ".get_string("playbutton", constants::M_COMPONENT)."</button>";
      $ret .= "<button id='".constants::M_STOP_BTN."' style='margin:10px;width:40%;float:right;' class='btn btn-secondary'><i class='fa fa-stop'></i> ".get_string("stopbutton", constants::M_COMPONENT)."</button>";
      $ret .= "</div>";
      
      return $ret; 
    }
  
    public function show_menubuttons ($moduleinstance, $canattempt) {
      
      global $CFG;

      $hasaudiobreaks = !empty($moduleinstance->modelaudiobreaks);

        $data=[];
        //are we previewing?
        if(!$moduleinstance->enablepreview) {
            $data['nopreview'] = 1;
        }

        //do we have audio breaks ?
        if(!$hasaudiobreaks) {
            $data['noaudiobreaks'] = 1;
        }
        //is listen and repeat enabled?
        if(!$moduleinstance->enablelandr) {
            $data['nolandr'] = 1;
        }
        //is shadow enabled
        if(!$moduleinstance->enableshadow) {
            $data['noshadow'] = 1;
        }
        //can we attempt this activity
        if(!$canattempt) {
            $data['cantattempt'] = 1;
        }
        //finally render template and return
        return $this->render_from_template('mod_readaloud/bigbuttonmenu', $data);

    }


    /*
     * Show a small summary of the activity
     */
    public function show_smallreport ($moduleinstance, $attempt=false, $aigrade=false) {
        global $CFG;

        //template data for small report
        $tdata = Array();
        $tdata['src']='';
        //filename
        if($attempt && $attempt->filename){
            $tdata['src']= $attempt->filename;
        }

        //star rating
        if($attempt) {
            $rating = utils::fetch_rating($attempt, $aigrade); // 0,1,2,3,4 or 5
            $ready = $rating > -1;
            $stars=[];
            for ($star = 0; $star < 5; $star++) {
                $stars[] = $rating > $star ? 'fa-star' : 'fa-star-o';
            }
            $tdata['stars']=$stars;
        }else{
            $ready = false;
        }
        if($ready) {
            $tdata['ready']=true;
        }

        //full report button
        $fullreportbutton = $this->output->single_button(new \moodle_url(constants::M_URL . '/view.php',
                array('n' => $moduleinstance->id, 'reviewattempts' => 1)), get_string('fullreport', constants::M_COMPONENT));
        $tdata['fullreportbutton']=$fullreportbutton;

        //finally render template
        $ret = $this->render_from_template('mod_readaloud/smallreport', $tdata);


        //If there is no remote transcriber
        //we do not want to get users hopes up by trying to fetch a transcript with ajax
        switch($moduleinstance->transcriber){
            case constants::TRANSCRIBER_AMAZONSTREAMING:
            case constants::TRANSCRIBER_NONE:
                $remotetranscribe = false;
                break;
            default:
                $remotetranscribe = true;
        }


        //Js to refresh small report
        $opts = Array();
        $opts['filename'] = $attempt->filename;
        $opts['attemptid'] = $attempt ? $attempt->id : false;
        $opts['ready'] = $ready;
        $opts['remotetranscribe'] = $remotetranscribe;
        $this->page->requires->js_call_amd(constants::M_COMPONENT . "/smallreporthelper", 'init', array($opts));
        $this->page->requires->strings_for_js(['secs_till_check','notgradedyet','evaluatedmessage', 'checking'],constants::M_COMPONENT);

        return $ret;
    }

    public function show_returntomenu_button(){
        $returnbutton =  \html_writer::tag('button', "<i class='fa fa-arrow-left'></i> ".get_string("returnmenu", constants::M_COMPONENT),
                array('class'=>constants::M_CLASS . '_center btn-block btn btn-secondary ' . constants::M_RETURNMENU,'type'=>'button','style'=>'display: none','id'=>constants::M_RETURNMENU));
        return $returnbutton;
    }

    /**
     *  NO LONGER USED
     */
    public function reattemptbutton($moduleinstance) {

        $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/view.php',
                array('n' => $moduleinstance->id, 'retake' => 1)), get_string('reattempt', constants::M_COMPONENT));

        $ret = \html_writer::div($button, constants::M_CLASS . '_afterattempt_cont');
        return $ret;

    }

    /**
     *
     */
    public function jump_tomenubutton($moduleinstance) {

        $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/view.php',
                array('n' => $moduleinstance->id, 'reviewattempts' => 0)), get_string('returntomenu', constants::M_COMPONENT));

        $ret = \html_writer::div($button, constants::M_CLASS . '_afterattempt_cont');
        return $ret;

    }

    /**
     *
     */
    public function show_wheretonext($moduleinstance) {

        $nextactivity = utils::fetch_next_activity($moduleinstance->activitylink);
        //show activity link if we are up to it
        $buttons=[];

        //back to menu button
        $buttons[] =  \html_writer::link(new \moodle_url(constants::M_URL . '/view.php',
                array('n' => $moduleinstance->id)), get_string("backtotop", constants::M_COMPONENT),
                array('class'=>constants::M_CLASS . '_center btn btn-secondary ' . constants::M_BACKTOTOP,'id'=>constants::M_BACKTOTOP));

        //next activity button
        if ($nextactivity->url) {
            $buttons[]= $this->output->single_button($nextactivity->url, $nextactivity->label);
        }

        $ret = \html_writer::div(implode('<br><br>',$buttons), constants::M_WHERETONEXT_CONTAINER);
        return $ret;

    }

    /**
     *
     */
    public function show_machineregradeallbutton($moduleinstance) {
        $options = [];
        $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/gradesadmin.php',
                array('n' => $moduleinstance->id, 'action' => 'machineregradeall')),
                get_string('machineregradeall', constants::M_COMPONENT), 'post', $options);

        $ret = \html_writer::div($button, constants::M_GRADESADMIN_CONTAINER);
        return $ret;
    }

    /**
     *
     */
    public function show_pushmachinegrades($moduleinstance) {

        $sectiontitle = get_string("pushmachinegrades", constants::M_COMPONENT);
        $heading = $this->output->heading($sectiontitle, 4);

        if (utils::can_transcribe($moduleinstance) &&
                ($moduleinstance->machgrademethod == constants::MACHINEGRADE_HYBRID ||
                $moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY)) {
            $options = [];
        } else {
            $options = array('disabled' => 'disabled');
        }
        $button = $this->output->single_button(new \moodle_url(constants::M_URL . '/gradesadmin.php',
                array('n' => $moduleinstance->id, 'action' => 'pushmachinegrades')),
                get_string('pushmachinegrades', constants::M_COMPONENT), 'post', $options);

        $ret = \html_writer::div($heading . $button, constants::M_GRADESADMIN_CONTAINER);
        return $ret;
    }

    /**
     * @param array an array of mistranscription objects (passageindex, passageword, mistranscription summary)
     * @return string an html table
     */
    public function show_all_mistranscriptions($items) {

        global $CFG;

        //set up our table
        $tableattributes = array('class' => 'generaltable ' . constants::M_CLASS . '_table');

        $htmltable = new \html_table();
        $tableid = \html_writer::random_id(constants::M_COMPONENT);
        $htmltable->id = $tableid;
        $htmltable->attributes = $tableattributes;

        $head = array(get_string('passageindex', constants::M_COMPONENT),
                get_string('passageword', constants::M_COMPONENT),
                get_string('mistrans_count', constants::M_COMPONENT),
                get_string('mistranscriptions', constants::M_COMPONENT));

        $htmltable->head = $head;
        $rowcount = 0;
        $total_mistranscriptions = 0;
        foreach ($items as $row) {
            //if this was not a mistranscription, skip
            if (!$row->mistranscriptions) {
                continue;
            }
            $rowcount++;
            $htr = new \html_table_row();

            $cell = new \html_table_cell($row->passageindex);
            $cell->attributes = array('class' => constants::M_CLASS . '_cell_passageindex');
            $htr->cells[] = $cell;

            $cell = new \html_table_cell($row->passageword);
            $cell->attributes = array('class' => constants::M_CLASS . '_cell_passageword');
            $htr->cells[] = $cell;

            $showmistranscriptions = "";
            $mistrans_count = 0;
            foreach ($row->mistranscriptions as $badword => $count) {
                if ($showmistranscriptions != "") {
                    $showmistranscriptions .= " | ";
                }
                $showmistranscriptions .= $badword . "(" . $count . ")";
                $mistrans_count += $count;
            }
            $total_mistranscriptions += $mistrans_count;

            $cell = new \html_table_cell($mistrans_count);
            $cell->attributes = array('class' => constants::M_CLASS . '_cell_mistrans_count');
            $htr->cells[] = $cell;

            $cell = new \html_table_cell($showmistranscriptions);
            $cell->attributes = array('class' => constants::M_CLASS . '_cell_mistranscriptions');
            $htr->cells[] = $cell;

            $htmltable->data[] = $htr;
        }
        $tabletitle = get_string("mistranscriptions_summary", constants::M_COMPONENT);
        $html = $this->output->heading($tabletitle, 4);
        if ($rowcount == 0) {
            $html .= get_string("nomistranscriptions", constants::M_COMPONENT);
        } else {
            $html .= \html_writer::tag('span', get_string("total_mistranscriptions",
                    constants::M_COMPONENT, $total_mistranscriptions),
                    array('class' => constants::M_CLASS . '_totalmistranscriptions'));
            $html .= \html_writer::table($htmltable);

            //set up datatables
            $tableprops = new \stdClass();
            $opts = Array();
            $opts['tableid'] = $tableid;
            $opts['tableprops'] = $tableprops;
            $this->page->requires->js_call_amd(constants::M_COMPONENT . "/datatables", 'init', array($opts));
            $this->page->requires->css(new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));

        }
        return $html;
    }

    //fetch modal container
    function fetch_modalcontainer($title,$content,$containertag){
        $data=[];
        $data['title']=$title;
        $data['content']=$content;
        $data['containertag']=$containertag;
        return $this->render_from_template('mod_readaloud/modalcontainer', $data);
    }


    public function show_landr($moduleinstance, $token){
        global $CFG, $USER;
        //recorder modal
        $title = get_string('landrreading',constants::M_COMPONENT);
        //are we going to force streaning transcription from AWS only if its android
        $hints = new \stdClass();
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(stripos($ua,'android') !== false) {
            $hints->streamingtranscriber = 'aws';
        }
        $string_hints = base64_encode(json_encode($hints));

        //the original poodll pushrecorder
        $data=array( 'data-id' => 'readaloud_pushrecorder',
                        'data-parent' => $CFG->wwwroot,
                        'data-localloading' => 'auto',
                        'data-localloader' => '/mod/readaloud/poodllloader.html',
                        'data-media' => "audio",
                        'data-language' => $moduleinstance->ttslanguage,
                        'data-region' => $moduleinstance->region,
                        'data-owner' => hash('md5',$USER->username),
                        'data-hints' => $string_hints,
                        'data-token' => $token);

        //the TT recorder
        $data['waveheight']= 75;
        $data['maxtime']= 15000;
        //passagehash if not empty will be region|hash eg tokyo|2353531453415134545
        //but we only send the hash up so we strip the region
        $data['passagehash']="";
        if(!empty($moduleinstance->passagehash)){
            $hashbits = explode('|',$moduleinstance->passagehash);
            if(count($hashbits)==2){
                $data['passagehash']  = $hashbits[1];
            }
        }

        //fetch lang services url
        $data['asrurl'] = utils::fetch_lang_server_url($moduleinstance->region,'transcribe');

        $content =  $this->render_from_template('mod_readaloud/listenandrepeat', $data);
        $containertag = 'landr_container';
        $amodalcontainer = $this->fetch_modalcontainer($title,$content,$containertag);
        return $amodalcontainer;
    }

    /**
     *
     */
    public function show_currenterrorestimate($errorestimate) {
        $message = get_string("currenterrorestimate", constants::M_COMPONENT, $errorestimate);
        $ret = \html_writer::div($message, constants::M_GRADESADMIN_CONTAINER);
        return $ret;

    }

    public function show_ungradedyet() {
        $message = get_string("notgradedyet", constants::M_COMPONENT);
        $ret = \html_writer::div($message, constants::M_CLASS . '_ungraded_cont');
        return $ret;
    }

    /**
     *  Show grades admin heading
     */
    public function show_gradesadmin_heading($showtitle, $showinstructions) {
        $thetitle = $this->output->heading($showtitle, 3, 'main');
        $displaytext = \html_writer::div($thetitle, constants::M_CLASS . '_center');
        $displaytext .= $this->output->box_start();
        $displaytext .= \html_writer::div($showinstructions, constants::M_CLASS . '_center');
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext);
        return $ret;
    }

    /**
     *  Show instructions/welcome
     */
    public function show_instructions($showtext) {
        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div($showtext,
                constants::M_CLASS . '_center ' . constants::M_INSTRUCTIONS);
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_ACTIVITYINSTRUCTIONS_CONTAINER,
                array('id' => constants::M_ACTIVITYINSTRUCTIONS_CONTAINER));
        return $ret;
    }
    /**
     *  Show instructions/welcome
     */
    public function show_previewinstructions($showtext) {
        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div($showtext,
                constants::M_CLASS . '_center ' . constants::M_PREVIEWINSTRUCTIONS);
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_PREVIEWINSTRUCTIONS_CONTAINER,
                array('id' => constants::M_PREVIEWINSTRUCTIONS_CONTAINER));
        return $ret;
    }

    /**
     *  Show listen and repeat instructions
     */
    public function show_landrinstructions($showtext) {
        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div($showtext,
                constants::M_CLASS . '_center ' . constants::M_LANDRINSTRUCTIONS);
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_LANDRINSTRUCTIONS_CONTAINER,
                array('id' => constants::M_LANDRINSTRUCTIONS_CONTAINER));
        return $ret;
    }

    /**
     *  Show instructions/welcome
     */
    public function show_welcome_menu() {
        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div(get_string('welcomemenu', constants::M_COMPONENT),
                constants::M_CLASS . '_center ' . constants::M_INSTRUCTIONS);
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_MENUINSTRUCTIONS_CONTAINER,
                array('id' => constants::M_MENUINSTRUCTIONS_CONTAINER));
        return $ret;
    }

    /**
     * Show the introduction text is as set in the activity description
     */
    public function show_intro($readaloud, $cm) {
        $ret = "";
        if (trim(strip_tags($readaloud->intro))) {
            $ret .= $this->output->box_start(constants::M_INTRO_CONTAINER . ' ' . constants::M_CLASS . '_center ');
            $ret .= format_module_intro('readaloud', $readaloud, $cm->id);
            $ret .= $this->output->box_end();
        }
        return $ret;
    }

    /**
     * Show the reading passage after the attempt, basically set it to display on load and give it a background color
     */
    public function show_passage_postattempt($readaloud, $collapsespaces=false) {
        $ret = "";
        $displaypassage = utils::lines_to_brs($readaloud->passage);

        //for some languages we do not want spaces. Japanese, Chinese. For now this is manual
        //TODO auto determine when to use collapsespaces
        $collapsespaces = $collapsespaces ? ' reviewmode collapsespaces' : '';

        $ret .= \html_writer::div($displaypassage, constants::M_PASSAGE_CONTAINER . ' '
                . constants::M_POSTATTEMPT . $collapsespaces,
                array('id' => constants::M_PASSAGE_CONTAINER));
        return $ret;
    }

    public function render_hiddenaudioplayer($audiourl=false) {
        $src = $audiourl? $audiourl : '';
        $audioplayer = \html_writer::tag('audio', '',
                array('src' => $src, 'id' => constants::M_HIDDEN_PLAYER, 'class' => constants::M_HIDDEN_PLAYER));
        return $audioplayer;
    }


    /**
     * Show the reading passage
     */
    public function show_passage($readaloud, $cm) {

        $ret = "";
        $displaypassage = utils::lines_to_brs($readaloud->passage);
        $ret .= \html_writer::div($displaypassage, constants::M_PASSAGE_CONTAINER,
                array('id' => constants::M_PASSAGE_CONTAINER));
        return $ret;
    }

    /**
     *  Show a progress circle overlay while uploading
     */
    public function show_progress($readaloud, $cm) {
        $hider = \html_writer::div('', constants::M_HIDER, array('id' => constants::M_HIDER));
        $message = \html_writer::tag('h4', get_string('processing', constants::M_COMPONENT), array());
        $spinner = \html_writer::tag('i', '', array('class' => 'fa fa-spinner fa-5x fa-spin'));
        $progressdiv = \html_writer::div($message . $spinner, constants::M_PROGRESS_CONTAINER,
                array('id' => constants::M_PROGRESS_CONTAINER));
        $ret = $hider . $progressdiv;
        return $ret;
    }

    public function show_evaluated_message() {
        $displaytext = get_string('evaluatedmessage', constants::M_COMPONENT);
        $ret = \html_writer::div($displaytext, constants::M_EVALUATED_MESSAGE. ' ' . constants::M_CLASS . '_center', array('id' => constants::M_EVALUATED_MESSAGE));
        return $ret;
    }

    /**
     * Show the feedback set in the activity settings
     */
    public function show_feedback($readaloud) {
        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div($readaloud->feedback, constants::M_CLASS . '_center');
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_FEEDBACK_CONTAINER, array('id' => constants::M_FEEDBACK_CONTAINER));
        return $ret;
    }

    /**
     * Show the feedback set in the activity settings
     */
    public function show_feedback_postattempt($readaloud) {

        $displaytext = $this->output->box_start();
        $displaytext .= \html_writer::div($readaloud->feedback, constants::M_CLASS . '_center');
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_FEEDBACK_CONTAINER . ' ' . constants::M_POSTATTEMPT,
                array('id' => constants::M_FEEDBACK_CONTAINER));
        return $ret;
    }

    /**
     * Show error (but when?)
     */
    public function show_error($readaloud, $cm) {
        $displaytext = $this->output->box_start();
        $displaytext .= $this->output->heading(get_string('errorheader', constants::M_COMPONENT), 3, 'main');
        $displaytext .= \html_writer::div(get_string('uploadconverterror', constants::M_COMPONENT), '', array());
        $displaytext .= $this->output->box_end();
        $ret = \html_writer::div($displaytext, constants::M_ERROR_CONTAINER, array('id' => constants::M_ERROR_CONTAINER));
        return $ret;
    }

    /**
     * The html part of the recorder (js is in the fetch_activity_amd)
     */
    public function show_recorder($moduleinstance, $token, $debug = false) {
        global $CFG,$USER;

        //recorder
        //=======================================
        $hints = new \stdClass();
        $hints->allowearlyexit = $moduleinstance->allowearlyexit;

        //perhaps we want to force stereoaudio
        if ($moduleinstance->transcriber == constants::TRANSCRIBER_GOOGLECLOUDSPEECH ||
                $moduleinstance->submitrawaudio) {
            $hints->encoder = 'stereoaudio';
        }


        $can_transcribe = \mod_readaloud\utils::can_transcribe($moduleinstance);

        //We no longer want to use AWS streaming transcription.
        switch ($moduleinstance->transcriber){
            case constants::TRANSCRIBER_AMAZONSTREAMING :
                $moduleinstance->transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
                //this flag tells AWS not to send to amazon transcribe
               // $transcribe = "0";
               // $hints->streamingtranscriber = 'aws';
               // $speechevents = '1';
               // break;
            case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
            case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
            case constants::TRANSCRIBER_NONE:
            default:
                $transcribe = $can_transcribe ? $moduleinstance->transcriber : "0";
                $speechevents="0";
        }

        //we encode any hints
        $string_hints = base64_encode(json_encode($hints));
        //get passage hash as key for transcription vocab
        //we sneakily add "[region]|" when we save passage hash .. so if user changes region ..we re-generate lang model
        $transcribevocab = 'none';
        if(!empty($moduleinstance->passagehash) && !$moduleinstance->stricttranscribe){
            $hashbits = explode('|',$moduleinstance->passagehash);
            if(count($hashbits)==2){
                $transcribevocab = $hashbits[1];
            }else{
                //in the early days there was no region prefix, so we just use the passagehash as is
                $transcribevocab = $moduleinstance->passagehash;
            }
        }


        $recorderdiv = \html_writer::div('', constants::M_CLASS . '_center',
                array('id' => constants::M_RECORDERID,
                        'data-id' => constants::M_RECORDERID,
                        'data-parent' => $CFG->wwwroot,
                        'data-localloading' => 'auto',
                        'data-localloader' => '/mod/readaloud/poodllloader.html',
                        'data-media' => "audio",
                        'data-appid' => constants::M_COMPONENT,
                        'data-owner' => hash('md5',$USER->username),
                        'data-type' => $debug ? "upload" : $moduleinstance->recorder,
                        'data-width' => $debug ? "500" : "360",
                        'data-height' => $debug ? "500" : "210",
                    //'data-iframeclass'=>"letsberesponsive",
                        'data-updatecontrol' => constants::M_UPDATE_CONTROL,
                        'data-timelimit' => $moduleinstance->timelimit,
                        'data-transcode' => "1",
                        'data-transcribe' => $transcribe,
                        'data-language' => $moduleinstance->ttslanguage,
                        'data-expiredays' => $moduleinstance->expiredays,
                        'data-region' => $moduleinstance->region,
                        'data-fallback' => 'warning',
                        'data-speechevents' => $speechevents,
                        'data-hints' => $string_hints,
                        'data-token' => $token, //localhost
                        'data-transcribevocab' => $transcribevocab
                    //'data-token'=>"643eba92a1447ac0c6a882c85051461a" //cloudpoodll
                )
        );
        $containerdiv = \html_writer::div($recorderdiv, constants::M_RECORDER_CONTAINER . " " . constants::M_CLASS . '_center',
                array('id' => constants::M_RECORDER_CONTAINER));
        //=======================================

        $recordingdiv = \html_writer::div($containerdiv, constants::M_RECORDING_CONTAINER);

        //prepare output
        $ret = "";
        $ret .= $recordingdiv;
        //return it
        return $ret;
    }

    function fetch_activity_amd($cm, $moduleinstance,$token) {
        global $CFG,$USER;
        //any html we want to return to be sent to the page
        $ret_html = '';

        //here we set up any info we need to pass into javascript

        $recopts = Array();
        //recorder html ids
        $recopts['recorderid'] = constants::M_RECORDERID;
        $recopts['recordingcontainer'] = constants::M_RECORDING_CONTAINER;
        $recopts['recordercontainer'] = constants::M_RECORDER_CONTAINER;

        //activity html ids
        $recopts['passagecontainer'] = constants::M_PASSAGE_CONTAINER;
        $recopts['instructionscontainer'] = constants::M_INSTRUCTIONS_CONTAINER;
        $recopts['recordbuttoncontainer'] = constants::M_RECORD_BUTTON_CONTAINER;
        $recopts['startbuttoncontainer'] = constants::M_START_BUTTON_CONTAINER;
        $recopts['hider'] = constants::M_HIDER;
        $recopts['progresscontainer'] = constants::M_PROGRESS_CONTAINER;
        $recopts['feedbackcontainer'] = constants::M_FEEDBACK_CONTAINER;
        $recopts['wheretonextcontainer'] = constants::M_WHERETONEXT_CONTAINER;
        $recopts['errorcontainer'] = constants::M_ERROR_CONTAINER;
        $recopts['menubuttonscontainer'] = constants::M_MENUBUTTONS_CONTAINER;
        $recopts['menuinstructionscontainer'] = constants::M_MENUINSTRUCTIONS_CONTAINER;
        $recopts['activityinstructionscontainer'] = constants::M_ACTIVITYINSTRUCTIONS_CONTAINER;
        $recopts['previewinstructionscontainer'] = constants::M_PREVIEWINSTRUCTIONS_CONTAINER;
        $recopts['landrinstructionscontainer'] = constants::M_LANDRINSTRUCTIONS_CONTAINER;
        $recopts['smallreportcontainer'] = constants::M_SMALLREPORT_CONTAINER;
        $recopts['modelaudioplayer'] = constants::M_MODELAUDIO_PLAYER;
        $recopts['enablelandr'] = $moduleinstance->enablelandr ? true : false;
        $recopts['ds_only'] = false; //if false, chrome will use its own speech rec. if true chrome wont be used (server may yet decide not to use DS)
        $recopts['enablepreview'] = $moduleinstance->enablepreview ? true : false;
        $recopts['enableshadow'] = $moduleinstance->enableshadow ? true : false;
        $recopts['allowearlyexit'] = $moduleinstance->allowearlyexit ? true : false;
        $recopts['breaks'] = $moduleinstance->modelaudiobreaks;

        $recopts['audioplayerclass'] = constants::M_MODELAUDIO_PLAYER;
        $recopts['startlandrbutton'] = constants::M_STARTLANDR;
        $recopts['startpreviewbutton'] = constants::M_STARTPREVIEW;
        $recopts['startreadingbutton'] = constants::M_STARTNOSHADOW;
        $recopts['startshadowbutton'] = constants::M_STARTSHADOW;
        $recopts['returnmenubutton'] = constants::M_RETURNMENU;
        $recopts['stopandplay'] = constants::M_STOPANDPLAY;
        $recopts['stopbutton'] = constants::M_STOP_BTN;
        $recopts['playbutton'] = constants::M_PLAY_BTN;

        $recopts['phonetics'] = '';
        if($moduleinstance->phonetic && !empty($moduleinstance->phonetic)) {
            $recopts['phonetics'] = explode(' ',$moduleinstance->phonetic);
        }
      
        //streaming transcriber: we do not want to use it anymore.
        if($moduleinstance->transcriber == constants::TRANSCRIBER_AMAZONSTREAMING){
            $moduleinstance->transcriber=constants::TRANSCRIBER_AMAZONTRANSCRIBE;
        }
        $recopts['transcriber']=$moduleinstance->transcriber;
        $recopts['language']=$moduleinstance->ttslanguage;
        $recopts['region']= $moduleinstance->region;
        $recopts['token']=$token;
        $recopts['parent']=$CFG->wwwroot;
        $recopts['owner']=hash('md5',$USER->username);
        $recopts['appid']=constants::M_COMPONENT;
        $recopts['expiretime']=300;//max expire time is 300 seconds


        //we need an update control tp hold the recorded filename, and one for draft item id
        $ret_html = $ret_html . \html_writer::tag('input', '', array('id' => constants::M_UPDATE_CONTROL, 'type' => 'hidden'));

        //this inits the M.mod_readaloud thingy, after the page has loaded.
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        //convert opts to json
        $jsonstring = json_encode($recopts);
        $widgetid = constants::M_RECORDERID . '_opts_9999';
        $opts_html =
                \html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetid, 'type' => 'hidden', 'value' => $jsonstring));

        //the recorder div
        $ret_html = $ret_html . $opts_html;

        $opts = array('cmid' => $cm->id, 'widgetid' => $widgetid);
        $this->page->requires->js_call_amd("mod_readaloud/activitycontroller", 'init', array($opts));
        $this->page->requires->strings_for_js(array('gotnosound', 'done', 'beginreading'), constants::M_COMPONENT);

        //these need to be returned and echo'ed to the page
        return $ret_html;
    }

    function fetch_clicktohear_amd($moduleinstance,$token) {
        global $USER;
        //any html we want to return to be sent to the page
        $ret_html = "";
        $opts = array('token'=>$token,'owner' => hash('md5',$USER->username),
                'region' => $moduleinstance->region, 'ttsvoice'=>$moduleinstance->ttsvoice);
        $this->page->requires->js_call_amd("mod_readaloud/clicktohear", 'init', array($opts));

        //these need to be returned and echo'ed to the page
        return "";
    }

    function fetch_clicktohear($moduleinstance,$token) {
        //any html we want to return to be sent to the page
        $ret_html = $this->render_hiddenaudioplayer();
        $ret_html .= $this->fetch_clicktohear_amd($moduleinstance,$token);
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