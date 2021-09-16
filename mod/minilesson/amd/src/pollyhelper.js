define(['jquery', 'core/log', 'mod_minilesson/definitions'], function ($, log, def) {
    "use strict"; // jshint ;_;
    /*
    This file helps you get Polly URLs at runtime
     */

    log.debug('Polly helper: initialising');

    return {
        token:  '',
        region: '',
        owner: '',

        init: function(token, region, owner){
            this.token =token;
            this.region=region;
            this.owner=owner;
        },

        clean_ssml_chars: function(speaktext){
            //deal with SSML reserved characters
            speaktext =  speaktext.replace(/&/g,'&amp;');
            speaktext = speaktext.replace(/'/g,'&apos;');
            speaktext= speaktext.replace(/"/g,'&quot;');
            speaktext = speaktext.replace(/</g,'&lt;');
            speaktext =  speaktext.replace(/>/g,'&gt;');
            return speaktext;
        },

        can_speak_neural: function(voice,region){
            switch(region){
                case "useast1":
                case "tokyo":
                case "sydney":
                case "dublin":
                case "ottawa":
                case "frankfurt":
                case "london":
                case "singapore":
                case "capetown":
                    //ok
                    break;
                default:
                    return false;
            }

            //check if the voice is supported
            if(def.neural_voices.indexOf(voice) !== -1){
                return true;
            }else{
                return false;
            }

        },

        fetch_polly_url: function(speaktext,voiceoption, voice) {
            var that = this;
            return new Promise(function(resolve,reject){
                //The REST API we are calling
                var functionname = 'local_cpapi_fetch_polly_url';

                //fetch the Posturl. We need this.
                //set up our ajax request
                var xhr = new XMLHttpRequest();

                //set up our handler for the response
                xhr.onreadystatechange = function (e) {
                    if (this.readyState === 4) {
                        if (xhr.status == 200) {

                            //get a yes or forgetit or tryagain
                            var payload = xhr.responseText;
                            var payloadobject = JSON.parse(payload);
                            if (payloadobject) {
                                //returnCode > 0  indicates an error
                                if (payloadobject.returnCode > 0) {
                                    reject(payloadobject.returnMessage);
                                    log.debug(payloadobject.returnMessage);
                                    return false;
                                    //if all good, then lets do the embed
                                } else if (payloadobject.returnCode === 0){
                                    var pollyurl = payloadobject.returnMessage;
                                    resolve(pollyurl);
                                } else {
                                    reject('Polly Signed URL Request failed:');
                                    log.debug('Polly Signed URL Request failed:');
                                    log.debug(payloadobject);
                                }
                            } else {
                                reject('Polly Signed URL Request something bad happened');
                                log.debug('Polly Signed URL Request something bad happened');
                            }
                        } else {
                            reject('Polly Signed URL Request Not 200 response:' + xhr.status);
                            log.debug('Polly Signed URL Request Not 200 response:' + xhr.status);
                        }
                    }
                };
                var texttype='ssml';

                switch(parseInt(voiceoption)){

                    //slow
                    case 1:
                        //fetch slightly slower version of speech
                        //rate = 'slow' or 'x-slow' or 'medium'
                        speaktext =that.clean_ssml_chars(speaktext);
                        speaktext = '<speak><break time="1000ms"></break><prosody rate="slow">' + speaktext + '</prosody></speak>';
                        break;
                    //veryslow
                    case 2:
                        //fetch slightly slower version of speech
                        //rate = 'slow' or 'x-slow' or 'medium'
                        speaktext =that.clean_ssml_chars(speaktext);
                        speaktext = '<speak><break time="1000ms"></break><prosody rate="x-slow">' + speaktext + '</prosody></speak>';
                        break;
                    //ssml
                    case 3:
                        speaktext='<speak>' + speaktext + '</speak>';
                        break;

                    //normal
                    case 0:
                    default:
                        //fetch slightly slower version of speech
                        //rate = 'slow' or 'x-slow' or 'medium'
                        speaktext =that.clean_ssml_chars(speaktext);
                        speaktext = '<speak><break time="1000ms"></break>' + speaktext + '</speak>';
                        break;

                }

                //to use the neural or standard synthesis engine
                var engine = that.can_speak_neural(voice,that.region) ?'neural' : 'standard';

                //log.debug(params);
                var xhrparams = "wstoken=" + that.token
                + "&wsfunction=" + functionname
                + "&moodlewsrestformat=" + 'json'
                + "&text=" + encodeURIComponent(speaktext)
                + '&texttype=' + texttype
                + '&voice=' + voice
                + '&appid=' + def.component
                + '&owner=' + that.owner
                + '&region=' + that.region
                + '&engine=' + engine;

                var serverurl = def.cloudpoodllurl + "/webservice/rest/server.php";
                xhr.open("POST", serverurl, true);
                xhr.setRequestHeader("Cache-Control", "no-cache");
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send(xhrparams);
            });
        }

    };//end of return value
});
