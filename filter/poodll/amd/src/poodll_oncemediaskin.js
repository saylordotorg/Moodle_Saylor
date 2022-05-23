/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/upskin_radial',
        'filter_poodll/anim_hwave_mic', 'filter_poodll/dlg_devicesettings'],
    function ($, log, utils, upskin_radial, hwave, settings) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Once Recorder Skin: initialising');

        return {

            instanceprops: null,
            pmr: null,
            devsettings: null,
            therecanim: null,

            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            init: function (ip, pmr) {
                this.instanceprops = ip;
                this.pmr = pmr;
                this.devsettings = settings.clone();
                this.devsettings.init(pmr, ip);
            },


            fetch_instanceprops: function () {
                return this.instanceprops;
            },


            onUploadSuccess: function (controlbarid) {
                $('#' + controlbarid + ' > .poodll_save-recording').hide();
                $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
            },

            onUploadFailure: function (controlbarid) {
                return;
            },

            fetch_status_bar: function (skinname) {
                var status = '<div class="poodll_status_' + skinname + '" width="320" height="50">00:00:00</div>';
                return status;
            },

            fetch_preview_audio: function (skinname) {
                var preview = '<audio class="poodll_preview_' + skinname + '" style="display: none;" playsinline="playsinline" muted></audio>';
                return preview;
            },
            fetch_preview_video: function (skinname) {
                var preview = '<video class="poodll_preview_' + skinname + '" style="display: none;" playsinline="playsinline" muted></video>';
                return preview;
            },
            fetch_resource_audio: function (skinname) {
                var resourceplayer = '<audio class="poodll_resourceplayer_' + skinname + '" style="display: none;" playsinline="playsinline"></audio>';
                return resourceplayer;
            },
            fetch_resource_video: function (skinname) {
                var resourceplayer = '<video class="poodll_resourceplayer_' + skinname + '" style="display: none;" playsinline="playsinline"></video>';
                return resourceplayer;
            },

            onMediaError: function (e) {
                console.error('media error', e);
            },

            onMediaSuccess_video: function (controlbarid) {
                var ip = this.fetch_instanceprops();

                //clear messages
                ip.uploader.Output('');

                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                this.update_status(controlbarid);

                this.set_visual_mode('recordingmode', controlbarid);
            },

            onMediaSuccess_audio: function (controlbarid) {
                var ip = this.fetch_instanceprops();
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
                this.set_visual_mode('recordingmode', controlbarid);
            },

            handle_timer_update: function (controlbarid) {
                var ip = this.fetch_instanceprops();
                this.therecanim.displaytime = ip.timer.fetch_display_time();
                this.update_status(controlbarid);
                if (ip.timer.seconds == 0 && ip.timer.initseconds > 0) {
                    ip.controlbar.stopbutton.click();
                }
            },

            update_status: function (controlbarid) {
                var ip = this.fetch_instanceprops();
                ip.controlbar.status.html(ip.timer.fetch_display_time());
            },

            fetch_uploader_skin: function (controlbarid, element) {
                var ip = this.fetch_instanceprops();
                var upskin = upskin_radial.clone();
                upskin.init(ip.config, element, ip.controlbar.uploadcanvas, ip.controlbar.uploadmessages);
                upskin.setDrawParam('lineWidth', 2);
                upskin.setDrawParam('font', '14px Arial');
                return upskin;
            },

            //set visuals for different states (ie recording or playing)
            set_visual_mode: function (mode, controlbarid) {
                var self = this;
                var ip = this.fetch_instanceprops();

                switch (mode) {

                    case 'readymode':

                        ip.controlbar.status.show();
                        self.enable_button(ip.controlbar.startbutton);
                        self.disable_button(ip.controlbar.finishedbutton);
                        self.disable_button(ip.controlbar.stopbutton);
                        ip.controlbar.uploadcanvas.hide();
                        self.therecanim.setDrawParam('wavColor', '#CCCCCC');
                        self.therecanim.clear();
                        if (ip.config.mediatype == 'video') {
                            ip.controlbar.playcanvas.hide();
                            ip.controlbar.preview.show();
                        } else {
                            ip.controlbar.playcanvas.show();
                            ip.controlbar.preview.hide();
                        }
                        break;

                    case 'recordingmode':
                        //when testing(timer off) we do not want the stop button. Just really recording and allowearlyexit
                        self.enable_button(ip.controlbar.stopbutton);
                        self.disable_button(ip.controlbar.startbutton);
                        if (ip.config.mediatype == 'video') {
                            ip.controlbar.playcanvas.hide();
                            ip.controlbar.preview.show();
                        } else {
                            ip.controlbar.playcanvas.show();
                            ip.controlbar.preview.hide();
                        }

                        ip.controlbar.uploadcanvas.hide();
                        self.therecanim.setDrawParam('wavColor', '#FF0000');
                        self.therecanim.clear();
                        ip.controlbar.status.show();
                        break;

                    case 'aftermode':
                        self.disable_button(ip.controlbar.startbutton);
                        self.disable_button(ip.controlbar.stopbutton);
                        self.therecanim.setDrawParam('wavColor', '#CCCCCC');
                        self.therecanim.clear();
                        ip.controlbar.playcanvas.hide();
                        if (ip.config.mediatype == 'video') {
                            ip.controlbar.preview.hide();
                        }
                        ip.controlbar.uploadcanvas.show();
                        ip.controlbar.status.show();

                        break;

                }

            },

            //insert the control bar and return it to be reused
            insert_controlbar_video: function (element, controlbarid, preview, resource) {
                var controlbar = this.prepare_controlbar(element, controlbarid, preview, resource, 'video');
                return controlbar;
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

                var recorder_class = mediatype == 'video' ? 'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';
                var size_class = 'poodll_mediarecorder_size_auto';

                var ss = this.pmr.fetch_strings();
                var ss_startlabel = ss['recui_record'];
                var ss_testlabel = ss['recui_testmic'];
                var ss_stoplabel = ss['recui_stop'];

                var status = this.fetch_status_bar('once');
                var controls = '<div class="poodll_mediarecorderholder_once '
                    + recorder_class + '" id="holder_' + controlbarid + '">';

                controls += '<div class="poodll_mediarecorderbox_once" id="' + controlbarid + '">';
                controls += this.devsettings.fetch_dialogue_box();
                controls += ip.downloaddialog.fetch_dialogue_box();
                controls += ip.errordialog.fetch_dialogue_box();
                controls += '<div class="style-holder ' + skin_style + '">';
                controls += preview,
                    controls += '<div class="settingsicon" id="settingsicon_' + controlbarid + '"><button type="button" class="btn-poodllsettings" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
                controls += '<canvas id="' + controlbarid + '_playcanvas" width="250" height="50" class="poodll_mediarecorder_playcanvas_once"></canvas>';
                controls += status,
                    controls += '<button type="button" class="poodll_mediarecorder_button_once poodll_start-recording_once">' + ss_startlabel + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_once poodll_stop-recording_once">' + ss_stoplabel + '</button>';
                if (mediatype == 'audio') {
                    controls += '<canvas id="' + controlbarid + '_uploadcanvas" width="250" height="50" class="poodll_mediarecorder_uploadcanvas_once"></canvas>';
                } else {
                    //we keep the canvas same height as video, but narrowe so the radial stays under the timer (we center it with CSS)
                    controls += '<canvas id="' + controlbarid + '_uploadcanvas" width="130" height="210" class="poodll_mediarecorder_uploadcanvas_once"></canvas>';
                }

                controls += '<div class="poodll_uploadmessages_once"></div>';

                controls += '</div></div></div>';
                $(element).prepend(controls);
                //<i class="fa fa-stop" aria-hidden="true"></i>
                var controlbar = {
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    downloaddialog: $('#' + controlbarid + ' .poodll_dialogue_box_download'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
                    settingsicon: $('#' + controlbarid + ' .settingsicon'),
                    status: $('#' + controlbarid + ' .poodll_status_once'),
                    preview: $('#' + controlbarid + ' .poodll_preview_once'),
                    startbutton: $('#' + controlbarid + ' .poodll_start-recording_once'),
                    stopbutton: $('#' + controlbarid + ' .poodll_stop-recording_once'),
                    playcanvas: $('#' + controlbarid + '_playcanvas'),
                    uploadcanvas: $('#' + controlbarid + '_uploadcanvas'),
                    uploadmessages: $('#' + controlbarid + ' .poodll_uploadmessages_once')
                };
                //settings and error and download dialogs
                //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
                ip.downloaddialog.set_dialogue_box(controlbar.downloaddialog);
                ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
                this.devsettings.set_dialogue_box(controlbar.settingsdialog);

                return controlbar;
            }, //end of fetch_control_bar_once


            register_controlbar_events_video: function (onMediaSuccess, controlbarid) {
                return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
            },

            register_controlbar_events_audio: function (onMediaSuccess, controlbarid) {

                var self = this;
                var pmr = this.pmr;
                var ip = this.fetch_instanceprops();

                //init recording anim
                ip.config.recanim = 'hwave_mic';
                var recanim = hwave.clone();
                self.therecanim = recanim;
                recanim.init(ip.audioanalyser, ip.controlbar.playcanvas.get(0));

                //set visual mode
                this.set_visual_mode('readymode', controlbarid);


                ip.controlbar.settingsicon.click(function () {
                    if (!self.uploaded) {
                        self.devsettings.open();
                    } else {
                        ip.downloaddialog.open();
                    }
                });

                //Start button click
                ip.controlbar.startbutton.click(function () {
                    //we start real recording here.
                    // so we enable messages to API client and timer
                    ip.config.hermes.enable();
                    ip.timer.enable();
                    pmr.do_start_audio(ip, onMediaSuccess);
                });


                //Stop button click
                ip.controlbar.stopbutton.click(function () {

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
                            self.disable_button(ip.controlbar.startbutton);
                        } else {
                            setTimeout(doDeferredUpload, 200);
                        }
                    }
                    setTimeout(doDeferredUpload, 200);

                    //set visuals
                    self.set_visual_mode('aftermode', controlbarid);

                });


                window.onbeforeunload = function () {
                    //no need to do anything here
                    // self.enable_button(ip.controlbar.startbutton);

                };
            }, //end of register_control_bar_events_once


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
