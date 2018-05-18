$(document).ready(function() {
	$("#page-grade-report-quizanalytics-index").find(".btn-navbar").on("click",function() {
		$(this).toggleClass("active-drop");
		if ($(this).hasClass("active-drop")) {
			$("#page-grade-report-quizanalytics-index").find(".nav-collapse").show();
		} else {
			$("#page-grade-report-quizanalytics-index").find(".nav-collapse").hide();
		}
	});
	$("#page-grade-report-quizanalytics-index .alyticsul li").each(function(index) {
		$(this).on("click",function() {
			$("#page-grade-report-quizanalytics-index .alyticsul li a").removeClass("active");
			$(this).find("a").addClass("active");
		});
	});
	$("#page-grade-report-quizanalytics-index").find(".nav").find(".dropdown").on('click', function (event) {
        $(this).toggleClass('open');
    });
    $("#page-grade-report-quizanalytics-index").find(".nav").find(".dropdown").find('.dropdown-menu').find('.dropdown-submenu ').on("click",function(event) {
    	event.preventDefault();
    	event.stopPropagation();
		$(this).toggleClass('open');
	});
	$("#page-grade-report-quizanalytics-index").find(".nav").find(".dropdown").find('.dropdown-menu').find('.dropdown-submenu ').find('ul').find('li').find('a').on("click",function(event) {
    	event.preventDefault();
    	event.stopPropagation();
    	window.open($(this).attr('href'), '_self');
	});
	
	var lastattemptsummary, loggedinuser, mixchart, allusers, questionpercat, timechart,
	gradeanalysis, quesanalysis, hardestques, allquestions, quizid, rooturl, userid;
	var attemptssnapshot_arr = [];
	$(".fetchdata").click(function(e) {
		e.preventDefault();
		var url = $(this).data('url');
		userid = $(this).data('user_id');
		var quizeid = $(this).data('quize_id');
		var courseid = $(this).data('course_id');

		Chart.plugins.register({
			beforeDraw: function(chartInstance) {
				var ctx = chartInstance.chart.ctx;
				ctx.fillStyle = "white";
				ctx.fillRect(0, 0, chartInstance.chart.width, chartInstance.chart.height);
			}
		});

		$.ajax({
			url : url,
			type : 'POST',
			data : {userid : userid, quiz : quizeid, courseid: courseid},
			success: function (data) {
				if (data) {
					var totaldata = jQuery.parseJSON(data);

					allquestions = totaldata.allquestion;
					quizid = totaldata.quizid;
					rooturl = totaldata.url;

					$(".showanalytics").find(".parentTabs").find("span.lastattemptsummary").hide();
					$(".showanalytics").find("#tabs-1").find("p.lastattemptsummarydes").hide();
					$(".showanalytics").find("#tabs-1").find("p.attemptsummarydes").show();
					if (totaldata.userattempts > 1) {
						$(".showanalytics").find(".parentTabs").find("span.lastattemptsummary").show();
						$(".showanalytics").find("#tabs-1").find("p.lastattemptsummarydes").show();
						$(".showanalytics").find("#tabs-1").find("p.attemptsummarydes").hide();
					}

					setTimeout(function() {
						$(".showanalytics").find("ul.nav-tabs a").click(function(e) {
			              e.preventDefault();
			              $(this).tab('show');
			              // Center scroll on mobile.
			              if ($(window).width() < 480) {
								var outerContent = $('.mobile_overflow');
								var innerContent = $('.canvas-wrap');
								if (outerContent.length > 0) {
									outerContent.scrollLeft( (innerContent.width() - outerContent.width()) / 2);
								}	
							}
			            });
					}, 100);

					$(".showanalytics").css("display", "block");

					if (totaldata.quizattempt != 1) {
						$("#tabs-2").find("ul").find("li").find("span.subtab1").show();
						$("#tabs-2").find("ul").find("li").find("span.subtab2").hide();
						$("#subtab21").find(".subtabmix").show();
						$("#subtab21").find(".subtabtimechart").hide();
					} else {
						$("#tabs-2").find("ul").find("li").find("span.subtab1").hide();
						$("#tabs-2").find("ul").find("li").find("span.subtab2").show();
						$("#subtab21").find(".subtabmix").hide();
						$("#subtab21").find(".subtabtimechart").show();
					}

					if(attemptssnapshot_arr.length > 0){
						$.each(attemptssnapshot_arr, function(i,v){
							v.destroy();
						});
					}
					$('.attemptssnapshot').html('');
					$.each(totaldata.attemptssnapshot.data, function(key,value) {
						var attemptssnapshotopt2 = {
							tooltips: {
								callbacks: {
								// use label callback to return the desired label
									label: function(tooltipItem, data) {
										return " "+ data.labels[tooltipItem.index] + " : " + data.datasets[0].data[tooltipItem.index];
									}
								}
							},
						};
						var attemptssnapshotopt = $.extend(totaldata.attemptssnapshot.opt[key], attemptssnapshotopt2);

						$('.attemptssnapshot').append('<div class="span6"><label><canvas id="attemptssnapshot'+key+'"></canvas><div id="js-legend'+key+'" class="chart-legend"></div></label><div class="downloadandshare"><a class="download-canvas" data-canvas_id="attemptssnapshot'+key+'"></a><div class="shareBtn" data-user_id="'+userid+'" data-canvas_id="attemptssnapshot'+key+'"></div></div></div>');
						var ctx = document.getElementById("attemptssnapshot"+key).getContext('2d');
						var attemptssnapshot = new Chart(ctx, {
							type: 'doughnut',
							data: totaldata.attemptssnapshot.data[key],
							options: attemptssnapshotopt,
						});
						document.getElementById('js-legend'+key).innerHTML = attemptssnapshot.generateLegend();

						$('#js-legend'+key).find('ul').find('li').on("click",function(snaplegende){
							var index = $(this).index();
							$(this).toggleClass("strike");
							var ci = attemptssnapshot;
							function first(p){
								for(var i in p) {return p[i]};
							}
							var curr = first(ci.config.data.datasets[0]._meta).data[index];
							curr.hidden = !curr.hidden
							ci.update();
						});
						attemptssnapshot_arr.push(attemptssnapshot);
					});
						
					var ctx = document.getElementById("questionpercat").getContext('2d');
					if (questionpercat !== undefined) {
						questionpercat.destroy();
					}
					var questionpercatopt2 = {
						tooltips: {
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									return " "+ data.labels[tooltipItem.index] + " : " + data.datasets[0].data[tooltipItem.index];
								}
							}
						},
					};
					var questionpercatopt = $.extend(totaldata.questionpercat.opt, questionpercatopt2);

					questionpercat = new Chart(ctx, {
						type: 'pie',
						data: totaldata.questionpercat.data,
						options: questionpercatopt,
					});
					document.getElementById('js-legendqpc').innerHTML = questionpercat.generateLegend();

					$("#js-legendqpc > ul > li").on("click",function(legende){
						var index = $(this).index();
						$(this).toggleClass("strike");
						var ci = questionpercat;
						function first(p){
							for(var i in p) {return p[i]};
						}
						var curr = first(ci.config.data.datasets[0]._meta).data[index];
						curr.hidden = !curr.hidden
						ci.update();
					});

					var allusersopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Hardest Categories' }}], yAxes: [{ scaleLabel: { display: true, labelString: 'Hardness in percentage (%)' }, ticks: { beginAtZero: true, max:100, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var allusersopt = $.extend(totaldata.allusers.opt, allusersopt2);

					var ctx = document.getElementById("allusers").getContext('2d');
					if (allusers !== undefined) {
						allusers.destroy();
					}
					allusers = new Chart(ctx, {
						type: 'bar',
						data: totaldata.allusers.data,
						options: allusersopt
					});

					var loggedinuseropt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Hardest Categories' }}], yAxes: [{ scaleLabel: { display: true, labelString: 'Hardness in percentage (%)' }, ticks: { beginAtZero: true, max:100, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var loggedinuseropt = $.extend(totaldata.loggedinuser.opt, loggedinuseropt2 );

					var ctx = document.getElementById("loggedinuser").getContext('2d');
					if (loggedinuser !== undefined) {
						loggedinuser.destroy();
					}
					loggedinuser = new Chart(ctx, {
						type: 'bar',
						data: totaldata.loggedinuser.data,
						options: loggedinuseropt
					});

					if (totaldata.lastattemptsummary.data != 0 && totaldata.lastattemptsummary.opt != 0) {
						$(".showanalytics").find(".noquesisattempted").hide();
						$(".showanalytics").find("#lastattemptsummary").show();

						var ctx = document.getElementById("lastattemptsummary");
						ctx.height = 100;
						var ctx1= ctx.getContext('2d');
						if (lastattemptsummary !== undefined) {
							lastattemptsummary.destroy();
						}

						var lastattemptsummaryopt2 = {
							tooltips: {
								custom: function(tooltip) {
									if (!tooltip) return;
									// disable displaying the color box;
									tooltip.displayColors = false;
								},
								callbacks: {
									// use label callback to return the desired label
									label: function(tooltipItem, data) {
										return tooltipItem.yLabel + " : " + tooltipItem.xLabel;
									},
									// remove title
									title: function(tooltipItem, data) {
										return;
									}
								}
							}
						};

						var lastattemptsummaryopt = $.extend(totaldata.lastattemptsummary.opt, lastattemptsummaryopt2);

						lastattemptsummary = new Chart(ctx1, {
							type: 'horizontalBar',
							data: totaldata.lastattemptsummary.data,
							options: lastattemptsummaryopt
						});				
					} else {
						$(".showanalytics").find("#lastattemptsummary").hide();
						$(".showanalytics").find("#lastattemptsummary").parent().append('<p class="noquesisattempted"><b>Please attempt at least one question.</b></p>');
					}

					var mixchartopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							},
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									return data.datasets[tooltipItem.datasetIndex].label + " : " + tooltipItem.yLabel;
								},
								// remove title
								title: function(tooltipItem, data) {
									return ;
								}
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Number of Attempts' }}], yAxes: [{ scaleLabel: { display: true, labelString: 'Cut Off Score' }, ticks: { beginAtZero: true, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var mixchartopt = $.extend(totaldata.mixchart.opt, mixchartopt2 );

					var ctx = document.getElementById("mixchart").getContext('2d');
					if (mixchart !== undefined) {
						mixchart.destroy();
					}
					mixchart = new Chart(ctx, {
						type: 'line',
						data: totaldata.mixchart.data,
						options: mixchartopt
					});
		
					var timechartopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							},
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									return tooltipItem.yLabel + " : " + tooltipItem.xLabel;
								},
								// remove title
								title: function(tooltipItem, data) {
									return ;
								}
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Score' }, ticks: { beginAtZero: true, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var timechartopt = $.extend(totaldata.timechart.opt, timechartopt2 );

					var ctx = document.getElementById("timechart").getContext('2d');
					if (timechart !== undefined) {
						timechart.destroy();
					}
					timechart = new Chart(ctx, {
						type: 'horizontalBar',
						data: totaldata.timechart.data,
						options: timechartopt
					});

					var ctx = document.getElementById("gradeanalysis").getContext('2d');
					if (gradeanalysis !== undefined) {
						gradeanalysis.destroy();
					}

					var gradeanalysisopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							},
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									return "Percentage Scored ("+ data.labels[tooltipItem.index] + ") : " + data.datasets[0].data[tooltipItem.index];
								}
							}
						}
					};
					var gradeanalysisopt = $.extend(totaldata.gradeanalysis.opt, gradeanalysisopt2 );

					gradeanalysis = new Chart(ctx, {
						type: 'pie',
						data: totaldata.gradeanalysis.data,
						options: gradeanalysisopt
					});
					document.getElementById('js-legendgrade').innerHTML = gradeanalysis.generateLegend();
					$("#js-legendgrade > ul > li").on("click",function(legendgrade){
						var index = $(this).index();
						$(this).toggleClass("strike");
						var ci = gradeanalysis;
						function first(p){
							for(var i in p) {return p[i]};
						}
						var curr = first(ci.config.data.datasets[0]._meta).data[index];
						curr.hidden = !curr.hidden
						ci.update();
					});

					var ctx = document.getElementById("quesanalysis").getContext('2d');
					if (quesanalysis !== undefined) {
						quesanalysis.destroy();
					}
					var quesanalysisopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							},
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									var newtooltipq = [data.datasets[tooltipItem.datasetIndex].label +" : "+ tooltipItem.yLabel, "(Click to Review Question)"];					
									return newtooltipq;
								}
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Question Number' }}], yAxes: [{ scaleLabel: { display: true, labelString: 'Number of Attempts' }, ticks: { beginAtZero: true, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var quesanalysisopt = $.extend(totaldata.quesanalysis.opt, quesanalysisopt2);
					
					quesanalysis = new Chart(ctx, {
						type: 'line',
						data: totaldata.quesanalysis.data,
						options: quesanalysisopt
					});

					var hardestquesopt2 = {
						tooltips: {
							custom: function(tooltip) {
								if (!tooltip) return;
								// disable displaying the color box;
								tooltip.displayColors = false;
							},
							callbacks: {
								// use label callback to return the desired label
								label: function(tooltipItem, data) {
									var newtooltip = [data.datasets[tooltipItem.datasetIndex].label + " : " + tooltipItem.yLabel, "(Click to Review Question)"];
									return  newtooltip;
								},
								// remove title
								title: function(tooltipItem, data) {
									return;
								}
							}
						},
						scales: { xAxes: [{ scaleLabel: { display: true, labelString: 'Hardest Questions' }}], yAxes: [{ scaleLabel: { display: true, labelString: 'Number of Attempts' }, ticks: { beginAtZero: true, callback: function (value) { if (Number.isInteger(value)) { return value; } } } }] }
					};
					var hardestquesopt = $.extend(totaldata.hardestques.opt, hardestquesopt2);

					var ctx = document.getElementById("hardestques").getContext('2d');
					if (hardestques !== undefined) {
						hardestques.destroy();
					}
					hardestques = new Chart(ctx, {
						type: 'bar',
						data: totaldata.hardestques.data,
						options: hardestquesopt
					});
				}
			},
			error: function (err, status, data) {
			}
		});
	});
	$("#fetchdata").one( "click", function() {
		$(".showanalytics").find("canvas").each(function() {
			var canvasid = $(this).attr("id");
			$(this).parent().append('<div class="downloadandshare"><a class="download-canvas" data-canvas_id="'+canvasid+'"></a><div class="shareBtn" data-user_id="'+userid+'" data-canvas_id="'+canvasid+'"></div></div>');
		});
	});

	var canvasquesanalysis = document.getElementById("quesanalysis");
	var canvashardestques = document.getElementById("hardestques");

	canvasquesanalysis.onclick = function (qevt) {
		var activePoints = quesanalysis.getElementsAtEvent(qevt);
		var chartData = activePoints[0]['_chart'].config.data;
		var idx = activePoints[0]['_index'];
		var label = chartData.labels[idx];

		if (allquestions !== undefined) {
			$.each(allquestions, function(i, quesid) {
				if (label == quesid.split(",")[0]) {
					var quesid = quesid.split(",")[1];
					var id = quizid;
					var newwindow = window.open(rooturl+'/grade/report/quizanalytics/questions.php?quizid='+id+'&quesid='+quesid, '', 'height=500,width=800');
					if (window.focus) {
						newwindow.focus();
					}
					return false;
				}
			});
		}
	};

	canvashardestques.onclick = function (aqevt) {
		var activePoints = hardestques.getElementsAtEvent(aqevt);
		var chartData = activePoints[0]['_chart'].config.data;
		var idx = activePoints[0]['_index'];
		var label = chartData.labels[idx];

		if (allquestions !== undefined) {
			$.each(allquestions, function(i, quesid) {
				if (label == quesid.split(",")[0]) {
					var quesid = quesid.split(",")[1];
					var id = quizid;
					var newwindow = window.open(rooturl+'/grade/report/quizanalytics/questions.php?quizid='+id+'&quesid='+quesid, '', 'height=500,width=800');
					if (window.focus) {
						newwindow.focus();
					}
					return false;
				}
			});
		}
	};

	$('body').on('click', ".shareBtn", function(index) {
		var useridsharecanvas = $(this).data('user_id');
		var files = document.getElementById($(this).data('canvas_id')).toDataURL("image/png");

	    	FB.getLoginStatus(function(response) {
	    		if (response.status === 'connected') {
	    			var uid = response.authResponse.userID;
	    			postImageToFacebook(response.authResponse.accessToken, "Canvas to Facebook", "image/png", files, useridsharecanvas, window.location.href);
	    		} else if (response.status === 'not_authorized') {
	    			FB.login(function(response) {
	    				if (response.authResponse) {
	    					postImageToFacebook(response.authResponse.accessToken, "Canvas to Facebook", "image/png", files, useridsharecanvas, window.location.href);
	    				} else {
	    					alert("Not Authorized");
	    				}
	    			});
	    		} else {
	    			FB.login(function(response) {
	    				if (response.authResponse) {
	    					postImageToFacebook(response.authResponse.accessToken, "Canvas to Facebook", "image/png", files, useridsharecanvas, window.location.href);
	    				} else {
	    					alert("Not Logged into Facebook");
	    				}
	    			});
	    		}
	    	});
	});

	$('body').on('click', '.download-canvas', function(e) {
		var canvasId = $(this).data('canvas_id');
		downloadCanvas(this, canvasId, canvasId+'.jpeg');
	});

});

function downloadCanvas(link, canvasId, filename) {
	link.href = document.getElementById(canvasId).toDataURL("image/jpeg");
	link.download = filename;
}

function postImageToFacebook(token, filename, mimeType, imageData, userid, message) {
	var fd = new FormData();
	fd.append("source", imageData);
	fd.append("userid", userid);

    // Upload image to facebook without story(post to feed)
    $.ajax({
    	url: siteurl+"/grade/report/quizanalytics/imagefile.php",
    	type: "POST",
    	data: fd,
    	processData: false,
    	contentType: false,
    	cache: false,
    	success: function (data) {
            // Get image source url
            if(data){
            	FB.ui({
            		method: 'share_open_graph',
            		action_type: 'og.shares',
            		action_properties: JSON.stringify({
            			object : {
            				'og:url': siteurl,
            				'og:title': fbtitle,
            				'og:image:width': '1200',
            				'og:image:height': '630',
            				'og:image': data,
            			}
            		})
            	});
            }
        },
        error: function (shr, status, data) {
        	console.log("error " + data + " Status " + shr.status);
        },
        complete: function (data) {
            //console.log('Post to facebook Complete');
        }
    });
}
