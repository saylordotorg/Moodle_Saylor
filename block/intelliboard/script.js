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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    block_intelliboard
 * @copyright  2017 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    https://intelliboard.net/
 */

var intelliboard_block_data = [];
var intelliboard_block_params = [];

function intelliboard_block_lang(key){
	if(intelliboard_block_params.lang.hasOwnProperty(key)){
		return intelliboard_block_params.lang[key];
	}else{
		return key;
	}
}
function intelliboard_block_init(Y, params){
	intelliboard_block_params = params || {};

	jQuery('.intelliboard-navcontent a:not(.external-link)').click(function(e){
		e.preventDefault();
		var mode = true;
		if (jQuery(this).hasClass('active')) {
			mode = false;
		}
		var id = jQuery(this).attr('href');
		jQuery('.intelliboard-navcontent a').removeClass('active');
		jQuery('.intelliboard-widgets li').removeClass('active');
		jQuery('#'+id).addClass('active');
		jQuery(this).addClass('active');

		jQuery('.intelliboard-widgets').toggle();
		jQuery('.intelliboard-navcontent').toggle();
		if (mode) {
			intelliboard_load_data(id);
		}
	});
	jQuery('.intelliboard-navcontent a:first').addClass('active');
	jQuery('.intelliboard-widgets li:first').addClass('active');

	intelliboard_load_data(jQuery('.intelliboard-navcontent a.active').attr('href'));


	jQuery('#intelliboard-resize').click(function(){
		jQuery('.block_intelliboard.block').toggleClass('fullsize');
		var element = jQuery('.intelliboard-navcontent a.active').attr('href');

		if(intelliboard_block_data[element]){
			console.log(element);
			console.log(intelliboard_block_data);
			var json = intelliboard_block_data[element];
			window[element](json.data, true);
		}
	});
	jQuery('#intelliboard-navcontent').click(function(){
		jQuery('.intelliboard-widgets').toggle();
		jQuery('.intelliboard-navcontent').toggle();
	});
}

function intelliboard_load_data(element)
{
	jQuery.ajax({
		url: intelliboard_block_params.url,
		type: "POST",
		data: "action="+element,
		beforeSend: function(){
			jQuery('.' + element).html(intelliboard_block_lang('s29'));
			jQuery('#'+element+' h4').append('<span></spn>');
			jQuery('#'+element+' h4 span').animate({width:'99%'}, 1000);
		}
	}).done(function( json ) {
		jQuery('#'+element+' h4 span').animate({width:'100%'}, 100, function() {
			jQuery(this).remove();
  		});
		intelliboard_block_data[element] = json;

		if(json.html){
			jQuery('.' + element).html(json.html);
		}
		window[element](json.data, false);
	});
}



function intelliboard_live_stream(json, mode)
{
	if (mode) {
		return false;
	}
	var element = 'intelliboard_live_stream';
	var intelliboard_live_id = setTimeout(function() {
		if (jQuery('#'+element).hasClass('active') && json) {
			jQuery.ajax({
				url: intelliboard_block_params.url,
				type: "POST",
				data: "action="+element+"&timepoint="+json,
				dataType: "json",
				beforeSend: function(){
					jQuery('#'+element+' h4').append('<span></spn>');
					jQuery('#'+element+' h4 span').animate({width:'99%'}, 1000);
				}
			}).done(function( json ) {
				jQuery('#'+element+' h4 span').animate({width:'100%'}, 100, function() {
					jQuery(this).remove();
		  		});
				if(json.html){
					jQuery('.intelliboard-block-activities').prepend(json.html);
				}
				intelliboard_live_stream(json.data, false);
			});
		} else {
			clearTimeout(intelliboard_live_id);
			intelliboard_live_id = null;
		}
	}, 3000)

}

