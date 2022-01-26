<?php

namespace mod_minilesson\output;

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

use \mod_minilesson\constants;

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_minilesson
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rsquestion_renderer extends \plugin_renderer_base {

 /**
 * Return HTML to display add first page links
 * @param lesson $lesson
 * @return string
 */
 public function add_edit_page_links($context, $tableid) {
		global $CFG;
        $itemid = 0;

        $output = $this->output->heading(get_string("whatdonow", "minilesson"), 3);
        $links = array();

        $qtypes = [constants::TYPE_PAGE,constants::TYPE_MULTICHOICE, constants::TYPE_DICTATIONCHAT,
                constants::TYPE_DICTATION,constants::TYPE_SPEECHCARDS, constants::TYPE_LISTENREPEAT];
        $qtypes[]= constants::TYPE_MULTIAUDIO;
        $qtypes[]=constants::TYPE_SHORTANSWER;
        if(isset($CFG->minilesson_experimental) &&$CFG->minilesson_experimental){
           $qtypes[]=constants::TYPE_SMARTFRAME;
        }
        foreach($qtypes as $qtype){
            $url=
            $data=['wwwroot' => $CFG->wwwroot, 'type'=>$qtype,'itemid'=>$itemid,'cmid'=>$this->page->cm->id,
                    'label'=>get_string('add' . $qtype . 'item', constants::M_COMPONENT)];
            $links[]= $this->render_from_template('mod_minilesson/additemlink', $data);
        }

     $usingajax=true;
     if($usingajax){
         $props=array('contextid'=>$context->id, 'tableid'=>$tableid);
         $this->page->requires->js_call_amd(constants::M_COMPONENT . '/rsquestionmanager', 'init', array($props));
     }

     return $this->output->box($output.implode("",$links), 'generalbox firstpageoptions mod_minilesson_link_box_container');

    }

    function setup_datatables($tableid){
        global $USER;

        $tableprops = array();
        $columns = array();
        //for cols .. .'itemname', 'itemtype', 'itemtags','timemodified', 'action'
        $columns[0]=array('orderable'=>false);
        $columns[1]=array('orderable'=>false);
        $columns[2]=array('orderable'=>false);
        $columns[3]=array('orderable'=>false);
        $columns[4]=array('orderable'=>false);
        $tableprops['columns']=$columns;

        //default ordering
        $order = array();
        $order[0] =array(0, "asc");
        $tableprops['order']=$order;

        //here we set up any info we need to pass into javascript
        $opts =Array();
        $opts['tableid']=$tableid;
        $opts['tableprops']=$tableprops;
        $this->page->requires->js_call_amd(constants::M_COMPONENT . "/datatables", 'init', array($opts));
        $this->page->requires->css( new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));
    }


    function show_noitems_message($itemsvisible){
        $message = $this->output->heading(get_string('noitems',constants::M_COMPONENT), 3, 'main');
        $displayvalue = $itemsvisible ? 'none' : 'block';
        $ret = \html_writer::div($message ,constants::M_NOITEMS_CONT,array('id'=>constants::M_NOITEMS_CONT,'style'=>'display: '.$displayvalue));
        return $ret;
    }

	/**
	 * Return the html table of items
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_items_list($items,$minilesson,$cm, $visible){

		//new code
        $data = [];
        $data['tableid']=constants::M_ITEMS_TABLE;
        $data['display'] = $visible ? 'block' : 'none';
        $items_array = [];
        foreach(array_values($items) as $i=>$item){
            $arrayitem = (Array)$item;
            $arrayitem['index']=($i+1);
            $arrayitem['typelabel']=get_string($arrayitem['type'],constants::M_COMPONENT);
            $items_array[]= $arrayitem;
        }
        $data['items']=$items_array;

        $up_pix = new \pix_icon('t/up', get_string('up'));
        $down_pix = new \pix_icon('t/down', get_string('down'));
        $data['up'] = $up_pix->export_for_pix();
        $data['down']=$down_pix->export_for_pix();


        return $this->render_from_template('mod_minilesson/itemlist', $data);

		//old code follows -  for reference only
        //_______________________________________________________
	
		$table = new \html_table();
		$table->id = 'mod_minilesson_qpanel';
		$table->head = array(
			get_string('itemname', constants::M_COMPONENT),
			get_string('itemtype', constants::M_COMPONENT),
			get_string('actions', constants::M_COMPONENT)
		);
		$table->headspan = array(1,1,3);
		$table->colclasses = array(
			'itemname', 'itemtype', 'order', 'edit','delete'
		);

		//sort by start date
		//core_collator::asort_objects_by_property($items,'timecreated',core_collator::SORT_NUMERIC);
		//core_collator::asort_objects_by_property($items,'name',core_collator::SORT_STRING);

		//loop through the items and add to table
        $currentitem=0;
		foreach ($items as $item) {
            $currentitem++;
            $row = new \html_table_row();


            $itemnamecell = new \html_table_cell($item->name);
            $itemtypename = get_string($item->type, constants::M_COMPONENT);
            $itemtypecell = new \html_table_cell($itemtypename);

            $actionurl = '/mod/minilesson/rsquestion/managersquestions.php';
            $editurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id));
            $editlink = \html_writer::link($editurl, get_string('edititem', constants::M_COMPONENT));
            $editcell = new \html_table_cell($editlink);

            $movecell_content='';
            $spacer = '';
            if ($currentitem > 1) {
                $upurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id, 'action' => 'up'));
               // $uplink = \html_writer::link($upurl,  new pix_icon('t/up', get_string('up'), '', array('class' => 'iconsmall')));
                $uplink = $this->output->action_icon($upurl,new \pix_icon('t/up', get_string('up'), '', array('class' => 'iconsmall')));
                $movecell_content .= $uplink;
            }else{
                $movecell_content .= $spacer;
            }

            if ($currentitem < count($items)) {
                $downurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id, 'action' => 'down'));
                //$downlink = \html_writer::link($downurl,  new pix_icon('t/down', get_string('down'), '', array('class' => 'iconsmall')));
                $downlink = $this->output->action_icon($downurl,new \pix_icon('t/down', get_string('down'), '', array('class' => 'iconsmall')));
                $movecell_content .= $downlink;
            }
            $movecell = new \html_table_cell($movecell_content);
		
			$deleteurl = new \moodle_url($actionurl, array('id'=>$cm->id,'itemid'=>$item->id,'action'=>'confirmdelete'));
			$deletelink = \html_writer::link($deleteurl, get_string('deleteitem', constants::M_COMPONENT));
			$deletecell = new \html_table_cell($deletelink);

			$row->cells = array(
				$itemnamecell, $itemtypecell, $movecell, $editcell, $deletecell
			);
			$table->data[] = $row;
		}

		return \html_writer::table($table);

	}
}