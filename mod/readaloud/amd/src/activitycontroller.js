/* jshint ignore:start */
define(['jquery', 'core/log', 'mod_readaloud/definitions',
        'mod_readaloud/recorderhelper', 'mod_readaloud/modelaudiokaraoke',
        'core/ajax','core/notification','mod_readaloud/smallreporthelper','mod_readaloud/listenandrepeat'],
    function ($, log, def, recorderhelper, modelaudiokaraoke, Ajax, notification, smallreporthelper, landr) {

    "use strict"; // jshint ;_;

    log.debug('Activity controller: initialising');

    return {

        cmid: null,
        activitydata: null,
        holderid: null,
        recorderid: null,
        playerid: null,
        sorryboxid: null,
        controls: null,
        ra_recorder: null,
        rec_time_start: 0,
        enableshadow: false,
        enablepreview: false,
        enablelandr: false,
        letsshadow: false,


        //CSS in this file
        passagefinished: def.passagefinished,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function (props) {
            var dd = this.clone();

            //pick up opts from html
            var theid = '#amdopts_' + props.widgetid;
            var configcontrol = $(theid).get(0);
            if (configcontrol) {
                dd.activitydata = JSON.parse(configcontrol.value);
                $(theid).remove();
            } else {
                //if there is no config we might as well give up
                log.debug('Read Aloud Test Controller: No config found on page. Giving up.');
                return;
            }

            dd.cmid = props.cmid;
            dd.holderid = props.widgetid + '_holder';
            dd.recorderid = props.widgetid + '_recorder';
            dd.playerid = props.widgetid + '_player';
            dd.sorryboxid = props.widgetid + '_sorrybox';

            //if the browser doesn't support html5 recording.
            //then warn and do not go any further
            if (!dd.is_browser_ok()) {
                $('#' + dd.sorryboxid).show();
                return;
            }

            //set up model audio
            dd.enableshadow =dd.activitydata.enableshadow;
            dd.enablepreview =dd.activitydata.enablepreview;
            dd.enablelandr =dd.activitydata.enablelandr;
            dd.setupmodelaudio();

            //set up listen an repeat
            dd.setuplandr();


            //init recorder and html and events
            dd.setup_recorder();
            dd.process_html(dd.activitydata);
            dd.register_events();

            //set initial mode
            //we used to check the settings but now we just show the non-options greyed out
            if(dd.enableshadow || dd.enablepreview || true){
                dd.domenulayout();
            }else{
                dd.doreadinglayout();
            }
        },

        setupmodelaudio: function(){
            var karaoke_opts={breaks:this.activitydata.breaks, audioplayerclass:this.activitydata.audioplayerclass };
            modelaudiokaraoke.init(karaoke_opts);
        },

        setuplandr: function(){
            var landr_opts={modelaudiokaraoke: modelaudiokaraoke, cmid: this.cmid, language: this.activitydata.language,
                region: this.activitydata.region, phonetics: this.activitydata.phonetics, stt_guided: this.activitydata.stt_guided};
            landr.init(landr_opts);
        },

        process_html: function (opts) {

            //these css classes/ids are all passed in from php in
            //renderer.php::fetch_activity_amd should maybe just simplify and declare them in definitions.js
            var controls = {
                hider: $('.' + opts['hider']),
                introbox: $('.' + 'mod_intro_box'),
                progresscontainer: $('.' + opts['progresscontainer']),
                feedbackcontainer: $('.' + opts['feedbackcontainer']),
                errorcontainer: $('.' + opts['errorcontainer']),
                passagecontainer: $('.' + opts['passagecontainer']),
                recordingcontainer: $('.' + opts['recordingcontainer']),
                dummyrecorder: $('.' + opts['dummyrecorder']),
                recordercontainer: $('.' + opts['recordercontainer']),
                menubuttonscontainer: $('.' + opts['menubuttonscontainer']),
                menuinstructionscontainer: $('.' + opts['menuinstructionscontainer']),
                previewinstructionscontainer: $('.' + opts['previewinstructionscontainer']),
                landrinstructionscontainer: $('.' + opts['landrinstructionscontainer']),
                activityinstructionscontainer: $('.' + opts['activityinstructionscontainer']),
                recinstructionscontainerright: $('.' + opts['recinstructionscontainerright']),
                recinstructionscontainerleft: $('.' + opts['recinstructionscontainerleft']),
                allowearlyexit: $('.' + opts['allowearlyexit']),
                wheretonextcontainer: $('.' + opts['wheretonextcontainer']),
                modelaudioplayer: $('#' + opts['modelaudioplayer']),
                startlandrbutton: $('#' + opts['startlandrbutton']),
                startpreviewbutton: $('#' + opts['startpreviewbutton']),
                startreadingbutton: $('#' + opts['startreadingbutton']),
                startshadowbutton: $('#' + opts['startshadowbutton']),
                returnmenubutton: $('#' + opts['returnmenubutton']),
                stopandplay: $('#' + opts['stopandplay']),
                smallreportcontainer: $('.' + opts['smallreportcontainer'])
            };
            this.controls = controls;
        },

        is_browser_ok: function () {
            return (navigator && navigator.mediaDevices
                && navigator.mediaDevices.getUserMedia);
        },

        setup_recorder: function () {
            var dd = this;

            //after the recorder reports that it has (really) started this functuon is called.
            var beginall= function(){
                dd.passagerecorded = true;
                if(dd.enableshadow && dd.letsshadow){
                    dd.controls.modelaudioplayer[0].play();
                }
            };

            var on_speech = function (eventdata) {
                var speech = eventdata.capturedspeech;
                var speechresults = eventdata.speechresults;
            };

            //originates from the recording:started event
            //contains no meaningful data
            //See https://api.poodll.com
            var on_recording_start = function (eventdata) {
                dd.rec_time_start = new Date().getTime();
                dd.dopassagelayout();
                dd.controls.passagecontainer.show(1000, beginall);
            };

            //originates from the recording:ended event
            //contains no meaningful data
            //See https://api.poodll.com
            var on_recording_end = function (eventdata) {
                //its a bit hacky but the rec end event can arrive immed. somehow probably when the mic test ends
                var now = new Date().getTime();
                if ((now - dd.rec_time_start) < 3000) {
                    return;
                }
                dd.douploadlayout();
                //if we are shadowing we should stop the audio player.
                if(dd.enableshadow && dd.letsshadow){
                    dd.controls.modelaudioplayer[0].currentTime=0;
                    dd.controls.modelaudioplayer[0].pause();
                }
            };

            //data sent here originates from the awaiting_processing event
            //See https://api.poodll.com
            var on_audio_processing = function (eventdata) {
                //at this point we know the submission has been uploaded and we know the fileURL
                //so we send the submission
                var now = new Date().getTime();
                var rectime = now - dd.rec_time_start;
                if (rectime > 0) {
                    rectime = Math.ceil(rectime / 1000);
                }

                dd.send_submission(eventdata.mediaurl, rectime);
                //and let the user know that they are all done
                dd.dofinishedlayout();
            };

            //init the recorder
            recorderhelper.init(dd.activitydata,
                on_recording_start,
                on_recording_end,
                on_audio_processing,
                on_speech);
        },

        register_events: function () {
            var dd = this;
          
            
            dd.controls.startpreviewbutton.click(function(e){
                dd.dopreviewlayout();
            });
            dd.controls.startpreviewbutton.keypress(function(e){
                if (e.which == 32 || e.which == 13 ) {
                    dd.dopreviewlayout();
                    e.preventDefault();
                }
            });
            dd.controls.startlandrbutton.click(function(e){
                dd.dolandrlayout();
            });
            dd.controls.startlandrbutton.keypress(function(e){
                if (e.which == 32 || e.which == 13 ) {
                    dd.dolandrlayout();
                    e.preventDefault();
                }
            });
            dd.controls.startreadingbutton.click(function(e){
                dd.letsshadow=false;
                dd.doreadinglayout();
            });
            dd.controls.startreadingbutton.keypress(function(e){
                if (e.which == 32 || e.which == 13) {
                    dd.letsshadow=false;
                    dd.doreadinglayout();
                    e.preventDefault();
                }
            });
            dd.controls.startshadowbutton.click(function(e){
                //landr shadowing
                //dd.dolandrlayout();
                // landr.shadow=true;

                dd.letsshadow=true;
                dd.doreadinglayout();
            });
            dd.controls.startshadowbutton.keypress(function(e){
                if (e.which == 32 || e.which == 13) {
                    //dd.dolandrlayout();
                    //landr.shadow=true;

                    dd.letsshadow=true;
                    dd.doreadinglayout();
                    e.preventDefault();
                }
            });
            dd.controls.returnmenubutton.click(function(e){
                //in most cases ajax hide show is ok, but L&R stuffs up android for normal readaloud so we reload
                if(dd.isandroid() && dd.controls.landrinstructionscontainer.is(":visible")){
                    location.reload();
                }else {
                    dd.controls.modelaudioplayer[0].currentTime = 0;
                    dd.controls.modelaudioplayer[0].pause();
                    dd.domenulayout();
                }
            });
        },

        send_submission: function (filename, rectime) {
            var that = this;
            var shadowing = (that.enableshadow && that.letsshadow) ? 1 : 0;
            Ajax.call([{
                methodname: 'mod_readaloud_submit_regular_attempt',
                args: {
                    cmid: that.cmid,
                    filename:  filename,//encodeURIComponent(filename),
                    rectime: rectime,
                    shadowing: shadowing
                },
                done: function(ajaxresult){
                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        switch (payloadobject.success) {
                            case true:
                                log.debug('attempted submission accepted');
                                break;
                            case false:
                            default:
                                log.debug('attempted item evaluation failure');
                                if (payloadobject.message) {
                                    log.debug('message: ' + payloadobject.message);
                                }
                        }
                    }
                },
                fail: notification.exception
            }]);
/*
            return;

            //set up our ajax request
            var xhr = new XMLHttpRequest();
            var that = this;

            //set up our handler for the response
            xhr.onreadystatechange = function (e) {
                if (this.readyState === 4) {
                    if (xhr.status === 200) {
                        log.debug('ok we got an attempt submission response');
                        //get a yes or forgetit or tryagain
                        var payload = xhr.responseText;
                        var payloadobject = JSON.parse(payload);
                        if (payloadobject) {
                            switch (payloadobject.success) {
                                case true:
                                    log.debug('attempted submission accepted');

                                    break;

                                case false:
                                default:
                                    log.debug('attempted item evaluation failure');
                                    if (payloadobject.message) {
                                        log.debug('message: ' + payloadobject.message);
                                    }
                            }
                        }
                    } else {
                        log.debug('Not 200 response:' + xhr.status);
                    }
                }
            };

            //to get through mod_security environments
            filename = filename.replace(/^https:\/\//i, 'https___');
            var params = "cmid=" + that.cmid + "&filename=" + encodeURIComponent(filename) + "&rectime=" + rectime;
            xhr.open("POST", M.cfg.wwwroot + '/mod/readaloud/ajaxhelper.php', true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.send(params);
*/
        },

        doreadinglayout: function () {
            var m = this;
            m.controls.hider.fadeOut('fast');
            m.controls.activityinstructionscontainer.show();
            m.controls.recordingcontainer.show();
            m.controls.introbox.hide();
            m.controls.menuinstructionscontainer.hide();
            m.controls.menubuttonscontainer.hide();
            m.controls.smallreportcontainer.hide();
            m.controls.returnmenubutton.show();
            m.controls.progresscontainer.hide();
            m.controls.passagecontainer.removeClass('previewmode shadowmode reviewmode nothingmode');
            m.controls.passagecontainer.addClass('readmode');
            m.controls.passagecontainer.hide();
            m.controls.feedbackcontainer.hide();
            m.controls.wheretonextcontainer.hide();
            m.controls.stopandplay.hide();

            modelaudiokaraoke.modeling=true;
        },

        domenulayout: function () {
            var m = this;
            m.controls.introbox.show();
            m.controls.menuinstructionscontainer.show();
            m.controls.menubuttonscontainer.show();
            m.controls.smallreportcontainer.show();
            m.controls.activityinstructionscontainer.hide();
            m.controls.returnmenubutton.hide();
            m.controls.previewinstructionscontainer.hide();

            m.controls.landrinstructionscontainer.hide();
            landr.deactivate();

            m.controls.progresscontainer.hide();
            m.controls.passagecontainer.hide();
            m.controls.recordingcontainer.hide();
            m.controls.feedbackcontainer.hide();
            m.controls.wheretonextcontainer.hide();
            m.controls.modelaudioplayer.hide();
            m.controls.hider.hide();
            m.controls.stopandplay.hide();

            modelaudiokaraoke.modeling=true;
        },

        dopreviewlayout: function () {
            var m = this;
            m.controls.passagecontainer.removeClass('readmode shadowmode reviewmode nothingmode');
            m.controls.passagecontainer.addClass('previewmode');
            m.controls.passagecontainer.show();
            m.controls.previewinstructionscontainer.show();
            m.controls.landrinstructionscontainer.hide();
            m.controls.introbox.hide();
            m.controls.returnmenubutton.show();
            m.controls.modelaudioplayer.hide();
            m.controls.smallreportcontainer.hide();
            m.controls.stopandplay.show();
            m.controls.menubuttonscontainer.hide();
            m.controls.hider.hide();
            m.controls.progresscontainer.hide();
            m.controls.menuinstructionscontainer.hide();
            m.controls.activityinstructionscontainer.hide();
            m.controls.recordingcontainer.hide();
            m.controls.feedbackcontainer.hide();
            m.controls.wheretonextcontainer.hide();
            m.controls.stopandplay.show();

            modelaudiokaraoke.modeling=false;
        },

        dolandrlayout: function () {
            var m = this;
            m.controls.passagecontainer.removeClass('readmode shadowmode reviewmode nothingmode');
            m.controls.passagecontainer.addClass('previewmode');
            m.controls.passagecontainer.show();
            m.controls.landrinstructionscontainer.show();
            m.controls.previewinstructionscontainer.hide();
            m.controls.introbox.hide();
            m.controls.returnmenubutton.show();
            m.controls.modelaudioplayer.hide();
            m.controls.smallreportcontainer.hide();
            m.controls.stopandplay.show();
            m.controls.menubuttonscontainer.hide();
            m.controls.hider.hide();
            m.controls.progresscontainer.hide();
            m.controls.menuinstructionscontainer.hide();
            m.controls.activityinstructionscontainer.hide();
            m.controls.recordingcontainer.hide();
            m.controls.feedbackcontainer.hide();
            m.controls.wheretonextcontainer.hide();
            m.controls.stopandplay.show();
            landr.activate();

            modelaudiokaraoke.modeling=false;

        },

        dopassagelayout: function () {
            var m = this;
            m.controls.introbox.hide();
        },

        douploadlayout: function () {
            var m = this;
            m.controls.passagecontainer.addClass(m.passagefinished);
            m.controls.hider.fadeIn('fast');
            m.controls.progresscontainer.fadeIn('fast');
        },

        dofinishedlayout: function () {
            var m = this;
            m.controls.hider.fadeOut('fast');
            m.controls.progresscontainer.fadeOut('fast');
            m.controls.smallreportcontainer.hide();
            m.controls.activityinstructionscontainer.hide();
            m.controls.passagecontainer.hide();
            m.controls.recordingcontainer.hide();
            m.controls.feedbackcontainer.show();
            m.controls.wheretonextcontainer.show();
            m.controls.returnmenubutton.hide();

        },
        doerrorlayout: function () {
            var m = this;
            m.controls.hider.fadeOut('fast');
            m.controls.progresscontainer.fadeOut('fast');
            m.controls.passagecontainer.hide();
            m.controls.recordingcontainer.hide();
            m.controls.errorcontainer.show();
            m.controls.wheretonextcontainer.show();
        },
        isandroid: function() {
                if (/Android/i.test(navigator.userAgent)) {
                    return true;
                } else {
                    return false;
                }
        }
    };//end of returned object
});//total end