function intelliboard_activities_progress(json, mode)
{
	if(jQuery('.block_intelliboard.block').hasClass('fullsize')){
		jQuery('.flat-list-item').addClass('active');
	}else{
		jQuery('.flat-list-item').removeClass('active');
	}
	jQuery('.flat-list-item h5').unbind( "click" ).click(function(){
		jQuery(this).parent().toggleClass('active');
	})
}
function intelliboard_learners_progress(json, mode){
	google.charts.load('current', {packages: ['corechart']});
	google.charts.setOnLoadCallback(function(){
		var data = google.visualization.arrayToDataTable([
			[intelliboard_block_lang('s35'), intelliboard_block_lang('s36')],
			[intelliboard_block_lang('s35'), parseInt(json.completed)],
			[intelliboard_block_lang('s36'), parseInt(json.enrolled - json.completed)]
		]);
		var options = {
		chartArea: {width: '100%',height: '90%',},
		  pieHole: 0.8,
		  pieSliceTextStyle: {
		    color: 'transparent',
		  },
		  colors:['#1db34f','#e74c3c'],
		  legend: 'none',
		  backgroundColor:{fill:'transparent'}
		};
		if(jQuery('.block_intelliboard.block').hasClass('fullsize')){
			jQuery('#widget_progress').css('height', '420px');
		}else{
			jQuery('#widget_progress').css('height', '250px');
		}
		var chart = new google.visualization.PieChart(document.getElementById('widget_progress'));
		chart.draw(data, options);
	});
}
function intelliboard_course_summary(json, mode){
	google.charts.load('current', {packages: ['corechart']});
	google.charts.setOnLoadCallback(function(){
		var data = google.visualization.arrayToDataTable([
			[intelliboard_block_lang('s42'), intelliboard_block_lang('s17')],
			[intelliboard_block_lang('s17'), parseInt(json.grade)],
			['', parseInt(100 - json.grade)]
		]);
		var options = {
		chartArea: {width: '100%',height: '90%',},
		  pieHole: 0.8,
		  pieSliceTextStyle: {
		    color: 'transparent',
		  },
		  colors:['#2980b9','#dddddd'],
		  legend: 'none',
		  backgroundColor:{fill:'transparent'}
		};
		if(jQuery('.block_intelliboard.block').hasClass('fullsize')){
			jQuery('#widget_summary').css('height', '460px');
		}else{
			jQuery('#widget_summary').css('height', '250px');
		}
		var chart = new google.visualization.PieChart(document.getElementById('widget_summary'));
		chart.draw(data, options);
	});
}
function intelliboard_learners_performance(json, mode){
	google.charts.load('current', {packages: ['corechart', 'bar']});
	google.charts.setOnLoadCallback(function(){
		var data = google.visualization.arrayToDataTable([
			[intelliboard_block_lang('s37'), intelliboard_block_lang('s38'), { role: "style" }],
			[intelliboard_block_lang('s39'), parseInt(json.grade_a), '#2e9a52'],
			['', parseInt(json.grade_b), '#80a649'],
			[intelliboard_block_lang('s40'), parseInt(json.grade_c), '#deb249'],
			['', parseInt(json.grade_d), '#ea7f3d'],
			[intelliboard_block_lang('s41'), parseInt(json.grade_f), '#f74c36'],
		]);

		var options = {
			title:'',
			legend: { position: "none" },
			chartArea:{width:'80%', height: '80%'},
			colors:['#1d7fb3', '#1db34f'],
			backgroundColor:{fill:'transparent'},
			hAxis: {title: ''},
	        vAxis: {title: ''}
		};
		if(jQuery('.block_intelliboard.block').hasClass('fullsize')){
			options.hAxis.title = intelliboard_block_lang('s38');
			options.vAxis.title = intelliboard_block_lang('s37');
			jQuery('#widget_grade').css('height', '560px');
		}else{

			jQuery('#widget_grade').css('height', '250px');
		}
		var chart = new google.visualization.BarChart(document.getElementById('widget_grade'));
		chart.draw(data, options);
	});
}
