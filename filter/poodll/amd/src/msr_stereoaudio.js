/* jshint ignore:start */
define(['jquery',
        'core/log', 'filter_poodll/msr_helper_stereoaudio'],
    function ($, log, helper) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Recorder: initialising');

        return {
            timeout: 0,
            mediaRecorder: null,
            mediaStream: null,
            msr: null,
            mediaType: 'audio',

            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            // init the poodll recorder
            // basically we check the users preferred recorders and if the rec supports the browser
            init: function (msr, mediaStream, audioctx, mediaType) {
                this.msr = msr;
                this.mediaStream = mediaStream;
                this.audioctx = audioctx;
                this.mediaType = mediaType;//always audio
            },

            start: function (timeSlice, audioctx) {
                //should start be called more than once, bad things could happen, so stop and then start
                if(this.timeout!==0){
                    this.stop();
                }

                timeSlice = timeSlice || 1000;
                var that = this;

                this.mediaRecorder = helper.clone();
                this.mediaRecorder.init(this.msr, this.mediaStream, this.audioctx);

                this.mediaRecorder.record();

                this.timeout = setInterval(function () {
                    that.mediaRecorder.requestData();
                }, timeSlice);
            },

            stop: function () {
                if (this.mediaRecorder) {
                    this.mediaRecorder.stop();
                    clearInterval(this.timeout);
                    this.timeout =0;
                }
            },
            pause: function () {
                if (!this.mediaRecorder) {
                    return;
                }

                this.mediaRecorder.pause();
            },

            resume: function () {
                if (!this.mediaRecorder) {
                    return;
                }
                this.mediaRecorder.resume();
            },

            ondataavailable: function (blob) {
                log.debug('ondataavailable:' + blob);
            },

            onstop: function (error) {
                log.debug(error);
            }
        };// end of returned object
    });// total end
