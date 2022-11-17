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
 * @package		Mb2 A-Z Courses
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2018 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/



jQuery(document).ready(function($){
	
	
	
	$('input.mb2azcourses_color').each(function(){
	
	$(this).spectrum({
			showInput: true,
			showButtons: false,
			preferredFormat: 'rgb',
			allowEmpty: true,
			color: '',
			showAlpha: true
			});
	
	});
	
	
	
	
	// Show on field
	$('*[data-showon]').each(function(){
		
		
		var itemId = $('#id_' + $(this).data('showon'));
		var itemValue = $(this).data('showonval');
		var parentDiv = $(this).parent().parent();
		var parentDiv2 = $(this).parent().parent().parent();
		
		
		showHideField();
		
		
		
		$(document).on('change', itemId, function(){
			
			showHideField();
			
		});
		
		
		
		
		
		
		function showHideField()
		{
			
			if (itemId.val() == itemValue)
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.show();
				}
				else
				{
					parentDiv.show();	
				}
					
			}
			else
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.hide();
				}
				else
				{
					parentDiv.hide();	
				}					
			}
				
		}
		
					
		
	});
	
	
	
	
	
	
	// Show on two fields
	$('*[data-showon3]').each(function(){
		
		
		var itemId = $('#id_' + $(this).data('showon3'));
		var itemValue = $(this).data('showonval');
		var itemValue2 = $(this).data('showonval2');
		var parentDiv = $(this).parent().parent();
		var parentDiv2 = $(this).parent().parent().parent();
		
		
		showHideField3();
		
		
		
		$(document).on('change', itemId, function(){
			
			showHideField3();
			
		});
		
		
		
		
		
		
		function showHideField3()
		{
			
			if (itemId.val() == itemValue || itemId.val() == itemValue2)
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.show();
				}
				else
				{
					parentDiv.show();	
				}
					
			}
			else
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.hide();
				}
				else
				{
					parentDiv.hide();	
				}					
			}
				
		}
		
					
		
	});
	
	
	
	
	
	
	// Show on field
	$('*[data-showon2]').each(function(){
		
		
		var itemId = $('#id_' + $(this).data('showon2'));
		var itemValue = $(this).data('showonval');
		var parentDiv = $(this).parent().parent();
		var parentDiv2 = $(this).parent().parent().parent();
		
		
		showHideField2();
		
		
		
		$(document).on('change', itemId, function(){
			
			showHideField2();
			
		});
		
		
		
		
		
		
		function showHideField2()
		{
			
			if (itemId.val() != itemValue)
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.show();
				}
				else
				{
					parentDiv.show();	
				}
					
			}
			else
			{
				
				if (parentDiv.hasClass('fadvcheckbox'))
				{
					parentDiv2.hide();
				}
				else
				{
					parentDiv.hide();	
				}					
			}
				
		}
		
					
		
	});
	
	
	
	
	
});