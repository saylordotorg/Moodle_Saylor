<?php

namespace mod_readaloud\output;

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

use \mod_readaloud\constants;

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_readaloud
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rsquestion_renderer extends \plugin_renderer_base {

 /**
 * Return HTML to display add first page links
 * @param lesson $lesson
 * @return string
 */
 public function add_edit_page_links($readaloud) {
		global $CFG;
        $itemid = 0;

        $output = $this->output->heading(get_string("whatdonow", constants::M_COMPONENT), 3);
        $links = array();
/*
		$addtextchoiceitemurl = new \moodle_url('/mod/readaloud/rsquestion/managersquestions.php',
			array('id'=>$this->page->cm->id, 'itemid'=>$itemid, 'type'=>constants::TYPE_TEXTPROMPT_LONG));
        $links[] = \html_writer::link($addtextchoiceitemurl, get_string('addtextpromptlongitem', constants::M_COMPONENT));
*/
     $addtextboxchoiceitemurl = new \moodle_url('/mod/readaloud/rsquestion/managersquestions.php',
         array('id'=>$this->page->cm->id, 'itemid'=>$itemid, 'type'=>constants::TYPE_TEXTPROMPT_SHORT));
        $links[] = \html_writer::link($addtextboxchoiceitemurl, get_string('addtextpromptshortitem', constants::M_COMPONENT));
/*
     $addaudioresponseitemurl = new \moodle_url('/mod/readaloud/rsquestion/managersquestions.php',
         array('id'=>$this->page->cm->id, 'itemid'=>$itemid, 'type'=>constants::TYPE_TEXTPROMPT_AUDIO));
     $links[] = \html_writer::link($addaudioresponseitemurl, get_string('addaudioresponseitem', constants::M_COMPONENT));
*/
        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }
	
	/**
	 * Return the html table of items
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_items_list($items,$readaloud,$cm){
	
		if(!$items){
			return $this->output->heading(get_string('noitems',constants::M_COMPONENT), 3, 'main');
		}
	
		$table = new \html_table();
		$table->id = constants::M_COMPONENT . '_qpanel';
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
            switch ($item->type) {

                case constants::TYPE_TEXTPROMPT_LONG:
                    $itemtype = get_string('textchoice', constants::M_COMPONENT);
                    break;

                case constants::TYPE_TEXTPROMPT_AUDIO:
                    $itemtype = get_string('audioresponse', constants::M_COMPONENT);
                    break;

                case constants::TYPE_TEXTPROMPT_SHORT:
                default:
                    $itemtype = get_string('textboxchoice', constants::M_COMPONENT);
            }
            $itemtypecell = new \html_table_cell($itemtype);

            $actionurl = '/mod/readaloud/rsquestion/managersquestions.php';
            $editurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id));
            $editlink = \html_writer::link($editurl, get_string('edititem', constants::M_COMPONENT));
            $editcell = new \html_table_cell($editlink);

            $movecell_content='';
            $spacer = '';
            if ($currentitem > 1) {
                $upurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id, 'action' => 'moveup'));
               // $uplink = \html_writer::link($upurl,  new pix_icon('t/up', get_string('up'), '', array('class' => 'iconsmall')));
                $uplink = $this->output->action_icon($upurl,new \pix_icon('t/up', get_string('up'), '', array('class' => 'iconsmall')));
                $movecell_content .= $uplink;
            }else{
                $movecell_content .= $spacer;
            }

            if ($currentitem < count($items)) {
                $downurl = new \moodle_url($actionurl, array('id' => $cm->id, 'itemid' => $item->id, 'action' => 'movedown'));
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