/* jshint ignore:start */
define(['jquery',  'core/log', 'filter_poodll/utils_amd', 'filter_poodll/upskin_radial', 'filter_poodll/upskin_bar',
        'filter_poodll/anim_hwave_mic', 'filter_poodll/dlg_devicesettings'],
    function ($,  log, utils, upskin_radial, upskin_bar, hwave_mic, settings) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Push Skin: initialising');

        return {

            instanceprops: null,
            pmr: null,
            devsettings: null,
            therecanim: null,
            buttonmode: null,
            strings: null,

            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            init: function (ip, pmr) {
                this.instanceprops = ip;
                this.pmr = pmr;
                this.devsettings = settings.clone();
                this.devsettings.init(pmr, ip);

                //init strings
                this.strings = this.pmr.fetch_strings();
                //   this.strings['recui_startactivity']=M.util.get_string('recui_startactivity','filter_poodll');
                //   this.strings['recui_testmic']=M.util.get_string('recui_testmic','filter_poodll');
                //   this.strings['recui_stop']=M.util.get_string('recui_stop','filter_poodll');

                this.strings['recui_pushtospeak'] = M.util.get_string('recui_pushtospeak', 'filter_poodll');
            },


            fetch_instanceprops: function () {
                return this.instanceprops;
            },


            onUploadSuccess: function (controlbarid) {
                this.set_visual_mode('allfinished', controlbarid);
                //$('#' + controlbarid + ' > .poodll_save-recording').hide();
                //$('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
            },

            onUploadFailure: function (controlbarid) {
                return;
            },

            fetch_status_bar: function (skin) {
                var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
                return status;
            },

            fetch_preview_audio: function (skin) {
                var preview = '<audio class="poodll_preview_' + skin + ' hide"></audio>';
                return preview;
            },
            fetch_preview_video: function (skin) {
                return this.fetch_preview_audio(skin);
            },
            fetch_resource_audio: function (skin) {
                var resourceplayer = '<audio class="poodll_resourceplayer_' + skin + ' hide" ></audio>';
                return resourceplayer;
            },
            fetch_resource_video: function (skin) {
                return this.fetch_resource_audio(skin);
            },

            onfinalspeechcapture: function (speechtext,speechresults) {
                this.just_stop();
            },

            onMediaError: function (e) {
                console.error('media error', e);
            },

            onMediaSuccess_video: function (controlbarid) {
                var ip = this.fetch_instanceprops();
                this.set_visual_mode('startbuttonrecording', controlbarid);
            },

            onMediaSuccess_audio: function (controlbarid) {
                var ip = this.fetch_instanceprops(controlbarid);
                ip.controlbar.preview.attr('src', null);

                //clear messages
                ip.uploader.Output('');

                //wave animation
                this.therecanim.start();

                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                this.update_status(controlbarid);

                //visuals
                this.set_visual_mode('startbuttonrecording', controlbarid);

            },

            handle_timer_update: function (controlbarid) {
                var ip = this.fetch_instanceprops();
                ip.controlbar.status.html(ip.timer.fetch_display_time());
                if (ip.timer.seconds == 0 && ip.timer.initseconds > 0) {
                    this.stop_and_upload(controlbarid);
                }
            },

            update_status: function (controlbarid) {
                var ip = this.fetch_instanceprops();
                ip.controlbar.status.html(ip.timer.fetch_display_time());
            },

            fetch_uploader_skin: function (controlbarid, element) {
                var ip = this.fetch_instanceprops();
                // var upskin = upskin_radial.clone();
                var upskin = upskin_bar.clone();
                upskin.init(ip.config, element, ip.controlbar.playcanvas, ip.controlbar.status);
                return upskin;
            },

            set_button_style: function (mode) {
                //remove all button styles
                var mode_css_classes = ['testbuttonready', 'testbuttonrecording', 'startbuttonready', 'startbuttoncountdown', 'startbuttonrecording', 'uploading', 'allfinished'];
                $.each(mode_css_classes, function (index, modeclass) {
                    $('.poodll_mediarecorderbox_push').removeClass('poodll_mediarecorder_push_' + modeclass);
                    $('.poodll_mediarecorderbox_push').removeClass('push_canclick');
                });
                $('.poodll_mediarecorderbox_push').addClass('poodll_mediarecorder_push_' + mode);
                if (mode == 'testbuttonready' || mode == 'startbuttonready') {
                    $('.poodll_mediarecorderbox_push').addClass('push_canclick');
                }
            },

            //set visuals for different states (ie recording or playing)
            set_visual_mode: function (mode) {
                var self = this;
                var ip = this.fetch_instanceprops();
                this.buttonmode = mode;

                //send a message to alert of status change
                var messageObject = {};
                messageObject.type = "recorderstatus";
                messageObject.status = mode;
                ip.config.hermes.postMessage(messageObject);

                var spinner = '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>';


                switch (mode) {


                    case 'startbuttonready':
                        ip.controlbar.thecaption.text(self.strings['recui_pushtospeak']);
                        ip.controlbar.thecaption.show();
                        ip.controlbar.playcanvas.hide();
                        ip.controlbar.status.hide();
                        ip.controlbar.settingsicon.hide();
                        self.set_button_style(mode);
                        break;

                    case 'startbuttonrecording':
                        ip.controlbar.thecaption.hide();
                        ip.controlbar.playcanvas.show();
                        self.set_button_style(mode);
                        ip.controlbar.status.hide();
                        break;

                    case 'uploading':
                        ip.controlbar.thecaption.html(spinner);

                        //the bar progress works too.
                        //to use that hide the caption and show the  canvas
                        //ip.controlbar.thecaption.hide();
                        //ip.controlbar.playcanvas.show();
                        ip.controlbar.thecaption.show();
                        ip.controlbar.playcanvas.hide();


                        self.set_button_style(mode);
                        ip.controlbar.status.hide();
                        break;

                    case 'allfinished':
                        ip.controlbar.thecaption.text('Finished');
                        ip.controlbar.thecaption.show();
                        ip.controlbar.playcanvas.hide();
                        ip.controlbar.status.hide();
                        self.set_button_style(mode);
                        break;

                }

            },

            //insert the control bar and return it to be reused
            insert_controlbar_video: function (element, controlbarid, preview, resource) {
                return this.prepare_controlbar_audio(element, controlbarid, preview, resource);
            },
            //insert the control bar and return it to be reused
            insert_controlbar_audio: function (element, controlbarid, preview, resource) {
                var controlbar = this.prepare_controlbar(element, controlbarid, preview, resource, 'audio');
                return controlbar;
            },

            //insert the control bar and return it to be reused
            prepare_controlbar: function (element, controlbarid, preview, resource, mediatype) {
                var ip = this.fetch_instanceprops();
                var skin_style = ip.config.media_skin_style;

                var recorder_class = 'poodll_mediarecorder_audio';
                var size_class = 'poodll_mediarecorder_size_auto';

                var status = this.fetch_status_bar('push');
                var controls = '<div class="poodll_mediarecorderholder_push '
                    + recorder_class + '" id="holder_' + controlbarid + '">';

                controls += '<div class="poodll_mediarecorderbox_push" id="' + controlbarid + '">';
                controls += this.devsettings.fetch_dialogue_box();
                controls += ip.downloaddialog.fetch_dialogue_box();
                controls += ip.errordialog.fetch_dialogue_box();
                controls += '<div class="style-holder ' + skin_style + '">';
                controls += preview,
                    controls += '<div class="settingsicon" id="settingsicon_' + controlbarid + '"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
                controls += '<canvas id="' + controlbarid + '_playcanvas" class="poodll_mediarecorder_playcanvas_push" width="180" height="50"></canvas>';
                controls += '<span id="' + controlbarid + '_caption" class="poodll_mediarecorder_caption_push"></span>';

                //removing bgus buttons from html is better. The bogus items will send events(do_play_stop). Thf jquery object refering to them is enough for bogus to work
                //controls += '<span id="' + controlbarid + '_bogusstartbutton" class="poodll_mediarecorder_bogusstartbutton_push"></span>';
                //controls += '<span id="' + controlbarid + '_bogusstopbutton" class="poodll_mediarecorder_bogusstopbutton_push"></span>';

                /*
                controls +=  '<button type="button" class="poodll_mediarecorder_button_push poodll_start-recording_push">' + this.ss['startlabel']  + '</button>';
                controls +=  '<button type="button" class="poodll_mediarecorder_button_push poodll_test-recording_push">' + this.ss['testlabel']  +  '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_push poodll_stop-recording_push">' +  this.ss['stoplabel']  +  '</button>';
                */

                controls += status,
                    controls += '</div></div></div>';
                $(element).prepend(controls);
                //<i class="fa fa-stop" aria-hidden="true"></i>
                var controlbar = {
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    downloaddialog: $('#' + controlbarid + ' .poodll_dialogue_box_download'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
                    settingsicon: $('#' + controlbarid + ' .settingsicon'),
                    status: $('#' + controlbarid + ' .poodll_status_push'),
                    preview: $('#' + controlbarid + ' .poodll_preview_push'),
                    bigbutton: $('#' + controlbarid + '.poodll_mediarecorderbox_push'),
                    playcanvas: $('#' + controlbarid + '_playcanvas'),
                    thecaption: $('#' + controlbarid + '_caption'),
                    themicicon: $('#' + controlbarid + '_micicon'),
                    //These start and stop buttons are bogus. poodll_mediarecorder goes looking for them.
                    //But we never use them here.
                    stopbutton: $('#' + controlbarid + ' .poodll_mediarecorder_bogusstopbutton_push'),
                    startbutton: $('#' + controlbarid + ' .poodll_mediarecorder_bogusstartbutton_push')
                };
                //settings and error and download dialogs
                //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
                ip.downloaddialog.set_dialogue_box(controlbar.downloaddialog);
                ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
                this.devsettings.set_dialogue_box(controlbar.settingsdialog);

                return controlbar;
            }, //end of fetch_control_bar_push


            register_controlbar_events_video: function (onMediaSuccess, controlbarid) {
                return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
            },

            register_controlbar_events_audio: function (onMediaSuccess, controlbarid) {

                var self = this;
                var pmr = this.pmr;
                var ip = this.fetch_instanceprops();

                //init recording anim
                ip.config.recanim = 'hwave_mic';
                var recanim = hwave_mic.clone();
                self.therecanim = recanim;
                recanim.init(ip.audioanalyser, ip.controlbar.playcanvas.get(0));

                //set visual mode
                this.set_visual_mode('startbuttonready');

                ip.controlbar.bigbutton.click(function (e) {
                    log.debug(e.target);
                    //we do not want the dialog and other things to trigger events here
                    //just the caption and the button itself
                    var clickable = false;
                    if (e.target === self ||
                        $(e.target).hasClass('style-holder') ||
                        $(e.target).hasClass('poodll_mediarecorderbox_push') ||
                        $(e.target).hasClass('poodll_mediarecorder_caption_push') ||
                        $(e.target).hasClass('poodll_mediarecorder_playcanvas_push')) {
                        clickable = true;
                    }
                    if (!clickable) {
                        return;
                    }

                    switch (self.buttonmode) {
                        case 'startbuttonready':

                            //send a message to alert of new status
                            //publish recording stopped event
                            var messageObject = {};
                            messageObject.type = "recorderstatus";
                            messageObject.status = 'startbuttonrecording';
                            ip.config.hermes.postMessage(messageObject);

                            //we will start recording here.
                            //but its just a throwaway so we disable messages to API client and timer
                            //ip.config.hermes.disable();
                            ip.timer.disable();
                            pmr.do_start_audio(ip, onMediaSuccess);
                            self.set_visual_mode('startbuttonrecording');
                            break;

                        case 'startbuttonrecording':
                            self.just_stop(controlbarid);
                            break;

                        case 'oldstartbuttonready':
                            //we start real recording here.
                            // so we enable messages to API client and timer
                            var countingdown = function () {
                                var currenttime = new Date().getTime();
                                var currentcount = currenttime - starttime;
                                if (currentcount > finalcount) {
                                    ip.timer.enable();
                                    pmr.do_start_audio(ip, onMediaSuccess);
                                } else {
                                    var newcount = false;
                                    if (previouscount < 0 && currentcount > 0) {
                                        newcount = finalcount / 1000;
                                    } else if (previouscount < 1000 && currentcount > 1000) {
                                        newcount = finalcount / 1000 - 1;
                                    } else if (previouscount < 2000 && currentcount > 2000) {
                                        newcount = finalcount / 1000 - 2;
                                    }
                                    if (newcount) {
                                        var messageObject = {};
                                        messageObject.type = "countdownstatus";
                                        messageObject.status = newcount;
                                        ip.config.hermes.postMessage(messageObject);
                                        //ip.controlbar.thecaption.text('--- ' + newcount +'  ---');
                                    }
                                    previouscount = currentcount;
                                    setTimeout(countingdown, 100);
                                }
                            };
                            ip.config.hermes.enable();
                            self.set_visual_mode('startbuttoncountdown');
                            var starttime = new Date().getTime();
                            var finalcount = 3000;
                            var previouscount = -1;
                            setTimeout(countingdown, 100);
                            break;

                        //there is no stop button ... just for consistency and testing
                        case 'stopbutton':
                            self.stop_and_upload();
                    }

                });

                ip.controlbar.settingsicon.click(function (e) {
                    log.debug("we no proapagato");
                    // Do not pass this event on
                    e.stopPropagation();
                    //handle click properly
                    if (!self.uploaded) {
                        self.devsettings.open();
                    } else {
                        ip.downloaddialog.open();
                    }
                });

                window.onbeforeunload = function () {
                    //no need to do anything here
                    // self.enable_button(ip.controlbar.startbutton);

                };
            }, //end of register_control_bar_events_push

            just_stop: function () {
                var pmr = this.pmr;
                var ip = this.fetch_instanceprops();
                var recanim = this.therecanim;
                if (ip.mediaRecorder) {
                    //stop recording
                    pmr.do_stop_audio(ip);
                }
                //wave animation
                recanim.clear();
                ip.config.hermes.enable();
                this.set_visual_mode('startbuttonready');

            },

            stop_and_upload: function (controlbarid) {
                var self = this;
                var pmr = this.pmr;
                var ip = this.fetch_instanceprops();
                var recanim = self.therecanim;

                //stop recording
                pmr.do_stop_audio(ip);

                //wave animation
                recanim.clear();


                //timer and status bar
                ip.timer.stop();
                self.update_status(controlbarid);

                //call upload right away
                //but we have to do it this lame deferred way because some mediastreamrecorders return a single
                //blob shortly after we stop. We init like that too, to make sure we do not truncate a users recording
                //if the mini blobs did not arrive
                var doDeferredUpload = function () {
                    if (ip.blobs && ip.blobs.length > 0) {
                        pmr.do_save_audio(ip);
                        ip.uploaded = true;
                        //  self.disable_button(ip.controlbar.startbutton);
                    } else {
                        setTimeout(doDeferredUpload, 200);
                    }
                }
                setTimeout(doDeferredUpload, 200);

                //set visuals
                self.set_visual_mode('uploading');
            },

            enable_button: function (button) {
                $(button).attr('disabled', false);
                $(button).removeClass('pmr_disabled');
            },
            disable_button: function (button) {
                $(button).attr('disabled', true);
                $(button).addClass('pmr_disabled');
            },

        };//end of returned object
    });//total end
