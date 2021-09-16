define(['jquery', 'core/log', 'mod_readaloud/ttaudiohelper', 'core/notification','mod_readaloud/ttbrowserrec'],
    function ($, log, audioHelper, notification, browserRec) {
        "use strict"; // jshint ;_;
        /*
        *  The TT recorder
         */

        log.debug('TT Recorder: initialising');

        return {
            waveHeight: 75,
            audio: {
                stream: null,
                blob: null,
                dataURI: null,
                start: null,
                end: null,
                isRecording: false,
                isRecognizing: false,
                transcript: null
            },
            submitting: false,
            owner: '',
            controls: {},
            uniqueid: null,
            audio_updated: null,
            maxTime: 15000,
            passagehash: null,
            region: null,
            asrurl: null,
            lang: null,
            browserrec: null,
            usebrowserrec: false,
            currentTime: 0,
            ds_only: false,

            //for making multiple instances
            clone: function () {
                return $.extend(true, {}, this);
            },

            init: function(opts){

                var that = this;

                this.uniqueid=opts['uniqueid'];
                this.callback=opts['callback'];
                this.ds_only = opts['ds_only'] ? opts['ds_only'] : false;
                this.prepare_html();
                this.controls.recordercontainer.show();
                this.register_events();

                //set up events
                var on_gotstream=  function(stream) {

                    var newaudio={stream: stream, isRecording: true};
                    that.update_audio(newaudio);
                    that.currentTime = 0;

                    that.interval = setInterval(function() {
                        if (that.currentTime < that.maxTime) {
                            that.currentTime += 10;
                        } else {
                            that.update_audio('isRecognizing',true);
                            // vm.isRecognizing = true;
                            that.audiohelper.stop();
                        }
                    }, 10);

                };

                var on_error = function(error) {
                    switch (error.name) {
                        case 'PermissionDeniedError':
                        case 'NotAllowedError':
                            notification.alert("Error",'Please allow access to your microphone!', "OK");
                            break;
                        case 'DevicesNotFoundError':
                        case 'NotFoundError':
                            notification.alert("Error",'No microphone detected!', "OK");
                            break;
                        default:
                            //other errors, like from Edge can fire repeatedly so a notification is not a good idea
                            //notification.alert("Error", error.name, "OK");
                            log.debug("Error", error.name);
                    }
                };

                var on_stopped = function(blob) {
                    clearInterval(that.interval);

                    //if ds recc
                    var newaudio = {
                        blob: blob,
                        dataURI: URL.createObjectURL(blob),
                        end: new Date(),
                        isRecording: false,
                        length: Math.round((that.audio.end - that.audio.start) / 1000),
                    };
                    that.update_audio(newaudio);

                    that.deepSpeech2(that.audio.blob, function(response){
                        log.debug(response);
                        that.update_audio('isRecognizing',false);
                        if(response.data.result==="success" && response.data.transcript){
                            that.gotRecognition(response.data.transcript.trim());
                        } else {
                            notification.alert("Information","We could not recognize your speech.", "OK");
                        }
                    });

                };




                //If browser rec (Chrome Speech Rec) (and ds is optiona)
                if(browserRec.will_work_ok() && ! this.ds_only){
                    //Init browserrec
                    log.debug("using browser rec");
                    this.browserrec = browserRec.clone();
                    this.browserrec.init(this.lang,this.waveHeight,this.uniqueid);
                    this.usebrowserrec=true;

                    //set up events
                    that.browserrec.onerror = on_error;
                    that.browserrec.onend = function(){
                        //do something here
                    };
                    that.browserrec.onstart = function(){
                        //do something here
                    };
                    that.browserrec.onfinalspeechcapture=function(speechtext){
                        that.gotRecognition(speechtext);
                        that.update_audio('isRecording',false);
                        that.update_audio('isRecognizing',false);
                    };

                    //If DS rec
                }else {
                    //set up wav for ds rec
                    log.debug("using ds rec");
                    this.audiohelper =  audioHelper.clone();
                    this.audiohelper.init(this.waveHeight,this.uniqueid,this);

                    that.audiohelper.onError = on_error;
                    that.audiohelper.onStop = on_stopped;
                    that.audiohelper.onStream = on_gotstream;

                }//end of setting up recorders
            },

            prepare_html: function(){
                this.controls.recordercontainer =$('#ttrec_container_' + this.uniqueid);
                this.controls.recorderbutton = $('#ttrec_' + this.uniqueid + '_recorderdiv');
                this.passagehash =this.controls.recorderbutton.data('passagehash');
                this.region=this.controls.recorderbutton.data('region');
                this.asrurl=this.controls.recorderbutton.data('asrurl');
                this.lang =this.controls.recorderbutton.data('lang');
                this.maxTime=this.controls.recorderbutton.data('maxtime');
                this.waveHeight=this.controls.recorderbutton.data('waveheight');
            },

            update_audio: function(newprops,val){
                if (typeof newprops === 'string') {
                    log.debug('update_audio:' + newprops + ':' + val);
                    if (this.audio[newprops] !== val) {
                        this.audio[newprops] = val;
                        this.audio_updated();
                    }
                }else{
                    for (var theprop in newprops) {
                        this.audio[theprop] = newprops[theprop];
                        log.debug('update_audio:' + theprop + ':' + newprops[theprop]);
                    }
                    this.audio_updated();
                }
            },

            register_events: function(){
                var that = this;
                this.controls.recordercontainer.click(function(){
                    that.toggleRecording();
                });

                this.audio_updated=function() {
                    //pointer
                    if (that.audio.isRecognizing) {
                        that.show_recorder_pointer('none');
                    } else {
                        that.show_recorder_pointer('auto');
                    }

                    if(that.audio.isRecognizing || that.audio.isRecording ) {
                        this.controls.recorderbutton.css('background', '#e52');
                    }else{
                        this.controls.recorderbutton.css('background', 'green');
                    }

                    //div content WHEN?
                    that.controls.recorderbutton.html(that.recordBtnContent());
                }

            },

            show_recorder_pointer: function(show){
                if(show) {
                    this.controls.recorderbutton.css('pointer-events', 'none');
                }else{
                    this.controls.recorderbutton.css('pointer-events', 'auto');
                }

            },


            gotRecognition:function(transcript){
                log.debug('transcript:' + transcript);
                var message={};
                message.type='speech';
                message.capturedspeech = transcript;
                //POINT
                this.callback(message);
            },

            cleanWord: function(word) {
                return word.replace(/['!"#$%&\\'()\*+,\-\.\/:;<=>?@\[\\\]\^_`{|}~']/g,"").toLowerCase();
            },

            recordBtnContent: function() {

                if(!this.audio.isRecognizing){

                    if (this.audio.isRecording) {
                        return '<i class="fa fa-stop">';
                    } else {
                        return '<i class="fa fa-microphone">';
                    }

                } else {
                    return '<i class="fa fa-spinner fa-spin">';
                }
            },
            toggleRecording: function() {
                var that =this;

                //If we are recognizing, then we want to discourage super click'ers
                if (this.audio.isRecognizing) {
                      return;
                }

                //If we are current recording
                if (this.audio.isRecording) {
                    //If using Browser Rec (chrome speech)
                    if(this.usebrowserrec){
                        clearInterval(that.interval);
                        that.update_audio('isRecording',false);
                        that.update_audio('isRecognizing',true);
                        this.browserrec.stop();

                        //If using DS rec
                    }else{
                        this.update_audio('isRecognizing',true);
                        this.audiohelper.stop();
                    }

                    //If we are NOT currently recording
                } else {

                    //If using Browser Rec (chrome speech)
                    if(this.usebrowserrec){
                        this.update_audio('isRecording',true);
                        this.browserrec.start();
                        that.currentTime = 0;
                        this.interval = setInterval(function() {
                            if (that.currentTime < that.maxTime) {
                                that.currentTime += 10;
                            } else {
                                that.update_audio('isRecording',false);
                                that.update_audio('isRecognizing',true);
                                clearInterval(that.interval);
                                that.browserrec.stop();
                            }
                        }, 10);

                        //If using DS Rec
                    }else {
                        var newaudio = {
                            stream: null,
                            blob: null,
                            dataURI: null,
                            start: new Date(),
                            end: null,
                            isRecording: false,
                            isRecognizing:false,
                            transcript: null
                        };
                        this.update_audio(newaudio);
                        this.audiohelper.start();
                    }
                }
            },


            deepSpeech2: function(blob, callback) {
                var bodyFormData = new FormData();
                var blobname = this.uniqueid + Math.floor(Math.random() * 100) +  '.wav';
                bodyFormData.append('audioFile', blob, blobname);
                bodyFormData.append('scorer', this.passagehash);
                bodyFormData.append('lang', this.lang);

                var oReq = new XMLHttpRequest();
                oReq.open("POST", this.asrurl, true);
                oReq.onUploadProgress= function(progressEvent) {};
                oReq.onload = function(oEvent) {
                    if (oReq.status === 200) {
                        callback(JSON.parse(oReq.response));
                    } else {
                        callback({data: {result: "error"}});
                        console.error(oReq.error);
                    }
                };
                try {
                    oReq.send(bodyFormData);
                }catch(err){
                    callback({data: {result: "error"}});
                    console.error(err);
                }
            },

        };//end of return value

    });