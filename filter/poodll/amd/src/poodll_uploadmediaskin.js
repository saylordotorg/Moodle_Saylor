/* jshint ignore:start */
define(['jquery', 'core/log', 'filter_poodll/utils_amd', 'filter_poodll/upskin_radial', 'filter_poodll/util_dragdrop'],
    function ($,  log, utils, upskin_radial, dd) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Upload Media skin: initialising');

        return {


            instanceprops: null,
            pmr: null,

            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            init: function (ip, pmr) {
                this.instanceprops = ip;
                this.pmr = pmr;
            },


            fetch_status_bar: function (skinname) {
                var status = '';
                return status;
            },

            fetch_preview_audio: function (skinname) {
                var preview = '';
                return preview;
            },
            fetch_preview_video: function (skinname) {
                var preview = '';
                return preview;
            },
            fetch_resource_audio: function (skinname) {
                var resourceplayer = '';
                return resourceplayer;
            },
            fetch_resource_video: function (skinname) {
                var resourceplayer = '';
                return resourceplayer;
            },

            fetch_uploader_skin: function (controlbarid, element) {
                var ip = this.fetch_instance_props();
                var upskin = upskin_radial.clone();
                upskin.init(ip.config, element, ip.controlbar.uploadcanvas, ip.controlbar.uploadmessages);
                upskin.setDrawParam('lineWidth', 2);
                upskin.setDrawParam('font', '14px Arial');
                return upskin;
            },
            handle_timer_update: function (controlbarid) {
                //do nothing
            },
            onUploadSuccess: function (controlbarid) {
                $('#' + controlbarid + ' > .poodll_save-recording').hide();
                $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
            },

            onUploadFailure: function (controlbarid) {
                return;
            },
            onMediaSuccess_video: function (controlbarid) {
                //this should never be called
            },

            onMediaSuccess_audio: function (controlbarid) {
                //this should never be called
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
            register_controlbar_events_video: function (onMediaSuccess, controlbarid) {
                return this.register_events(controlbarid);
            },

            register_controlbar_events_audio: function (onMediaSuccess, controlbarid) {
                return this.register_events(controlbarid);
            },

            fetch_instance_props: function () {
                return this.instanceprops;
            },

            prepare_controlbar: function (element, controlbarid, preview, resource, mediatype) {
                var ip = this.fetch_instance_props();

                //what media are we accepting and how to display that
                var acceptmedia = '';
                switch (mediatype) {
                    case 'video':
                        acceptmedia = 'video/webm,video/ogg,video/mp4,video/x-m4v,video/*';
                        break;
                    case 'snapshot':
                    case 'image':
                        acceptmedia = 'image/*';
                    case 'audio':
                        var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                        if (iOS) {
                            acceptmedia = 'video/*';
                        } else {
                            acceptmedia = 'audio/*';
                        }
                    default:
                        acceptmedia = '';
                }

                //strings for UI
                if (utils.is_ios() || utils.is_android()) {
                    var ss_chooselabel = M.util.get_string('recui_recordorchoose', 'filter_poodll');
                } else {
                    var ss_chooselabel = M.util.get_string('recui_choosefile', 'filter_poodll');
                }

                //styles and classes
                var skin_style = ip.config.media_skin_style;
                var recorder_class = mediatype == 'video' ? 'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';
                var size_class = 'poodll_mediarecorder_size_auto';

                //the controls (all the html)
                var controls = '<div class="poodll_mediarecorderholder_upload '
                    + recorder_class + '" id="holder_' + controlbarid + '">';

                controls += '<div class="poodll_mediarecorderbox_upload" id="' + controlbarid + '">';
                controls += ip.errordialog.fetch_dialogue_box();
                controls += '<div class="style-holder ' + skin_style + '">';

                if (mediatype == 'audio') {
                    var canvaswidth = 250;
                    var canvasheight = 50;
                } else {
                    var canvaswidth = 130;
                    var canvasheight = 210;
                }
                //we keep the canvas same height as video, but narrowe so the radial stays under the timer (we center it with CSS)
                controls += '<div class="poodll_mediarecorder_uploadcanvas_cont_upload"><canvas id="' + controlbarid + '_uploadcanvas" width="' + canvaswidth + '" height="'
                    + canvasheight + '" class="poodll_mediarecorder_uploadcanvas_upload"></canvas></div>';
                //upload button/s proper
                controls += '<div class="p_btn_wrapper">';
                controls += '<input type="file" id="' + controlbarid + '_poodllfileselect" name="poodllfileselect[]" accept="' + acceptmedia + '"/>';
                controls += '<button type="button" id="' + controlbarid + '_p_btn" class="p_btn">' + ss_chooselabel + '</button>';
                controls += '</div>';

                controls += '<div class="poodll_uploadmessages_once"></div>';

                controls += '</div></div></div>';

                //put the html on the page
                $(element).prepend(controls);


                //get a handle on the controls and return the collection
                var controlbar = {
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
                    filebutton: $('#' + controlbarid + '_poodllfileselect'),
                    startbutton: $('#' + controlbarid + '_p_btn'),
                    uploadcanvas: $('#' + controlbarid + '_uploadcanvas'),
                    uploadmessages: $('#' + controlbarid + ' .poodll_uploadmessages_once'),
                };

                //settings and error dialogs
                //They use the same dialog and just fill it with diofferent stuff
                //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
                ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
                return controlbar;
            },


            // handle audio/video/image file uploads for Mobile
            register_events: function (controlbarid) {
                var self = this;
                var pmr = this.pmr;
                var ip = this.fetch_instance_props();

                // so we enable messages to API client and timer
                ip.config.hermes.enable();


                ip.controlbar.filebutton.on('change', function (e) {
                    // fetch Files and pass to upload
                    var files = e.target.files || e.dataTransfer.files;
                    self.FileSelectHandler(files, ip.uploader);

                    //remove drag and drop and button events
                    dd.turnoff_dragdrop();

                    //turn off the button
                    ip.controlbar.filebutton.off('change');
                    ip.controlbar.filebutton.attr('disabled', true);
                });

                dd.init(ip.controlbar.uploadcanvas, this.FileSelectHandler, ip);
            },

            // file selection
            FileSelectHandler: function (files, uploader) {

                // process all File objects
                for (var i = 0, file; file = files[i]; i++) {
                    log.debug('filetype:' + file.type);
                    uploader.uploadBlob(file, file.type);
                }

            }
        }//end of returned object


    });//total end