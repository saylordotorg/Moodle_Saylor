/* jshint ignore:start */
define(['jquery',
        'core/log', 'filter_poodll/utils_amd'],
    function ($, log, utils) {

        "use strict"; // jshint ;_;

        log.debug('PoodLL Plain Recorder: initialising');

        return {
            timeout: 0,
            mediaRecorder: null,
            mediaStream: null,
            audioctx: null, //unused
            msr: null,
            mediaType: null,
            //Firefox fails to calc properly at 128000, and blobs concatenate at about 1/4 the correct length
            //its a hack to get us over the broken firefox but 12800 works.
            audioBitsPerSecond: 12800,
            videoBitsPerSecond: 2500000,


            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            // init the recorder
            init: function (msr, mediaStream, audioctx, mediaType) {
                this.msr = msr;
                this.mediaStream = mediaStream;
                this.audioctx = audioctx; //unused
                this.mediaType = mediaType;
            },

            /**
             * This method records MediaStream.
             * @method
             * @memberof MediaStreamRecorder
             * @example
             * recorder.record();
             */
            start: function (timeSlice, __disableLogs) {
                var that = this;


                if (this.mediaType === 'audio') {
                    //this section can probably be removed. I can not see a need for it.
                    if (this.mediaStream.getVideoTracks().length && this.mediaStream.getAudioTracks().length) {
                        log.debug('Somehow we got a video stream, paring it down to just audio. ');
                        var stream;
                        if (!!navigator.mozGetUserMedia) {
                            stream = new MediaStream();
                            stream.addTrack(this.mediaStream.getAudioTracks()[0]);
                        } else {
                            // webkitMediaStream
                            stream = new MediaStream(this.mediaStream.getAudioTracks());
                        }
                        this.mediaStream = stream;
                    }
                }

                //lets work out our mime type
                this.mimeType=false;
                //if audio
                if(this.mediaType==='audio') {
                    //if its a mediarecorder and does not support 'isTypeSupported' ..it can only be Safari ..
                    if (!MediaRecorder.isTypeSupported) {
                        this.mimeType = "audio/mp4";
                    }else {
                        var audiotypes = ['ogg', 'webm', 'quicktime', 'mp4', 'm4a', 'wav'];
                        for (var i = 0; i < audiotypes.length; i++) {
                            var themimetype = 'audio/' + audiotypes[i];
                            if (MediaRecorder.isTypeSupported(themimetype)) {
                                this.mimeType = themimetype;
                                break;
                            }
                        }
                        //we make an intelligent choice if required to do so
                        if (this.mimeType === false) {
                            this.mimeType = utils.is_chrome() ? 'audio/webm' : 'audio/ogg';
                        }
                    }
                    var rec_options = {
                        //videoBitsPerSecond : this.videoBitsPerSecond,
                        audioBitsPerSecond: this.audioBitsPerSecond, //Firefox needs this
                        mimeType: this.mimeType
                    };

                //else video
                }else{
                    //if its a mediarecorder and does not support 'isTypeSupported' ..it can only be Safari ..
                    if (!MediaRecorder.isTypeSupported) {
                        this.mimeType = "video/mp4";
                    }else {
                        var videotypes = ['webm', 'ogv', 'quicktime', 'mp4', 'mpeg'];
                        for (var i = 0; i < videotypes.length; i++) {
                            var themimetype = 'video/' + videotypes[i];
                            if (MediaRecorder.isTypeSupported(themimetype)) {
                                this.mimeType = themimetype;
                                break;
                            }
                        }
                        //we make an "intelligent" choice if required to do so
                        if (this.mimeType === false) {
                            this.mimeType = 'video/webm';
                        }
                    }

                    var rec_options = {
                        //videoBitsPerSecond : this.videoBitsPerSecond,
                        //audioBitsPerSecond: this.audioBitsPerSecond,
                        mimeType: this.mimeType
                    };
                }


                try {
                    this.mediaRecorder = new MediaRecorder(this.mediaStream, rec_options);
                } catch (e) {
                    // if a NON_supported rec_option got us here
                    // or if Firefox on Android
                    this.mediaRecorder = new MediaRecorder(this.mediaStream);
                    log.debug('MediaRecorder API seems unable to record mimeType:' + this.mimeType);
                }
                log.debug("msr_plain using mime type:" + this.mimeType);

                // i.e. stop recording when <video> is paused by the user; and auto restart recording
                // when video is resumed. E.g. yourStream.getVideoTracks()[0].muted = true; // it will auto-stop recording.
                this.mediaRecorder.ignoreMutedMedia = this.ignoreMutedMedia || false;


                // Dispatching OnDataAvailable Handler
                this.mediaRecorder.ondataavailable = function (e) {
                    //  log.debug('data available:' + e.data.size );
                    if (!e.data || !e.data.size) {
                        return;
                    }

                    //var blob = new Blob([e.data], {type: that.mimeType});
                    var blob = e.data; //in firefox this leaves hints about the codec
                    that.msr.ondataavailable(blob);
                 //   log.debug('e.data.size:' + e.data);
                 //   log.debug('abr:' + that.mediaRecorder.audioBitsPerSecond);
                 //   log.debug('vbr:' + that.mediaRecorder.videoBitsPerSecond);

                };

                this.mediaRecorder.onerror = function (error) {
                    if (error.name === 'InvalidState') {
                        log.debug('The MediaRecorder is not in a state in which the proposed operation is allowed to be executed.');
                    } else if (error.name === 'OutOfMemory') {
                        log.debug('The UA has exhaused the available memory. User agents SHOULD provide as much additional information as possible in the message attribute.');
                    } else if (error.name === 'IllegalStreamModification') {
                        log.debug('A modification to the stream has occurred that makes it impossible to continue recording. An example would be the addition of a Track while recording is occurring. User agents SHOULD provide as much additional information as possible in the message attribute.');
                    } else if (error.name === 'OtherRecordingError') {
                        log.debug('Used for an fatal error other than those listed above. User agents SHOULD provide as much additional information as possible in the message attribute.');
                    } else if (error.name === 'GenericError') {
                        log.debug('The UA cannot provide the codec or recording option that has been requested.', error);
                    } else {
                        log.debug('MediaRecorder Error', error);
                    }

                    // When the stream is "ended" set recording to 'inactive'
                    // and stop gathering data. Callers should not rely on
                    // exactness of the timeSlice value, especially
                    // if the timeSlice value is small. Callers should
                    // consider timeSlice as a minimum value

                    if (!!that.mediaRecorder && that.mediaRecorder.state !== 'inactive' && that.mediaRecorder.state !== 'stopped') {
                        that.mediaRecorder.stop();
                    }
                };

                //We need a source node to connect the analyser to. The analyser is for visualisations
                var audioInput = this.audioctx.createMediaStreamSource(this.mediaStream);
                audioInput.connect(this.msr.audioanalyser.core);

                // void start(optional long mTimeSlice)
                // The interval of passing encoded data from EncodedBufferCache to onDataAvailable
                // handler. "mTimeSlice < 0" means Session object does not push encoded data to
                // onDataAvailable, instead, it passive wait the client side pull encoded data
                // by calling requestData API.
                try {
                    that.mediaRecorder.start(timeSlice);
                } catch (e) {
                    that.mediaRecorder = null;
                }

//end of start
            },

            /**
             * This method stops recording MediaStream.
             * @param {function} callback - Callback function, that is used to pass recorded blob back to the callee.
             * @method
             * @memberof MediaStreamRecorder
             * @example
             * recorder.stop(function(blob) {
     *     video.src = URL.createObjectURL(blob);
     * });
             */
            stop: function (callback) {
                if (!this.mediaRecorder) {
                    return;
                }

                if (this.mediaRecorder.state === 'recording') {
                    this.mediaRecorder.stop();
                }
            },

            /**
             * This method pauses the recording process.
             * @method
             * @memberof MediaStreamRecorder
             * @example
             * recorder.pause();
             */
            pause: function () {
                if (!this.mediaRecorder) {
                    return;
                }

                if (this.mediaRecorder.state === 'recording') {
                    this.mediaRecorder.pause();
                }
            },

            /**
             * This method resumes the recording process.
             * @method
             * @memberof MediaStreamRecorder
             * @example
             * recorder.resume();
             */
            resume: function () {
                if (!this.mediaRecorder) {
                    return;
                }
                if (this.mediaRecorder.state === 'paused') {
                    this.mediaRecorder.resume();
                }
            },

            /**
             * This method resets currently recorded data.
             * @method
             * @memberof MediaStreamRecorder
             * @example
             * recorder.clearRecordedData();
             */
            clearRecordedData: function () {
                if (!this.mediaRecorder) {
                    return;
                }

                this.pause();

                this.stop();
            },

            // Reference to "MediaRecorder" object
            //  var mediaRecorder;

            isMediaStreamActive: function () {
                if ('active' in this.mediaStream) {
                    if (!this.mediaStream.active) {
                        return false;
                    }
                } else if ('ended' in this.mediaStream) { // old hack
                    if (this.mediaStream.ended) {
                        return false;
                    }
                }
                return true;
            },

            // this method checks if media stream is stopped
            // or any track is ended.
            looper: function () {
                if (!this.mediaRecorder) {
                    return;
                }

                if (this.isMediaStreamActive() === false) {
                    this.stop();
                    return;
                }

                setTimeout(this.looper, 1000); // check every second
            }
        };// end of returned object
    });// total end