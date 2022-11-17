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
 * @package		Mb2 Content
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2018 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/

defined('MOODLE_INTERNAL') || die;
 

 
class block_mb2content_edit_form extends block_edit_form 
{
 
 
 
 
	protected function specific_definition($mform) 
	{
 
       
	   	global $CFG, $PAGE;
		
		
		$PAGE->requires->jquery();
		//$PAGE->requires->css('/blocks/mb2content/assets/spectrum/spectrum.css');
	   	//$PAGE->requires->js('/blocks/mb2content/assets/spectrum/spectrum.js');
	   	$PAGE->requires->js('/blocks/mb2content/scripts/admin.js'); 
		
		
		// Arrays for select form fields
		$ctypeArr = array(
			'announcement' => get_string('announcement','block_mb2content'),
			//'blog' => get_string('blog','block_mb2content'),
			'course' => get_string('course','block_mb2content'),
			'category' => get_string('category','block_mb2content'),
			'event' => get_string('event','block_mb2content')
		);
		
		
		$styleArr = array(
			'none' => get_string('none','block_mb2content'),
			'cols' => get_string('cols','block_mb2content'),
			'slidercols' => get_string('slidercols','block_mb2content'),
			'imgli' => get_string('imgli','block_mb2content'),
			'ticker' => get_string('ticker','block_mb2content')
		);
		
		
		$gutterArr = array(
			'default' => get_string('default','block_mb2content'),
			'small' => get_string('small','block_mb2content'),
			'none' => get_string('none','block_mb2content')
		);	
		
		
		$lfArr = array(
			'left' => get_string('left','block_mb2content'),
			'right' => get_string('right','block_mb2content')
		);
		
		$exclCatArr = array(
			'exclude' => get_string('exclude','block_mb2content'),
			'include' => get_string('include','block_mb2content')
		);		
		
		$yesNoArr = array(
			'1' => get_string('yes','block_mb2content'),
			'0' => get_string('no','block_mb2content')
		);
		
		$currencyArr = array('ALL:4c,65,6b'=>'ALL','AFN:60b'=>'AFN','ARS:24'=>'ARS','AWG:192'=>'AWG','AUD:24'=>'AUD','AZN:43c,430,43d'=>'AZN','BSD:24'=>'BSD','BBD:24'=>'BBD','BYR:70,2e'=>'BYR','BZD:42,5a,24'=>'BZD','BMD:24'=>'BMD','BOB:24,62'=>'BOB','BAM:4b,4d'=>'BAM','BWP:50'=>'BWP','BGN:43b,432'=>'BGN','BRL:52,24'=>'BRL','BND:24'=>'BND','KHR:17db'=>'KHR','CAD:24'=>'CAD','KYD:24'=>'KYD','CLP:24'=>'CLP','CNY:a5'=>'CNY','COP:24'=>'COP','CRC:20a1'=>'CRC','HRK:6b,6e'=>'HRK','CUP:20b1'=>'CUP','CZK:4b,10d'=>'CZK','DKK:6b,72'=>'DKK','DOP:52,44,24'=>'DOP','XCD:24'=>'XCD','EGP:a3'=>'EGP','SVC:24'=>'SVC','EEK:6b,72'=>'EEK','EUR:20ac'=>'EUR','FKP:a3'=>'FKP','FJD:24'=>'FJD','GHC:a2'=>'GHC','GIP:a3'=>'GIP','GTQ:51'=>'GTQ','GGP:a3'=>'GGP','GYD:24'=>'GYD','HNL:4c'=>'HNL','HKD:24'=>'HKD','HUF:46,74'=>'HUF','ISK:6b,72'=>'ISK','INR:20a8'=>'INR','IDR:52,70'=>'IDR','IRR:fdfc'=>'IRR','IMP:a3'=>'IMP','ILS:20aa'=>'ILS','JMD:4a,24'=>'JMD','JPY:a5'=>'JPY','JEP:a3'=>'JEP','KZT:43b,432'=>'KZT','KES:4b,73,68,73'=>'KES','KGS:43b,432'=>'KGS','LAK:20ad'=>'LAK','LVL:4c,73'=>'LVL','LBP:a3'=>'LBP','LRD:24'=>'LRD','LTL:4c,74'=>'LTL','MKD:434,435,43d'=>'MKD','MYR:52,4d'=>'MYR','MUR:20a8'=>'MUR','MXN:24'=>'MXN','MNT:20ae'=>'MNT','MZN:4d,54'=>'MZN','NAD:24'=>'NAD','NPR:20a8'=>'NPR','ANG:192'=>'ANG','NZD:24'=>'NZD','NIO:43,24'=>'NIO','NGN:20a6'=>'NGN','KPW:20a9'=>'KPW','NOK:6b,72'=>'NOK','OMR:fdfc'=>'OMR','PKR:20a8'=>'PKR','PAB:42,2f,2e'=>'PAB','PYG:47,73'=>'PYG','PEN:53,2f,2e'=>'PEN','PHP:50,68,70'=>'PHP','PLN:7a,142'=>'PLN','QAR:fdfc'=>'QAR','RON:6c,65,69'=>'RON','RUB:440,443,431'=>'RUB','SHP:a3'=>'SHP','SAR:fdfc'=>'SAR','RSD:414,438,43d,2e'=>'RSD','SCR:20a8'=>'SCR','SGD:24'=>'SGD','SBD:24'=>'SBD','SOS:53'=>'SOS','ZAR:52'=>'ZAR','KRW:20a9'=>'KRW','LKR:20a8'=>'LKR','SEK:6b,72'=>'SEK','CHF:43,48,46'=>'CHF','SRD:24'=>'SRD','SYP:a3'=>'SYP','TWD:4e,54,24'=>'TWD','THB:e3f'=>'THB','TTD:54,54,24'=>'TTD','TRY:54,4c'=>'TRY','TRL:20a4'=>'TRL','TVD:24'=>'TVD','UAH:20b4'=>'UAH','GBP:a3'=>'GBP','USD:24'=>'USD','UYU:24,55'=>'UYU','UZS:43b,432'=>'UZS','VEF:42,73'=>'VEF','VND:20ab'=>'VND','YER:fdfc'=>'YER','ZWD:5a,24'=>'ZWD');
		
		
		// Form elements
		$sepAttr = ' class="mb2form-separator" style="height:1px;border-top:solid 1px #e5e5e5;margin:30px 0;"';
		
		
		
		// General options
		$mform->addElement('header', 'config_generaloptions', get_string('generaloptions', 'block_mb2content'));
		
	   
	   	$mform->addElement('text', 'config_title', get_string('configtitle', 'block_mb2content'));
		$mform->setType('config_title', PARAM_TEXT);		
				
		
		$mform->addElement('text', 'config_customcls', get_string('customcls','block_mb2content'));
        $mform->setType('config_customcls', PARAM_TEXT);
		
		
		$mform->addElement('text', 'config_margin', get_string('margin','block_mb2content'));
		$mform->addHelpButton('config_margin', 'margin', 'block_mb2content');
        $mform->setType('config_margin', PARAM_TEXT);
		
		
		$mform->addElement('text', 'config_langtag', get_string('langtag','block_mb2content'));
		$mform->addHelpButton('config_langtag', 'langtag', 'block_mb2content');
        $mform->setType('config_langtag', PARAM_TEXT);
		
		
		$mform->addElement('textarea', 'config_textbefore', get_string('textbefore','block_mb2content'));
		$mform->setType('config_textbefore', PARAM_TEXT);	
		
		
		$mform->addElement('textarea', 'config_textafter', get_string('textafter','block_mb2content'));
		$mform->setType('config_textafter', PARAM_TEXT);	
		
		
		$mform->addElement('textarea', 'config_colors', get_string('colors','block_mb2content'));
		$mform->addHelpButton('config_colors', 'colors', 'block_mb2content');
		$mform->setType('config_colors', PARAM_TEXT);	
	   
	   
	   
	   	
		// Content type options
		$mform->addElement('header', 'config_contentoptions', get_string('contentoptions', 'block_mb2content'));
		
		   	
		$mform->addElement('select', 'config_ctype', get_string('ctype', 'block_mb2content'), $ctypeArr);
				
		
		// If content source is category
		$ifCats = array('data-showon3'=>'config_ctype', 'data-showonval'=>'category', 'data-showonval2'=>'course');		
		$mform->addElement('textarea', 'config_catids', get_string('catids','block_mb2content'),$ifCats);
		$mform->addHelpButton('config_catids', 'catids', 'block_mb2content');
		$mform->setType('config_catids', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_excats', get_string('excats', 'block_mb2content'), $exclCatArr, $ifCats);
		$mform->setDefault('config_excats', 'exclude');	
		
		
		$ifCourses = array('data-showon'=>'config_ctype', 'data-showonval'=>'course');	
		$mform->addElement('textarea', 'config_courseids', get_string('courseids','block_mb2content'),$ifCourses);
		$mform->addHelpButton('config_courseids', 'courseids', 'block_mb2content');
		$mform->setType('config_courseids', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_excourses', get_string('excourses', 'block_mb2content'), $exclCatArr, $ifCourses);
		$mform->setDefault('config_excourses', 'exclude');		
		
		
		$mform->addElement('text', 'config_limit', get_string('limit','block_mb2content'));
		$mform->setDefault('config_limit', 7);
        $mform->setType('config_limit', PARAM_INT);
		
		
		$ifCalendar = array('data-showon'=>'config_ctype', 'data-showonval'=>'event');
		$mform->addElement('text', 'config_eventslookahead', get_string('eventslookahead','block_mb2content'),$ifCalendar);
		$mform->setDefault('config_eventslookahead', 90);
        $mform->setType('config_eventslookahead', PARAM_INT);	
		
		
		$mform->addElement('textarea', 'config_featured', get_string('featured','block_mb2content'));
		$mform->addHelpButton('config_featured', 'featured', 'block_mb2content');
		$mform->setType('config_featured', PARAM_TEXT);	
		
		
		$mform->addElement('textarea', 'config_courseurls', get_string('courseurls','block_mb2content'),$ifCourses);
		$mform->addHelpButton('config_courseurls', 'courseurls', 'block_mb2content');
		$mform->setType('config_courseurls', PARAM_TEXT);
		
		
		$mform->addElement('textarea', 'config_courseprices', get_string('courseprices','block_mb2content'),$ifCourses);
		$mform->addHelpButton('config_courseprices', 'courseprices', 'block_mb2content');
		$mform->setType('config_courseprices', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_currency', get_string('currency', 'block_mb2content'), $currencyArr, $ifCourses);
		$mform->setDefault('config_currency', 'USD:24');
		
		
		$mform->addElement('text', 'config_alllink', get_string('alllink','block_mb2content'));
        $mform->setType('config_alllink', PARAM_TEXT);	
				
		
		
		// Layout options
		$mform->addElement('header', 'config_layoutoptions', get_string('layoutoptions', 'block_mb2content'));
		
						
		
		$mform->addElement('select', 'config_style', get_string('style', 'block_mb2content'), $styleArr);
		$mform->setDefault('config_style', 'none');
		
		
		// If style is "images and links"
		$ifImgli = array('data-showon'=>'config_style', 'data-showonval'=>'imgli');
		$mform->addElement('text', 'config_imgnum', get_string('imgnum','block_mb2content'), $ifImgli);
		$mform->setDefault('config_imgnum', 1);
        $mform->setType('config_imgnum', PARAM_INT);
		
		
		$ifNone = array('data-showon3'=>'config_style', 'data-showonval'=>'none');
		$mform->addElement('select', 'config_images', get_string('showimages', 'block_mb2content'), $yesNoArr, $ifNone);
		$mform->setDefault('config_images', '0');
		
		
		// If style is "columns" or "slider columns"
		$ifCols = array('data-showon3'=>'config_style', 'data-showonval'=>'cols', 'data-showonval2'=>'slidercols');
		$mform->addElement('text', 'config_colnum', get_string('colnum','block_mb2content'), $ifCols);
		$mform->setDefault('config_colnum', 3);
        $mform->setType('config_colnum', PARAM_INT);
		
		
		// If columns
		$ifColsNoslider = array('data-showon'=>'config_style', 'data-showonval'=>'cols');	
		$mform->addElement('select', 'config_gutter', get_string('gutter', 'block_mb2content'), $gutterArr, $ifColsNoslider);
		$mform->setDefault('config_gutter', 'default');
		
		
		$mform->addElement('textarea', 'config_addtext', get_string('addtext','block_mb2content'), $ifCols);
		$mform->setType('config_addtext', PARAM_TEXT);
		
		
		$mform->addElement('select', 'config_addtextpos', get_string('addtextpos', 'block_mb2content'), $lfArr, $ifCols);
		$mform->setDefault('config_addtextpos', 'left');
		
		
		$mform->addElement('text', 'config_addtextw', get_string('addtextw','block_mb2content'), $ifCols);
		$mform->setDefault('config_addtextw', 25);
        $mform->setType('config_addtextw', PARAM_INT);
		
		
		$ifnoTicker = array('data-showon2'=>'config_style', 'data-showonval'=>'ticker');
		
		
		$mform->addElement('text', 'config_titlelimit', get_string('titlelimit','block_mb2content'));
		$mform->setDefault('config_titlelimit',6);
        $mform->setType('config_titlelimit', PARAM_INT);
		
				
		$mform->addElement('text', 'config_desclimit', get_string('desclimit','block_mb2content'),$ifnoTicker);
		$mform->addHelpButton('config_desclimit', 'desclimit', 'block_mb2content');
		$mform->setDefault('config_desclimit',0);
        $mform->setType('config_desclimit', PARAM_INT);
		
		
		$ifAnn = array('data-showon'=>'config_ctype', 'data-showonval'=>'announcement');
		$mform->addElement('select', 'config_itemdate', get_string('date', 'block_mb2content'), $yesNoArr, $ifAnn );
		$mform->setDefault('config_itemdate', 0);
		
		
		$mform->addElement('select', 'config_readmore', get_string('readmore', 'block_mb2content'), $yesNoArr, $ifnoTicker);
		$mform->setDefault('config_readmore', 0);
		
		
		$ifEvents = array('data-showon'=>'config_ctype', 'data-showonval'=>'event');	
		$mform->addElement('select', 'config_shortdate', get_string('shortdate', 'block_mb2content'), $yesNoArr, $ifEvents);
		$mform->setDefault('config_shortdate', 1);
		
		
		$ifNoReadmore = array('data-showon'=>'config_readmore', 'data-showonval'=>0);	
		$mform->addElement('select', 'config_wholelink', get_string('wholelink', 'block_mb2content'), $yesNoArr, $ifNoReadmore);
		$mform->setDefault('config_wholelink', 0);
		
		
		
		// Slider options
		$mform->addElement('header', 'config_slideroptions', get_string('gslideroptions', 'block_mb2content'));
					
		//$mform->addElement('text', 'config_sitems', get_string('sitems','block_mb2content'));
		//$mform->setDefault('config_sitems', 3);
		//$mform->setType('config_sitems', PARAM_INT);
				
		
		$mform->addElement('text', 'config_smargin', get_string('smargin','block_mb2content'));
		$mform->setDefault('config_smargin', 30);
        $mform->setType('config_smargin', PARAM_INT);
		
		
		$mform->addElement('select', 'config_snav', get_string('snav', 'block_mb2content'), $yesNoArr);
		$mform->setDefault('config_snav', '1');
		
		
		$mform->addElement('select', 'config_sdots', get_string('sdots', 'block_mb2content'), $yesNoArr);
		$mform->setDefault('config_sdots', '0');
		
		
		$mform->addElement('select', 'config_sautoplay', get_string('sautoplay', 'block_mb2content'), $yesNoArr);
		$mform->setDefault('config_sautoplay', '1');
		
		
		$mform->addElement('select', 'config_sloop', get_string('sloop', 'block_mb2content'), $yesNoArr);
		$mform->setDefault('config_sloop', '0');
		
				
		$mform->addElement('text', 'config_spausetime', get_string('spausetime','block_mb2content'));
		$mform->setDefault('config_spausetime', 7000);
        $mform->setType('config_spausetime', PARAM_INT);
		
		
		$mform->addElement('text', 'config_sanimate', get_string('sanimate','block_mb2content'));
		$mform->setDefault('config_sanimate', 600);
        $mform->setType('config_sanimate', PARAM_INT);		
		
		
		// Images options
		//$mform->addElement('header', 'config_imgoptions', get_string('imgoptions', 'block_mb2content'));
//		
//		$mform->addElement('select', 'config_cropimg', get_string('cropimg', 'block_mb2content'), $yesNoArr);
//		$mform->setDefault('config_cropimg', 0);
//		
//		$ifCrop = array('data-showon'=>'config_cropimg', 'data-showonval'=>'1');		
//		
//		$mform->addElement('text', 'config_imgw', get_string('imgw','block_mb2content'),$ifCrop);
//		$mform->setDefault('config_imgw', 480);
//        $mform->setType('config_imgw', PARAM_INT);	
		
		
		
	}
	
	
	
	function set_data($defaults) {

        
		$slidesCount = 7;
		
		if (empty($entry->id)) 
		{
           
		    $entry = new stdClass;
            $entry->id = null;
			
        }

        $draftitemid = file_get_submitted_draft_itemid('config_images');

       	//file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_mb2content', 'content', 0, array('subdirs'=>true));

      	$entry->attachments = $draftitemid;	
		
		
			
		

        parent::set_data($defaults);		
		
		
        if ($data = parent::get_data()) 		
		{
           
		   
			//file_save_draft_area_files($data->config_images, $this->block->context->id, 'block_mb2content', 'content', 0, array('subdirs' => true));
			
			
        }
		
		
    }
	
	
}