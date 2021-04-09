<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_solo\output;

use \mod_solo\constants;
use \mod_solo\utils;
use \mod_solo\attempthelper;

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

        if (has_capability('mod/solo:selecttopics', $context) || has_capability('mod/solo:viewreports', $context)) {


            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/solo/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }


        return $output;
    }



    /**
     * Show the introduction text is as set in the activity description
     */
    public function show_intro($solo,$cm){
        $ret = "";
        if (trim(strip_tags($solo->intro))) {
            $ret .= $this->output->box_start('mod_introbox');
            $ret .= format_module_intro(constants::M_MODNAME, $solo, $cm->id);
            $ret .= $this->output->box_end();
        }
        return $ret;
    }

    public function show_no_content($cm, $showsetup){
        $displaytext = $this->output->box_start();
        $displaytext .= $this->output->heading(get_string('nosetup', constants::M_COMPONENT), 3, 'main');
        if ($showsetup) {
            $displaytext .= \html_writer::div(get_string('letsaddsetup', constants::M_COMPONENT), '', array());
            $displaytext .= $this->output->single_button(new \moodle_url(constants::M_URL . '/setup.php',
                    array('id' => $cm->id)), get_string('addsetup', constants::M_COMPONENT));
        }else{
            $displaytext .= \html_writer::div(get_string('waitforsetup', constants::M_COMPONENT), '', array());
        }
        $displaytext .= $this->output->box_end();
        $ret= \html_writer::div($displaytext,constants::M_CLASS . '_nosetup_msg',array('id'=>constants::M_CLASS . '_nosetup_msg'));
        return $ret;

    }


    function fetch_activity_amd($cm, $moduleinstance){
        global $USER;
        //any html we want to return to be sent to the page
        $ret_html = '';

        //here we set up any info we need to pass into javascript

        $recopts =Array();
        //recorder html ids
        $recopts['recorderid'] = constants::M_RECORDERID;

        //items
        $attempt_helper =  new attempthelper($cm);
        $recopts['itemdata']= $attempt_helper->fetch_attempts_for_js();



        //this inits the M.mod_solo thingy, after the page has loaded.
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        //convert opts to json
        $jsonstring = json_encode($recopts);
        $widgetid = constants::M_RECORDERID . '_opts_9999';
        $opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetid, 'type' => 'hidden', 'value' => $jsonstring));

        //the recorder div
        $ret_html = $ret_html . $opts_html;

        $opts=array('cmid'=>$cm->id,'widgetid'=>$widgetid);
        $this->page->requires->js_call_amd("mod_solo/activitycontroller", 'init', array($opts));

        //these need to be returned and echo'ed to the page
        return $ret_html;
    }

    public function imagecombo(){

        $opts=array();
        $this->page->requires->js_call_amd("mod_solo/niceform", 'init', array($opts));
        $this->page->requires->css('/mod/solo/3rdparty/semantic/Semantic-UI-CSS-master/semantic.min.css');
    }

    public function developerpage($cmid,$moduleid){
        $items = array();
        $items[]= "<div>Generate random attempts from the last attempt in the table, 1 for each enrolled user</div>";
        $sb= new \single_button(
            new \moodle_url(constants::M_URL . '/developer.php', array('action' => 'generatedata', 'id' => $cmid, 'n' => $moduleid)),
            "Generate Attempt Data", 'get');
        $items[]=$this->render($sb);
        return $items;
    }

}