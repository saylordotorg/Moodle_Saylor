/* jshint ignore:start */
define(['jquery', 'core/log', 'filter_poodll/speech_browser', 'filter_poodll/speech_awstranscriber'], function ($, log, browserrecognition, awstranscriber) {

    "use strict"; // jshint ;_;

    log.debug('speech_poodll: initialising');

    return {

        recognizer: null,


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //check that we can streaming transcribe
        will_work_ok: function (opts) {
            //a specific streaming transcriber might be requested
            //if not , we can just use what is available
            if('streamingtranscriber' in opts) {
                switch (opts['streamingtranscriber']) {
                    case 'aws':
                        return awstranscriber.will_work_ok(opts);
                    case 'browser':
                        return browserrecognition.will_work_ok(opts);
                }
            }
            //if no valid streamingtranscriber suggested just defaults
           return ( browserrecognition.will_work_ok(opts) ||
                    awstranscriber.will_work_ok(opts));

        },

        init: function (opts) {
            //multiple recognizers presenting a single interface
            //if a transcriber is requested we use that, otherwise we default to browser then AWS
            if('streamingtranscriber' in opts){
                switch (opts['streamingtranscriber']){
                    case 'aws':
                        this.recognizer = awstranscriber.clone();
                        this.recognizer.init(opts);
                        break;
                    case 'browser':
                        this.recognizer = browserrecognition.clone();
                        this.recognizer.init(opts);
                        break;
                }
            }

            //if no hinted transcriber, just choose
            if(!this.recognizer) {
                if (browserrecognition.will_work_ok(opts)) {
                    this.recognizer = browserrecognition.clone();
                    this.recognizer.init(opts);
                } else if (awstranscriber.will_work_ok(opts)) {
                    this.recognizer = awstranscriber.clone();
                    this.recognizer.init(opts)
                } else {
                    //should never arrive here. supposed to check first
                    log.debug('no usable speech recognizer found');
                    return false;
                }
            }
        },

        set_grammar: function (grammar) {
            this.recognizer.set_grammar(grammar);
        },

        start: function (stream) {
            if (!this.recognizer) {
                return;
            }
            this.recognizer.onfinalspeechcapture = this.onfinalspeechcapture;
            this.recognizer.oninterimspeechcapture = this.oninterimspeechcapture;
            if (this.recognizer) {
                this.recognizer.start(stream);
            }
        },
        stop: function () {
            if (!this.recognizer) {
                return;
            }
            if (this.recognizer) {
                this.recognizer.stop();
            }
        },

        onfinalspeechcapture: function (speechtext,speechresults) {
            if (!this.recognizer) {
                return;
            }
            log.debug('final:' + speechtext);
        },
        oninterimspeechcapture: function (speechtext) {
            if (!this.recognizer) {
                return;
            }
            log.debug('interim:' + speechtext);
        }
    };//end of returned object
});//total end
