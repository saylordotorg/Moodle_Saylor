define(['jquery', 'core/log', 'mod_readaloud/definitions', 'mod_readaloud/popoverhelper'], function ($, log, def, popoverhelper) {
    "use strict"; // jshint ;_;

    log.debug('Gradenow helper: initialising');

    return {
        //controls

        controls: {},
        currentmode: 'grading',

        constants: {
            REVIEWMODE_NONE: 0,
            REVIEWMODE_MACHINE: 1,
            REVIEWMODE_HUMAN: 2,
            REVIEWMODE_SCORESONLY: 3,
            SESSIONSCORE_STRICT: 1
        },

        //class definitions
        cd: {
            audioplayerclass: def.audioplayerclass,
            wordplayerclass: def.wordplayerclass,
            wordclass: def.wordclass,
            spaceclass: def.spaceclass,
            badwordclass: def.badwordclass,
            endspaceclass: def.endspaceclass,
            unreadwordclass: def.unreadwordclass,
            unreadspaceclass: def.unreadspaceclass,
            wpmscoreid: def.wpmscoreid,
            accuracyscoreid: def.accuracyscoreid,
            sessionscoreid: def.sessionscoreid,
            errorscoreid: def.errorscoreid,
            formelementwpmscore: def.formelementwpmscore,
            formelementaccuracy: def.formelementaccuracy,
            formelementsessionscore: def.formelementsessionscore,
            formelementendword: def.formelementendword,
            formelementtime: def.formelementtime,
            formelementerrors: def.formelementerrors,
            modebutton: def.modebutton,

            spotcheckbutton: def.spotcheckbutton,
            transcriptcheckbutton: def.transcriptcheckbutton,
            gradingbutton: def.gradingbutton,
            clearbutton: def.clearbutton,
            spotcheckmode: def.spotcheckmode,
            aiunmatched: def.aiunmatched,
            passagecontainer: def.passagecontainer,

            maybeselfcorrectclass: def.maybeselfcorrectclass,
            selfcorrectclass: def.selfcorrectclass,
            notesclass: def.notesclass,
            structuralclass: def.structuralclass,
            meaningclass: def.meaningclass,
            visualclass: def.visualclass,

        },

        options: {
            enabletts: false,
            targetwpm: 100,
            ttslanguage: 'en',
            totalseconds: 60,
            allowearlyexit: false,
            timelimit: 60,
            enforcemarker: true,
            totalwordcount: 0,
            wpm: 0,
            accuracy: 0,
            sessionscore: 0,
            sessionscoremethod: 0,
            endwordnumber: 0,
            errorwords: {},
            activityid: null,
            attemptid: null,
            sesskey: null
        },


        init: function (config) {

            //pick up opts from html
            var theid = '#' + config['id'];
            var configcontrol = $(theid).get(0);
            if (configcontrol) {
                var opts = JSON.parse(configcontrol.value);
                $(theid).remove();
            } else {
                //if there is no config we might as well give up
                log.debug('Gradenow helper js: No config found on page. Giving up.');
                return;
            }

            //register the controls
            this.register_controls();

            //stash important info
            this.options.activityid = opts['activityid'];
            this.options.attemptid = opts['attemptid'];
            this.options.sesskey = opts['sesskey'];
            this.options.enabletts = opts['enabletts'];
            this.options.ttslanguage = opts['ttslanguage'];
            this.options.targetwpm = opts['targetwpm'];
            this.options.sessionscoremethod = opts['sessionscoremethod'];
            this.options.allowearlyexit = opts['allowearlyexit'];
            this.options.timelimit = opts['timelimit'];
            this.options.reviewmode = opts['reviewmode'];
            this.options.readonly = opts['readonly'];
            this.options.totalwordcount = $('.' + this.cd.wordclass).length;

            if (opts['sessiontime'] > 0) {
                if (opts['sessionerrors'] !== '') {
                    this.options.errorwords = JSON.parse(opts['sessionerrors']);
                } else {
                    this.options.errorwords = {};
                }
                this.options.totalseconds = opts['sessiontime'];
                this.options.endwordnumber = opts['sessionendword'];
                this.options.sessionscore = opts['sessionscore'];
                this.options.accuracy = opts['accuracy'];
                this.options.wpm = opts['wpm'];

                //We may have session matches and AI data, if AI is turned on
                this.options.sessionmatches = JSON.parse(opts['sessionmatches']);
                this.options.aidata = opts['aidata'];
                if (this.options.aidata) {
                    this.options.transcriptwords = opts['aidata'].transcript.split(" ");

                    //remove empty elements ... these can get in there
                    this.options.transcriptwords = this.options.transcriptwords.filter(function (el) {
                        return el !== '';
                    });

                } else {
                    this.options.transcriptwords = [];
                }

                //if this has been graded, draw the gradestate
                this.redrawgradestate();
            } else {
                //set up our end passage marker
                this.options.endwordnumber = this.options.totalwordcount;
            }

            //add the endword marker
            var thespace = $('#' + this.cd.spaceclass + '_' + this.options.endwordnumber);
            thespace.addClass(this.cd.endspaceclass);

            //register events
            this.register_events();

            //initialise our audio duration. We need this to calc. wpm
            //but if allowearlyexit is false, actually we can skip waiting for audio.
            //After audio loaded(if nec.) we call processscores to init score boxe
            //TODO: really should get audio duration at recording time.
            var m = this;
            var processloadedaudio = function () {
                if (m.options.allowearlyexit ||m.options.timelimit==0 ) {
                    //using the audio player duration is actually more accurate than aidata.sessiontime
                    //but it will give diff results to score used in autograding which when allowing earlyexit uses aiddata.sessiontime
                    // (aidata.sessiontime is the end time of last recognised word.)
                    //So to ensure consistency we also use the aidata.sessiontime here
                    if (m.options.aidata && m.options.aidata.sessiontime) {
                        m.options.totalseconds = m.options.aidata.sessiontime;
                    } else {
                        m.options.totalseconds = Math.round($('#' + m.cd.audioplayerclass).prop('duration'));
                    }
                } else {
                    m.options.totalseconds = m.options.timelimit;
                }
                //update form field
                m.controls.formelementtime.val(m.options.totalseconds);
                m.processscores();
            };


            //we used to use the audio player time, but we try not to, and anyway its best not to
            //depend on a duration being available. Audio might expire
            /*
            var audioplayer = $('#' + this.cd.audioplayerclass);
            if (audioplayer.prop('readyState') < 1 && this.options.allowearlyexit) {
                audioplayer.on('loadedmetadata', processloadedaudio);
            } else {
                processloadedaudio();
            }
            */
            processloadedaudio();

            //init our popover helper which sets up the button events
            this.init_popoverhelper();


        },

        //set up events related to popover helper
        init_popoverhelper: function () {
            var that = this;

            //when the user clicks the reject popover accept button, we arrive here
            popoverhelper.onReject = function () {
                var clickwordnumber = $(this).attr('data-wordnumber');

                //if nothing changed, just close the popover
                var nochange = (clickwordnumber in that.options.errorwords);
                if (nochange) {
                    popoverhelper.remove();
                    return;
                }

                //if a new choice was made update things
                var playchain = that.fetchPlayChain(clickwordnumber);
                for (var wordindex = playchain.startword; wordindex <= playchain.endword; wordindex++) {
                    if (!(wordindex in that.options.errorwords)) {
                        var theword = $('#' + that.cd.wordclass + '_' + wordindex);
                        var thespace = $('#' + that.cd.spaceclass + '_' + wordindex);
                        if(wordindex==clickwordnumber) {
                            that.adderrorword(wordindex, theword.text());
                            theword.addClass(that.cd.badwordclass);
                            theword.addClass(that.cd.spotcheckmode);
                            if (wordindex !== playchain.endword) {
                                thespace.addClass(that.cd.spotcheckmode);
                            }
                        }
                    }
                }
                //that.markup_badspaces();
                that.markup_aiunmatchedspaces();
                that.processscores();
                popoverhelper.remove();
            };

            //when the user clicks the popover accept button, we arrive here
            popoverhelper.onAccept = function () {
                var clickwordnumber = $(this).attr('data-wordnumber');

                //if nothing changed, just close the popover
                var nochange = !(clickwordnumber in that.options.errorwords);
                if (nochange) {
                    popoverhelper.remove();
                    return;
                }
                //if a new choice was made update things
                var playchain = that.fetchPlayChain(clickwordnumber);
                for (var wordindex = playchain.startword; wordindex <= playchain.endword; wordindex++) {
                    if (wordindex in that.options.errorwords) {
                        if(wordindex==clickwordnumber) {
                            delete that.options.errorwords[wordindex];
                            var theword = $('#' + that.cd.wordclass + '_' + wordindex);
                            var thespace = $('#' + that.cd.spaceclass + '_' + wordindex);
                            theword.removeClass(that.cd.badwordclass);
                            theword.removeClass(that.cd.spotcheckmode);
                            thespace.removeClass(that.cd.spotcheckmode);
                        }
                    }
                }
                //that.markup_badspaces();
                that.markup_aiunmatchedspaces();
                that.processscores();
                popoverhelper.remove();
            };

            //init the popover now that we have set the correct callback event handling thingies
            popoverhelper.init();
        },

        register_controls: function () {

            this.controls.wordplayer = $('#' + this.cd.wordplayerclass);
            this.controls.audioplayer = $('#' + this.cd.audioplayerclass);
            this.controls.eachword = $('.' + this.cd.wordclass);
            this.controls.eachspace = $('.' + this.cd.spaceclass);
            this.controls.endwordmarker = $('#' + this.cd.spaceclass + '_' + this.options.endwordnumber);
            this.controls.spotcheckword = $('.' + this.cd.spotcheckmode);

            this.controls.wpmscorebox = $('#' + this.cd.wpmscoreid);
            this.controls.accuracyscorebox = $('#' + this.cd.accuracyscoreid);
            this.controls.sessionscorebox = $('#' + this.cd.sessionscoreid);
            this.controls.errorscorebox = $('#' + this.cd.errorscoreid);

            this.controls.formelementwpmscore = $("#" + this.cd.formelementwpmscore);
            this.controls.formelementsessionscore = $("#" + this.cd.formelementsessionscore);
            this.controls.formelementaccuracy = $("#" + this.cd.formelementaccuracy);
            this.controls.formelementendword = $("#" + this.cd.formelementendword);
            this.controls.formelementerrors = $("#" + this.cd.formelementerrors);
            this.controls.formelementtime = $("#" + this.cd.formelementtime);

            this.controls.passagecontainer = $("." + this.cd.passagecontainer);

            //passage action buttons
            this.controls.modebutton = $("#" + this.cd.modebutton);

            this.controls.gradingbutton = $("#" + this.cd.gradingbutton);
            this.controls.spotcheckbutton = $("#" + this.cd.spotcheckbutton);
            this.controls.transcriptcheckbutton = $("#" + this.cd.transcriptcheckbutton);
            this.controls.clearbutton = $("#" + this.cd.clearbutton);

        },

        register_events: function () {
            var that = this;
            //set up event handlers


            //Play audio from and to spot check part
            this.controls.passagecontainer.on('click', '.' + this.cd.spotcheckmode + ', .' + this.cd.aiunmatched, function () {
                if (that.currentmode === 'spotcheck') {
                    var wordnumber = parseInt($(this).attr('data-wordnumber'));
                    that.doPlaySpotCheck(wordnumber);
                }
            });


            //in review mode, do nuffink though ... thats for the student
            if (this.options.readonly) {
                //do nothing

                //here we will put real options for playing the model reading and user reading etc
            } else if (false) {
                /*
                if(this.enabletts && this.options.ttslanguage != 'none'){
                    this.controls.eachword.click(this.playword);
                }
                */

                //if we have AI data then turn on spotcheckmode
                if (this.options.sessionmatches) {
                    this.doSpotCheckMode();
                }

                //add listeners for click events
                this.controls.eachword.click(
                    function () {
                        //if we are in spotcheck mode just return, we do not grade
                        if (that.currentmode === 'spotcheck') {
                            return;
                        }

                        //get the word that was clicked
                        var wordnumber = $(this).attr('data-wordnumber');
                        var theword = $(this).text();

                        if (that.currentmode === 'transcriptcheck') {
                            var chunk = that.fetchTranscriptChunk(wordnumber);
                            if (chunk) {
                                popoverhelper.addTranscript(this, chunk);
                            }


                        }
                    });

                //if not in review mode
            } else {

                //process word clicks
                this.controls.eachword.click(
                    function () {


                        //get the word that was clicked
                        var wordnumber = $(this).attr('data-wordnumber');
                        var theword = $(this).text();

                        //if we are in spotcheck mode lets enable quick grade popovers
                        if (that.currentmode === 'spotcheck') {
                            if ($(this).hasClass(that.cd.badwordclass) || $(this).hasClass(that.cd.aiunmatched)) {
                                popoverhelper.addQuickGrader(this);
                            }
                            return;
                        }

                        if (that.currentmode === 'transcriptcheck') {
                            var chunk = that.fetchTranscriptChunk(wordnumber);
                            if (chunk) {
                                popoverhelper.addTranscript(this, chunk);
                            }
                            return;
                        }

                        //this will disallow badwords after the endmarker
                        if (that.options.enforcemarker && Number(wordnumber) > Number(that.options.endwordnumber)) {
                            return;
                        }

                        if (wordnumber in that.options.errorwords) {
                            delete that.options.errorwords[wordnumber];
                            $(this).removeClass(that.cd.badwordclass);
                        } else {
                            that.adderrorword(wordnumber, theword);
                            $(this).addClass(that.cd.badwordclass);
                        }
                        that.processscores();
                    }
                ); //end of each word click

                //process space clicks
                this.controls.eachspace.click(
                    function () {

                        //if we are in spotcheck or transcript check mode just return, we do not grade
                        if (that.currentmode === 'spotcheck' || that.currentmode === 'transcriptcheck') {
                            return;
                        }

                        //this event is entered by  click on space
                        //it relies on attr data-wordnumber being set correctly
                        var wordnumber = $(this).attr('data-wordnumber');
                        var thespace = $('#' + that.cd.spaceclass + '_' + wordnumber);

                        if (wordnumber === that.options.endwordnumber) {
                            that.options.endwordnumber = that.options.totalwordcount;
                            thespace.removeClass(that.cd.endspaceclass);
                            $('#' + that.cd.spaceclass + '_' + that.options.totalwordcount).addClass(that.cd.endspaceclass);
                        } else {
                            $('#' + that.cd.spaceclass + '_' + that.options.endwordnumber).removeClass(that.cd.endspaceclass);
                            that.options.endwordnumber = wordnumber;
                            thespace.addClass(that.cd.endspaceclass);
                        }
                        that.processunread();
                        that.processscores();
                    }
                );//end of each space click

                //process clearbutton's click event
                this.controls.clearbutton.click(function () {

                    //if we are in spotcheck or transcript check mode just return, we do not grade
                    if (that.currentmode === 'spotcheck' || that.currentmode === 'transcriptcheck') {
                        return;
                    }

                    //clear all the error words
                    $('.' + that.cd.badwordclass).each(function (index) {
                        var wordnumber = $(this).attr('data-wordnumber');
                        delete that.options.errorwords[wordnumber];
                        $(this).removeClass(that.cd.badwordclass);
                    });

                    //remove unread words
                    $('.' + that.cd.wordclass).removeClass(that.cd.unreadwordclass);

                    //set endspace to last space
                    that.options.endwordnumber = that.options.totalwordcount;
                    $('.' + that.cd.spaceclass).removeClass(that.cd.endspaceclass);
                    $('#' + that.cd.spaceclass + '_' + that.options.totalwordcount).addClass(that.cd.endspaceclass);

                    //reprocess scores
                    that.processscores();
                });


                //modebutton: turn on grading
                this.controls.gradingbutton.click(function () {
                    that.undoCurrentMode();
                    that.doGradingMode();
                    that.updateButtonStates();
                });

            }//end of if/else reviewmode

            //either in or out of review mode we want these
            //modebutton: turn on spotchecking
            this.controls.spotcheckbutton.click(function () {
                that.undoCurrentMode();
                that.doSpotCheckMode();
                that.updateButtonStates();
            });

            //modebutton: turn on transcript checking
            this.controls.transcriptcheckbutton.click(function () {
                that.undoCurrentMode();
                that.doTranscriptCheckMode();
                that.updateButtonStates();
            });


        },

        undoCurrentMode: function () {
            switch (this.currentmode) {
                case 'grading':
                    //we do not undo this, its the default
                    break;
                case 'spotcheck':
                    this.undoSpotCheckMode();
                    break;
                case 'transcriptcheck':
                    this.undoTranscriptCheckMode();
                    break;
            }

        },

        updateButtonStates: function () {
            switch (this.currentmode) {
                case 'grading':
                    this.controls.gradingbutton.prop('disabled', true);
                    this.controls.spotcheckbutton.prop('disabled', false);
                    this.controls.transcriptcheckbutton.prop('disabled', false);
                    break;
                case 'spotcheck':
                    this.controls.gradingbutton.prop('disabled', false);
                    this.controls.spotcheckbutton.prop('disabled', true);
                    this.controls.transcriptcheckbutton.prop('disabled', false);
                    break;
                case 'transcriptcheck':
                    this.controls.gradingbutton.prop('disabled', false);
                    this.controls.spotcheckbutton.prop('disabled', false);
                    this.controls.transcriptcheckbutton.prop('disabled', true);
                    break;
            }

        },

        /*
        * Here we fetch the playchain, start playing frm audiostart and add an event handler to stop at audioend
         */
        doPlaySpotCheck: function (spotcheckindex) {
            var playchain = this.fetchPlayChain(spotcheckindex);
            var theplayer = this.controls.audioplayer[0];
            //we pad the play audio by 0.5 seconds beginning and end
            var pad = 0.5;
            var duration = theplayer.duration;
            //determine starttime
            var endtime = parseFloat(playchain.audioend);
            if (!isNaN(duration) && duration > (endtime + pad)) {
                endtime = endtime + pad;
            }
            //determine endtime
            var starttime = parseFloat(playchain.audiostart);
            if ((starttime - pad) > 0) {
                starttime = starttime - pad;
            }

            theplayer.currentTime = starttime;
            $(this.controls.audioplayer).off("timeupdate");
            $(this.controls.audioplayer).on("timeupdate", function (e) {
                var currenttime = theplayer.currentTime;
                if (currenttime >= endtime) {
                    $(this).off("timeupdate");
                    theplayer.pause();
                }
            });
            theplayer.play();
        },

        /*
        * The playchain is all the words in a string of badwords.
        * The complexity comes because a bad word  is usually one that isunmatched by AI.
        * So if the teacher clicks on a passage word that did not appear in the transcript, what should we play?
        * Answer: All the words from the last known to the next known word. Hence we create a play chain
        * For consistency, if the teacher flags matched words as bad, while we do know their precise location we still
        * make a play chain. Its not a common situation probably.
         */
        fetchPlayChain: function (spotcheckindex) {

            //find startword
            var startindex = spotcheckindex;
            var starttime = -1;
            for (var wordnumber = spotcheckindex; wordnumber > 0; wordnumber--) {
                var isbad = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.badwordclass);
                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                //if current wordnumber part of the playchain, set it as the startindex.
                // And get the audiotime if its a matched word. (we only know audiotime of matched words)
                if (isbad || isunmatched) {
                    startindex = wordnumber;
                    if (!isunmatched) {
                        starttime = this.options.sessionmatches['' + wordnumber].audiostart;
                    } else {
                        starttime = -1;
                    }
                } else {
                    break;
                }
            }//end of for loop --
            //if we have no starttime then we need to get the next matched word's audioend and use that
            if (starttime === -1) {
                starttime = 0;
                for (var wordnumber = startindex - 1; wordnumber > 0; wordnumber--) {
                    if (this.options.sessionmatches['' + wordnumber]) {
                        starttime = this.options.sessionmatches['' + wordnumber].audioend;
                        break;
                    }
                }
            }

            //find endword
            var endindex = spotcheckindex;
            var endtime = -1;
            var passageendword = this.options.totalwordcount;
            for (var wordnumber = spotcheckindex; wordnumber <= passageendword; wordnumber++) {
                var isbad = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.badwordclass);
                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                //if its part of the playchain, set it to startindex. And get time if its a matched word.
                if (isbad || isunmatched) {
                    endindex = wordnumber;
                    if (!isunmatched) {
                        endtime = this.options.sessionmatches['' + wordnumber].audioend;
                    } else {
                        endtime = -1;
                    }
                } else {
                    break;
                }
            }//end of for loop --
            //if we have no endtime then we need to get the next matched word's audiostart and use that
            if (endtime === -1) {
                endtime = this.options.totalseconds;
                for (var wordnumber = endindex + 1; wordnumber <= passageendword; wordnumber++) {
                    if (this.options.sessionmatches['' + wordnumber]) {
                        endtime = this.options.sessionmatches['' + wordnumber].audiostart;
                        break;
                    }
                }
            }
            var playchain = {};
            playchain.startword = startindex;
            playchain.endword = parseInt(endindex);
            playchain.audiostart = starttime;
            playchain.audioend = parseInt(endtime);
            //console.log('audiostart:' + starttime);
            //console.log('audioend:' + endtime);

            return playchain;

        },

        /*
        * Here we mark up the passage for spotcheck mode. This will make up the spaces and the words as either badwords
        * or aiunmatched words. We need to create playchains so aiunmatched still is indeicated visibly even where its
        * not a badword (ie has been corrected)
         */
        doSpotCheckMode: function () {
            var that = this;

            //mark up all ai unmatched words as aiunmatched
            this.markup_aiunmatchedwords();

            //mark up all badwords as spotcheck words
            $('.' + this.cd.badwordclass).addClass(this.cd.spotcheckmode);

            //mark up spaces between spotcheck word and spotcheck/aiunmatched word (bad spaces)
            //this.markup_badspaces();

            //mark up spaces between aiunmatched word and spotcheck/aiunmatched word (aiunmatched spaces)
            this.markup_aiunmatchedspaces();

            this.currentmode = "spotcheck";
        },

        //mark up all ai unmatched words as aiunmatched
        markup_aiunmatchedwords: function () {
            var that = this;
            if (this.options.sessionmatches) {
                var prevmatch = 0;
                $.each(this.options.sessionmatches, function (index, match) {
                    var unmatchedcount = index - prevmatch - 1;
                    if (unmatchedcount > 0) {
                        for (var errorword = 1; errorword < unmatchedcount + 1; errorword++) {
                            var wordnumber = prevmatch + errorword;
                            $('#' + that.cd.wordclass + '_' + wordnumber).addClass(that.cd.aiunmatched);
                        }
                    }
                    prevmatch = parseInt(index);
                });

                //mark all words from last matched word to the end as aiunmatched
                for (var errorword = prevmatch + 1; errorword <= this.options.endwordnumber; errorword++) {
                    $('#' + that.cd.wordclass + '_' + errorword).addClass(that.cd.aiunmatched);
                }
            }

        },

        //mark up spaces between spotcheck word and spotcheck/aiunmatched word (bad spaces)
        /* NO LONGER USED  */
        markup_badspaces: function () {
            var that = this;
            //mark up spaces between spotcheck word and spotcheck/aiunmatched word (bad spaces)
            $('.' + this.cd.badwordclass + '.' + this.cd.spotcheckmode).each(function (index) {
                var wordnumber = parseInt($(this).attr('data-wordnumber'));
                //build chains (highlight spaces) of badwords or aiunmatched
                if ($('#' + that.cd.wordclass + '_' + (wordnumber + 1)).hasClass(that.cd.spotcheckmode) ||
                    $('#' + that.cd.wordclass + '_' + (wordnumber + 1)).hasClass(that.cd.aiunmatched)) {
                    $('#' + that.cd.spaceclass + '_' + wordnumber).addClass(that.cd.spotcheckmode);
                }
            });
        },

        markup_aiunmatchedspaces: function () {
            var that = this;
            $('.' + this.cd.wordclass + '.' + this.cd.aiunmatched).each(function (index) {
                var wordnumber = parseInt($(this).attr('data-wordnumber'));
                //build chains (highlight spaces) of badwords or aiunmatched
                if ($('#' + that.cd.wordclass + '_' + (wordnumber + 1)).hasClass(that.cd.spotcheckmode) ||
                    $('#' + that.cd.wordclass + '_' + (wordnumber + 1)).hasClass(that.cd.aiunmatched)) {
                    $('#' + that.cd.spaceclass + '_' + wordnumber).addClass(that.cd.aiunmatched);
                }
            });
        },


        undoSpotCheckMode: function () {
            $('.' + this.cd.badwordclass).removeClass(this.cd.spotcheckmode);
            $('.' + this.cd.spaceclass).removeClass(this.cd.spotcheckmode);
            $('.' + this.cd.wordclass).removeClass(this.cd.aiunmatched);
            $('.' + this.cd.spaceclass).removeClass(this.cd.aiunmatched);
            $(this.controls.audioplayer).off("timeupdate");
            popoverhelper.remove();
        },

        /*
       * Here we mark up the passage for transcriptcheck mode.
        */
        doTranscriptCheckMode: function () {
            var that = this;
            //mark up all ai unmatched words as transcriptcheck
            if (this.options.sessionmatches) {
                var prevmatch = 0;
                $.each(this.options.sessionmatches, function (index, match) {
                    var unmatchedcount = index - prevmatch - 1;
                    if (unmatchedcount > 0) {
                        for (var errorword = 1; errorword < unmatchedcount + 1; errorword++) {
                            var wordnumber = prevmatch + errorword;
                            $('#' + that.cd.wordclass + '_' + wordnumber).addClass(that.cd.aiunmatched);
                        }
                    }
                    prevmatch = parseInt(index);
                });

                //mark all words from last matched word to the end as aiunmatched
                for (var errorword = prevmatch + 1; errorword <= this.options.endwordnumber; errorword++) {
                    $('#' + that.cd.wordclass + '_' + errorword).addClass(that.cd.aiunmatched);
                }
            }

            //mark up spaces between aiunmatched word and aiunmatched (bad spaces)
            $('.' + this.cd.aiunmatched).each(function (index) {
                var wordnumber = parseInt($(this).attr('data-wordnumber'));
                //build chains (highlight spaces) of badwords or aiunmatched
                if ($('#' + that.cd.wordclass + '_' + (wordnumber + 1)).hasClass(that.cd.aiunmatched)) {
                    $('#' + that.cd.spaceclass + '_' + wordnumber).addClass(that.cd.aiunmatched);
                }
            });

            this.currentmode = "transcriptcheck";
        },

        undoTranscriptCheckMode: function () {
            $('.' + this.cd.wordclass).removeClass(this.cd.aiunmatched);
            $('.' + this.cd.spaceclass).removeClass(this.cd.aiunmatched);
            popoverhelper.remove();
        },

        doGradingMode: function () {
            this.currentmode = "grading";
        },

        /*
       * This will take a wordindex and find the previous and next transcript indexes that were matched and
       * return all the transcript words in between those.
        */
        fetchTranscriptChunk: function (checkindex) {

            var transcriptlength = this.options.transcriptwords.length;
            if (transcriptlength === 0) {
                return "";
            }

            //find startindex
            var startindex = -1;
            for (var wordnumber = checkindex; wordnumber > 0; wordnumber--) {

                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                //if we matched then the subsequent transcript word is the last unmatched one in the checkindex sequence
                if (!isunmatched) {
                    startindex = this.options.sessionmatches['' + wordnumber].tposition + 1;
                    break;
                }
            }//end of for loop

            //find endindex
            var endindex = -1;
            for (var wordnumber = checkindex; wordnumber <= transcriptlength; wordnumber++) {

                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                //if we matched then the previous transcript word is the last unmatched one in the checkindex sequence
                if (!isunmatched) {
                    endindex = this.options.sessionmatches['' + wordnumber].tposition - 1;
                    break;
                }
            }//end of for loop --

            //if there was no previous matched word, we set start to 1
            if (startindex === -1) {
                startindex = 1;
            }
            //if there was no subsequent matched word we flag the end as the -1
            if (endindex === transcriptlength) {
                endindex = -1;
                //an edge case is where the first word is not in transcript and first match is the second or later passage
                //word. It might not be possible for endindex to be lower than start index, but we don't want it anyway
            } else if (endindex === 0 || endindex < startindex) {
                return false;
            }

            //up until this point the indexes have started from 1, since the passage word numbers start from 1
            //but the transcript array is 0 based so we adjust. array splice function does not include item and endindex
            ///so it needs to be one more then start index. hence we do not adjust that
            startindex--;

            //finally we return the section
            var ret = false;
            if (endindex > 0) {
                ret = this.options.transcriptwords.slice(startindex, endindex).join(" ");
            } else {
                ret = this.options.transcriptwords.slice(startindex).join(" ");
            }
            if (ret.trim() === '') {
                return false;
            } else {
                return ret;
            }
        },


        playword: function () {
            var m = this;//M.mod_readaloud.gradenowhelper;
            m.controls.wordplayer.attr('src', M.cfg.wwwroot + '/mod/readaloud/tts.php?txt=' + encodeURIComponent($(this).text())
                + '&lang=' + m.options.ttslanguage + '&n=' + m.options.activityid);
            m.controls.wordplayer[0].pause();
            m.controls.wordplayer[0].load();
            m.controls.wordplayer[0].play();
        },
        redrawgradestate: function () {
            var m = this;
            this.processunread();
            if (this.options.reviewmode !== this.constants.REVIEWMODE_SCORESONLY) {
                $.each(m.options.errorwords, function (index) {
                        $('#' + m.cd.wordclass + '_' + m.options.errorwords[index].wordnumber).addClass(m.cd.badwordclass);
                    }
                );
            }

            this.markup_maybeselfcorrects();

        },
        adderrorword: function (wordnumber, word) {
            this.options.errorwords[wordnumber] = {word: word, wordnumber: wordnumber};
            //console.log(this.errorwords);

        },
        processword: function () {
            var m = this;
            var wordnumber = $(this).attr('data-wordnumber');
            var theword = $(this).text();
            //this will disallow badwords after the endmarker
            if (m.options.enforcemarker && Number(wordnumber) > Number(m.options.endwordnumber)) {
                return;
            }

            if (wordnumber in m.options.errorwords) {
                delete m.options.errorwords[wordnumber];
                $(this).removeClass(m.cd.badwordclass);
            } else {
                m.adderrorword(wordnumber, theword);
                $(this).addClass(m.cd.badwordclass);
            }
            m.processscores();
        },
        //this function is never called it seems ....
        processspace: function () {
            //this event is entered by  click on space
            //it relies on attr data-wordnumber being set correctly
            var m = this;
            var wordnumber = $(this).attr('data-wordnumber');
            var thespace = $('#' + m.cd.spaceclass + '_' + wordnumber);

            if (wordnumber === m.options.endwordnumber) {
                m.options.endwordnumber = m.options.totalwordcount;
                thespace.removeClass(m.cd.endspaceclass);
                $('#' + m.cd.spaceclass + '_' + m.options.totalwordcount).addClass(m.cd.endspaceclass);
            } else {
                $('#' + m.cd.spaceclass + '_' + m.options.endwordnumber).removeClass(m.cd.endspaceclass);
                m.options.endwordnumber = wordnumber;
                thespace.addClass(m.cd.endspaceclass);
            }
            m.processunread();
            m.processscores();
        },

        markup_maybeselfcorrects: function () {
            var that = this;
            if (this.options.sessionmatches) {
                var prevmatch = false;
                //loop through matches checking for insertions prior to matches
                $.each(this.options.sessionmatches, function (index, match) {
                    var maybe = false; // insertions exist between this match and prev match
                    var verymaybe = false; //this word is matched and prev word is matched, but insertions exist

                    if (prevmatch) {
                        if (match.tposition - prevmatch.tposition > 1 && match.pposition - prevmatch.pposition === 1) {
                            maybe = true;
                            verymaybe = true;
                        } else {
                            if (match.tposition - prevmatch.tposition > 1) {
                                maybe = true;
                            }
                        }
                    } else if (prevmatch === false) {
                        if (match.pposition < match.tposition) {
                            maybe = true;
                        }
                    }
                    //for now we will just work with very maybes, but we could do maybes
                    if (verymaybe) {
                        $('#' + that.cd.wordclass + '_' + match.pposition).addClass(that.cd.maybeselfcorrectclass);
                    }
                    prevmatch = match;
                });
            }
        },

        processunread: function () {
            var m = this;
            m.controls.eachword.each(function (index) {
                var wordnumber = $(this).attr('data-wordnumber');
                var thespace = $('#' + m.cd.spaceclass + '_' + wordnumber);

                if (Number(wordnumber) > Number(m.options.endwordnumber)) {
                    $(this).addClass(m.cd.unreadwordclass);
                    thespace.addClass(m.cd.unreadspaceclass);

                    //this will clear badwords after the endmarker
                    if (m.options.enforcemarker && wordnumber in m.options.errorwords) {
                        delete m.options.errorwords[wordnumber];
                        $(this).removeClass(m.cd.badwordclass);
                    }
                } else {
                    $(this).removeClass(m.cd.unreadwordclass);
                    thespace.removeClass(m.cd.unreadspaceclass);
                }
            });
        },
        processscores: function () {
            var m = this;
            var errorscore = Object.keys(m.options.errorwords).length;
            m.controls.errorscorebox.text(errorscore);

            //wpm score
            //we do not apply accuracy adjustment here, that is only for machine grades.
            var wpmscore = 0;
            var strictwpmscore = 0;
            var totalwords=m.options.endwordnumber - errorscore;
            if (m.options.totalseconds > 0) {

                //regular WPM
                wpmscore = Math.round((totalwords * 60) / m.options.totalseconds);

                //strict WPM for grading
                totalwords = totalwords - errorscore;
                if(totalwords < 0){totalwords =0;}
                strictwpmscore = Math.round((totalwords * 60) / m.options.totalseconds);
            }
            m.options.wpm = wpmscore;
            m.controls.wpmscorebox.text(wpmscore);

            //accuracy score
            var accuracyscore = 0;
            if (m.options.endwordnumber > 0) {
                accuracyscore = Math.round((m.options.endwordnumber - errorscore) / m.options.endwordnumber * 100);
            }
            m.options.accuracy = accuracyscore;
            m.controls.accuracyscorebox.text(accuracyscore);

            //sessionscore
            if(m.options.sessionscoremethod == m.constants.SESSIONSCORE_STRICT) {
                var usewpmscore = strictwpmscore;
            }else{
                var usewpmscore = wpmscore;
            }

            if (usewpmscore > m.options.targetwpm) {
                usewpmscore = m.options.targetwpm;
            }
            var sessionscore = Math.round(usewpmscore / m.options.targetwpm * 100);
            m.controls.sessionscorebox.text(sessionscore);

            //update form field
            m.controls.formelementwpmscore.val(wpmscore);
            m.controls.formelementsessionscore.val(sessionscore);
            m.controls.formelementaccuracy.val(accuracyscore);
            m.controls.formelementendword.val(m.options.endwordnumber);
            m.controls.formelementerrors.val(JSON.stringify(m.options.errorwords));

        }

    };
});