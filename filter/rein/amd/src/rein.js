/*
 * @package    filter_rein
 * @copyright  2016 Remote-Learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module filter_rein/rein
  */
define(['jquery', 'jqueryui','filter_rein/jquery.touchpunch.improved', 'filter_rein/raphael', 'filter_rein/swiper', 'core/str'], function($, jqueryui, touchpunch, Raphael, Swiper, Str) { // Loads touchpunch.
    //
    // rein.js
    // Remote-Learner.net eLearning Interactions Library
    // Amy Groshek of Remote-Learner.net
    // 2011.10.03
    //

    // Accessory functions
    function is_touch_device() {// Return true if touch events supported
        return !!('ontouchstart' in window);
    }
    // Global debug mode, for handling console logging.
    var g_debugmode = false;
    /**
     * Logs msg if global debug mode is on.
     * @return none
     */
    var log_debug = function(msg) {
        if (!!g_debugmode) {
            console.log(msg);
        }
    };

            //
            // rotator
            // Rotates a set of images at a set time span in a set duration
            //
            $.fn.rotator = function(options) {
                //console.log('$.fn.rotator');  // jshint ignore:line
                var $this = $(this);

                // Options from defaults and passed
                var opts = $.extend({}, $.fn.rotator.defaults, options);

                // Hide all but the first image <div>
                $this.children(':eq(0)').siblings().hide();

                // Counter for which image is visible
                var ind = 0;

                // Size rotator height to the largest .photo.
                function sizeRotator(){
                    var h = 0;
                    $this.find(".photo").each(function() {
                        if ($(this).outerHeight() > h) {
                            h = $(this).outerHeight();
                        }
                    });
                    $this.css("min-height", h);
                }
                sizeRotator();

                // Reposition the click target any time the window is resized.
                $(window).resize( function() {
                        sizeRotator();
                    });

                // Fade elements in and out
                function doFadeOut() {
                    //console.log('doFadeOut called'); // jshint ignore:line
                    var fDur = opts.fadeDuration;
                    var len = $this.children().length;
                    var current = $this.children(':eq(' + ind + ')');
                    // If index is at highest number, return to 0, else increment
                    if ((ind + 1) === len) {
                        //console.log('last one reached'); // jshint ignore:line
                        // Fade visible image out, reset ind, fade new in
                        //$this.children(':eq(' + ind + ')').fadeOut(fDur);
                        ind = 0;
                        //$this.children(':eq(' + ind + ')').fadeIn(fDur);

                    } else {
                        // Fade visible image out, increment ind, fade new in
                        //$this.children(':eq(' + ind + ')').fadeOut(fDur);
                        ind++;
                        //$this.children(':eq(' + ind + ')').fadeIn(fDur);
                    }
                    var next = $this.children(':eq(' + ind + ')');

                    current.fadeOut(fDur);
                    next.fadeIn(fDur);

                }

                function startInterval() {
                    // console.log('interval started');
                    var fInt = opts.fadeInterval;
    //                $.fn.rotator.interval = setInterval(doFadeOut, opts.fadeInterval);
                    $.fn.rotator.interval = setInterval(doFadeOut, fInt);
                }

                // Mouseover image stops interval
                $(this).hover(
                    // Stop the timer
                    function() {
                        //console.log('hover'); // jshint ignore:line
                        clearInterval($.fn.rotator.interval);
                    },
                    // Start the timer again when hover is left
                    function() {
                        startInterval();
                    }
                );



                // Start the rotation
                startInterval();

            };

            $.fn.rotator.defaults = {
                fadeDuration: 1000,
                fadeInterval: 2000
            };

            //
            // End rotator
            //
            //
            // onedrag
            // One-item drag and drop
            //
            $.fn.onedrag = function(options) {
                //console.log('onedrag'); // jshint ignore:line
                var $this = $(this);
                var opts = $.extend({}, $.fn.onedrag.defaults, options);
                var oneDrag = {};// obj to contain some variables and functions
                oneDrag.answerIsCorr = false;
                $this.find('.checkBtn').button({disabled:true});
                // Add draggable funct
                $this.find('.draggable').draggable({
                        drag: function() {
                            oneDrag.answIsCorr = false;
                        }
                    });// end draggable init
                // Add droppable funct
                $this.find(".droppable").droppable({
                        activeClass: 'ui-state-active',
                        hoverClass: 'ui-state-highlight',
                        drop: function(event, ui) {
                            $this = $(this);
                            //console.log('drop'); // jshint ignore:line
                            $this.addClass('ui-state-disabled');
                            // If the id of the draggable matches the name of the correct item in the correct array
                            if ($this.hasClass('corr01')) {
                                // Draggable was dropped into the correct target, so we set its array val to true
                                oneDrag.answIsCorr = true;
                            }
                            else {
                                // Draggable was dropped into the incorrect target, so we set its array val to false
                                oneDrag.answIsCorr = false;
                            }
                            $this.parents('.oneDrag').find('.checkBtn').button({disabled:false});
                            //console.log('drop end of code'); // jshint ignore:line
                        },// end drop
                        out: function(event, ui) {
                            $this = $(this);
                            $this.removeClass('ui-state-disabled');
                            oneDrag.answIsCorr = false;
                            $this.parents('.oneDrag').find('.checkBtn').button({disabled:true});
                        }// end out
                    });// end droppable inits

                // Generate dialogs
                oneDrag.posRemediation = $this.find('.remediation.positive').dialog({
                        modal: true,
                        autoOpen: false,
                        buttons: {
                            "Ok": function() {
                                $(this).dialog("close");
                            }
                        }
                    });// end pos remediation dialog init

                oneDrag.negRemediation = $this.find('.remediation.negative').dialog({
                        modal: true,
                        autoOpen: false,
                        buttons: {
                            "Ok": function() {
                                $(this).dialog("close");
                            }
                        }
                    });// end neg remediation dialog init

                // .checkBtn functionality
                $this.find(".checkBtn").click(function(e) {
                        if (oneDrag.answIsCorr === true) {
                            oneDrag.posRemediation.dialog('open');
                        }
                        else {
                            oneDrag.negRemediation.dialog('open');
                        }
                    });// end .checkBtn click funct

            };// end $.fn.onedrag()

            $.fn.onedrag.defaults = {};
            //
            // End one-item drag and drop
            //

            //
            // Click hotspot
            //
            $.fn.clickhotspot = function(options) {
                //console.log('click hotspot'); // jshint ignore:line
                var $this = $(this);
                var opts = $.extend({}, $.fn.clickhotspot.defaults, options);
                // Get the offset of the stage parent div
                // Position target
                function positionTarg(){
                    //console.log('positionTarg'); // jshint ignore:line
                    var offset = $this.children('.clickStage').offset();// Get offset of stage (visible viable target)

                    $this.children('.clickTarg').css( 'position', 'absolute').offset({
                        // set offset of the correct area (invisible laid over visible target)
                        left: offset.left + Number( $this.find('.clickTarg .xcoord').html() ),
                        top: offset.top + Number( $this.find('.clickTarg .ycoord').html() )
                    });
                }
                positionTarg();
                // Position target again. Fixes bug which
                // seems to be caused by late loading/init
                // of other CSS or JS in certain themes.
                setTimeout(function() {
                    positionTarg();
                }, 500);

                // Reposition the click target any time the window is resized.
                $(window).resize( function() {
                        //console.log('window resize'); // jshint ignore:line
                        positionTarg();
                    });

                var posDialog = $this.find('.remediation.positive').dialog({
                        autoOpen: false,
                        modal: true,
                        buttons: {
                            'Ok': function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                var negDialog = $this.find('.remediation.negative').dialog({
                        autoOpen: false,
                        modal: true,
                        buttons: {
                            "Ok": function() {
                                $(this).dialog("close");
                            }
                        }
                    });

                // When the click target is clicked, show remediation
                $this.find('.clickTarg').click(function(e) {
                        //console.log('click on target'); // jshint ignore:line
                        posDialog.dialog('open');

                    });

                // When the click target is clicked, show remediation
                $this.find('.clickStage').click(function(e) {
                        //console.log('click on stage'); // jshint ignore:line
                        negDialog.dialog('open');
                    });

            };// end $.fn.clickhotspot

            $.fn.clickhotspot.defaults = {};
            //
            // End click hotspot
            //

            //
            // Drag and drop sorting
            //
            $.fn.draganddropsorting = function(options) {
                //console.log('$.fn.draganddropsorting'); // jshint ignore:line
                $(this).each(function() {
                    var $this = $(this);
                    var opts = $.extend({}, $.fn.draganddropsorting.defaults, options);
                    // Variable to track whether all answers are correct
                    var allCorrect = false;
                    var remShown = false;
                    // Set width
                    var w = 0;
                    $this.find(".sortable").each(function() {
                        w += $(this).outerWidth(true);
                    });
                    $this.find(".sortableCols").css("max-width", w);
                    // Set uniform height for columns
                    var h = 0;
                    $this.find(".sortable").each(function() {
                        if ($(this).height() > h) {
                            h = $(this).height();
                        }
                    });
                    $this.find(".sortable").css("min-height", h);
                    // Sortable functionality
                    $this.find(".sortable").sortable({
                            connectWith: 'ul',
                            placeholder: 'ui-state-highlight',
                            dropOnEmpty: true
                        });
                    // Create dialogs for the pos and neg remediation
                    var posDialog = $this.find('.remediation.positive').dialog({
                            autoOpen: false,
                            modal: true,
                            buttons: {
                                'Ok': function() {
                                    $(this).dialog('close');
                                }
                            }
                        });
                    var negDialog = $this.find('.remediation.negative').dialog({
                            autoOpen: false,
                            modal: true,
                            buttons: {
                                "Ok": function() {
                                    $(this).dialog("close");
                                }
                            }
                        });
                    // Show remediation
                    function showRemediation() {
                        if ( remShown === false ) {
                            //console.log( 'allCorrect is '+allCorrect ); // jshint ignore:line
                            if (allCorrect === true) {
                                posDialog.dialog('open');
                            } else {
                                negDialog.dialog('open');
                            }
                            remShown = true;
                        }
                    }
                    // Function to check answers
                    function checkAnswers() {
                        allCorrect = true;
                        // Run a loop to check to see that all items belong in their column
                        for (var x = 0; x < $this.find('li').length; x++) {
                            //console.log('for loop, x = ' + x); // jshint ignore:line
                            var $item = $this.find('li:eq(' + x + ')');
                            $item.removeClass('ui-icon-arrow-4');
                            // If the item has the class which matches the container ID show correct remediation
                            if ($item.hasClass('belongs-in-' + $item.parent().attr('id'))) {
                                //console.log($item.html() + ' is in the right column'); // jshint ignore:line
                                $item.addClass('ui-state-highlight');
                                $item.find('span').addClass('ui-icon-check');
                            }
                            else {
                                //console.log($item.html() + ' is in the wrong column');  // jshint ignore:line
                                $item.find('span.ui-icon-check').removeClass('ui-icon-check');
                                $item.find('span').addClass('ui-icon-closethick');
                                allCorrect = false;
                            }// end if/else

                        }// end for loop

                        $this.find('.sortable').animate({
                                opacity: '100'
                            },
                            500, '', showRemediation);
                    }// end function checkAnswers()

                    // Check Answers button click
                    $this.find('button.checkAnswers').click(function() {
                            //console.log('checkBtn'); // jshint ignore:line
                            remShown = false;
                            $this.find('.sortable').animate({
                                    opacity: '0'
                                },
                                500, '', checkAnswers);
                    });// end check answer button click
                });
            };// end $.fn.draganddropsorting

            $.fn.draganddropsorting.defaults = {};
            //
            // End drag and drop sorting
            //

            //
            //  domultidrop
            //  multi-item drag and drop into large circle
            //
            $.fn.domultidrop = function(options) {
                // This activity presents a circle droppable target.
                // Draggables are dropped inside the target.
                // Remediation in the form of check mark or
                // X icon is shown in a fashion similar to that of
                // the multi-drag sorting activity.

                //console.log('$.fn.domultidrop'); // jshint ignore:line
                var $this = $(this);
                var opts = $.extend({}, $.fn.domultidrop.defaults, options);

                function removeClasses($el) {// Remove color classes from passed element
                    $el.css('background', '');
                    $el.find('span').removeClass('ui-icon-check ui-icon-closethick').addClass('ui-icon-arrow-4');
                }// end removeClasses()

                function fadeBack($el) {// Fade element opacity back
                    //console.log('fadeBack'); // jshint ignore:line
                    $el.fadeTo(400, 1);
                }// end fadeBack()

                function checkContents($el) {// Apply remediation colors, then call funct to fade opacity back
                    if ($el.hasClass('drop-ok')) {
                        $el.css('background', opts.correct);
                        $el.find('span').removeClass('ui-icon-arrow-4').addClass('ui-icon-check');
                    } else {
                        $el.css('background', opts.incorrect);
                        $el.find('span').removeClass('ui-icon-arrow-4').addClass('ui-icon-closethick');
                    }// end if/else
                    fadeBack($el);
                }// end checkContents()

                function checkAllCorrect() {// Check if all draggables in droppable are correct and change color
                    var allcorrect = true;
                    $this.find('.ui-draggable').each(function() {
                        if ($(this).hasClass('is-in-droppable') && !$(this).hasClass('drop-ok')) {
                            allcorrect = false;
                        }
                        if (!$(this).hasClass('is-in-droppable') && $(this).hasClass('drop-ok')) {
                            allcorrect = false;
                        }
                    });
                    if (allcorrect) {
                        $this.find('.big-droppable').addClass('allcorrect');
                        $this.find('.big-droppable').css('border-color', opts.allcorrect);
                    } else {
                        $this.find('.big-droppable').removeClass('allcorrect');
                        $this.find('.big-droppable').css('border-color', '');
                    }
                }

                $this.find('.ui-draggable').draggable();// Add draggable funct to draggables
                $this.find('.big-droppable').droppable({// Add droppable funct to drop target
                        //hoverClass: "ui-state-active",
                        drop: function(event, ui) {
                            //console.log('drop'); // jshint ignore:line
                            var $el = $(ui.draggable);
                            $el.addClass('is-in-droppable').fadeTo(400, 0.1,
                                function() {
                                    removeClasses($el);// Remove any classes on droppable
                                    checkContents($el); // Check element and assign remediation color
                                });
                            checkAllCorrect();
                        },
                        out: function(event, ui) {
                            //console.log('out'); // jshint ignore:line
                            var $el = $(ui.draggable);
                            $el.removeClass('is-in-droppable').fadeTo(400, 0.1,
                                function() {
                                    removeClasses($el);// Remove any classes on the droppable
                                    fadeBack($el);
                                });
                            checkAllCorrect();
                        }
                    });// end droppable init
            };// end $.fn.domultidrop

            $.fn.domultidrop.defaults = {
                'correct'     :'#B1EBAB',
                'incorrect'   :'#EBB7AB',
                'allcorrect'  :'#B1EBAB'
            };
            //
            // end domultidrop
            //

            //
            //  dostepwise
            //  Stepwise process with animated pop-ups
            //
            $.fn.dostepwise = function(options) {
                //console.log('$.fn.dostepwise'); // jshint ignore:line
                var $this = $(this);
                var opts = $.extend({}, $.fn.dostepwise.defaults, options);
                var dostepwiseDialogArr = [];
                $this.find('.arrow').each( function(index, element) {
                        $(this).empty();
                        var paper = Raphael(element, 100, 60);
                        paper.path('M 30 10 L 30 30 L 10 30 L 50 50 L 90 30 L 70 30 L 70 10 Z').attr({
                                fill: 'gray',
                                stroke: 'black',
                                'stroke-linecap': 'round',
                                'stroke-width': 5,
                                'stroke-linejoin': 'round'
                            });
                    });

                // init dialogs for each step
                $this.find('.step-detail').each( function(index, element) {

                        var $el = $(element);

                        dostepwiseDialogArr[index] = $el.dialog({
                                autoOpen: false,
                                modal: true,
                                buttons: {
                                    'Ok': function() {
                                        $(this).dialog('close');
                                    }
                                },
                                title: $el.siblings('.step-title').text(),
                                width: $el.width(),
                                show: 'blind',
                                hide: 'blind'
                            });
                    });// end dialog inits

                // Click funct for each step (launches dialog)
                $this.find('.step').click( function(e) {
                        var $this = $(this);
                        var targetIndex = $this.parent().find('.step').index( $this );
                        //console.log( targetIndex ); // jshint ignore:line
                        dostepwiseDialogArr[ targetIndex ].dialog('open');
                    });// end click funct init

            };// end $.fn.dostepwise
            $.fn.dostepwise.defaults = {};
            //
            // end dostepwise
            //

            //
            //  dosequential
            //  Sequential animation
            //
            $.fn.dosequential = function(options) {
                //console.log('$.fn.dosequential'); // jshint ignore:line
                var opts = $.extend({}, $.fn.dosequential.defaults, options);
                var $this = $(this);
                //console.log( $this ); // jshint ignore:line
                // Set bg and bg size for the parent element of the divs
                var $bgimg = $this.find('img.sequential-bg');
                // css to make bgimage the background of the content area
                $this.find('.sequential-cont').css({
                        'background': 'url(' + $bgimg.attr('src') + ')',
                        'background-size': $bgimg.attr('width') + 'px ' + $bgimg.attr('height') + 'px',
                        '-moz-background-size': $bgimg.attr('width') + 'px ' + $bgimg.attr('height') + 'px'
                    });// end add bg to click target stack

                // Create dialogs
                var dialogArray = [];
                $this.find('div.item-details').each( function(index, element) {
                        dialogArray[index] = $(element).dialog({
                                autoOpen: false,
                                modal: true,
                                dialogClass: 'sequential-dialog',
                                width: '80%',
                                buttons: {
                                    'Ok': function() {
                                        $(this).dialog('close');
                                    }
                                }
                            });
                    });// end create dialogs

                // Add click event to items and display each one in sequence
                $this.find('div.sequential-item').each(function(index, element) {
                        var $el = $(element);
                        $el.click(function(e) {
                                //console.log( index ); // jshint ignore:line
                                dialogArray[index].dialog('open');
                            });

                        $el.delay(2000 * index).show('blind', 1000);

                    });// end click event init

            };// end $.fn.dosequential

            $.fn.dosequential.defaults = {};
            //
            // End dosequential
            //

            //
            // Markit
            //
            $.fn.markit = function(options) {
                //console.log('$.fn.markit');
                var opts = $.extend({}, $.fn.markit.defaults, options);
                var $this = $(this);
                if ($this.find(".draw").length > 0) { return; } // Don't initialize if .draw already exists.
                $this.append('<div class="draw"></div>')
                //console.log( $this ); // jshint ignore:line

                $this[0].ontouchmove = function(e){ e.preventDefault(); };// Prevents ipad touch from scrolling page, so drag can be captured

                var width = $this.css('width');
                width = width.replace('px','');
                width = Number( width );

                var height = $this.css('height');
                height = height.replace('px','');
                height = Number( height );

                var g_masterPaper = Raphael($this.find(".draw")[0], width, height); // Create raphael paper
                var masterBackground = g_masterPaper.rect(0,0,width,height); // Draw rectangle inside raph paper
                masterBackground.attr("fill", "#000000");// Background color of drawing rectangle
                masterBackground.attr("fill-opacity",0);// Opacity of this bgcolor
                masterBackground.attr("stroke","#000000");// Opacity of this bgcolor
                masterBackground.attr('stroke-opacity',0);// Turn off rectangle border. We will give this to the svg in the css.

                var loadSet = g_masterPaper.set();  // Set for items drawn on load of page
                var checkSet = g_masterPaper.set(); // Set for items drawn when check button is pressed
                var drawSet = g_masterPaper.set();  // Set for items drawn by drag interaction

                masterBackground.mousemove( function(event) {
                        //console.log('mousemove'); // jshint ignore:line
                        var evt = event;
                        var IE = document.all?true:false;
                        var x, y;
                        if (IE) {
                            x = evt.clientX + document.body.scrollLeft +
                                document.documentElement.scrollLeft;
                            y = evt.clientY + document.body.scrollTop +
                                document.documentElement.scrollTop;
                        }
                        else {
                            x = evt.pageX;
                            y = evt.pageY;
                        }
                        // subtract paper coords on page
                        var paperOffset = $this.offset();// get paper x and paper y
                        this.ox = x - paperOffset.left;
                        this.oy = y - paperOffset.top;
                    });

                var g_masterDrawingBox;

                var start = function () {// Drag event starts
                    //console.log('start'); // jshint ignore:line
                    g_masterPathArray = new Array();// Array for drawn path
                    toggleDisabled($objsForToggleDisabled );// Enable undo and clear btns
                },
                move = function (dx, dy) {// Drag event mousemove
                    //console.log('move'); // jshint ignore:line
                    if (g_masterPathArray.length == 0) {
                        g_masterPathArray[0] = ["M",this.ox,this.oy];
                        g_masterDrawingBox = g_masterPaper.path(g_masterPathArray);
                        g_masterDrawingBox.attr({
                            'stroke': opts.utensils[opts.utensilIndex].stroke,
                            'stroke-width': opts.utensils[opts.utensilIndex].strokeWidth,
                            'stroke-opacity': opts.utensils[opts.utensilIndex].strokeOpacity,
                            'stroke-linecap':'round',
                            'stroke-linejoin':'round'
                        });
                    }
                    else {
                        g_masterPathArray[g_masterPathArray.length] =["L",this.ox,this.oy];
                        g_masterDrawingBox.attr({path: g_masterPathArray});
                    }
                },
                up = function () {// Drag event ends
                    drawSet.push(g_masterDrawingBox);// Put assembled path object into drawSet for later access
                    //console.log( drawSet ); // jshint ignore:line
                };

                masterBackground.drag(move, start, up);

                //
                //  MARKIT BUTTONS
                //

                // Add controls container.
                $controls = $this.siblings(".markit-controls-cont").children(".markit-controls");
                if ($controls.length == 0) {

                    $this.after('<div class="markit-controls-cont"><div class="markit-controls"></div></div>');
                    $controls = $this.siblings(".markit-controls-cont").children(".markit-controls");

                    // Add pencil button.
                    var penbtn = ' ';
                        penbtn += '<div class="use-pen-btn control selected" title="'+M.util.get_string('usepenbuttonlabel','filter_rein')+'">';
                        penbtn += '<div class="icon"></div>';
                        penbtn += '<div class="label"></div>';
                        penbtn += '</div>';

                    $controls.append(penbtn);

                    // Add highlight button.
                    var highlightbtn = ' ';
                        highlightbtn += '<div class="use-highlight-btn control" title="'+M.util.get_string('usehighlightbuttonlabel','filter_rein')+'">';
                        highlightbtn += '<div class="icon"></div>';
                        highlightbtn += '<div class="label"></div>';
                        highlightbtn += '</div>';

                    $controls.append(highlightbtn);

                    // Add undo button.
                    var undobtn = ' ';
                        undobtn += '<div class="undo-btn control disabled" title="'+M.util.get_string('undobuttonlabel','filter_rein')+'">';
                        undobtn += '<span class="ui-icon ui-icon ui-icon-arrowreturnthick-1-w"></span>';
                        undobtn += '<span class="label">'+M.util.get_string('undobuttonlabel','filter_rein')+'</span>';
                        undobtn += '</div>';

                    $controls.append(undobtn);

                    // Add clear button.
                    var clearbtn = ' ';
                        clearbtn += '<div class="clear-btn control disabled" title="'+M.util.get_string('clearbuttonlabel','filter_rein')+'">';
                        clearbtn += '<span class="ui-icon ui-icon ui-icon-close"></span>';
                        clearbtn += '<span class="label">'+M.util.get_string('clearbuttonlabel','filter_rein')+'</span>';
                        clearbtn += '</div>';

                    $controls.append(clearbtn);

                }

                /* Designer mode controls. */
                if ($this.hasClass("designermode")) {

                    function smoothpaths() {
                        drawSet.forEach(function(e) {
                            var path = e.attr("path").toString();
                            if (path.search(/[lL]/) != -1) {
                                // Separate out the first path.
                                var paths = path.split("L");
                                var fistPath = paths[0];
                                paths.shift();
                                // Go through remaining line paths and eliminate any that are
                                // less than 30 pixels from each other.
                                var newpaths = [];
                                for (var i=0; i<paths.length; i++) {
                                    var xdiff = 0;
                                    var ydiff = 0;
                                    if (newpaths.length > 0) {
                                        var oldcoords = newpaths[newpaths.length-1].split(',');
                                        var newcoords = paths[i].split(',');
                                        var xdiff = Math.abs(oldcoords[0] - newcoords[0]);
                                        var ydiff = Math.abs(oldcoords[1] - newcoords[1]);
                                    }
                                    if (i==0 || i==paths.length-1 || xdiff>30 || ydiff>30) {
                                        newpaths.push(paths[i]);
                                    }
                                }
                                // Convert to Catmull-Rom path.
                                // http://en.wikipedia.org/wiki/Cubic_Hermite_spline#Catmull.E2.80.93Rom_spline
                                var catmullrompath = fistPath + " R" + newpaths.join(" ");
                                e.attr("path", catmullrompath);
                            }
                        });
                    }

                    var smoothbtn = ' ';
                        smoothbtn += '<div class="smooth-btn control disabled" title="'+M.util.get_string('smoothlinesbuttonlabel','filter_rein')+'">';
                        smoothbtn += '<span class="ui-icon ui-icon-shuffle"></span>';
                        smoothbtn += '<span class="label">'+M.util.get_string('smoothlinesbuttonlabel','filter_rein')+'</span>';
                        smoothbtn += '</div>';

                    $controls.append(smoothbtn);
                    $controls.find('.smooth-btn').click(smoothpaths);

                    var clipboardbtn = ' ';
                        clipboardbtn += '<div class="markup-btn control disabled" title="'+M.util.get_string('getmarkupbuttonlabel','filter_rein')+'">';
                        clipboardbtn += '<span class="ui-icon ui-icon-clipboard"></span>';
                        clipboardbtn += '<span class="label">'+M.util.get_string('getmarkupbuttonlabel','filter_rein')+'</span>';
                        clipboardbtn += '</div>';

                    $controls.append(clipboardbtn);
                    $controls.find('.markup-btn').click(function() {
                        var pathhtml = "";
                        drawSet.forEach(function(e) {
                            pathhtml += '<div class="draw-path">\n';
                            pathhtml += '   <div class="path">\n';
                            pathhtml += '       '+e.attr('path')+'\n';
                            pathhtml += '   </div>\n';
                            pathhtml += '   <div class="stroke">'+e.attr('stroke')+'</div>\n';
                            pathhtml += '   <div class="stroke-width">'+e.attr('stroke-width')+'</div>\n';
                            pathhtml += '   <div class="stroke-opacity">'+e.attr('stroke-opacity')+'</div>\n';
                            pathhtml += '</div>\n';
                        });
                        if (pathhtml != "") {
                            $("#markupclipboard pre").text(pathhtml);
                            markupmodal.dialog('open');
                        }
                    });

                    // Add modal for displaying markup:
                    var markupdiv = '<div id="markupclipboard">';
                        markupdiv += '<p>'+M.util.get_string('markitmodalmarkupinstructions','filter_rein')+'</p>';
                        markupdiv += '<pre></pre>';
                        markupdiv += '</div>';

                    $this.append(markupdiv);

                    var markupmodal = $("#markupclipboard").dialog({
                        autoOpen: false,
                        modal: true,
                        buttons: {
                            'Ok': function() {
                                $(this).dialog('close');
                            }
                        },
                        title: M.util.get_string('markitmodalmarkuptitle','filter_rein'),
                        width: 600
                    });
                }
                /* End designer mode controls. */

                function usepen(obj) {
                    opts.utensilIndex = 0;// Change the current markit cursor to the pen
                    $(obj).addClass('selected').siblings('.use-highlight-btn').removeClass('selected');// remove highlight class from highligher, add to pen
                    $this.find('svg').css('cursor','url('+M.cfg.wwwroot+'/filter/rein/pix/pencil-flip.png), pointer');// update svg css cursor
                }// End use pen

                function usehighlight(obj) {
                    opts.utensilIndex = 1;// Change the current markit cursor to the highlight
                    $(obj).addClass('selected').siblings('.use-pen-btn').removeClass('selected');// remove highlight class from pen btn, add to highlight
                    $this.find('svg').css('cursor','url('+M.cfg.wwwroot+'/filter/rein/pix/highlight-flip.png), pointer');// Change css cursor to higlight marker
                }// End use highlight

                function clearmarkit() {
                    //console.log('clearMarkit()'); // jshint ignore:line

                    if ( !$objsForToggleDisabled.hasClass('disabled') ) {
                        var i = drawSet.items.length - 1;
                        for (i;i>-1;i--) {
                            //console.log('index is '+i);// jshint ignore:line

                            drawSet.items[i].remove();// Remove element from paper
                            drawSet.items.splice(i,1);// Clean up arrays
                            drawSet.splice(i,1);

                        }
                    }
                    $objsForToggleDisabled.addClass('disabled');
                }// End clearMarkit()

                // TODO: There is a very difficult to replicate bug in clearMarkit(), seemingly related with mutiple drawn paths, use of both utensils, and the selection of undo several times in a row very quickly...

                function undomarkit() {
                    //console.log('undoMarkit()');// jshint ignore:line

                    if ( !$objsForToggleDisabled.hasClass('disabled') ) {
                        var i = drawSet.items.length - 1;
                        //console.log('index is '+i);// jshint ignore:line
                        drawSet.items[i].remove();// Remove element from paper
                        drawSet.items.splice(i,1);// Clean up arrays
                        drawSet.splice(i,1);
                    }

                    if (drawSet.items.length === 0) {
                        $objsForToggleDisabled.addClass('disabled');
                    }
                }// End undoMarkit()


                $('.markit-controls .use-highlight-btn').click( function(e) { // Highlight button click listener
                        //console.log('highlight selected');// jshint ignore:line
                        var $this = $(this);
                        //console.log($this);// jshint ignore:line
                        usehighlight($this);

                    });// End highlight click listener

                $('.markit-controls .use-pen-btn').click( function(e) { // Pen button click listener
                        //console.log('highlight selected');// jshint ignore:line
                        var $this = $(this);
                        //console.log($this);// jshint ignore:line
                        usepen($this);

                    });// End pen click listener

                $('.markit-controls .clear-btn').click( function(e) { // Pen button click listener
                        //console.log('clear btn selected');// jshint ignore:line
                        clearmarkit();
                    });// End pen click listener


                $('.markit-controls .undo-btn').click( function(e) { // Pen button click listener
                        //console.log('clear btn selected');// jshint ignore:line
                        undomarkit();
                    });// End pen click listener

                /* Check for .draw-on-check content create 'check it' button. */
                if ($.trim($('.draw-on-check').html())) {

                    var checkitbtn = ' ';
                        checkitbtn += '<div class="checkit-btn control disabled" title="'+M.util.get_string('getcheckitbuttonlabel','filter_rein')+'">';
                        checkitbtn += '<span class="ui-icon ui-icon-check"></span>';
                        checkitbtn += '<span class="ui-icon ui-icon-close"></span>';
                        checkitbtn += '<span class="label">'+M.util.get_string('getcheckitbuttonlabel','filter_rein')+'</span>';
                        checkitbtn += '</div>';

                    $('.markit-controls').append(checkitbtn);
                    $(".markit-controls .checkit-btn").click( function(e) {
                        if ($(this).hasClass("disabled")) {
                            return;
                        }
                        $(this).toggleClass('showing')
                        if (!$(this).hasClass('showing')) {
                            drawSet.attr({'opacity':1});
                            checkSet.hide();
                        } else {
                            checkSet.show();
                            drawSet.attr({'opacity':.3});
                        }
                    });

                }

                var $objsForToggleDisabled = $controls.find('.undo-btn, .clear-btn, .smooth-btn, .markup-btn, .checkit-btn'); // Declare here so we reduce number of queries
                function toggleDisabled(objs) {
                    // toggles disabled state for both undo and clear buttons
                    // used when clear btn is selected, and when a draw event occurs
                    //console.log('toggleDisabled()');// jshint ignore:line
                    if ($(objs).hasClass('disabled')){

                        $(objs).removeClass('disabled');
                    }
                }// End toggleDisabled()

                $('.markit-controls .control').button();// skin markit controls

                //
                // END MARKIT CONTROLS
                //

                //
                // Draw text, lines, images, and paths from markup.
                //
                function drawelements(el, set){
                    var $el = $(el);
                    if ($el.hasClass("draw-text")) {
                        // The following check that required and optional values exist. If none are supplied, a default value is applied.
                        var txt = $el.children('.text').html();
                        var txtRet = ( txt && txt.length >= 1 ) ? String( txt ) : 'Text string not provided!';// No sense going on without this value

                        var x = $el.children('.x').html();
                        var xRet = ( x && x.length >= 1 ) ? Number( x ) : 100;

                        var y = $el.children('.y').html();
                        var yRet = ( y && y.length >= 1 ) ? Number( y ) : 100;

                        var fontFamily = $el.children('.font-family').html();
                        var fontFamilyRet = ( fontFamily && fontFamily.length >= 1 )?String( fontFamily ):'Helvetica, Arial, sans-serif';

                        var fontSize = $el.children('.font-size').html();
                        var fontSizeRet = ( fontSize && fontSize.length >= 1 )?Number( fontSize ):20;

                        var fill = $el.children('.fill').html();
                        var fillRet = ( fill && fill.length >= 1 )?String( fill ):'#000';

                        var opacity = $el.children('.opacity').html();
                        var opacityRet = (opacity && opacity.length >= 1 )?Number( opacity ):1;

                        var fontWeight = $el.children('.font-weight').html();
                        var fontWeightRet = ( fontWeight && fontWeight.length >= 1 )?String( fontWeight ):'bold';

                        var elem = g_masterPaper.text( xRet, yRet, txtRet ).attr({
                                'font-family': fontFamilyRet,
                                'fill': fillRet,
                                'font-size': fontSizeRet,
                                'font-weight': fontWeightRet,
                                'opacity': opacityRet,
                                'text-anchor': 'start'
                            });

                    } else if ($el.hasClass("draw-line")) {
                        // The following check that required and optional values exist. If none are supplied, a default value is applied.
                        var startx = $el.children('.startx').html();
                        var startxReturn = ( startx && startx.length >= 1 )?Number( startx ):0;

                        var starty = $el.children('.starty').html();
                        var startyReturn = ( starty && starty.length >= 1 )?Number( starty ):100;

                        var stopx = $el.children('.stopx').html();
                        var stopxReturn = ( stopx && stopx.length >= 1 )?Number( stopx ):200;

                        var stopy = $el.children('.stopy').html();
                        var stopyReturn = ( stopy && stopy.length >= 1 )?Number( stopy ):100;

                        var strokeWidth = $el.children('.stroke-width').html();
                        var strokeWidthReturn = ( strokeWidth && strokeWidth.length >= 1 )?Number( strokeWidth ):3;

                        var stroke = $el.children('.stroke').html();// Color of the stroke
                        var strokeReturn = ( stroke && stroke.length >= 1 ) ? String( stroke ) : '#000';

                        var strokeOpacity = $el.children('.stroke-opacity').html();
                        var strokeOpacityReturn = ( strokeOpacity && strokeOpacity.length >= 1 ) ? Number( strokeOpacity ):1;

                        // TODO: note this difference from pw sketchpad when adapting documentation
                        //var strokeLinecap = element.children('.stroke-linecap').html();
                        //var strokeLinecapReturn = ( element.children('.stroke-linecap').html() && element.children('.stroke-linecap').html().length >= 1 )?String( element.children('.stroke-linecap').html() ):'round';

                        var elem = g_masterPaper.path('M'+startxReturn+','+startyReturn+' L'+stopxReturn+','+stopyReturn).attr({
                                'stroke-width': strokeWidthReturn,
                                'stroke': strokeReturn,
                                'stroke-opacity': strokeOpacityReturn,
                                'stroke-linecap': 'round'
                            });

                    } else if ($el.hasClass("draw-image")) {
                        // The following check that required and optional values exist. If none are supplied, a default value is applied.
                        var imgSrc = $el.children('.src').html();
                        var imgSrcReturn = ( imgSrc && imgSrc.length >= 1 )?String(imgSrc):alert('Image src not specified.');// alert rather than default value. We cannot proceed without an img src

                        var imgX = $el.children('.x').html();
                        var imgXReturn = (imgX && imgX.length >= 1)?Number(imgX):0;

                        var imgY = $el.children('.y').html();
                        var imgYReturn = (imgY && imgY.length >= 1 )?Number(imgY):0;

                        var imgWidth = $el.children('.width').html();
                        var imgWidthReturn = (imgWidth && imgWidth.length >= 1)?Number(imgWidth):200;

                        var imgHeight = $el.children('.height').html();
                        var imgHeightReturn = (imgHeight && imgHeight.length >= 1)?Number(imgHeight):200;

                        var elem = g_masterPaper.image( imgSrcReturn, imgXReturn, imgYReturn, imgWidthReturn, imgHeightReturn );

                    } else if ($el.hasClass("draw-path")) {

                        var path = $el.children('.path').html();
                        var pathReturn = ( path && path.length >= 1 )?String(path):alert('Path not specified.');// alert rather than default value. We cannot proceed without a path

                        var strokeWidth = $el.children('.stroke-width').html();
                        var strokeWidthReturn = ( strokeWidth && strokeWidth.length >= 1 )?Number( strokeWidth ):3;

                        var stroke = $el.children('.stroke').html();// Color of the stroke
                        var strokeReturn = ( stroke && stroke.length >= 1 ) ? String( stroke ) : '#000';

                        var strokeOpacity = $el.children('.stroke-opacity').html();
                        var strokeOpacityReturn = ( strokeOpacity && strokeOpacity.length >= 1 ) ? Number( strokeOpacity ):1;

                        var elem = g_masterPaper.path(path).attr({
                                'stroke-width': strokeWidthReturn,
                                'stroke': strokeReturn,
                                'stroke-opacity': strokeOpacityReturn,
                                'stroke-linecap': 'round'
                            });

                    }
                    set.push(elem);
                }// End draw elements

                /* Draw .draw-on-load items. */
                $this.find('.draw-on-load').children().each(function(i,el) {
                    drawelements(el, loadSet);
                });

                /* Draw .draw-on-check items. */
                $this.find('.draw-on-check').children().each(function(i,el) {
                    drawelements(el, checkSet);
                });

                checkSet.hide();

                masterBackground.toFront();


                //
                // END DRAW FROM MARKUP
                //

                return g_masterPaper;

            };

            $.fn.markit.defaults = {
                'utensils' : [
                    {// Pen default settings
                        'stroke':'#000',
                        'strokeWidth': 3,
                        'strokeOpacity':1,
                        'cursor':function() { if (M) { return 'url('+M.cfg.wwwroot+'/theme/idstandard/pix/pencil-flip.png), auto;'; } }
                    },
                    {// Highlight default settings
                        'stroke':'#EDF30C',
                        'strokeWidth':10,
                        'strokeOpacity':0.5,
                        'cursor':function() { if (M) { return 'url('+M.cfg.wwwroot+'/theme/idstandard/pix/highlight-flip.png), auto;'; } }
                    }
                ],
                'utensilIndex': 0
            };// End markit defaults

            //
            // End markit
            //

            //
            // jQuery UI tooltip
            //
            $.fn.dotip = function(options) {
                var opts = $.extend({}, $.fn.dotip.defaults, options);

                $('.tip-trigger').tooltip({
                    items: "img, span, div, [title]",
                    classes: {
                        "ui-tooltip": "rein-tooltip"
                    },
                    track: true,
                    content: function() {
                        var $this = $(this);
                        // If .tip-trigger has a title attribute, use that for the tooltip.
                        if ($this.is( "[title]")) {
                            return $this.attr( "title" );
                        }
                        // If .tip-trigger is an img, use the alt for the tooltip.
                        if ($this.is("img")) {
                            return $this.attr("alt");
                        }
                        // If .tip-trigger is a span or div with no title attribute, use another element
                        // with a specified ID for the tooltip.
                        if ($this.is("span") || $this.is("div")) {
                            var sourceid = $this.attr('id');
                            var tipcontentid = '#tip-source-'+sourceid;
                            return $(tipcontentid).html();
                        }
                    }
                });
            };

            $.fn.dotip.defaults = {};

            //
            // End tooltip
            //


            //
            // Responsive tables
            //

            $.fn.doresponsivetables = function() {
                $(this).each(function() {
                    var table = $(this).find("table");
                    var th = table.find("th");
                    if (th.length > 0) {
                        // Find and store table headers
                        var headerlabels = new Array();
                        th.each(function() {
                            headerlabels.push($(this).text());
                        });

                        // store data-label attribute in individual data cells
                        table.find("tr").each(function(i,v) {
                            var row = $(this);
                            var td = row.find("td");
                            if (headerlabels.length == td.length) {
                                td.each(function(j,v) {
                                    var label = $(this).attr('data-label');
                                    // Write data-label to td if one is not present.
                                    // For some browsers, `label` is undefined; for others,
                                    // `label` is false.  Check for both.
                                    if (typeof label === typeof undefined || label === false) {
                                        $(this).attr("data-label", headerlabels[j]);
                                    }
                                });
                            }
                        });
                    }
                });
            };

            //
            // End responsive tables
            //

            // jQuery UI dialog/modal
            //
            $.fn.domodal = function(options) {
                var opts = $.extend({}, $.fn.domodal.defaults, options);

                var $modallink = $(this);
                var modaldetailid = $modallink.attr("data-modaldetail");
                var $modaldetail = $('#'+modaldetailid);

                // init dialogs for
                var modal = $modaldetail.dialog({
                    autoOpen: false,
                    modal: true,
                    buttons: {
                        'Ok': function() {
                            $(this).dialog('close');
                        }
                    },
                    title: $modallink.attr("title"),
                    width: $modaldetail.width(),
                    beforeClose: function() {
                        var iframe = modal.find("iframe");
                        if (iframe.length > 0) {
                            iframe.each(function() {
                                // Reset the src attribute so any embedded iframes don't continue functioning
                                // after close.
                                var src = $(this).attr("src");
                                $(this).attr("src", "");
                                $(this).attr("src", src);
                            });
                        }
                    }
                });
                // end dialog init

                // click init function
                $modallink.click( function(e) {
                    modal.dialog('open');
                    e.preventDefault();
                });
                // end click function init

            }

            $.fn.domodal.defaults = {};
            //
            // End jQuery UI dialog/modal
            //

            //
            // Equal columns
            //
            $.fn.doequalcolumns = function() {
                $(this).each(function() {
                    // Add in classes for the number of columns to size columns evenly.
                    var numcolumns = $(this).children(".equal-column").length;
                    $(this).children(".equal-column").addClass("equal-column-"+numcolumns);
                });
            }
            //
            // End equal columns
            //

            //
            // Toggle
            //
            $.fn.dotoggle = function() {
                $(this).each(function() {
                    // Assign click functionality to button/link
                    $(this).unbind("click");
                    $(this).click(function() {
                        var sel = $(this).attr("data-target");
                        var $target = $(sel);
                        if ($target.hasClass("toggle-hidden")) {
                            $target.removeClass("toggle-hidden");
                            $target.addClass("toggle-showing");
                            $target.css("height", "");
                            var targetheight = $target.height();
                            $target.height(0);
                            $target.height(targetheight);
                        } else {
                            $target.removeClass("toggle-showing");
                            $target.height(0);
                            $target.addClass("toggle-hidden");
                        }
                    });
                    // Read height, hide, and initialize target.
                    var sel = $(this).attr("data-target");
                    var $target = $(sel);
                    $target.addClass("toggle-hidden");
                    $target.height(0);
                    $target.addClass("toggle-initialized");
                });
            }
            //
            // End toggle
            //

            $.fn.flipbook = function(options) {
                /**
                 * Number of books on a page.
                 */
                var bookcount = this.length;

                this.each(function() {
                    /**
                     * Store the booklet DOM object.
                     */
                    var $booklet = $(this);
                    /**
                     * Setup options object and override defaults with data attributes in html.
                     */
                    var ops = $.extend({}, $.fn.flipbook.defaults, options);
                    if ($booklet.attr("data-singlepagewidth")) {
                        ops.singlepagewidth = Number($booklet.attr("data-singlepagewidth"));
                    }
                    /**
                     * Detect animation support (requires modernizer).
                     */
                    var animsupport = $("html").hasClass("csstransitions");
                    /**
                     * CSS properties for animation.
                     */
                    var transforms = new Array(
                        "-webkit-transform",
                        "-moz-transform",
                        "-o-transform",
                        "-ms-transform",
                        "transform"
                    );
                    /**
                     * Create placeholder for page flip animations.
                     */
                    $booklet.append("<div class='flip-book-transition'></div>");
                    /**
                     * Set current page to start page.
                     */
                    ops.curpage = ops.startpage;

                    /**
                     * Size all pages to be the same height.
                     */
                    function set_page_size() {
                        if ($(window).width() <= ops.singlepagewidth && !$booklet.hasClass("vertical")) {
                            $booklet.addClass("singlepage");
                            $booklet.parent().addClass("singlepage");
                        } else {
                            $booklet.removeClass("singlepage");
                            $booklet.parent().removeClass("singlepage");
                        }
                        show_cur_pages();
                        var tallestheight = 0;
                        $booklet.find(".page").each(function(index) {
                            $(this).addClass("tempshow");
                            $(this).css("height", "auto");
                            if ($(this).height() > tallestheight) {
                                tallestheight = $(this).height();
                            }
                            $(this).removeClass("tempshow")
                        });
                        $booklet.find(".page").height(tallestheight);
                        if ($booklet.hasClass("vertical")) {
                            $booklet.height(tallestheight * 2);
                        } else {
                            $booklet.height(tallestheight);
                        }
                    }
                    $(window).resize(set_page_size);
                    set_page_size();

                    /**
                     * Show the page(s) that should show.
                     */
                    function show_cur_pages() {
                        // Go back a page if moving from single page to two page views if the current page is odd.
                        if (ops.curpage/2 != Math.floor(ops.curpage/2) && !$booklet.hasClass("singlepage")) {
                            ops.curpage--;
                        }
                        check_page_position();
                        // Hide all but pages besides current pages.
                        $booklet.find(".cur").removeClass("cur");
                        $booklet.find(".page:eq("+(ops.curpage)+")").addClass("cur");
                        if (!$booklet.hasClass("singlepage")) {
                            $booklet.find(".page:eq("+(ops.curpage+1)+")").addClass("cur");
                        }
                    }
                    show_cur_pages();

                    /**
                     * Check for page position and add .atfirstpage or .atlastpage classes where appropriate.
                     */
                    function check_page_position() {
                        // Set .atfirstpage class if on the first page.
                        if (ops.curpage == 0) {
                            $booklet.addClass("noleftstack");
                        } else {
                            $booklet.removeClass("noleftstack");
                        }
                        if (ops.curpage == 2 && !$booklet.hasClass("singlepage")) {
                            $booklet.addClass("singleleftstack");
                        } else {
                            $booklet.removeClass("singleleftstack");
                        }
                        if (ops.curpage == 4 && !$booklet.hasClass("singlepage")) {
                            $booklet.addClass("doubleleftstack");
                        } else {
                            $booklet.removeClass("doubleleftstack");
                        }
                        if ((ops.curpage == $booklet.find(".page").length-3 && $booklet.hasClass("singlepage"))
                                || (ops.curpage+1 == $booklet.find(".page").length-5 && !$booklet.hasClass("singlepage"))) {
                            $booklet.addClass("doublerightstack");
                        } else {
                            $booklet.removeClass("doublerightstack");
                        }
                        if ((ops.curpage == $booklet.find(".page").length-2 && $booklet.hasClass("singlepage"))
                                || (ops.curpage+1 == $booklet.find(".page").length-3 && !$booklet.hasClass("singlepage"))) {
                            $booklet.addClass("singlerightstack");
                        } else {
                            $booklet.removeClass("singlerightstack");
                        }
                        if ((ops.curpage == $booklet.find(".page").length-1 && $booklet.hasClass("singlepage"))
                                || (ops.curpage+1 == $booklet.find(".page").length-1 && !$booklet.hasClass("singlepage"))) {
                            $booklet.addClass("norightstack");
                        } else {
                            $booklet.removeClass("norightstack");
                        }
                        pglen = $booklet.find(".page").length;
                        cp = pglen - ops.curpage;
                        if (cp == 1) {
                            $booklet.addClass("norightstack");
                        }
                    }

                    /**
                     * Clone pages for animation.
                     */
                    function clone_pages(page1,page2) {
                        $booklet.find('.flip-book-transition').html('');
                        $booklet.find(".page:eq("+(page1)+")").clone().appendTo($booklet.find('.flip-book-transition'));
                        if (page2) {
                            $booklet.find(".page:eq("+(page2)+")").clone().appendTo($booklet.find('.flip-book-transition'));
                        }
                    }

                    /**
                     * Apply browser specific transforms for animation.
                     */
                    function set_transform($el, t) {
                        for (var i = 0; i < transforms.length; i++) {
                            $el.css(transforms[i], t);
                        }
                    }

                    /**
                     * Show the next page(s) and handle animation if supported.
                     */
                    function next_page() {
                        // Show the pages that are supposed to be showing.
                        show_cur_pages();
                        set_page_size();

                        // Increase pages to show.
                        ops.curpage++;
                        if (!$booklet.hasClass("singlepage")) {
                            ops.curpage++;
                        }
                        if (ops.curpage >= $booklet.find(".page").length - $booklet.find('.flip-book-transition .page').length) {
                            ops.curpage--;
                            if (!$booklet.hasClass("singlepage")) {
                                ops.curpage--;
                            }
                            return; // On the last page.
                        }
                        var curpage = ops.curpage;

                        if (animsupport) {

                            check_page_position();

                            if (!$booklet.hasClass("singlepage")) {
                                // Clone pages for animation.
                                clone_pages(curpage,curpage-1);

                                // Turn page on the right up.
                                $rightpage = $booklet.find('.flip-book-transition .page:eq(1)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($rightpage, 'rotateX(0deg)');
                                } else {
                                    set_transform($rightpage, 'rotateY(0deg)');
                                }
                                $rightpage.height();
                                $rightpage.addClass("turnanimation");
                                $rightpage.addClass("shade");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($rightpage, 'rotateX(180deg)');
                                } else {
                                    set_transform($rightpage, 'rotateY(-180deg)');
                                }
                                $rightpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $rightpage.remove();
                                    }
                                );
                                $booklet.find(".page:eq("+(curpage-1)+")").removeClass("cur");
                                $booklet.find(".page:eq("+(curpage+1)+")").addClass("cur");

                                // Turn page on the left down.
                                $leftpage = $booklet.find('.flip-book-transition .page:eq(0)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(-180deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(180deg)');
                                }
                                $leftpage.addClass("shade");
                                $leftpage.height(); // This is a hack to get jquery to register new classes/styles before proceeding.
                                $leftpage.addClass("turnanimation");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(0deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(0deg)');
                                }
                                $leftpage.removeClass("shade");
                                $leftpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $booklet.find(".page:eq("+(curpage-2)+")").removeClass("cur");
                                        $booklet.find(".page:eq("+(curpage)+")").addClass("cur");
                                        $leftpage.remove();
                                    }
                                );
                            } else {
                                // Clone pages for animation.
                                clone_pages(curpage-1);

                                // Turn page over.
                                $leftpage = $booklet.find('.flip-book-transition .page:eq(0)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(0)');
                                } else {
                                    set_transform($leftpage, 'rotateY(0)');
                                }
                                $leftpage.height(); // This is a hack to get jquery to register new classes/styles before proceeding
                                $leftpage.addClass("turnanimation");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(120deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(-120deg)');
                                }
                                $leftpage.addClass("shade");
                                $leftpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $leftpage.remove();
                                    }
                                );
                                $booklet.find(".page:eq("+(curpage-1)+")").removeClass("cur");
                                $booklet.find(".page:eq("+(curpage)+")").addClass("cur");
                            }
                        } else {
                            show_cur_pages();
                            set_page_size();
                        }
                    }

                    /**
                     * Show the previous page(s) and handle animation if supported.
                     */
                    function prev_page() {

                        // Show the pages that are supposed to be showing.
                        show_cur_pages();
                        set_page_size();

                        // Decrease pages to show.
                        ops.curpage--;
                        if (!$booklet.hasClass("singlepage")) {
                            ops.curpage--;
                        }
                        if (ops.curpage < 0) {
                            ops.curpage++;
                            if (!$booklet.hasClass("singlepage")) {
                                ops.curpage++;
                            }
                            return; // On the first page.
                        }
                        var curpage = ops.curpage;

                        if (animsupport) {

                            check_page_position();

                            if (!$booklet.hasClass("singlepage")) {
                                // Clone pages for animation.
                                clone_pages(curpage+2,curpage+1);

                                // Turn page on the right down.
                                $rightpage = $booklet.find('.flip-book-transition .page:eq(1)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($rightpage, 'rotateX(180deg)');
                                } else {
                                    set_transform($rightpage, 'rotateY(-180deg)');
                                }
                                $rightpage.addClass("shade");
                                $rightpage.height(); // This is a hack to get jquery to register new classes/styles before proceeding.
                                $rightpage.addClass("turnanimation");
                                $rightpage.removeClass("shade");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($rightpage, 'rotateX(0deg)');
                                } else {
                                    set_transform($rightpage, 'rotateY(0deg)');
                                }
                                $rightpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $rightpage.remove();
                                    }
                                );
                                $booklet.find(".page:eq("+(curpage+2)+")").removeClass("cur");
                                $booklet.find(".page:eq("+(curpage)+")").addClass("cur");

                                // Turn page on the left up.
                                $leftpage = $booklet.find('.flip-book-transition .page:eq(0)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(0deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(0deg)');
                                }
                                $leftpage.height();
                                $leftpage.addClass("turnanimation");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(-180deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(180deg)');
                                }
                                $leftpage.addClass("shade");
                                $leftpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $booklet.find(".page:eq("+(curpage+3)+")").removeClass("cur");
                                        $booklet.find(".page:eq("+(curpage+1)+")").addClass("cur");
                                        $leftpage.remove();
                                    }
                                );
                            } else {
                                // Clone pages for animation.
                                clone_pages(curpage);

                                // Turn page over.
                                $leftpage = $booklet.find('.flip-book-transition .page:eq(0)');
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(120deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(-120deg)');
                                }
                                $leftpage.addClass("shade");
                                $leftpage.height(); // This is a hack to get jquery to register new classes/styles before proceeding.
                                $leftpage.addClass("turnanimation");
                                if ($booklet.hasClass('vertical')) {
                                    set_transform($leftpage, 'rotateX(0deg)');
                                } else {
                                    set_transform($leftpage, 'rotateY(0deg)');
                                }
                                $leftpage.removeClass("shade");
                                $leftpage.on(
                                    "transitionend MSTransitionEnd webkitTransitionEnd oTransitionEnd",
                                    function() {
                                        $booklet.find(".page:eq("+(curpage+1)+")").removeClass("cur");
                                        $booklet.find(".page:eq("+(curpage)+")").addClass("cur");
                                        $leftpage.remove();
                                    }
                                );
                            }
                        } else {
                            show_cur_pages();
                            set_page_size();
                        }
                    }

                    /**
                     * Mouse/touch events applied below.
                     * mousedown/touchstart stores the x position for swiping.
                     * Tapping or clicking the right side of the book calls next_page()
                     * Tapping or clicking the left side of the book calls prev_page()
                     * Swiping to the left further than the ops.swipthreshold (default values is 30px) calls next_page()
                     * Swiping to the right further than the ops.swipthreshold (default values is 30px) calls next_page()
                     */
                    $booklet.bind('mousedown touchstart', function(event) {
                        // Store the x position on mousedown/touchstart.
                        if ($booklet.hasClass("vertical")) {
                            var ypos;
                            if (event.originalEvent.touches) {
                                ypos = event.originalEvent.touches[0].pageY - $booklet.offset().top;
                            } else {
                                ypos = event.pageY - $booklet.offset().top;
                            }
                            $(this).attr("data-starty", ypos);
                        } else {
                            var xpos;
                            if (event.originalEvent.touches) {
                                xpos = event.originalEvent.touches[0].pageX - $booklet.offset().left;
                            } else {
                                xpos = event.pageX - $booklet.offset().left;
                            }
                            $(this).attr("data-startx", xpos);
                        }
                        event.preventDefault();
                    });
                    $booklet.bind('mouseup touchend', function(event) {
                        if ($booklet.hasClass("vertical")) {
                            var ypos;
                            if (event.originalEvent.changedTouches) {
                                ypos = event.originalEvent.changedTouches[0].pageY - $booklet.offset().top;
                            } else {
                                ypos = event.pageY - $booklet.offset().top;
                            }
                            var startypos = Number($(this).attr("data-starty"));
                            var dist = ypos - startypos;
                        } else {
                            var xpos;
                            if (event.originalEvent.changedTouches) {
                                xpos = event.originalEvent.changedTouches[0].pageX - $booklet.offset().left;
                            } else {
                                xpos = event.pageX - $booklet.offset().left;
                            }
                            var startxpos = Number($(this).attr("data-startx"));
                            var dist = xpos - startxpos;
                        }

                        // If absolute value of distance is greater than swipe threshold
                        // use swipe direction, else use click position.
                        if (Math.abs(dist) >= ops.swipethreshold) {
                            if (dist < 0) {
                                next_page();
                            } else {
                                prev_page();
                            }
                        } else {
                            if ($booklet.hasClass("vertical")) {
                                if (ypos > $booklet.height()/2) {
                                    next_page();
                                } else {
                                    prev_page();
                                }
                            } else {
                                if (xpos > $booklet.width()/2) {
                                    next_page();
                                } else {
                                    prev_page();
                                }
                            }
                        }
                    });

                    /**
                     * Keyboard events applied below.
                     * Only if there is one book on the page.
                     */
                    if (bookcount == 1) {
                        $(document).keydown(function(event) {
                            if (event.keyCode === 37 || event.keyCode == 38) {
                                prev_page();
                            } else if (event.keyCode == 39 || event.keyCode == 40) {
                                next_page();
                            }
                        });
                    }
                });
            }
            /**
             * Set book defaults.
             */
            $.fn.flipbook.defaults = {
                startpage : 0,
                swipethreshold : 30,
                singlepagewidth : 767
            };

    /**
     * Overlay: creates a modal overlay, like a simplified lightbox
     * presentation, for content within the indicated markup.
     * @param object defaults Default configuration options.
     */
    $.fn.overlay = function() {
        // This is designed to be triggered off a link styled as a thumbnail or button.
        // Any contents we assume are the contents of the overlay.
        // A specific image within the contents with an identifying ID
        // we assume is the thumbnail.
        // <a href="#" class="rein-plugin overlay" data-format="image" data-thumbID="test"
        //    data-thumbwidth="200" data-thumb-maxwidth="800" data-thumb-maxheight="1200"></a>

        // Get the format.
        var format = $(this).attr('data-format') ? $(this).attr('data-format') : $.fn.overlay.defaults.format;
        var thumbnail = $(this).attr('data-thumbID') ? $(this).attr('data-thumbID') : false;
        var thumbwidth, thumbmaxheight, thumbmaxwidth;
        var linktitle = '';
        var thumbnailHTML = null;
        var hasvideo = false;
        // If format is thumbnail, get dimensions and url.
        if (!!thumbnail) {
            var thumbnail = $(this).attr('data-thumbID') ? $(this).attr('data-thumbID') : false;
            var thumbwidth = $(this).attr('data-thumbwidth') ?
                                Number($(this).attr('data-thumbwidth')) :
                                $.fn.overlay.defaults.thumbwidth;
            var thumbmaxheight = $(this).attr('data-thumb-maxheight') ?
                                Number($(this).attr('data-thumb-maxheight')) :
                                $.fn.overlay.defaults.thumbmaxheight;
            var thumbmaxwidth = $(this).attr('data-thumb-maxwidth') ?
                                Number($(this).attr('data-thumb-maxwidth')) :
                                $.fn.overlay.defaults.thumbmaxwidth;
            // Strip out px if it's in there.
            // var thumbwidth = Number(thumbwidth.replace('px', ''));
        } else {
            var linktitle = $(this).attr('title') ? $(this).attr('title') : $.fn.overlay.defaults.title;
        }
        // Set up close button and content.
        var content = '';
        var closebtn = '<i class="close" title="Close" tabindex="2001">&times;</i>';
        if (format === 'image') {
            // If the format is image, assume the first image.
            // We don't assume that any indicated thumbnail is also the featured image.
            content = closebtn + $(this).find('img:first-child').clone().wrap('<p>').parent().html();
        } else {
            var hasvideo = $(content).find('video, iframe');
            content = closebtn + $(this).html();
        }
        // If no existing overlay, generate overlay. Stick at bottom of the page.
        var hasoverlay = $('#rein_overlay').length >= 1;
        if (!hasoverlay) {
            var overlayHTML = '<div id="rein_overlay" class="rein-overlay">' +
                                    '<div class="content" tabindex="2000"></div>' +
                                '</div>';
            $('body').append(overlayHTML);
        }
        // Generate thumbnail markup and insert into link.
        if (!!thumbnail) {
            if (!!thumbwidth){
                // We have thumbwidth, so let's determine ratio,
                // and then do the scaling.
                $thumbnail = $('#' + thumbnail);
                var thumbsrc = $thumbnail.attr('src');
                var actualwidth = $thumbnail.attr('width');
                // The height has been set to 0 by CSS so
                // we have to get the attribute instead.
                var actualheight = $thumbnail.attr('height');
                var ratio = actualwidth/actualheight;
                var scaledheight = thumbwidth/ratio;
                // If the scaled value exceeds the max allowed
                // value, then set to the max allowed value.
                if (scaledheight > thumbmaxheight) {
                    scaledheight = thumbmaxheight;
                }
                if (thumbwidth > thumbmaxwidth) {
                    thumbwidth = thumbmaxwidth;
                }
                thumbnailHTML = '<img src="' + thumbsrc +
                    '" width="' + thumbwidth +
                    '" height="' + scaledheight +
                    '" class="img-responsive" />';
            } else {
                thumbnailHTML = '<img src="' + thumbsrc + '" " class="img-responsive" />';
            }
        }
        // Empty out the link, then insert the thumb if there is one.
        $(this).empty();
        if (!!thumbnail) {
            $(this).html(thumbnailHTML);
        } else {
            $(this).text(linktitle);
        }

        var closeoverlay = function() {
            // Remove show class.
            $('#rein_overlay').removeClass('show ' + format);
            // Empty content.
            $('#rein_overlay .content').empty();
            // Remove listeners.
            $(document).unbind('keyup');
            $('#rein_overlay i').unbind('click');
            // Remove body class.
            $('body').removeClass('rein-overlay-active');
            // Remove vid fullscreen prohibition.
            if (!!hasvideo) {
                if (document.mozFullscreenEnabled) {
                    document.mozFullscreenEnabled = true;
                } else if (document.webkitFullscreenEnabled) {
                    document.webkitFullscreenEnabled = true;
                } else {
                    document.fullscreenEnabled = true;
                }
            }
            // Return focus to the clicked element.
            $(this).focus();
        };
        // Onclick, add our content to the rein_overlay,
        // and show the rein overlay, and add listeners to close it.
        // Remove those listeners on close.
        $(this).bind('click', function(e) {
            // Prevent scroll to top.
            e.preventDefault();
            // Add body class.
            $('body').addClass('rein-overlay-active');
            $('#rein_overlay .content').html(content);
            if (format === 'image') {
                $('#rein_overlay .content .close').focus();
            } else {
                $('#rein_overlay .content').focus();
            }
            // Classes to show and designate type of format.
            $('#rein_overlay').addClass('show ' + format);
            // Listeners for ESC and close click.
            $(document).keyup(function(e) {
                if (e.keyCode == 27) {
                    closeoverlay();
                }
            });
            $('#rein_overlay i').click(function () {
                closeoverlay();
            });
            if (!!hasvideo) {
                // Turn off fullscreen for video elements.
                // This may or may not work.
                if (document.mozFullscreenEnabled) {
                    document.mozFullscreenEnabled = false;
                } else if (document.webkitFullscreenEnabled) {
                    document.webkitFullscreenEnabled = false;
                } else {
                    document.fullscreenEnabled = false;
                }
                // Remove allowfullscreen for video in iframes.
                $('#rein_overlay .content iframe').removeAttr('allowfullscreen');
            }
        });
        // Thumbnail class if necessary, else btn class.
        if (!!thumbnail) {
            $(this).addClass('thumb');
        } else {
            $(this).addClass('btn');
        }
        // Now that everything's set up, show thumbnail if available.
        $('.rein-plugin.overlay > *').addClass('show');
    };
    $.fn.overlay.defaults = {
        format: 'mixed', // mixed or image, for now.
        thumbwidth: 250, // width to scale and present thumbnail, for now.
        thumbID: 'rein_overlay_thumb', // ID of thumbnail to scale.
        title: 'View overlay content.', // Title, to use of there's nothing else inside the link.
        thumbmaxheight: 500,
        thumbmaxwidth: 800
    };

    /**
     * Swiper horizontal scrolling carousel.
     * @return  object  Swiper object.
     */
    $.fn.swipe = function() {
        log_debug('swiper init function');
        // Assign a unique ID to each swiper.
        var collection = $('.rein-plugin.swiper-container');
        var swipers = [];
        var assignIDs = function() {
            log_debug('assignIDs');
            $(collection).each(function(i, el) {
                var id = 'swiper' + i;
                $(el).attr('id', id);
            });
        };
        // Check number type.
        var isnumber = function(num) {
            log_debug('isnumber');
            return (typeof num == 'string' ||
                    typeof num == 'number') &&
                    !isNaN(num - 0) &&
                    num !== '';
        };
        // Set up a single swiper.
        var singleinit = function(el) {
            log_debug('singleinit');
            var options = $.fn.swipe.defaults;
            // Fetch data-* elements.
            // Parallax.
            if ($(el).attr('data-parallax')) {
                var parallax = $(el).attr('data-parallax');
                if (parallax === true || parallax === false) {
                    options.parallax = parallax;
                }
            }
            // slidesPerView.
            if ($(el).attr('data-slidesPerView')) {
                var slidesPerView = $(el).attr('data-slidesPerView');
                if (isnumber(Number(slidesPerView)) && slidesPerView !== 1) {
                    options.slidesPerView = slidesPerView;
                }
            }
            // Freemode.
            if ($(el).attr('data-freemode')) {
                var freemode = $(el).attr('data-freemode');
                if (freemode === true || freemode === false) {
                    options.freemode = freemode;
                }
            }
            // Loop.
            if ($(el).attr('data-loop')) {
                // Fetch data attribute and convert to boolean.
                var loop = ($(el).attr('data-loop') === 'true');
                if (loop === true || loop === false) {
                    options.loop = loop;
                } else {
                    options.loop = false;
                }
            }
            // spaceBetween.
            if ($(el).attr('data-spaceBetween')) {
                var spaceBetween = $(el).attr('data-spaceBetween');
                if (spaceBetween === true || spaceBetween === false) {
                    options.spaceBetween = spaceBetween;
                }
            }
            // autoplay.
            if ($(el).attr('data-autoplay')) {
                var autoplay = $(el).attr('data-autoplay');
                if (autoplay !== null) {
                    options.autoplay = autoplay;
                }
            }
            // zoom.
            if ($(el).attr('data-zoom')) {
                var zoom = $(el).attr('data-zoom');
                if (zoom === true || zoom === false) {
                    options.zoom = zoom;
                }
            }
            // paginationType.
            if ($(el).attr('data-paginationType')) {
                var paginationType = $(el).attr('data-paginationType');
                if (paginationType === 'bullets' ||
                    paginationType === 'fraction' ||
                    paginationType === 'progress') {
                    options.paginationType = paginationType;
                }
            }
            // effect.
            if ($(el).attr('data-effect')) {
                var effect = $(el).attr('data-effect');
                if (effect === 'slide' ||
                    effect === 'fade' ||
                    effect === 'cube' ||
                    effect === 'coverflow' ||
                    effect === 'flip') {
                    options.effect = effect;
                }
            }
            // speed.
            if ($(el).attr('data-speed')) {
                var speed = Number($(el).attr('data-speed'));
                if (isnumber(speed) && speed >= 1 && speed !== 300) {
                    options.speed = speed;
                }
            }
            // Init the swiper on the selected element with the given options.
            // console.log(options);
            var id = $(el).attr('id');
            var sw = new Swiper('#' + id, options);
            log_debug(sw);
            // Now that everything's set up, show thumbnail if available.
            $('#' + id).addClass('show');
            // Update slider presentation after show, just in case layout was not
            // calculated right when the swiper was hidden.
            setTimeout(function(){sw.update();}, 600);
            return sw;
        }
        assignIDs();
        $('.rein-plugin.swiper-container').each(function(i, el) {
            swipers.push(singleinit(el));
        });
        log_debug(swipers);
        return swipers;
    };
    $.fn.swipe.defaults = {
        // ↓↓↓ Fixed parameters for this widget.
        direction: 'horizontal',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        pagination: '.swiper-pagination',
        simulateTouch: false, // Don't allow click and drag on non-touch devices.
        keyboardControl: true,
        freeModeSticky: true, // Stops at a tile after freeMode scroll.
        freeModeMomentumRatio: 0.75, // Swipe momentum settings. Decrease a bit from default.
        freeModeMomentumVelocityRatio: 0.75,
        onInit: function() {
            log_debug('Swiper init.');
        },
        a11y: true, // Accessibility labels. ALWAYS ON.
        observer: true, // Reset whenever the content is altered (ie. show/hide).
        // ↓↓↓ The things which can be set with data elements.
        parallax: false,
        slidesPerView: 1, // Can also be auto, in which case width of slides would be considered.
        freemode: false, // Scrolls multiple on drag.
        loop: false,
        spaceBetween: 0,
        autoplay: null, // Needs to be a number (for delay between transitions) to work.
        zoom: false,
        paginationType: 'bullets', // bullets, fraction, or progress
        effect: 'slide', // Could be "slide", "fade", "cube", "coverflow" or "flip"
        speed: 300 // Duration of transition. Plugin default = 300;
    };

    /**
     * Checks for and warns against complex REIN widgets and
     * navigational elements nested inside of one another.
     * @return boolean False if no nesting issues.
     */
    $.fn.check_widget_nesting = function() {
        var is_nesting = $('.rein-plugin .rein-plugin');
        if (is_nesting.length >= 1) {
            var nesting_issues = $('.doAccordion .doAccordion, ' +
                                    '.doAccordion .doTabs, ' +
                                    '.doAccordion .onedrag, ' +
                                    '.doAccordion .sortMultipleLists, ' +
                                    '.doAccordion .do-multi-drop, ' +
                                    '.do-modal .do-modal, ' +
                                    '.doAccordion .swiper-container, ' +
                                    '.doTabs .doAccordion, ' +
                                    '.doTabs .doTabs, ' +
                                    '.doTabs .onedrag, ' +
                                    '.doTabs .sortMultipleLists, ' +
                                    '.doTabs .do-multi-drop, ' +
                                    '.do-modal .do-modal, ' +
                                    '.do-modal .overlay, ' +
                                    '.doTabs .swiper-container');
            if (nesting_issues.length >= 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    };

    /**
     * Checks for font-awesome in the page and if it's not there
     * injects the link into the page head.
     * @return none
     */
    $.fn.check_fontawesome = function() {
        var span = document.createElement('span');
        span.className = 'fa';
        span.style.display = 'none';
        document.body.insertBefore(span, document.body.firstChild);
        function css(element, property) {
            return window.getComputedStyle(element, null).getPropertyValue(property);
        }
        if (css(span, 'font-family') !== 'FontAwesome') {
            var fa_tag = '<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">';
            $('head').children('script, link').last().after(fa_tag);
        }
        document.body.removeChild(span);
    };

    //
    // Inits for all plugins
    //
    return /** @alias module:filter_rein/rein */ {
        /**
         * reininit rein
         * @access public
         * @param {int} debugmode
         */
        reininit: function(params) {
            debugmode = params.debugmode ? params.debugmode : false;
            g_debugmode = debugmode;

            // Check for complex modules inside of other complex modules.
            var nesting_issues = $('body').check_widget_nesting();

            // Check for FontAwesome and inject it if it's not there.
            // We have to do this with JS because the filters don't have
            // access to the document head.
            var fa = $('body').check_fontawesome();

            if (!nesting_issues) {

                // Button
                $('.doButton').button();

                // Accordion
                $('.doAccordion').accordion({
                    heightStyle: 'content'
                });

                // Tabs
                if ($('.doTabs').length >= 1) {
                    $('.doTabs').each(function(i, el) {
                        // Assign tabs-X class for sizing tabs.
                        $tabmenu = $(this).find("ul:first-child li");
                        var numtabs = $tabmenu.length;
                        $tabs = $(this);
                        $tabs.addClass("tabs-"+numtabs);
                        // Check that tab content divs have ids.
                        // If ids aren't present, add them from the menu href.
                        $tabsdivs = $tabmenu.parent().siblings("div");
                        $tabsdivs.each(function(i, el) {
                            $tab = $(this);
                            var id = $tabmenu.eq(i).find("a").attr("href");
                            if ($tabs.find(id).length == 0) {
                                if (id.substr(0,1) == "#") {
                                    id = id.substr(1);
                                }
                                $tab.attr("id", id);
                            }
                        });
                    });
                    var tabs = $('.doTabs').tabs();
                }

                // Image rotator
                if ($('#rotator').length >= 1) {
                    // If rotator element exists on the page, then do the following
                    $('#rotator').rotator({
                            fadeInt: 2000
                        });
                }

                // One-item drag and drop
                if ($('.oneDrag').length >= 1) {
                    // If oneDrag exists on the page then do the following
                    if (console.warn) { console.warn('One-item drag and drop is deprecated. You might want to try a different module.'); }
                    $('.oneDrag').onedrag({});
                }

                // Click hotspot
                if ($('.clickHotspot').length >= 1) {
                    $('.clickHotspot').clickhotspot({});
                }

                // Drag and drop column sorting
                if ($('.sortMultipleLists').length >= 1) {
                    $('.sortMultipleLists').draganddropsorting({});
                }

                // domultidrop or 'bubble of safety'
                if ($('.do-multi-drop').length >= 1) {
                    $('.do-multi-drop').each(function() {

                        // Check for color settings in data attributes on the element.
                        var options = new Object();
                        var correctcolor = $(this).attr('data-correct-color');
                        // Check if color is a vald hex color.
                        var ishex = /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(correctcolor);
                        if (ishex) {
                            options.correct = correctcolor;
                        }
                        var incorrectcolor = $(this).attr('data-incorrect-color');
                        // Check if color is a vald hex color.
                        ishex = /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(incorrectcolor);
                        if (ishex) {
                            options.incorrect = incorrectcolor;
                        }
                        var allcorrectcolor = $(this).attr('data-all-correct-color');
                        // Check if color is a vald hex color.
                        ishex = /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(allcorrectcolor);
                        if (ishex) {
                            options.allcorrect = allcorrectcolor;
                        }
                        $(this).domultidrop(options);
                    });
                }

                // dostepwise
                if ($('.do-stepwise').length >= 1) {
                    $('.do-stepwise').each( function (i,el) {
                        $(el).dostepwise({});
                    });
                }

                // dosequential
                if ($('.do-sequential').length >= 1) {
                    $('.do-sequential').dosequential({});
                }

                // markit
                if ($('.markit').length >= 1) {
                    $('.markit:eq(0)').markit({});
                }

                // tooltip
                if ($('.tip-trigger').length >= 1) {
                    $('.tip-trigger').dotip({});
                }

                // responsive tables
                if ($('.table-responsive').length >= 1) {
                    $('.table-responsive').doresponsivetables();
                }

                // modal
                if ($('.do-modal').length >= 1) {
                    $('.do-modal').each(function() {
                        $(this).domodal();
                    });
                }

                // equal columns
                if ($('.do-equal-columns').length >= 1) {
                    $('.do-equal-columns').doequalcolumns();
                }

                // toggle
                if ($('.toggle-button').length >= 1) {
                    $('.toggle-button').dotoggle();
                }

                // flip book
                if ($('.flip-book').length >= 1) {
                    $('.flip-book').flipbook();
                }

                // flip card
                if ($(".flipcard-item").length >= 1) {
                    // Add empty event listener to touchstart so :hover status is triggerd on iOS.
                    $(".flipcard-item").bind("touchstart", function(){});
                }

                // overlay
                if ($('.rein-plugin.overlay').length >= 1) {
                    $('.rein-plugin.overlay').each(function() {
                        $(this).overlay();
                    });
                }

                // swiper
                if ($('.rein-plugin.swiper-container').length >= 1) {
                    $(this).swipe();
                }

                // Show all jQuery UI REIN interactions now that jQuery is available.
                $(".doTabs, .doAccordion").css("visibility", "visible");

                // If debug is on, then we also run the JS debugging checks.
                if (debugmode) {

                    $(document).ready( function(){

                        var tests = {
                            doRun: null,
                            runTests: function() {
                                console.log('Running tests of rein.js.');
                                // Is jQuery defined?
                                if ($) {
                                    console.log('Pass: jQuery is defined.');
                                } else {
                                    console.error('Fail: jQuery is not defined.');
                                }

                                // Is jQuery UI defined?
                                if ($.ui) {
                                    console.log('Pass: jQuery.ui is defined.');
                                } else {
                                    console.error('Fail: jQuery.ui is not defined.');
                                }

                                // Is Raphael defined?
                                if (Raphael) {
                                    console.log('Pass: Raphael is defined.');
                                } else {
                                    console.error('Fail: Raphael is not defined.');
                                }

                                // jmediaelement
                                /* if (jQuery.fn.jmeEmbed) {
                                console.log('Pass: jme is defined');
                                } else {
                                console.error('Fail: jme is not defined');
                                }*/

                                // Is click hotspot plugin defined?
                                if ( $.fn.clickhotspot ){
                                    console.log('Pass: Click hotspot is defined.');
                                } else {
                                    console.error('Fail: Click hotspot is not defined.');
                                }

                                // If click hotspot exists, query length of children should be > 1.
                                if ( $('.clickHotspot').length >= 1 ){
                                    if ($('.clickHotspot').children().length >= 1) {
                                        console.log('Pass: Click hotspot DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: Click hotspot DOM object detected but has no children.');
                                    }
                                }

                                // Is one item drag and drop plugin defined?
                                if ( $.fn.onedrag ){
                                    console.log('Pass: one item drag and drop is defined.');
                                } else {
                                    console.error('Fail: one item drag and drop is not defined.');
                                }

                                // If one item drag and drop exists, query length of children should be > 1.
                                if ( $('.oneDrag').length >= 1 ){
                                    if ($('.oneDrag').children().length >= 1) {
                                        console.log('Pass: One item drag and drop DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: One item drag and drop DOM object detected but has no children.');
                                    }
                                }

                                // Is sort multiple lists plugin defined?
                                if ( $.fn.draganddropsorting ){
                                    console.log('Pass: Drag and drop list sorting is defined.');
                                } else {
                                    console.error('Fail: Drag and drop list sorting is not defined.');
                                }

                                // If sort multiple lists plugin exists, query length of children should be > 1.
                                if ( $('.sortMultipleLists').length >= 1 ){
                                    if ($('.sortMultipleLists').children().length >= 1) {
                                        console.log('Pass: Sort multiple lists DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: Sort multiple lists DOM object detected but has no children.');
                                    }
                                }

                                // Is sort multiple lists plugin defined?
                                if ( $.fn.domultidrop ){
                                    console.log('Pass: Domultidrop plugin is defined.');
                                } else {
                                    console.error('Fail: Domultidrop plugin is not defined.');
                                }

                                // If domultidrop plugin exists, query length of children should be > 1.
                                if ( $('.do-multi-drop').length >= 1 ){
                                    if ($('.do-multi-drop').children().length >= 1) {
                                        console.log('Pass: .do-multi-drop DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: .do-multi-drop DOM object detected but has no children.');
                                    }
                                }

                                // Is stepwise plugin defined?
                                if ( $.fn.dostepwise ){
                                    console.log('Pass: Dostepwise plugin is defined.');
                                } else {
                                    console.error('Fail: Dostepwise plugin is not defined.');
                                }

                                // If domultidrop plugin exists, query length of children should be > 1.
                                if ( $('.do-stepwise').length >= 1 ){
                                    if ($('.do-stepwise').children().length >= 1) {
                                        console.log('Pass: .do-stepwise DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: .do-stepwise DOM object detected but has no children.');
                                    }
                                }

                                // Is sequential appearance plugin defined?
                                if ( $.fn.dosequential ){
                                    console.log('Pass: Dosequential plugin is defined.');
                                } else {
                                    console.error('Fail: Dosequential plugin is not defined.');
                                }

                                // If sequential appearance plugin exists, query length of children should be > 1.
                                if ( $('.do-sequential').length >= 1 ){
                                    if ($('.do-sequential').children().length >= 1) {
                                        console.log('Pass: .do-sequential DOM element exists and has children.');
                                    } else {
                                        console.error('Fail: .do-sequential DOM object detected but has no children.');
                                    }
                                }

                            }// end runTests funct

                        };// end tests object

                        tests.doRun = true;

                        if (tests.doRun) {
                            tests.runTests();
                        }

                    });
                }
            } else {
                var nesting_error_response =  M.util.get_string('nestingerror', 'filter_rein');
                $('.rein-plugin:first').before('<div class="alert alert-danger" role="alert">' + nesting_error_response + '</div>');
            }
        }
    };

    //
    // listen for ajax requests and initialize REIN onReadyStateListen
    //
    var open = window.XMLHttpRequest.prototype.open,
        send = window.XMLHttpRequest.prototype.send,
        onReadyStateChange;
    function openreplacement(method, url, async, user, password) {
        return open.apply(this, arguments);
    }
    function sendreplacement(data) {
        if(this.onreadystatechange) {
            this._onreadystatechange = this.onreadystatechange;
        }
        this.onreadystatechange = onreadystatelisten;
        return send.apply(this, arguments);
    }
    function onreadystatelisten() {
        if(this._onreadystatechange) {
            setTimeout(reininit,100); // initialize REIN 100ms after ready state to allow for DOM changes
            return this._onreadystatechange.apply(this, arguments);
        }
    }
    window.XMLHttpRequest.prototype.open = openreplacement;
    window.XMLHttpRequest.prototype.send = sendreplacement;

});
