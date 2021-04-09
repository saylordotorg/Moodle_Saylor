define(['jquery', 'core/log', 'mod_solo/definitions'], function ($, log, def) {
    "use strict"; // jshint ;_;

    log.debug('Poodll Solo hidden player helper: initialising');

    return {

        controls: {},
        hiddenplayer: def.hiddenplayer,
        hiddenplayerbutton: def.hiddenplayerbutton,
        hiddenplayerbuttonactive: def.hiddenplayerbuttonactive,
        hiddenplayerbuttonpaused: def.hiddenplayerbuttonpaused,
        hiddenplayerbuttonplaying: def.hiddenplayerbuttonpaused,

        init: function (opts) {
            this.register_controls();
            this.register_events();
        },

        register_controls: function () {
            this.controls.hiddenplayer = $('.' + this.hiddenplayer);
            this.controls.hiddenplayerbutton = $('.' + this.hiddenplayerbutton);
            this.controls.playercontrols = $('.' + this.hiddenplayerbutton + ' i');
        },

        register_events: function () {
            var that = this;
            var audioplayer = this.controls.hiddenplayer;
            //handle the button click
            this.controls.hiddenplayerbutton.click(function (e) {
                var audiosrc = $(this).attr('data-audiosource');
                if (audiosrc === audioplayer.attr('src') && !(audioplayer.prop('paused'))) {
                    that.dohiddenstop();
                    that.controls.playercontrols.removeClass('fa-stop-circle');
                    that.controls.playercontrols.addClass('fa-play-circle');
                } else {
                    that.controls.playercontrols.removeClass('fa-stop-circle');
                    that.controls.playercontrols.addClass('fa-play-circle');
                    $(this).find('i').addClass('fa-stop-circle');
                    that.dohiddenplay(audiosrc);
                }
            });

        },


        dohiddenplay: function (audiosrc) {
            var m = this;
            var audioplayer = m.controls.hiddenplayer;
            audioplayer.attr('src', audiosrc);
            audioplayer[0].pause();
            audioplayer[0].load();
            var pp = audioplayer[0].play();
            if (pp !== undefined) {
                pp.then(function () {
                    // Yay we are playing
                }).catch(function (error) {
                    // somethings up ... but we can ignore it
                });
            }
            m.dobuttonicons();
        },
        dohiddenstop: function () {
            var m = this;
            var audioplayer = m.controls.hiddenplayer;
            audioplayer[0].pause();
            m.dobuttonicons();
        },

        dobuttonicons: function (theaudiosrc) {
            var m = this;
            var audioplayer = m.controls.hiddenplayer;
            if (!theaudiosrc) {
                theaudiosrc = audioplayer.attr('src');
            }
            m.controls.hiddenplayerbutton.each(function (index) {
                var audiosrc = $(this).attr('data-audiosource');
                if (audiosrc === theaudiosrc) {
                    $(this).addClass(m.activebutton);
                    if (audioplayer.prop('paused')) {
                        $(this).removeClass(m.hiddenplayerbuttonplaying);
                        $(this).addClass(m.hiddenplayerbuttonpaused);
                        //for now we make it look like no button is selected
                        //later we can implement better controls
                        $(this).removeClass(m.hiddenplayerbuttonactive);
                    } else {
                        $(this).removeClass(m.hiddenplayerbuttonpaused);
                        $(this).addClass(m.hiddenplayerbuttonplaying);
                    }
                } else {
                    $(this).removeClass(m.hiddenplayerbuttonactive);
                    $(this).removeClass(m.hiddenplayerbuttonplaying);
                    $(this).removeClass(m.hiddenplayerbuttonpaused);
                }
            });
        }
    };//end of return object

});