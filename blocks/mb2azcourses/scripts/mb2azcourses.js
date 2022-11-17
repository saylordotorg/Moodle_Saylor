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
	
	
	
	// Scroll
	$('a.mb2azc-scroll').bind('click', function(event) {
		var $anchor = $(this);
		var isPlus = $anchor.data('plus') ? $anchor.data('plus') : 0; 
		
		$('html, body').stop().animate({
			
			
			scrollTop: $($anchor.attr('href')).offset().top - isPlus
		}, 600);
		event.preventDefault();
	});
	
	
	
});