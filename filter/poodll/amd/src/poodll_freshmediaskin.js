/* jshint ignore:start */
define(['jquery', 'jqueryui', 'core/log', 'filter_poodll/utils_amd', 'filter_poodll/dlg_devicesettings', 'filter_poodll/anim_progress_bar_fresh', 'filter_poodll/anim_hwave_fresh', 'filter_poodll/anim_hwave_timer', 'filter_poodll/upskin_text'], function ($, jqui, log, utils, settings, anim_progress_bar, hwave, hwave_timer, upskin) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL BMR Skin: initialising');


    return {

        instanceprops: null,
        pmr: null,
        uploaded: false,
        devsettings: null,
        therecanim: null,
        canpause: true,
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
            var status = '<div class="poodll_status_' + skin + '">00:00:00</div>';
            return status;
        },

        fetch_preview_audio: function (skin) {
            var preview = '<audio class="poodll_preview_' + skin + '" style="display: none;" controls playsinline="playsinline" muted></audio>';
            return preview;
        },
        fetch_preview_video: function (skin) {
            var preview = '<video class="poodll_preview_' + skin + '" playsinline="playsinline" muted></video>';
            return preview;
        },
        fetch_resource_audio: function (skin) {
            var resourceplayer = '';
            return resourceplayer;
        },
        fetch_resource_video: function (skin) {
            var resourceplayer = '';
            return resourceplayer;
        },

        fetch_uploader_skin: function (controlbarid, element) {
            var ip = this.fetch_instanceprops();
            var theupskin = upskin.clone();
            theupskin.init(ip.config, element, ip.controlbar.bmr_progresscanvas, ip.controlbar.status);
            return theupskin;
        },

        onMediaError: function (e) {
            console.error('media error', e);
        },

        onMediaSuccess_video: function (controlbarid) {

            var ip = this.fetch_instanceprops();
            //clear messages
            ip.uploader.Output('');
            this.set_visual_mode('recordmode', controlbarid);
            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);
        },

        onMediaSuccess_audio: function (controlbarid) {

            var ip = this.fetch_instanceprops();

            //clear messages
            ip.uploader.Output('');
            ip.controlbar.preview.attr('src', null);
            this.set_visual_mode('recordmode', controlbarid);

            //wave animation
            this.therecanim.start();

            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);

        },

        handle_timer_update: function (controlbarid) {
            var ip = this.fetch_instanceprops();
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            if (ip.timer.seconds == 0 && ip.timer.initseconds > 0) {
                if (ip.controlbar.startbutton.hasClass('poodll_in_progress')) {
                  ip.controlbar.startbutton.click();
                }
            }
        },

        update_status: function (controlbarid) {
            var ip = this.fetch_instanceprops();
            ip.controlbar.status.html(ip.timer.fetch_display_time());

        },

        //set visuals for different states (ie recording or playing)
        set_visual_mode: function (mode, controlbarid) {
            var self = this;
            var ip = this.fetch_instanceprops();
            this.visualmode = mode;

            switch (mode) {

                case 'initmode':
                    ip.controlbar.statusText.text('Ready to record');
                    ip.controlbar.status.html('00:00:00');

                    ip.controlbar.stoprecbutton.attr('class', 'poodll_stop-recording_fresh poodll_fresh_stop_btn bmr_disabled');
                    ip.controlbar.startbutton.attr('class', 'poodll_start-recording_fresh poodll_fresh_main_btn');
                    ip.controlbar.playbutton.attr('class', 'poodll_play-recording_fresh poodll_fresh_play_btn bmr_disabled');

                    //ip.controlbar.root.attr('class')

                    //disable save button
                    self.disable_button(ip.controlbar.savebutton);
                    self.disable_button(ip.controlbar.recordAgain);
                    break;

                case 'recordmode':
                    ip.controlbar.statusText.text('Recording..');
                    self.enable_button(ip.controlbar.startbutton);
                    self.show_element(ip.controlbar.startbutton);

                    self.hide_element(ip.controlbar.bmr_progresscanvas);

                    self.enable_button(ip.controlbar.pausebutton);
                    self.show_element(ip.controlbar.pausebutton);

                    self.disable_button(ip.controlbar.playbutton);
                    self.hide_element(ip.controlbar.resumebutton);
                    self.enable_button(ip.controlbar.stoprecbutton);

                    //disable save button
                    self.disable_button(ip.controlbar.savebutton);
                    self.disable_button(ip.controlbar.recordAgain);

                    ip.controlbar.preview.addClass('poodll_recording');
                    ip.controlbar.status.addClass('poodll_recording');

                    ip.controlbar.preview.removeClass('poodll_playing');
                    ip.controlbar.status.removeClass('poodll_playing');

                    ip.controlbar.startbutton
                        .addClass('poodll_in_progress')
                        .removeClass('poodll_resume_button');

                    ip.controlbar.playbutton
                        .removeClass('poodll_play_special poodll_play_green');

                    if (ip.config.mediatype == 'audio') {
                        self.hide_element(ip.controlbar.preview);
                    }

                    ip.controlbar.root.removeClass('poodll_controllbar_playmode');

                    break;

                case 'previewmode':
                    ip.controlbar.statusText.text('Ready to Play');
                    self.disable_button(ip.controlbar.stoprecbutton);
                    self.disable_button(ip.controlbar.startbutton);
                    self.enable_button(ip.controlbar.playbutton);
                    self.disable_button(ip.controlbar.pausebutton);

                    //reset timer button
                    ip.controlbar.status.html(ip.timer.fetch_display_time(ip.timer.finalSeconds));


                    //If stop has been pressed there is no "resuming"
                    self.show_element(ip.controlbar.startbutton);

                    self.disable_button(ip.controlbar.resumebutton);
                    self.hide_element(ip.controlbar.resumebutton);
                    self.hide_element(ip.controlbar.bmr_progresscanvas);
                    if (self.uploaded) {
                        self.disable_button(ip.controlbar.savebutton);
                        self.disable_button(ip.controlbar.recordAgain);
                    } else {
                        self.enable_button(ip.controlbar.savebutton);
                        self.enable_button(ip.controlbar.recordAgain);
                    }

                    self.show_element(ip.controlbar.pausebutton);

                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');

                    ip.controlbar.preview.removeClass('poodll_playing');
                    ip.controlbar.status.removeClass('poodll_playing');


                    ip.controlbar.startbutton.removeClass('poodll_in_progress');
                    ip.controlbar.playbutton
                        .addClass('poodll_play_green')
                        .removeClass('poodll_play_special')
                        .removeClass('poodll_play_pause')
                        .removeClass('poodll_stop_green');

                    if (ip.config.mediatype == 'audio') {
                        self.show_element(ip.controlbar.preview);
                    }
                    // ip.controlbar.status.addClass('hide');
                    //ip.controlbar.bmr_progresscanvas.removeClass('hide');
                    self.enable_button(ip.controlbar.playbutton);
                    ip.controlbar.root.removeClass('poodll_controllbar_playmode poodll_controllbar_pausemode');
                    break;

                case 'playmode':

                    ip.controlbar.statusText.text('Playing');
                    ip.controlbar.status.html('00:00:00');

                    self.enable_button(ip.controlbar.stoprecbutton);
                    self.enable_button(ip.controlbar.pausebutton);
                    self.disable_button(ip.controlbar.startbutton);
                    self.disable_button(ip.controlbar.resumebutton);

                    self.disable_button(ip.controlbar.savebutton);
                    self.disable_button(ip.controlbar.recordAgain);

                    self.show_element(ip.controlbar.bmr_progresscanvas);

                    ip.controlbar.playbutton
                        .removeClass('poodll_play_green')
                        .removeClass('poodll_play_special')
                        .addClass('poodll_stop_green');

                    ip.controlbar.preview.addClass('poodll_playing');
                    ip.controlbar.status.addClass('poodll_playing');

                    //If play has been pressed there is no "resuming"
                    self.show_element(ip.controlbar.startbutton);
                    self.hide_element(ip.controlbar.resumebutton);

                    ip.controlbar.root
                        .addClass('poodll_controllbar_playmode')
                        .removeClass('poodll_controllbar_pausemode');

                    break;


                case 'pausedmode':
                    ip.controlbar.statusText.text('Paused');
                    //self.disable_button(ip.controlbar.pausebutton);
                    //self.hide_element(ip.controlbar.startbutton);
                    self.show_element(ip.controlbar.resumebutton);
                    self.enable_button(ip.controlbar.resumebutton);
                    self.enable_button(ip.controlbar.savebutton);
                    //self.enable_button(ip.controlbar.playbutton);

                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    ip.controlbar.startbutton
                        .removeClass('poodll_in_progress')
                        .addClass('poodll_resume_button');

                    ip.controlbar.playbutton
                    //.addClass('poodll_play_special')
                        .removeClass('poodll_play_green')
                        .removeClass('poodll_play_pause');


                    ip.controlbar.root.addClass('poodll_controllbar_pausemode');
                    break;

                case 'uploadmode':
                    ip.controlbar.statusText.text('Uploading');
                    self.disable_button(ip.controlbar.savebutton);
                    self.disable_button(ip.controlbar.recordAgain);
                    self.disable_button(ip.controlbar.startbutton);
                    self.show_element(ip.controlbar.bmr_progresscanvas);

                    ip.controlbar.root.removeClass('poodll_controllbar_playmode poodll_controllbar_pausemode');
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
            var record_icon = mediatype == 'video' ? 'fa-video-camera' : 'fa-microphone';
            var recorder_class = mediatype == 'video' ? 'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';

            var controls = '<div class="poodll_mediarecorderholder_fresh poodll_videorecorderholder_fresh '
                + recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">';

            controls += '<div class="poodll_mediarecorderbox_fresh" id="' + controlbarid + '">';
            controls += '<audio style="display: none !important;" class="poodll-alert-recording" id="' + controlbarid + '"><source src="#" type="audio/mpeg"></audio>';

            controls += '<div class="style-holder ' + skin_style + '">';
            controls += '<div class="poodll_statusholder_fresh" >';
            var status = this.fetch_status_bar('fresh');
            controls += status;
            controls += '</div>';

            controls += '<div class="poodll_fresh_musik_line">' +
                '<canvas class="fresh_range"></canvas>' +
                '<canvas id="' + controlbarid + '_playcanvas" class="poodll_fresh_playcanvas" width="300" height="50"></canvas>' +
                '<div class="poodll_fresh_wave"></div>' +
                '</div>' +
                '<div class="poodll_fresh_control">' +
                '<div class="poodll_fresh_txt_control">' + ss['recui_readytorecord'] + '</div>' +
                '<div class="poodll_fresh_settings_btn settingsicon" data-toggle="modal" data-target="#myModal id="settingsicon_' + controlbarid + '"></div>' +
                '<div class="poodll_start-recording_fresh poodll_fresh_main_btn"></div>' +
                '<div class="poodll_stop-playing-recording_fresh" style="display: none;"></div>' +
                '<div class="poodll_pause-recording_fresh poodll_fresh_pause_btn bmr_disabled"></div>' +
                '<div class="poodll_play-recording_fresh poodll_fresh_play_btn bmr_disabled"></div>' +
                '</div>' +
                '<div class="poodll_fresh_bottom_btns">' +
                '<a class="poodll_save-recording_fresh poodll_fresh_upload_btn bmr_disabled" href="#">' + ss['recui_upload'] + '</a>' +
                '<a class="poodll_fresh_record_btn bmr_disabled" href="#">' + ss['recui_recordagain'] + '</a>' +
                '</div>';

            controls += preview;
            controls += '</div>';
            controls += this.devsettings.fetch_dialogue_box();
            controls += ip.downloaddialog.fetch_dialogue_box();
            controls += ip.errordialog.fetch_dialogue_box();
            controls += '</div>';
            controls += '</div>';
            $(element).prepend(controls);
            var controlbar = {
                poodll_recording_alert: $('#' + controlbarid + ' .poodll-alert-recording'),
                bmr_progresscanvas: $('#' + controlbarid + ' .fresh_range'),
                settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                downloaddialog: $('#' + controlbarid + ' .poodll_dialogue_box_download'),
                errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
                settingsicon: $('#' + controlbarid + ' .settingsicon'),
                status: $('#' + controlbarid + ' .poodll_status_fresh'),
                statusText: $('#' + controlbarid + ' .poodll_fresh_txt_control'),
                preview: $('#' + controlbarid + ' .poodll_preview_fresh'),
                startbutton: $('#' + controlbarid + ' .poodll_start-recording_fresh'),
                stoprecbutton: $('#' + controlbarid + ' .poodll_stop-recording_fresh'),
                pausebutton: $('#' + controlbarid + ' .poodll_pause-recording_fresh'),
                resumebutton: $('#' + controlbarid + ' .poodll_resume-recording_fresh'),
                playbutton: $('#' + controlbarid + ' .poodll_play-recording_fresh'),
                stopbutton: $('#' + controlbarid + ' .poodll_stop-playing-recording_fresh'),
                savebutton: $('#' + controlbarid + ' .poodll_save-recording_fresh'),
                recordAgain: $('#' + controlbarid + ' .poodll_fresh_record_btn'),
                playcanvas: $('#' + controlbarid + '_playcanvas'),
                root: $('#' + controlbarid)
            };


            //settings and error dialogs
            //They use the same dialog and just fill it with diofferent stuff
            //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
            ip.downloaddialog.set_dialogue_box(controlbar.downloaddialog);
            ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
            this.devsettings.set_dialogue_box(controlbar.settingsdialog);
            return controlbar;

        }, //end of fetch_control_bar_bmr


        register_controlbar_events_video: function (onMediaSuccess, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
        },

        register_controlbar_events_audio: function (onMediaSuccess, controlbarid) {
            var self = this;
            var pmr = this.pmr;
            var ip = this.fetch_instanceprops();

            //Open the settings dialog
            ip.controlbar.settingsicon.click(function () {
                if (!self.uploaded) {
                    self.devsettings.open();
                } else {
                    ip.downloaddialog.open();
                }
            });

            //init progress bar
            var hprogress = anim_progress_bar.clone();
            hprogress.init(ip.controlbar.bmr_progresscanvas);

            hprogress.readyBars();

            var recanim = hwave.clone();
            recanim.init(ip.audioanalyser, ip.controlbar.playcanvas.get(0));

            /*
            START BUTTON
            */
            ip.controlbar.startbutton.click(function () {

                //Play alert sound
                //ip.controlbar.poodll_recording_alert.get(0).play();

                //start recording
                if ($(this).hasClass('poodll_in_progress')) {

                    //stop the audio
                    pmr.do_stop_audio(ip);

                    var preview = ip.controlbar.preview;
                    if (preview && preview.get(0)) {
                        preview.get(0).pause();
                    }

                    recanim.clear();

                    //timer and status bar
                    ip.timer.stop();
                    self.update_status(controlbarid);

                    //do visuals
                    self.set_visual_mode('previewmode', controlbarid);

                } else if ($(this).hasClass('poodll_resume_button')) {
                    pmr.do_resume_audio(ip);

                    //timer and status bar
                    ip.timer.resume();
                    self.update_status(controlbarid);
                    //do visuals
                    self.set_visual_mode('recordmode', controlbarid);
                } else {

                    self.therecanim = recanim;
                    pmr.do_start_audio(ip, onMediaSuccess);

                }


            });

            ip.controlbar.recordAgain.click(function () {
                pmr.do_stop_audio(ip);

                self.update_status(controlbarid);
                //do visuals
                self.set_visual_mode('initmode', controlbarid);

                //self.therecanim = recanim;
                //pmr.do_start_audio(ip, onMediaSuccess);
            });

            ip.controlbar.stoprecbutton.click(function () {
                //stop the audio
                pmr.do_stop_audio(ip);

                var preview = ip.controlbar.preview;
                if (preview && preview.get(0)) {
                    preview.get(0).pause();
                }

                recanim.clear();

                //timer and status bar
                ip.timer.stop();
                self.update_status(controlbarid);

                //do visuals
                self.set_visual_mode('previewmode', controlbarid);

            });

            ip.controlbar.pausebutton.click(function () {

                pmr.do_pause_audio(ip);
                //pmr.do_stop_audio(ip);
                var preview = ip.controlbar.preview;
                if (preview && preview.get(0)) {
                    preview.get(0).pause();
                    preview.get(0).controls = false;
                }
                console.log('PAUSE');
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

            ip.controlbar.preview.on('timeupdate', function () {
                if (self.visualmode != 'playmode') {
                    return;
                }
                var currentTime = this.currentTime;
                ip.controlbar.status.html(ip.timer.fetch_display_time(currentTime));
            });

            //this is really just a fudge because PMR expects the stopbutton to be the stop playback button
            //but in this recorder, the stop playback button is the "play" button
            //so we double up the code just so that when PMR calls it when playback ends, we reset playback state
            ip.controlbar.stopbutton.click(function () {
                //stop the audio
                pmr.do_stop_audio(ip);

                var preview = ip.controlbar.preview;
                if (preview && preview.get(0)) {
                    preview.get(0).pause();
                }

                recanim.clear();

                //timer and status bar
                ip.timer.stop();
                self.update_status(controlbarid);

                //do visuals
                self.set_visual_mode('previewmode', controlbarid);
            });

            /*
             PLAY BUTTON
             */

            ip.controlbar.playbutton.click(function () {

                if ($(this).hasClass('poodll_play_pause')) {

                    var preview = ip.controlbar.preview.get(0);

                    // if(preview && preview.get(0)){
                    //     preview.get(0).pause();
                    //     preview.get(0).controls = false;
                    // }

                    pmr.do_stopplay_audio(ip, preview);
                    pmr.do_stop_audio(ip);

                    //timer and status bar
                    ip.timer.pause();
                    self.update_status(controlbarid);
                    //do visuals
                    self.set_visual_mode('pausedmode', controlbarid);


                } else if ($(this).hasClass('poodll_stop_green')) {

                    //stop the audio
                    pmr.do_stop_audio(ip);

                    var preview = ip.controlbar.preview;
                    if (preview && preview.get(0)) {
                        preview.get(0).pause();
                    }

                    recanim.clear();

                    //timer and status bar
                    ip.timer.stop();
                    self.update_status(controlbarid);

                    //do visuals
                    self.set_visual_mode('previewmode', controlbarid);

                } else {

                    var preview = ip.controlbar.preview.get(0);
                    pmr.do_play_audio(ip, preview);

                    //do visuals
                    self.set_visual_mode('playmode', controlbarid);

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

                    var ct = ip.controlbar.preview.prop('currentTime');
                    var duration = ip.controlbar.preview.prop('duration');
                    hprogress.playBars(ct, duration);

                }
            });

            ip.controlbar.savebutton.click(function () {

                if (ip.blobs && ip.blobs.length > 0) {
                    self.set_visual_mode('uploadmode', controlbarid);
                    pmr.do_save_audio(ip);
                    self.uploaded = true;
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
                    preview.get(0).controls = false;
                }
            };

        }, //end of register_control_bar_events_bmr

        show_element: function (element) {
            $(element).show();
        },
        hide_element: function (element) {
            $(element).hide();
        },
        enable_button: function (button) {
            $(button).attr('disabled', false);
            $(button).removeClass('bmr_disabled');
        },
        disable_button: function (button) {
            $(button).attr('disabled', true);
            $(button).addClass('bmr_disabled');
        },

    };//end of returned object


});//total end