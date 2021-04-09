/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('aws_instant: initialising');

    return {

        final_transcript: '',
        start_timestamp: 0,
        transcriber: null,


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        will_work_ok: function(opts){
            var ret = false;

            //The instance languages
            switch(opts['language']){
                case 'en-AU':
                case 'en-GB':
                case 'en-US':
                case 'es-US':
                case 'fr-FR':
                case 'fr-CA':
                    ret =true;
                    break;
                default:
                    ret = false;
            }

            //The supported regions
            if(ret) {
                switch (opts['region']) {
                    case "useast1":
                    case "useast2":
                    case "uswest2":
                    case "sydney":
                    case "dublin":
                    case "ottawa":
                        ret =true;
                        break;
                    default:
                        ret = false;
                }
            }
            return ret;
        },


        init: function (opts) {
            var that = this;
            //require(['http://localhost/moodle/local/cpapi/cloudpoodll/amd/build/awstranscriber.min.js'],function(transcriber){
            require(['https://cdn.jsdelivr.net/gh/justinhunt/cloudpoodll@latest/amd/build/awstranscriber.min.js'],function(transcriber){
                that.transcriber = transcriber;
                opts['expiretime'] = 300;
                opts['token'] = opts['wstoken'];
                opts['wsserver'] = M.cfg.wwwroot;
                that.transcriber.init(opts);
                that.register_events();
            });

            //init streaming transcriber
            /*
            var opts = {};
            opts['language'] = lang;
            opts['region'] = app.props.region;
            opts['token'] = app.props.token;
            opts['parent'] = app.props.parent;
            opts['owner'] = app.props.owner;
            opts['appid'] = app.props.appid;
            opts['expiretime'] = app.props.expiretime;
            */


        },

        set_grammar: function (grammar) {
            /*
            var SpeechGrammarList = SpeechGrammarList || webkitSpeechGrammarList;
            if (SpeechGrammarList) {
                var speechRecognitionList = new SpeechGrammarList();
                speechRecognitionList.addFromString(grammar, 1);
                this.recognition.grammars = speechRecognitionList;
            }
            */
        },

        start: function (stream) {
            if (this.transcriber.active) {
                return;
            }
            this.final_transcript = '';
            this.transcriber.start(stream, this.transcriber);
            this.ignore_onend = false;
            this.start_timestamp = Date.now();//event.timeStamp;

        },
        stop: function () {
            if (!this.transcriber.active) {
                return;
            }
            this.transcriber.stop(this.transcriber);
        },

        register_events: function () {
            var that=this;
            this.transcriber.onFinalResult = function(speechtext, speechresults) {
                        that.onfinalspeechcapture(speechtext,speechresults);
            };

        },//end of register events

        onfinalspeechcapture: function (speechtext,speechresults) {
            log.debug(speechtext);
        },
        oninterimspeechcapture: function (speechtext) {
            // log.debug(speechtext);
        }

    };//end of returned object
});//total end
