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

jQuery(document).ready(function($){
	
	
	
	$('.mb2content-slider').each(function(){
		
		slider = $(this).find('.mb2content-list');		
		block_mb2content_slider(slider);	
		
	});	
	
	
	function block_mb2content_slider (slider)
	{
		
		// Slider options
		isItems = slider.data('items');
		isMargin = slider.data('margin');
		isLoop = slider.data('loop') == 0 ? false : true;
		isNav = slider.data('nav') == 0 ? false : true;
		isDots = slider.data('dots') == 0 ? false : true;
		isAutoplay = slider.data('autoplay') == 0 ? false : true;
		isPauseTime = slider.data('pausetime');
		isAnimTime = slider.data('animtime');
		
				
		var is2res = isItems > 2 ? 2 : isItems;
		var is3res = isItems > 3 ? 3 : isItems;
		var is4res = isItems > 5 ? 5 : isItems;		
		isRes =  {0:{items:1},600: {items:is2res},780: {items:is3res},1000:{items:is4res}};
		var isRtl = $('body').hasClass('dir-rtl') ? true : false;
		
		slider.owlCarousel({
			
			items: isItems,
			margin: isMargin,
			loop: isLoop,
			nav: isNav,
			dots: isDots,
			autoplay: isAutoplay,
			responsive: isRes,
			autoplayHoverPause: true,
			autoplayTimeout: isPauseTime,
			smartSpeed: isAnimTime,
			rtl: isRtl,
			navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>']
				
		});	
		
	}
	
	
	
	
});