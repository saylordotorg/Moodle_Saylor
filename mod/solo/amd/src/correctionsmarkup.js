define(['jquery', 'core/log'], function ($, log) {
    "use strict"; // jshint ;_;

    log.debug('Corrections Markup: initialising');

    return {
        //controls

        controls: {},

        //class definitions
        cd: {
            correctionscontainer: 'mod_solo_corrections_cont',
            wordclass: 'mod_solo_grading_correctionsword',
            suggestionclass: 'mod_solo_corrections_suggestedword',
            aiunmatched: 'mod_solo_aiunmatched',
        },

        options: {
            errorwords: {},
            grammarmatches: {},
        },


        init: function (config) {

            //pick up opts from html
            var theid = '#' + config['id'];
            var configcontrol = $(theid).get(0);
            if (configcontrol) {
                var opts = JSON.parse(configcontrol.value);
                log.debug(opts);
                $(theid).remove();


                if (opts['sessionerrors'] !== '') {
                    this.options.suggestedwords = JSON.parse(opts['sessionerrors']);
                } else {
                    this.options.suggestedwords = {};
                }
                if (opts['sessionmatches'] !== '') {
                    this.options.grammarmatches = JSON.parse(opts['sessionmatches']);
                } else {
                    this.options.grammarmatches  = {};
                }


            } else if(config.hasOwnProperty('sessionerrors') &&
                config.hasOwnProperty('sessionmatches')){

                    this.options.suggestedwords = JSON.parse(config['sessionerrors']);
                    this.options.grammarmatches = JSON.parse(config['sessionmatches']);

            } else {
                //if there is no config we might as well give up
                log.debug('Corrections Markup js: No config found on page. Giving up.');
                return;
            }

            //register the controls
            this.register_controls();

            log.debug(this.options);

            //markup suggested words
            this.markup_suggestedwords();
            //mark up unmatched words
            this.markup_unmatchedwords();

            //register events
            this.register_events();

        },


        register_controls: function () {

            this.controls.correctionscontainer = $("." + this.cd.correctionscontainer);

        },

        register_events: function () {
            var that = this;
            //set up event handlers
            this.controls.correctionscontainer.on('click','.' + this.cd.wordclass, function () {
                    var wordnumber = parseInt($(this).attr('data-wordnumber'));
                    //do something
                log.debug(wordnumber);

            });

        },


        markup_suggestedwords: function () {
            var m = this;
            $.each(m.options.suggestedwords, function (index) {
                log.debug('.' + m.cd.correctionscontainer + ' #' + m.cd.wordclass + '_' + (m.options.suggestedwords[index].wordnumber));
                    $('.' + m.cd.correctionscontainer + ' #' + m.cd.wordclass + '_' + (m.options.suggestedwords[index].wordnumber)).addClass(m.cd.suggestionclass);
                }
            );
        },

        //mark up all ai unmatched words as aiunmatched
        // if words have been removed from passage, these wont be marked up yet
        markup_unmatchedwords: function () {
            var that = this;
            if (this.options.grammarmatches) {
                var prevmatch = 0;
                $.each(this.options.grammarmatches, function (index, match) {
                    //if there is a gap since the previous word match, it's a missing word (ie not in original)
                    if((match.tposition - prevmatch)>1) {
                        var missingcnt = match.tposition - prevmatch -1;
                        for(var mi= 0; mi <missingcnt;mi++) {
                            var missingwordnumber = match.tposition - 1 - mi;
                            $('#' + that.cd.wordclass + '_' + missingwordnumber).addClass(that.cd.suggestionclass);
                        }
                    }
                    prevmatch = match.tposition;
                });
            }

        },
    };
});