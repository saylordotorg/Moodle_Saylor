/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/dlg_devicesettings', 'filter_poodll/anim_progress_bar', 'filter_poodll/upskin_bar'],
    function ($, log, utils, settings, anim_progress_bar, upskin_bar) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Base Skin: initialising');

        return {

            instanceprops: null,
            pmr: null,
            uploaded: false,
            devsettings: null,

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
                // $('#' + controlbarid  + '_messages').hide();
                $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
            },

            onUploadFailure: function (controlbarid) {
                return;
            },

            fetch_status_bar: function (skin) {
                var status = '<div class="poodll_status_' + skin + '" width="100%" height="50">00:00:00</div>';
                return status;
            },

            fetch_preview_audio: function (skin) {
                var preview = '<audio class="poodll_preview_' + skin + '"  playsinline muted style="display: none;" controls></audio>';
                return preview;
            },
            fetch_preview_video: function (skin) {
                var preview = '<video class="poodll_preview_' + skin + '" playsinline muted ></video>';
                return preview;
            },
            fetch_resource_audio: function (skin) {
                var resourceplayer = '<audio class="poodll_resourceplayer_' + skin + '" style="display: none;" ></audio>';
                return resourceplayer;
            },
            fetch_resource_video: function (skin) {
                var resourceplayer = '<video class="poodll_resourceplayer_' + skin + '" style="display: none;" playsinline="playsinline"></video>';
                return resourceplayer;
            },
            fetch_uploader_skin: function (controlbarid, element) {
                var ip = this.fetch_instanceprops(controlbarid);
                var upskin = upskin_bar.clone();
                upskin.init(ip.config, element, ip.controlbar.progresscanvas, ip.controlbar.timer);
                return upskin;
            },

            onMediaError: function (e) {
                console.error('media error', e);
            },

            onMediaSuccess_video: function (controlbarid) {
                var ip = this.fetch_instanceprops(controlbarid);
                //clear messages
                ip.uploader.Output('');

                //set visuals
                this.set_visual_mode('recordmode', controlbarid);
                ip.controlbar.preview.attr('src', null);

                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                this.update_status(controlbarid);
            },

            onMediaSuccess_audio: function (controlbarid) {
                var ip = this.fetch_instanceprops(controlbarid);

                //clear messages
                ip.uploader.Output('');

                //set visuals
                this.set_visual_mode('recordmode', controlbarid);

                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                this.update_status(controlbarid);

            },

            handle_timer_update: function (controlbarid) {
                var ip = this.fetch_instanceprops(controlbarid);
                ip.controlbar.status.html(ip.timer.fetch_display_time());
                if (ip.timer.seconds == 0 && ip.timer.initseconds > 0) {
                    ip.controlbar.stopbutton.click();
                }
            },

            update_status: function (controlbarid) {
                var ip = this.fetch_instanceprops(controlbarid);
                ip.controlbar.status.html(ip.timer.fetch_display_time());
            },

            //set visuals for different states (ie recording or playing)
            set_visual_mode: function (mode, controlbarid) {
                var self = this;
                var ip = this.fetch_instanceprops(controlbarid);

                switch (mode) {

                    case 'recordmode':
                        ip.controlbar.progress.hide();
                        self.disable_button(ip.controlbar.startbutton);
                        self.disable_button(ip.controlbar.playbutton);
                        ip.controlbar.resumebutton.hide();
                        self.enable_button(ip.controlbar.stopbutton);
                        self.disable_button(ip.controlbar.savebutton);
                        ip.controlbar.pausebutton.show();
                        self.enable_button(ip.controlbar.pausebutton);

                        ip.controlbar.preview.addClass('poodll_recording');
                        ip.controlbar.status.addClass('poodll_recording');
                        if (ip.config.mediatype == 'audio') {
                            ip.controlbar.preview.hide();
                        }
                        ip.controlbar.status.show();
                        break;

                    case 'playingmode':
                        self.disable_button(ip.controlbar.playbutton);
                        self.enable_button(ip.controlbar.stopbutton);
                        self.disable_button(ip.controlbar.startbutton);
                        self.disable_button(ip.controlbar.savebutton);
                        ip.controlbar.progresscanvas.show();
                        break;

                    case 'previewmode':
                        self.enable_button(ip.controlbar.playbutton);
                        self.disable_button(ip.controlbar.pausebutton);
                        self.disable_button(ip.controlbar.stopbutton);
                        if (!self.uploaded) {
                            self.enable_button(ip.controlbar.startbutton);
                            self.enable_button(ip.controlbar.savebutton);
                        }
                        ip.controlbar.resumebutton.hide();
                        ip.controlbar.pausebutton.show();
                        ip.controlbar.preview.removeClass('poodll_recording');
                        ip.controlbar.status.removeClass('poodll_recording');
                        ip.controlbar.timer.html('00:00:00');
                        ip.controlbar.progress.show();
                        ip.controlbar.progresscanvas.hide();
                        ip.controlbar.status.hide();
                        break;

                    case 'pausedmode':
                        self.enable_button(ip.controlbar.resumebutton);
                        self.enable_button(ip.controlbar.savebutton);
                        ip.controlbar.preview.removeClass('poodll_recording');
                        ip.controlbar.status.removeClass('poodll_recording');

                        break;


                    case 'uploadingmode':

                        self.disable_button(ip.controlbar.startbutton);
                        ip.controlbar.progresscanvas.show();

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
                var ip = this.fetch_instanceprops(controlbarid);
                var skin_style = ip.config.media_skin_style;

                var recorder_class = mediatype == 'video' ? 'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';

                var size_class = 'poodll_mediarecorder_size_auto';
                switch (ip.config.size) {
                    case 'small':
                        size_class = 'poodll_mediarecorder_size_small';
                        break;
                    case 'big':
                        size_class = 'poodll_mediarecorder_size_big';
                        break;
                    case 'auto':
                        size_class = 'poodll_mediarecorder_size_auto';
                }

                var ss = this.pmr.fetch_strings();
                var hideshowupload = ip.showupload ? 'inline-block' : 'none';
                var controls = '<div class="poodll_mediarecorderholder_standard ' + recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">';
                controls += '<div class="poodll_mediarecorderbox_standard" id="' + controlbarid + '">';
                controls += this.devsettings.fetch_dialogue_box();
                controls += ip.downloaddialog.fetch_dialogue_box();
                controls += ip.errordialog.fetch_dialogue_box();
                controls += '<div class="style-holder ' + skin_style + '">';
                var status = this.fetch_status_bar('standard');
                controls += status;
                controls += '<div class="hp_slide" id="slide_' + controlbarid + '">';
                controls += '<div class="hp_timer"></div>';
                controls += '<canvas class="hp_range"></canvas>';
                controls += '</div>';
                controls += preview,
                    controls += '<div class="settingsicon" id="settingsicon_' + controlbarid + '"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_start-recording_standard">' + ss['recui_record'] + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_stop-recording_standard pmr_disabled" disabled>' + ss['recui_stop'] + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_pause-recording_standard pmr_disabled" disabled>' + ss['recui_pause'] + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_resume-recording_standard pmr_disabled" style="display: none;" disabled>' + ss['recui_continue'] + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_play-recording_standard pmr_disabled" disabled>' + ss['recui_play'] + '</button>';
                controls += '<button type="button" class="poodll_save-recording_standard pmr_disabled" style="display: ' + hideshowupload + ';" disabled>' + ss['recui_save'] + '</button>';
                controls += '</div>';
                controls += '</div>';
                controls += '</div>';

                $(element).prepend(controls);

                var controlbar = {
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    downloaddialog: $('#' + controlbarid + ' .poodll_dialogue_box_download'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
                    settingsicon: $('#' + controlbarid + ' .settingsicon'),
                    progresscanvas: $('#' + controlbarid + ' .hp_range'),
                    progress: $('#' + controlbarid + ' .hp_slide'),
                    timer: $('#' + controlbarid + ' .hp_timer'),
                    status: $('#' + controlbarid + '  .poodll_status_standard'),
                    preview: $('#' + controlbarid + '  .poodll_preview_standard'),
                    startbutton: $('#' + controlbarid + '  .poodll_start-recording_standard'),
                    stopbutton: $('#' + controlbarid + '  .poodll_stop-recording_standard'),
                    pausebutton: $('#' + controlbarid + '  .poodll_pause-recording_standard'),
                    resumebutton: $('#' + controlbarid + '  .poodll_resume-recording_standard'),
                    playbutton: $('#' + controlbarid + '  .poodll_play-recording_standard'),
                    savebutton: $('#' + controlbarid + '  .poodll_save-recording_standard')
                };
                //settings and error dialogs
                //They use the same dialog and just fill it with different stuff
                //settings is on 'this' because it is shown from skin events, but errors are from pmr stuff
                ip.downloaddialog.set_dialogue_box(controlbar.downloaddialog);
                ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
                this.devsettings.set_dialogue_box(controlbar.settingsdialog);

                return controlbar;
            }, //end of fetch_control_bar_standard


            register_controlbar_events_video: function (onMediaSuccess, controlbarid) {
                return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
            },

            register_controlbar_events_audio: function (onMediaSuccess, controlbarid) {
                var self = this;
                var pmr = this.pmr;
                var ip = this.fetch_instanceprops(controlbarid);

                //init linear progress
                var hprogress = anim_progress_bar.clone();
                hprogress.init(ip.controlbar.progresscanvas);


                ip.controlbar.settingsicon.click(function () {
                    if (!self.uploaded) {
                        self.devsettings.open();
                    } else {
                        ip.downloaddialog.open();
                    }
                });

                ip.controlbar.startbutton.click(function () {
                    pmr.do_start_audio(ip, onMediaSuccess);
                });

                ip.controlbar.stopbutton.click(function () {

                    var preview = ip.controlbar.preview;
                    if (preview && preview.get(0)) {
                        preview.get(0).pause();
                    }

                    pmr.do_stop_audio(ip);

                    //timer and status bar
                    ip.timer.stop();
                    ip.timer.reset();
                    self.update_status(controlbarid);
                    //stop progress
                    hprogress.stop();

                    //do visuals
                    self.set_visual_mode('previewmode', controlbarid);


                });

                ip.controlbar.pausebutton.click(function () {
                    this.disabled = true;
                    $(this).hide();
                    ip.controlbar.resumebutton.show();
                    pmr.do_pause_audio(ip);

                    //timer and status bar
                    ip.timer.pause();
                    self.update_status(controlbarid);

                    //do visuals
                    self.set_visual_mode('pausedmode', controlbarid);
                });

                ip.controlbar.resumebutton.click(function () {

                    pmr.do_resume_audio(ip);

                    //timer and status bar
                    ip.timer.resume();
                    self.update_status(controlbarid);

                    //do visuals
                    self.set_visual_mode('recordmode', controlbarid);
                });

                ip.controlbar.playbutton.click(function () {

                    var preview = ip.controlbar.preview.get(0);
                    pmr.do_play_audio(ip, preview);

                    ip.controlbar.preview.on('timeupdate', function () {
                        if (!this.paused) {
                            var currentTime = this.currentTime;
                            ip.controlbar.timer.html(ip.timer.fetch_display_time(currentTime));
                        }
                    });

                    //do visuals
                    self.set_visual_mode('playingmode', controlbarid);

                    //start animation
                    hprogress.clear();
                    hprogress.fetchCurrent = function () {
                        var ct = ip.controlbar.preview.prop('currentTime');
                        var duration = ip.controlbar.preview.prop('duration');
                        if (!isFinite(duration)) {
                            duration = ip.timer.finalseconds;
                        }
                        return ct / duration;
                    };
                    hprogress.start();

                });

                ip.controlbar.savebutton.click(function () {
                    self.disable_button(this);
                    if (ip.blobs && ip.blobs.length > 0) {
                        pmr.do_save_audio(ip);
                        self.uploaded = true;

                        //do visuals
                        self.set_visual_mode('uploadingmode', controlbarid);

                    } else {
                        ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror', 'filter_poodll'));
                    }//end of if self.blobs		
                    //probably not necessary  ... but getting odd ajax errors occasionally
                    return false;
                });//end of save recording

                window.onbeforeunload = function () {
                    self.enable_button(ip.controlbar.startbutton);
                    var preview = ip.controlbar.preview;
                    if (preview && preview.get(0)) {
                        preview.get(0).pause();
                    }
                };
            }, //end of register_control_bar_events_standard

            enable_button: function (button) {
                $(button).attr('disabled', false);
                $(button).removeClass('pmr_disabled');
            },
            disable_button: function (button) {
                $(button).attr('disabled', true);
                $(button).addClass('pmr_disabled');
            }

        };//end of returned object
    });//total end
