define(['jquery', 'core/log'], function ($, log) {
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

        fetch_polly_url: function(speaktext,texttype, voice) {

                //The REST API we are calling
                var functionname = 'local_cpapi_fetch_polly_url';

                //fetch the Posturl. We need this.
                //set up our ajax request
                var xhr = new XMLHttpRequest();
                var that = this;

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
                                    log.debug(payloadobject.returnMessage);
                                    return false;
                                    //if all good, then lets do the embed
                                } else if (payloadobject.returnCode === 0){
                                    var pollyurl = payloadobject.returnMessage;
                                    that.onnewpollyurl(pollyurl);
                                } else {
                                    log.debug('Polly Signed URL Request failed:');
                                    log.debug(payloadobject);
                                }
                            } else {
                                log.debug('Polly Signed URL Request something bad happened');
                            }
                        } else {
                            log.debug('Polly Signed URL Request Not 200 response:' + xhr.status);
                        }
                    }
                };

                //log.debug(params);
                var xhrparams = "wstoken=" + this.token
                + "&wsfunction=" + functionname
                + "&moodlewsrestformat=" + 'json'
                + "&text=" + encodeURIComponent(speaktext)
                + '&texttype=' + texttype
                + '&voice=' + voice
                + '&appid=' + 'mod_readaloud'
                + '&owner=' + this.owner
                + '&region=' + this.region;

                var serverurl = 'https://cloud.poodll.com' + "/webservice/rest/server.php";
                xhr.open("POST", serverurl, true);
                xhr.setRequestHeader("Cache-Control", "no-cache");
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send(xhrparams);
        },

        onnewpollyurl: function (pollyurl) {
            //do something
            log.debug(pollyurl);
        },

    };//end of return value
});