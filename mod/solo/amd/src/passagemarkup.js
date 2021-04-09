define(['jquery', 'core/log', 'mod_solo/popoverhelper'], function ($, log, popoverhelper) {
    "use strict"; // jshint ;_;

    log.debug('Passage Markup: initialising');

    return {
        //controls

        controls: {},
        currentmode: 'grading',

        constants: {
            REVIEWMODE_NOERRORS: 0,
            REVIEWMODE_SHOWERRORS: 1
        },

        //class definitions
        cd: {
            passagecontainer: 'mod_solo_grading_passagecont',
            summarytranscript: 'mod_solo_summarytranscript',
            summarytranscriptplaceholder: 'mod_solo_summarytranscriptplaceholder',
            audioplayerclass: 'mod_solo_passageaudioplayer',
            wordplayerclass: 'mod_solo_hidden_player',
            wordclass: 'mod_solo_grading_passageword',
            spaceclass: 'mod_solo_grading_passagespace',
            badwordclass: 'mod_solo_grading_badword',
            endspaceclass: 'mod_solo_grading_endspace',
            unreadwordclass: 'mod_solo_grading_unreadword',
            unreadspaceclass: 'mod_solo_grading_unreadspace',
            aiunmatched: 'mod_solo_aiunmatched',
            turnclass: 'summarytranscriptpart'
        },

        options: {
            endwordnumber: 0,
            errorwords: {},
            activityid: null,
            attemptid: null,
            sesskey: null,
            turns: [],
            reviewmode: 1 //this.constants.REVIEWMODE_SHOWERRORS
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
                log.debug('Passage Markup js: No config found on page. Giving up.');
                return;
            }

            //register the controls
            this.register_controls();

            //stash important info
            this.options.activityid = opts['activityid'];
            this.options.attemptid = opts['attemptid'];
            this.options.sesskey = opts['sesskey'];
            this.options.turns = opts['turns'];
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


                //We may have session matches and AI data, if AI is turned on
                if(opts['sessionmatches']) {
                    this.options.sessionmatches = JSON.parse(opts['sessionmatches']);
                }else{
                    this.options.sessionmatches =[];
                }
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

                //if we have the AI data then visually, and internally mark up the mismatches
                this.markup_badwords();
                this.markup_aiunmatchedwords();
                this.markup_aiunmatchedspaces();

                //mark up turns
                this.markup_turns();

                //display passage turns nicely
                $('.' + this.cd.passagecontainer).removeClass(this.cd.summarytranscriptplaceholder).addClass(this.cd.summarytranscript);


            } else {
                //set up our end passage marker
                this.options.endwordnumber = this.options.totalwordcount;
            }

            //add the endword marker
            var thespace = $('#' + this.cd.spaceclass + '_' + this.options.endwordnumber);
            thespace.addClass(this.cd.endspaceclass);

            //register events
            this.register_events();


            //init our popover helper which sets up the button events
            this.init_popoverhelper();

        },

        //set up events related to popover helper
        init_popoverhelper: function () {
            var that = this;

            //init the popover now that we have set the correct callback event handling thingies
            popoverhelper.init();
        },

        register_controls: function () {

            this.controls.wordplayer = $('#' + this.cd.wordplayerclass);
            this.controls.audioplayer = $('#' + this.cd.audioplayerclass);
            this.controls.eachword = $('.' + this.cd.wordclass);
            this.controls.eachspace = $('.' + this.cd.spaceclass);
            this.controls.endwordmarker = $('#' + this.cd.spaceclass + '_' + this.options.endwordnumber);
            this.controls.passagecontainer = $("." + this.cd.passagecontainer);

        },

        register_events: function () {
            var that = this;
            //set up event handlers


            //Play audio from and to spot check part
            this.controls.passagecontainer.on('click','.' + this.cd.wordclass, function () {
                    var wordnumber = parseInt($(this).attr('data-wordnumber'));
                    //some clicks are opening popup, some are playing and some are closing popups
                    //dont play when closing popups
                    if (!popoverhelper.isShowing(this)) {
                        that.doPlaySpotCheck(wordnumber);
                    }

                    if($(this).hasClass(that.cd.aiunmatched)){
                        var chunk = that.fetchTranscriptChunk(wordnumber);
                        if (chunk) {
                            popoverhelper.addTranscript(this, chunk);
                        }
                    }

            });

        },


        /*
        * Here we fetch the playchain, start playing frm audiostart and add an event handler to stop at audioend
         */
        doPlaySpotCheck: function (spotcheckindex) {
            var playchain = this.fetchWordPlayChain(spotcheckindex);
            log.debug(playchain);
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


        fetchTurnEndWord: function(currentwordindex){
            var currentword = $('#' + this.cd.wordclass + '_' + currentwordindex);
            var turnendword = currentword.siblings('.' + this.cd.wordclass).last();
            var turnend = turnendword.attr('data-wordnumber');
            if(!turnend || turnend < currentwordindex ){
                turnend = currentwordindex;
            }
            return turnend;

        },

        fetchTurnStartWord: function(currentwordindex){
            var currentword = $('#' + this.cd.wordclass + '_' + currentwordindex);
            var turnstartword = currentword.siblings('.' + this.cd.wordclass).first();
            var turnstart = turnstartword.attr('data-wordnumber');
            if(!turnstart || turnstart >currentwordindex ){
                turnstart = currentwordindex;
            }
            return turnstart;

        },


        /*
        * The playchain is all the words in a string of badwords.
        * The complexity comes because a bad word  is usually one that isunmatched by AI.
         */
        fetchWordPlayChain: function (wordnumber) {
            var isbad = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.badwordclass);
            var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
            if(isbad || isunmatched){
                return this.fetchBadWordPlayChain(wordnumber);
            }else{
                var starttime = this.options.sessionmatches['' + wordnumber].audiostart;
                var endtime = this.options.sessionmatches['' + wordnumber].audioend;
                var playchain = {};
                playchain.startword = wordnumber;
                playchain.endword = wordnumber;
                playchain.audiostart = starttime;
                playchain.audioend = endtime;
                return playchain;
            }
        },

        /*
        * The playchain is all the words in a string of badwords.
        * The complexity comes because a bad word  is usually one that isunmatched by AI.
        * So if the teacher clicks on a passage word that did not appear in the transcript, what should we play?
        * Answer: All the words from the last known to the next known word. Hence we create a play chain
        * For consistency, if the teacher flags matched words as bad, while we do know their precise location we still
        * make a play chain. Its not a common situation probably.
         */
        fetchBadWordPlayChain: function (spotcheckindex) {

            //The session matched words we use to get the audiostart and end
            var audiostartword=0;
            var audioendword=0;

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
                        audiostartword=wordnumber;
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
                        audiostartword=wordnumber;
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
                        audioendword=wordnumber;
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
                        audioendword=wordnumber;
                        break;
                    }
                }
            }

            //work out the playchain
            var playchain = {};
            playchain.startword = startindex;
            playchain.endword = parseInt(endindex);
            playchain.audiostart = starttime;
            playchain.audioend = parseInt(endtime);
            //console.log('audiostart:' + starttime);
            //console.log('audioend:' + endtime);

            //here we adjust the playback if we have gone out of the turn
            //its totally black magic guesswork, but it works ok
            var turnstart = this.fetchTurnStartWord(spotcheckindex);
            var turnend = this.fetchTurnEndWord(spotcheckindex);
            var beforelimit =0;
            var afterlimit =0;
            var startadjust =0;
            var endadjust =0;
            if(audiostartword > 0 && audiostartword < turnstart){
                beforelimit = spotcheckindex-turnstart + 1;
            }
            if(audioendword > turnend){
                afterlimit = turnend -spotcheckindex+ 1;
            }
            //no point trying to guess a completely mismatched turn.
            if(beforelimit && afterlimit){
                //give up ... what a disaster
            }else {
                //if the start is out of the turn
                if (beforelimit) {
                    startadjust = 0.5 * beforelimit;
                    playchain.audiostart = playchain.audioend - startadjust;
                    console.log('startadjust:' + startadjust);

                    //if the end is out of the turn
                } else if (afterlimit) {
                    endadjust = 0.7 * afterlimit;
                    playchain.audioend = playchain.audiostart + endadjust;
                    console.log('endadjust:' + startadjust);
                }
            }
            console.log(playchain);
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
   //         popoverhelper.remove();
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
            var startpassageindex=-1;
            for (var wordnumber = checkindex; wordnumber > 0; wordnumber--) {

                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                var isunreadword = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.unreadwordclass);
                //if we matched then the subsequent transcript word is the last unmatched one in the checkindex sequence
                if (!isunmatched && !isunreadword) {
                    startindex = this.options.sessionmatches['' + wordnumber].tposition + 1;
                    var startpassageindex=wordnumber;
                    break;
                }
            }//end of for loop

            //find endindex
            var endindex = -1;
            var endpassageindex=-1;
            for (var wordnumber = checkindex; wordnumber <= transcriptlength; wordnumber++) {

                var isunmatched = $('#' + this.cd.wordclass + '_' + wordnumber).hasClass(this.cd.aiunmatched);
                //if we matched then the previous transcript word is the last unmatched one in the checkindex sequence
                if (!isunmatched) {
                    endindex = this.options.sessionmatches['' + wordnumber].tposition - 1;
                    endpassageindex=wordnumber;
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

            //here we check if we have gone out of the turn
            var turnstart = this.fetchTurnStartWord(checkindex);
            var turnend = this.fetchTurnEndWord(checkindex);
            var beforelimit =0;
            var afterlimit =0;
            if(startpassageindex < turnstart){
                var beforelimit = checkindex-turnstart + 1;
            }
            if(endpassageindex > turnend ||endpassageindex ==-1){
                var afterlimit = turnend -checkindex+ 1;
            }
            //no point trying to guess a completely mismatched turn.
            if(beforelimit && afterlimit){
                return false;
            }
            //if the start is out of the turn
            if(beforelimit){
                startindex = endindex -beforelimit;
                if(startindex<1){return false;}
                //if the end is out of the turn
            }else if(afterlimit){
                endindex = startindex +afterlimit;
                if(endindex>+transcriptlength){return false;}
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

        markup_badwords: function () {
            var m = this;
            this.processunread();
            if (this.options.reviewmode == this.constants.REVIEWMODE_SHOWERRORS) {
                $.each(m.options.errorwords, function (index) {
                        $('#' + m.cd.wordclass + '_' + m.options.errorwords[index].wordnumber).addClass(m.cd.badwordclass);
                    }
                );
            }
        },

        markup_turns: function() {
            var m= this;
            var turnspan ='<span class="' + m.cd.turnclass + '"></span>';
            $.each(m.options.turns, function (index) {
                   var startelement = $('#' + m.cd.wordclass + '_' + m.options.turns[index].start);
                   var newturnspan  = $(turnspan).insertBefore(startelement);;
                   for(var i=m.options.turns[index].start; i<=m.options.turns[index].end;i++) {
                       newturnspan.append($('#' + m.cd.wordclass + '_' + i));
                       newturnspan.append($('#' + m.cd.spaceclass + '_' + i));
                   }
                }
            );
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
        }
    };
});