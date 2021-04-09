/* jshint ignore:start */
define(['jquery', 'jqueryui', 'core/log', 'filter_poodll/utils_amd'],
    function ($, jqui, log, utils) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Warning Media skin: initialising');

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
                return false;
            },
            handle_timer_update: function (controlbarid) {
                //do nothing
            },
            onUploadSuccess: function (controlbarid) {
                return;
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

                switch (mediatype) {
                    case 'audio':
                    case 'video':
                    default:
                        var message = M.util.get_string('recui_unsupportedbrowser', 'filter_poodll');

                }

                //styles and classes
                var skin_style = ip.config.media_skin_style;
                var recorder_class = mediatype == 'video' ? 'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';
                var size_class = 'poodll_mediarecorder_size_auto';

                //the controls (all the html)
                var controls = '<div class="poodll_mediarecorderholder_warning '
                    + recorder_class + '" id="holder_' + controlbarid + '">';

                controls += '<div class="poodll_mediarecorderbox_warning" id="' + controlbarid + '">';
                controls += '<div class="style-holder ' + skin_style + '">';
                controls += '<div class="poodll_mediarecorderbox_warning_message ' + skin_style + '">' + message + '</div>';
                controls += '</div></div></div>';

                //put the html on the page
                $(element).prepend(controls);


                //get a handle on the controls and return the collection
                var controlbar = {};

                return controlbar;
            },


            // handle audio/video/image file uploads for Mobile
            register_events: function (controlbarid) {
                //no events
            }
        }//end of returned object


    });//total end