/* jshint ignore:start */
define(['jquery', 'core/log', 'filter_poodll/utils_amd',
    'filter_poodll/adapter', 'filter_poodll/uploader', 'filter_poodll/hermes', 'filter_poodll/timer',
    'filter_poodll/audioanalyser',
    'filter_poodll/msr_poodll',
    'filter_poodll/dlg_errordisplay',
    'filter_poodll/dlg_download',
    'filter_poodll/speech_poodll',
    'filter_poodll/poodll_mediaskins'], function ($, log, utils, adapter, uploader, hermes, timer, audioanalyser,
                                                  poodll_msr, errordialog, downloaddialog, speechrecognition, mediaskins) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Media Recorder: initialising');

    return {

        instanceprops: [],
        skins: [],
        laststream: [],

        fetch_instanceprops: function (controlbarid) {
            return this.instanceprops[controlbarid];
        },

        fetch_skin: function (controlbarid) {
            return this.skins[controlbarid];
        },

        is_ios: function () {
            return utils.is_ios();
        },

        // This recorder supports the current browser
        supports_current_browser: function (config) {

            var protocol_ok = M.cfg.wwwroot.indexOf('https:') == 0 ||
                M.cfg.wwwroot.indexOf('http://localhost') == 0;
            if(!protocol_ok){return false;}

            if (config.mediatype != 'audio' && config.mediatype != 'video') {
                return false;
            }

            var ret = utils.can_html5_record(config.mediatype);
            if (ret) {
                log.debug('PoodLL Media Recorder: supports this browser');
            }
            return ret;
        },

        // Perform the embed of this recorder on the page
        // into the element passed in. with config
        embed: function (element, config) {
            var that = this;

            var controlbarid = "filter_poodll_controlbar_" + config.widgetid;
            this.init_instance_props(controlbarid);
            var ip = this.fetch_instanceprops(controlbarid);
            ip.config = config;
            ip.controlbarid = controlbarid;
            if (config.hideupload) {
                ip.showupload = false;
            } else {
                ip.showupload = true;
            }
            ip.timeinterval = config.media_timeinterval;
            ip.audiomimetype = config.media_audiomimetype;
            ip.videorecordertype = config.media_videorecordertype;
            ip.videocaptureheight = config.media_videocaptureheight;
            ip.errordialog = errordialog.clone();
            ip.errordialog.init(ip);
            ip.downloaddialog = downloaddialog.clone();
            ip.downloaddialog.init(this, ip);

            //init the hermes
            //putting it in config allows us to post messages from uploader and skin as required
            ip.config.hermes = hermes.clone();
            ip.config.hermes.init(config.id, config.allowedURL, config.iframeembed);

            // init our skin
            var theskin = this.init_skin(controlbarid, ip.config.media_skin, ip);

            //Speech recognition
            if (ip.config.speechevents && ip.speechrec.will_work_ok(ip.config)) {
                if (!ip.config.language) {
                    ip.config.language = 'en-US';
                }
                ip.speechrec.init(ip.config);
                ip.speechrec.onfinalspeechcapture = function (speechtext,speechresults) {
                    var messageObject = {};
                    messageObject.type = "speech";
                    messageObject.capturedspeech = speechtext;
                    messageObject.speechresults = speechresults;
                    ip.config.hermes.postMessage(messageObject);
                    //send message to our skin
                    if (theskin.hasOwnProperty('onfinalspeechcapture')) {
                        theskin.onfinalspeechcapture(speechtext,speechresults);
                    }
                };
            }else{
                //just turn off speech events to make it easier to check later
                log.debug('turning off speech events. not req. or not supported.');
                ip.config.speechevents = false;
            }

            // add callbacks for uploadsuccess and upload failure
            ip.config.onuploadsuccess = function (widgetid) {
                that.onUploadSuccess(widgetid, theskin);
            };
            ip.config.onuploadfailure = function (widgetid) {
                that.onUploadFailure(widgetid, theskin);
            };

            switch (config.mediatype) {
                case 'audio':
                    var preview = theskin.fetch_preview_audio(config.media_skin);
                    var resource = theskin.fetch_resource_audio(config.media_skin);


                    ip.controlbar = this.fetch_controlbar_audio(element, controlbarid, preview, resource);
                    ip.uploader = uploader.clone();

                    //init uploader skin and uploader
                    //uploader skin(upskin) if set to false here will default to naff green bar
                    //should be called after controlbar is created, because thats when canvas is created
                    var upskin = theskin.fetch_uploader_skin(ip.controlbarid, element);
                    ip.uploader.init(element, config, upskin);

                    this.register_events_audio(controlbarid);

                    //if this is the uploader skin, then we do not bother to get mediaDevices
                    if (ip.config.media_skin == 'upload' || ip.config.media_skin == 'warning') {
                        break;
                    }

                    // force permissions;
                    navigator.mediaDevices.getUserMedia({"audio": true}).then(function (stream) {
                        //do nothing
                        log.debug('successfully forced permissions and got user media');

                    }).catch(function (err) {
                        log.debug('location 9998');
                        log.debug(err);
                        ip.errordialog.open(err);
                    });


                    break;
                case 'video':
                    var preview = theskin.fetch_preview_video(config.media_skin);
                    var resource = theskin.fetch_resource_video(config.media_skin);
                    ip.controlbar = this.fetch_controlbar_video(element, controlbarid, preview, resource);
                    ip.uploader = uploader.clone();
                    //init uploader skin and uploader
                    //uploader skin(upskin) if set to false here will default to naff green bar
                    //should be called after controlbar is created, because thats when canvas is created
                    var upskin = theskin.fetch_uploader_skin(ip.controlbarid, element);
                    ip.uploader.init(element, config, upskin);

                    this.register_events_video(controlbarid);

                    //if this is any of the uploader/warning/screen skins, then we do not bother to get mediaDevices
                    if (ip.config.media_skin == 'upload' || ip.config.media_skin == 'warning' || ip.config.media_skin == 'screen') {
                        break;
                    }

                    //force permissions and show in preview
                    navigator.mediaDevices.getUserMedia({"audio": true, "video": true}).then(function (stream) {
                        //stop any playing tracks of the current stream
                        that.restream_preview_video_player(controlbarid, stream)

                    }).catch(function (err) {
                        log.debug('location 9999');
                        log.debug(err);
                    });
                    break;

            }


            // init timer
            ip.timer = timer.clone();
            ip.timer.init(ip.config.timelimit, function () {
                    theskin.handle_timer_update(controlbarid);
                    // ip.controlbar.status.html(ip.timer.fetch_display_time());
                }
            );
            theskin.handle_timer_update(controlbarid);

            //in the case of an API embed, the caller might want a handle on the skin
            return theskin;
        },


        init_instance_props: function (controlbarid) {
            this.instanceprops[controlbarid] = {};
            this.instanceprops[controlbarid].recorded_index = 0;
            this.instanceprops[controlbarid].mediaRecorder = null;
            this.instanceprops[controlbarid].blobs = [];
            this.instanceprops[controlbarid].timeinterval = 1000;
            this.instanceprops[controlbarid].audiomimetype = 'audio/webm';
            this.instanceprops[controlbarid].videorecordertype = 'auto';// mediarec or webp
            this.instanceprops[controlbarid].videocapturewidth = 320;
            this.instanceprops[controlbarid].videocaptureheight = 240;
            this.instanceprops[controlbarid].controlbar = '';
            this.instanceprops[controlbarid].previewvolume = 1;
            this.instanceprops[controlbarid].timer = {};
            this.instanceprops[controlbarid].timer = {};
            this.instanceprops[controlbarid].showupload = true;
            this.instanceprops[controlbarid].uploader = {};
            this.instanceprops[controlbarid].uploaded = false;

            // we create the audio context object here because so its created in the init and passed around
            // video context is associated with a player so it seems to be ok.
            this.instanceprops[controlbarid].useraudiodeviceid = false;
            this.instanceprops[controlbarid].uservideodeviceid = false;
            this.instanceprops[controlbarid].devices = [];

            //we only want one context per recorder, but beyond 6 we hit Chromes limit, so we reuse the first we stashed in
            //window
            var AudioContext = window.AudioContext // Default
                || window.webkitAudioContext // Safari and old versions of Chrome
                || false;
            if (typeof window.poodllmediarecorder_actx === 'undefined') {
                var ac = new AudioContext();
                window.poodllmediarecorder_actx = ac;
                window.poodllmediarecorder_actx_cnt = 1;
            } else if (window.poodllmediarecorder_actx_cnt == 6) {
                var ac = window.poodllmediarecorder_actx;
                log.debug('More than 6 contexts, reusing first one. visualizations might go weird');
            } else {
                var ac = new AudioContext();
                window.poodllmediarecorder_actx_cnt += 1;
            }

            this.instanceprops[controlbarid].audioctx = ac;

            var aa = audioanalyser.clone();
            aa.init(ac);
            this.instanceprops[controlbarid].audioanalyser = aa;
            this.instanceprops[controlbarid].previewstillcold = true;

            //speech recognition
            this.instanceprops[controlbarid].speechrec = speechrecognition.clone();

        },

        init_skin: function (controlbarid, skinname, instanceprops) {
            this.skins[controlbarid] = mediaskins.fetch_skin_clone(skinname);
            this.skins[controlbarid].init(instanceprops, this);
            return this.skins[controlbarid];
        },

        onUploadSuccess: function (widgetid, theskin) {
            log.debug('from poodllmediarecorder: uploadsuccess');
            var controlbarid = 'filter_poodll_controlbar_' + widgetid;
            theskin.onUploadSuccess(controlbarid);
        },

        onUploadFailure: function (widgetid, theskin) {
            log.debug('from poodllmediarecorder: uploadfailure');
            var controlbarid = 'filter_poodll_controlbar_' + widgetid;
            theskin.onUploadFailure(controlbarid);
            //if it failed we want to push the user to download this file
            theskin.fetch_instanceprops().downloaddialog.open(theskin.pmr, theskin.instanceprops);
        },


        onMediaError: function (e, ip) {
            if(ip.hasOwnProperty('errordialog')) {
                ip.errordialog.open(e);
            }
            log.error('media error', e);
        },

        captureUserMedia: function (mediaConstraints, successCallback, errorCallback) {
            navigator.mediaDevices.getUserMedia(mediaConstraints).then(successCallback).catch(errorCallback);

        },


        warmup_context: function (ip) {
            var ctx = ip.audioctx;
            //for chrome oct 2018
            if (ctx.state == 'suspended') {
                ctx.resume();
            }

            var buffer = ctx.createBuffer(1, 1, 22050);
            var source = ctx.createBufferSource();
            source.buffer = buffer;
            source.connect(ctx.destination);
            source.start(0);
        },
        warmup_preview: function (ip) {
            var preview = ip.controlbar.preview;
            if (ip.previewstillcold && preview && preview.get(0)) {
                var pPromise = ip.controlbar.preview[0].play();
                // the promise thing here is just to suppress console warnings
                if (pPromise !== undefined) {
                    pPromise.then(function () {
                        // playback started we do not need to do anything
                    }).catch(function (error) {
                        log.debug(error);
                    });
                }
                ip.previewstillcold = false;
            }

        },
        do_start_audio: function (ip, onMediaSuccess) {

            var that = this;
            // we warm up the context object
            this.warmup_context(ip);

            // warmup. the preview object
            this.warmup_preview(ip);

            //mute the preview
            ip.controlbar.preview[0].muted=true;

            ip.blobs = [];
            switch (ip.config.mediatype) {
                case 'audio':
                    var mediaConstraints = this.fetch_audio_constraints(ip);
                    break;
                case 'video':
                    var mediaConstraints = this.fetch_video_constraints(ip);
            }

            //We always tidy up old streams before calling getUserMedia
            //this.tidy_old_stream(ip.controlbarid);
            this.captureUserMedia(mediaConstraints, onMediaSuccess, function (e) {
                that.onMediaError(e, ip);
            });

        },


        do_start_screen: function (ip, onMediaSuccess) {

            var that = this;
            // we warm up the context object
            this.warmup_context(ip);

            // warmup. the preview object
            this.warmup_preview(ip);

            //mute the preview
            ip.controlbar.preview[0].muted=true;

            ip.blobs = [];
            //get media constraints
            var mediaConstraints = {
                audio: {'echoCancellation': true},
                video: {cursor: "motion"}
            };

            //set aspect ratio and I think the "exact" below should be "ideal"
            //  mediaConstraints.video = {aspectRatio: 1920/1080};


            //do all our stream stuff
            navigator.mediaDevices.getDisplayMedia(mediaConstraints)
                .then(function(displayStream){
                    // check for a user audio selected device
                    if (ip.useraudiodeviceid) {
                        var audiodeviceid = ip.useraudiodeviceid.valueOf();
                        mediaConstraints.audio.deviceId = audiodeviceid ? {exact: audiodeviceid} : undefined;
                    }

                    navigator.mediaDevices.getUserMedia({ audio: mediaConstraints.audio, video: false }).then(
                        function(voiceStream) {
                            var tracks = displayStream.getTracks().concat(voiceStream.getAudioTracks());
                            var stream = new MediaStream(tracks);
                            onMediaSuccess(stream);
                        }
                    )
                })
                .catch(function (e) {
                    that.onMediaError(e, ip);
            });
        },

        do_start_video: function (ip, onMediaSuccess) {

        },

        do_stopplay_audio: function (ip, preview) {
            preview.pause();
            preview.muted=false;

            switch (ip.config.mediatype) {
                case 'audio':
                    //we already did everything we need to
                    break;
                case 'video':
                    //Safari can not reuse the preview player, so we created a 'review' and now dispose of it
                    if(ip.controlbar.hasOwnProperty('livepreview')){
                        ip.controlbar.preview.hide();
                        ip.controlbar.preview = ip.controlbar.livepreview;
                        ip.controlbar.preview.show();
                    }
            }
        },

        do_play_audio: function (ip, preview_unused) {

            if (ip.blobs && ip.blobs.length > 0) {
                log.debug('playing type:' + ip.blobs[0].type);
                log.debug(ip.blobs);
                utils.doConcatenateBlobs(ip.blobs, function (concatenatedBlob) {

                    log.debug(concatenatedBlob);
                    var mediaurl = URL.createObjectURL(concatenatedBlob);

                    //Safari can not reuse the preview player [sigh]
                    //audio is only used for review, so for video only we create a second video element
                    switch (ip.config.mediatype) {
                        case 'audio':
                            var preview = ip.controlbar.preview[0];
                            break;
                        case 'video':
                            ip.controlbar.livepreview = ip.controlbar.preview;
                            ip.controlbar.preview = ip.controlbar.preview.clone().insertAfter(ip.controlbar.preview);
                            var preview = ip.controlbar.preview[0];
                            ip.controlbar.livepreview.hide();

                    }
                    preview.src = mediaurl;
                    preview.controls = false;
                    preview.volume = ip.previewvolume;
                    preview.muted=false;

                    // Click the stop button if playback ends;
                    $(preview).bind('ended', function () {
                        ip.controlbar.stopbutton.click();
                    });

                    var ppromise = preview.play();
                    if (ppromise !== undefined) {
                        ppromise.then(function () {
                            // playback started we do not need to do anything
                        }).catch(function (error) {
                            log.debug('location: do_play_audio');
                            log.debug(error);
                        });
                    }

                });


            }// end of if blobs
        },
        do_play_video: function (ip) {

        },
        do_save_audio: function (ip) {
            // We do want to allow multiple submissions off one page load BUT
            // this will require a new filename. The filename is the basis of the
            // s3filename, s3uploadurl and filename for moodle. The problem with
            // allowing multiple uploads is that once the placeholder is overwritten
            // the subsequent submissions ad_hoc move task can no longer find the file to
            // replace. So we need a whole new filename or to cancel the previous ad hoc move.
            // This should probably be
            // an ajax request from the uploader, or even a set of 10 filenames/s3uploadurls
            // pulled down at PHP time ..
            // this is one of those cases where a simple thing is hard ...J 20160919
            if (ip.blobs && ip.blobs.length > 0) {
                utils.doConcatenateBlobs(ip.blobs, function (concatenatedBlob) {
                    ip.uploader.uploadBlob(concatenatedBlob, ip.blobs[0].type);
                });
                ip.uploaded = true;
                ip.controlbar.startbutton.attr('disabled', true);
            }// end of if self.blobs
        },
        do_save_video: function (ip) {

        },
        do_stop_audio: function (ip) {
            //if its paused we need to resume it before stopping.
            ip.mediaRecorder.resume();
            ip.mediaRecorder.stop();

            //stop Speech to text if doing that
            if (ip.config.speechevents) {
                ip.speechrec.stop();
            }

            //publish recording stopped event
            var messageObject = {};
            messageObject.type = "recording";
            messageObject.action = 'stopped';
            ip.config.hermes.postMessage(messageObject);
        },
        do_stop_video: function (ip) {
            //just use do_stop_audio
        },
        do_stop_screen: function (ip) {
         //just use do_stop_audio
        },
        do_pause_audio: function (ip) {
            //if its paused we need to resume it before pausing again.
            //should never happen ...right?
            ip.mediaRecorder.resume();
            ip.mediaRecorder.pause();
        },
        do_pause_video: function (ip) {

        },
        do_resume_audio: function (ip) {
            ip.mediaRecorder.resume();
        },
        do_resume_video: function (ip) {

        },

        /* fetch the video constraints for passing to mediastream */
        fetch_video_constraints: function (ip) {
            var mediaConstraints = {
                audio: !utils.is_opera() && !utils.is_edge(),
                video: {
                    width: {ideal: 640},
                    height: {ideal: 480}
                }
            };

            //set aspect ratio and I think the "exact" below should be "ideal"
            //  mediaConstraints.video = {aspectRatio: 1920/1080};
            //alert('set');

            // check for a user video selected device
            if (ip.uservideodeviceid) {
                var videodeviceid = ip.uservideodeviceid.valueOf();
                var videoconstraints = {deviceId: videodeviceid ? {exact: videodeviceid} : undefined};
                videoconstraints.width={ideal: 640};
                videoconstraints.height={ideal: 480};
                mediaConstraints.video = videoconstraints;
            }
            // check for a user audio selected device
            if (ip.useraudiodeviceid) {
                var audiodeviceid = ip.useraudiodeviceid.valueOf();
                var audioconstraints = {deviceId: audiodeviceid ? {exact: audiodeviceid} : undefined};
                mediaConstraints.audio = audioconstraints;
            }
            return mediaConstraints;
        },

        /* fetch the screen constraints for passing to mediastream */
        fetch_screen_constraints: function (ip) {
            var mediaConstraints = {
                audio: !utils.is_opera() && !utils.is_edge(),
                video: true
            };

            //set aspect ratio and I think the "exact" below should be "ideal"
            //  mediaConstraints.video = {aspectRatio: 1920/1080};
            //alert('set');

            // check for a user video selected device
            if (ip.uservideodeviceid) {
                var videodeviceid = ip.uservideodeviceid.valueOf();
                var constraints = {deviceId: videodeviceid ? {exact: videodeviceid} : undefined};

                mediaConstraints.video = constraints;
            }
            // check for a user audio selected device
            if (ip.useraudiodeviceid) {
                var audiodeviceid = ip.useraudiodeviceid.valueOf();
                var constraints = {deviceId: audiodeviceid ? {exact: audiodeviceid} : undefined};
                mediaConstraints.audio = constraints;
            }
            return mediaConstraints;
        },

        /* fetch the audio constraints for passing to mediastream */
        fetch_audio_constraints: function (ip) {

            // really we need to deal with preferences properly
            // this will get the available media constraints that need to be set like deviceid above

              //  var sc = navigator.mediaDevices.getSupportedConstraints();
              //  log.debug(sc);


            // init return object
            var mediaConstraints = {
                audio: true
                //audio: {volume: 0.0}
            };


            // tried hard on safari to get the chosen devce, this but just gave up.
            if (utils.is_safari() && !ip.useraudiodeviceid) {

                // fix mime type to wav
                ip.audiomimetype = 'audio/wav';


            }// end of if Safari

            // check for a user selected device
            if (ip.useraudiodeviceid) {
                var constraints = {"deviceId": ip.useraudiodeviceid};
                mediaConstraints.audio = constraints;
            }
            // log.debug(mediaConstraints);

            return mediaConstraints;
        },

        /* register audio events, including those of skin*/
        register_events_audio: function (controlbarid) {

            var self = this;
            var ip = this.fetch_instanceprops(controlbarid);
            var skin = this.skins[controlbarid];

            var onMediaSuccess = function (stream) {


                //stop any playing tracks of the current stream
                //DONT call this. caused problems
                //self.tidy_old_stream(controlbarid);

                //save a reference to the stream
                self.laststream[controlbarid] = stream;

                //set encoder
                var encoder = 'auto';
                if (ip.config.hasOwnProperty('encoder')) {
                    encoder = ip.config.encoder;
                }

                // get blob after specific time interval
                ip.mediaRecorder = poodll_msr;
                ip.mediaRecorder.init(stream, ip.audioctx, ip.audioanalyser, ip.config.mediatype, encoder);
                //the following are really for stereoaudio  msr
                ip.mediaRecorder.mimeType = ip.audiomimetype;
                ip.mediaRecorder.audioChannels = 1;

                // we pass in the context object because it needs to be activated right on the event.
                // so its created in the init and passed around
                ip.mediaRecorder.start(ip.timeinterval, ip.audioctx);
                ip.mediaRecorder.ondataavailable = function (blob) {
                    //log.debug(blob);
                    ip.blobs.push(blob);
                };



                //We want to publish a "started" recording event.
                //----------------------------------
                //but non-iframe (Classic) and in-iframe (Cloud) events work a bit different
                //this is for non-iframe (Classic)
                if (!ip.config.iframeembed) {
                    if (ip.config.callbackjs && ip.config.callbackjs != '') {

                        //publish recording started event to non-iframe
                        //For callbackjs and for postmessage we need an array of stuff
                        var callbackObject = new Array();
                        callbackObject[0] = ip.config.widgetid;
                        callbackObject[1] = "started";
                        callbackObject[2] = ip.config.filename;
                        callbackObject[3] = ip.config.updatecontrol;
                        callbackObject[4] = ip.config.s3filename;

                        if (typeof(ip.config.callbackjs) === 'function') {
                            ip.config.callbackjs(callbackObject);
                        } else {
                            uploader.executeFunctionByName(ip.config.callbackjs, window, callbackObject);
                        }
                    }
                }

                //this is for in-iframe (Cloud)
                var messageObject = {};
                messageObject.type = "recording";
                messageObject.action = 'started';
                ip.config.hermes.postMessage(messageObject);
                //----------------------------------

                //start  speech to text
                if (ip.config.speechevents) {
                    ip.speechrec.start(stream);
                }

                //defer to the skins code
                skin.onMediaSuccess_audio(controlbarid);

            };

            skin.register_controlbar_events_audio(onMediaSuccess, controlbarid);

        }, // end of register audio events

        /* fetch the video events */
        register_events_video: function (controlbarid) {

            var self = this;
            var ip = this.fetch_instanceprops(controlbarid);
            var skin = this.skins[controlbarid];

            var onMediaSuccess = function (stream) {

                //restream preview video_player
                self.restream_preview_video_player(controlbarid, stream);

                //set encoder
                var encoder = 'auto';
                if (ip.config.hasOwnProperty('encoder')) {
                    encoder = ip.config.encoder;
                }

                //choose and turn on the recorder
                ip.mediaRecorder = poodll_msr;
                ip.mediaRecorder.init(stream, ip.audioctx, ip.audioanalyser, ip.config.mediatype, encoder);


                // set recorder type
                if (ip.videorecordertype === 'mediarec') {
                    ip.mediaRecorder.recorderType = MediaRecorderWrapper;
                }

                // set capture size
                ip.mediaRecorder.videoWidth = ip.videocapturewidth;
                ip.mediaRecorder.videoHeight = ip.videocaptureheight;

                // start recording
                ip.mediaRecorder.start(ip.timeinterval);
                ip.mediaRecorder.ondataavailable = function (blob) {
                    ip.blobs.push(blob);
                    // log.debug('We got a blobby');
                    // log.debug(URL.createObjectURL(blob));
                };

                //We want to publish a "started" recording event.
                //----------------------------------
                //but non-iframe (Classic) and in-iframe (Cloud) events work a bit different
                //this is for non-iframe (Classic)
                if (!ip.config.iframeembed) {
                    if (ip.config.callbackjs && ip.config.callbackjs != '') {

                        //publish recording started event to non-iframe
                        //For callbackjs and for postmessage we need an array of stuff
                        var callbackObject = new Array();
                        callbackObject[0] = ip.config.widgetid;
                        callbackObject[1] = "started";
                        callbackObject[2] = ip.config.filename;
                        callbackObject[3] = ip.config.updatecontrol;
                        callbackObject[4] = ip.config.s3filename;

                        if (typeof(ip.config.callbackjs) === 'function') {
                            ip.config.callbackjs(callbackObject);
                        } else {
                            uploader.executeFunctionByName(ip.config.callbackjs, window, callbackObject);
                        }
                    }
                }

                //this is for in-iframe (Cloud)
                var messageObject = {};
                messageObject.type = "recording";
                messageObject.action = 'started';
                ip.config.hermes.postMessage(messageObject);

                //start speech to text
                if (ip.config.speechevents) {
                    ip.speechrec.start(stream);
                }

                //defer to the skins code
                skin.onMediaSuccess_video(controlbarid);

            };

            skin.register_controlbar_events_video(onMediaSuccess, controlbarid);
        }, // end of register video events

        //clear up the old stream
        tidy_old_stream: function (controlbarid) {

            //stop any playing tracks of the current stream
            if (this.laststream[controlbarid]) {
                this.laststream[controlbarid].getTracks().forEach(
                    function (track) {
                        track.stop();
                    });
            }
        },

        restream_preview_video_player: function (controlbarid, stream) {

            //store new stream
            this.laststream[controlbarid] = stream;
            //play in preview
            this.init_video_preview(controlbarid);

            //do we need to do this? ..
            //lets just do it for android and see how it works out it causes a flicker and few second delays
            if (utils.is_android()) {
                navigator.mediaDevices.enumerateDevices();
            }

        },

        //play the stream in the preview
        init_video_preview: function (controlbarid) {
            var ip = this.fetch_instanceprops(controlbarid);
            var preview = ip.controlbar.preview[0];

            preview.srcObject = this.laststream[controlbarid];
            preview.controls = false;
            preview.volume = 0;
            var ppromise = preview.play();
            if (ppromise !== undefined) {
                ppromise.then(function () {
                    // playback started we do not need to do anything
                }).catch(function (error) {
                    log.debug('location: init_video_preview');
                    log.debug(error);
                });
            }
        },


        update_status: function (controlbarid) {
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
        },


        fetch_controlbar_audio: function (element, controlbarid, preview, resource) {
            var ip = this.fetch_instanceprops(controlbarid);
            var skin = this.fetch_skin(controlbarid);
            var controlbar = skin.insert_controlbar_audio(element, controlbarid, preview, resource);
            return controlbar;
        },

        fetch_controlbar_video: function (element, controlbarid, preview, resource) {
            var ip = this.fetch_instanceprops(controlbarid);
            var skin = this.fetch_skin(controlbarid);
            var controlbar = skin.insert_controlbar_video(element, controlbarid, preview, resource);
            return controlbar;
        },

        fetch_strings: function () {
            var ss = [];
            var keys = ['record', 'play', 'pause', 'continue', 'stop', 'save', 'restart', 'testmic', 'upload', 'recordagain', 'readytorecord', 'downloadfile'];
            $.each(keys, function (index, key) {
                ss['recui_' + key] = M.util.get_string('recui_' + key, 'filter_poodll');
                //log.debug(key + ':' + ss['recui_' + key]);
                if (ss['recui_' + key].indexOf(',filter_poodll]]') > 1 || ss['recui_' + key] == '') {
                    ss['recui_' + key] = key;
                }
            });
            return ss;
        }

    };// end of returned object
});// total end
