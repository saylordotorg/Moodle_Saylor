<?php

namespace mod_solo\output;

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


defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;
use \mod_solo\utils;

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_solo
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class topic_renderer extends \plugin_renderer_base {

 /**
 * Return HTML to display add first page links
 * @param lesson $lesson
 * @return string
 */
 public function add_edit_page_links($solo) {
		global $CFG;
        $topicid = 0;

        $output = $this->output->heading(get_string("managetopics", "solo"), 3);
        $output .=  \html_writer::div(get_string('topicinstructions',constants::M_COMPONENT ),constants::M_COMPONENT .'_instructions');
        $links = array();

		$addurl = new \moodle_url(constants::M_URL . '/topic/managetopics.php',
			array('moduleid'=>$this->page->cm->instance, 'id'=>$topicid));
        $links[] = \html_writer::link($addurl,  get_string('addtopic', constants::M_COMPONENT),
            array('class'=>'btn ' . constants::M_COMPONENT .'_menubutton ' . constants::M_COMPONENT .'_activemenubutton'));



    $buttonsdiv = \html_writer::div(implode('', $links),constants::M_COMPONENT .'_mbuttons');
     return $this->output->box($output . $buttonsdiv, 'generalbox firstpageoptions');
    }
	
	/**
	 * Return the html table of topics
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_topics_list($topics,$tableid,$cm, $selectedtopics){
	
		if(!$topics){
			return $this->output->heading(get_string('notopics',constants::M_COMPONENT), 3, 'main');
		}

		//prepare AMD
        $opts=array('activityid'=>$cm->instance);
        $this->page->requires->js_call_amd("mod_solo/updateselectedtopic", 'init', array($opts));

        //prepare table with data
		$table = new \html_table();
		$table->id = $tableid;
		$table->attributes =array('class'=>constants::M_CLASS_TOPICSCONTAINER);


		$table->head = array(
             get_string('topicselected', constants::M_COMPONENT),
			get_string('topicname', constants::M_COMPONENT),
			get_string('topiclevel', constants::M_COMPONENT),
            get_string('topicicon', constants::M_COMPONENT),
            get_string('topictargetwords', constants::M_COMPONENT),
            get_string('timemodified', constants::M_COMPONENT),
			get_string('actions', constants::M_COMPONENT),
            ''
		);
		$table->headspan = array(1,1,1,1,1,1,2);
		$table->colclasses = array(
			'selected','topicname', 'topiclevel', 'topicicon', 'topictargetwords','timemodified', 'edit','delete'
		);


		//loop through the topics and add to table
        $currenttopic=0;
		foreach ($topics as $topic) {
            $currenttopic++;
            $row = new \html_table_row();

            //topic name
            $checked = $selectedtopics && array_key_exists($topic->id,$selectedtopics);
            $topicselectedcell = new \html_table_cell(\html_writer::checkbox('check_' . $topic->id,'checked',$checked,'',array('class'=>constants::M_CLASS_TOPICSCHECKBOX,'data-topicid'=>$topic->id)));

            //topic name
            $topicnamecell = new \html_table_cell($topic->name);

            //topic level
            switch($topic->topiclevel) {
                case constants::M_TOPICLEVEL_CUSTOM:
                    $topiclevel = get_string('topiclevelcustom',constants::M_COMPONENT);
                    break;
                case constants::M_TOPICLEVEL_COURSE:
                default:
                    $topiclevel = get_string('topiclevelcourse',constants::M_COMPONENT);
                    break;
            }
            $topiclevelcell = new \html_table_cell($topiclevel);

            //topic fonticon
            $fonticon = utils::fetch_fonticon($topic->fonticon);
            $topiciconcell = new \html_table_cell($fonticon);

            //topic targetwords
            $tdata=array();
            $tdata['targetwords']=explode(PHP_EOL,$topic->targetwords);
            $targetwords =  $this->output->render_from_template( constants::M_COMPONENT . '/targetwords', $tdata);
            $topictargetwordscell = new \html_table_cell($targetwords);

            //modify date
            $datecell_content = date("Y-m-d H:i:s",$topic->timemodified);
            $topicdatecell = new \html_table_cell($datecell_content);

            //topic edit

            $actionurl = '/mod/solo/topic/managetopics.php';
            $editurl = new \moodle_url($actionurl, array('moduleid' => $topic->moduleid, 'id' => $topic->id));
            //if its not this module we will be going elsewhere...
            if($cm->instance==$topic->moduleid) {
                $editlink = \html_writer::link($editurl, get_string('edittopic', constants::M_COMPONENT));
            }else{
                $editlink = \html_writer::link($editurl, get_string('leaveedittopic', constants::M_COMPONENT));
            }
            $editcell = new \html_table_cell($editlink);

		    //topic delete
            if($cm->instance==$topic->moduleid) {
                $deleteurl = new \moodle_url($actionurl,
                        array('moduleid' => $cm->instance, 'id' => $topic->id, 'action' => 'confirmdelete'));
                $deletelink = \html_writer::link($deleteurl, get_string('deletetopic', constants::M_COMPONENT));
            }else{
                $deletelink='';
            }
			$deletecell = new \html_table_cell($deletelink);

			$row->cells = array(
                    $topicselectedcell, $topicnamecell, $topiclevelcell,$topiciconcell, $topictargetwordscell,$topicdatecell, $editcell, $deletecell
			);
			$table->data[] = $row;
		}
		return \html_writer::table($table);

	}

    function setup_datatables($tableid){
        global $USER;

        $tableprops = array();
        $notorderable = array('orderable'=>false);
        $columns = [$notorderable,null,null,$notorderable,$notorderable,null,$notorderable,$notorderable];
        $tableprops['columns']=$columns;

        //default ordering
        $order = array();
        $order[0] =array(1, "asc");
        $tableprops['order']=$order;

        //here we set up any info we need to pass into javascript
        $opts =Array();
        $opts['tableid']=$tableid;
        $opts['tableprops']=$tableprops;
        $this->page->requires->js_call_amd("mod_solo/datatables", 'init', array($opts));
        $this->page->requires->css( new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));
    }
}