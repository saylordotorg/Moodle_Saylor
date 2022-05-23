/* jshint ignore:start */
define(['jquery', 'core/log', 'filter_poodll/upskin_plain'], function ($, log, upskin_plain) {

    "use strict"; // jshint ;_;

    log.debug('Universal Uploader: initialising');

    return {

        config: null,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        init: function (element, config, upskin) {
            this.config = config;
            if (upskin) {
                this.upskin = upskin;
            } else {
                this.upskin = upskin_plain.clone();
                this.upskin.init(config, element, false, false);
            }
            this.upskin.initControls();
            this.registerEvents();
        },

        registerEvents: function () {
            var that = this;
            //whteboard does not have a hermes
           if(this.config.hermes) {
                this.config.hermes.on('fetch_upload_url', function (e) {
                    that.fetchNewUploadDetails();
                });
           }
        },

        fetchNewUploadDetails: function () {

            //The REST API we are calling
            var functionname = 'local_cpapi_fetch_upload_details';

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
                                //We alert the iframe host that something did not go right
                                var messageObject = {};
                                messageObject.id = that.config.id;
                                messageObject.type = "error";
                                messageObject.code = payloadobject.returnCode;
                                messageObject.message = payloadobject.returnMessage;
                                that.config.hermes.postMessage(messageObject);
                                return;
                                //if all good, then lets do the embed
                            } else {
                                that.config.allowedURL = payloadobject.allowedURL;
                                that.config.posturl = payloadobject.postURL;
                                that.config.filename = payloadobject.filename;
                                that.config.s3filename = payloadobject.s3filename;
                                that.config.s3root = payloadobject.s3root;
                                that.config.cloudfilename = payloadobject.shortfilename;
                                that.config.cloudroot = payloadobject.shortroot;
                            }

                        } else {
                            log.debug('error:' + payloadobject.message);

                        }
                    } else {
                        log.debug('Not 200 response:' + xhr.status);
                    }
                }
            };

            //log.debug(params);
            var xhrparams = "wstoken=" + this.config.wstoken
                + "&wsfunction=" + functionname
                + "&moodlewsrestformat=" + this.config.moodlewsrestformat
                + "&mediatype=" + this.config.mediatype
                + '&parent=' + this.config.parent
                + '&appid=' + this.config.appid
                + '&owner=' + this.config.owner
                + '&region=' + this.config.region
                + '&expiredays=' + this.config.expiredays
                + '&transcode=' + this.config.transcode
                + '&transcoder=' + this.config.transcoder
                + '&transcribe=' + this.config.transcribe
                + '&subtitle=' + this.config.subtitle
                + '&transcribelanguage=' + this.config.language
                + '&transcribevocab=' + this.config.transcribevocab
                + '&notificationurl=' + this.config.notificationurl
                + '&sourcemimetype=' + this.config.sourcemimetype;

            var serverurl = M.cfg.wwwroot + "/webservice/rest/server.php";
            xhr.open("POST", serverurl, true);
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(xhrparams);
        },

        uploadBlob: function (blob, filetype) {
            this.uploadFile(blob, filetype);
            return;
        },
        //extract filename from the text returned as response to upload
        extractFilename: function (returntext) {
            var searchkey = "success<filename>";
            var start = returntext.indexOf(searchkey);
            if (start < 1) {
                return false;
            }
            var end = returntext.indexOf("</filename>");
            var filename = returntext.substring(start + (searchkey.length), end);
            return filename;
        },

        //fetch file extension from the filetype
        fetchFileExtension: function (filetype) {
            var ext = "";
            //in the case of a string like this:
            // "audio/webm;codecs=opus" we do not the codecs
            if(filetype.indexOf(';')>0){
                filetype = filetype.split(';')[0];
            }
            switch (filetype) {
                case "image/jpeg":
                    ext = "jpg";
                    break;
                case "image/png":
                    ext = "png";
                    break;
                case "audio/wav":
                    ext = "wav";
                    break;
                case "audio/ogg":
                    ext = "ogg";
                    break;
                case "audio/mpeg3":
                    ext = "mp3";
                    break;
                case "audio/mp3":
                    ext = "mp3";
                    break;
                case "audio/webm":
                    ext = "webm";
                    break;
                case "audio/wma":
                    ext = "wma";
                    break;
                case "audio/x-mpeg-3":
                    ext = "mp3";
                    break;
                case "audio/mp4":
                case "audio/m4a":
                case "audio/x-m4a":
                    ext = "m4a";
                    break;
                case "audio/3gpp":
                    ext = "3gpp";
                    break;
                case "video/mpeg3":
                    ext = "3gpp";
                    break;
                case "video/m4v":
                    ext = "m4v";
                    break;
                case "video/mp4":
                    ext = "mp4";
                    break;
                case "video/mov":
                case "video/quicktime":
                    ext = "mov";
                    break;
                case "video/x-matroska":
                case "video/webm":
                    ext = "webm";
                    break;
                case "video/wmv":
                    ext = "wmv";
                    break;
                case "video/ogg":
                    ext = "ogg";
                    break;
            }
            //if we get here we have an unknown mime type, just guess based on the mediatype
            if(ext===""){
                if(filetype.indexOf('video')>-1){
                    ext = "mp4";
                }else{
                    ext = "mp3";
                }
            }
            return ext;
        },

        pokeFilename: function (filename, uploader) {

            var upc = '';
            if (typeof uploader.config.updatecontrol !== 'undefined' && uploader.config.updatecontrol !== '') {
                upc = $('[id="' + uploader.config.updatecontrol + '"]');
                //the code below used to work until odd chars in question id annoyed jquery 3
                //upc = $('#' + uploader.config.updatecontrol);
            }
            if (upc.length < 1) {
                upc = $('[id="' + uploader.config.updatecontrol + '"]', window.parent.document);
            }
            if (upc.length > 0) {
                upc.get(0).value = filename;
            } else {
                log.debug('upload failed #2');
                uploader.upskin.showMessage(M.util.get_string('recui_uploaderror', 'filter_poodll'), 'recui_uploaderror');
                return false;
            }
            upc.trigger('change');
            return true;
        },

        alertRecorderSuccess: function (widgetid) {
            if (this.config.hasOwnProperty('onuploadsuccess')) {
                this.config.onuploadsuccess(widgetid);
            }
        },

        alertRecorderFailure: function (widgetid) {
            if (this.config.hasOwnProperty('onuploadfailure')) {
                this.config.onuploadfailure(widgetid);
            }
        },

        //We can detect conversion by pinging the s3 out filename
        //this is only done in the iFrame
        completeAfterProcessing: function (uploader, filename, waitms) {

            //alert the skin that we are awaiting processing
            this.upskin.showMessage(M.util.get_string('recui_awaitingconversion', 'filter_poodll'), 'recui_awaitingconversion');

            //this will always be true ...
            if (uploader.config.iframeembed) {
                filename = uploader.config.s3root + uploader.config.s3filename;
            }

            //We alert the iframe host that a file is now awaiting conversion
            var messageObject = {};
            messageObject.type = "awaitingprocessing";
            messageObject.mediaurl = filename;
            messageObject.mediafilename = uploader.config.s3filename;
            messageObject.sourcefilename = uploader.config.sourcefilename;
            messageObject.sourcemimetype = uploader.config.sourcemimetype;
            messageObject.s3root = uploader.config.s3root;
            messageObject.id = uploader.config.id;
            messageObject.updatecontrol = uploader.config.updatecontrol;
            if (uploader.config.transcribe) {
                messageObject.transcripturl = filename + '.txt';
                messageObject.transcriptfilename = uploader.config.s3filename + '.txt';
            }
            uploader.config.hermes.postMessage(messageObject);

            //we commence a series of ping and retries until the recorded file is available
            var that = this;
            $.ajax({
                url: uploader.config.s3root + uploader.config.s3filename,
                method: 'HEAD',
                cache: false,
                error: function () {
                    //We get here if its a 404 or 403. So settimout here and wait for file to arrive
                    //we increment the timeout period each time to prevent bottlenecks
                    log.debug('403 errors are normal here, till the file arrives back from conversion');
                    setTimeout(function () {
                        that.completeAfterProcessing(uploader, filename, waitms + 500);
                    }, waitms);
                },
                success: function (data, textStatus, xhr) {
                    switch (xhr.status) {
                        case 200:
                            that.doUploadCompleteCallback(uploader, filename);
                            break;
                        default:
                            setTimeout(function () {
                                that.completeAfterProcessing(uploader, filename, waitms + 500);
                            }, waitms);
                    }

                }
            });
        },

        doUploadCompleteCallback: function (uploader, filename) {

            //in the case of an iframeembed we need a full URL not just a filename
            if (uploader.config.iframeembed) {
                filename = uploader.config.s3root + uploader.config.s3filename;
            }

            //For callbackjs and for postmessage we need an array of stuff
            var callbackObject = new Array();
            callbackObject[0] = uploader.config.widgetid;
            callbackObject[1] = "filesubmitted";
            callbackObject[2] = filename;
            callbackObject[3] = uploader.config.updatecontrol;
            callbackObject[4] = uploader.config.s3filename;

            //alert the skin that we were successful
            this.upskin.showMessage(M.util.get_string('recui_uploadsuccess', 'filter_poodll'), 'recui_uploadsuccess');

            //invoke callbackjs if we have one, otherwise just update the control(default behav.)
            if (!uploader.config.iframeembed) {
                if (uploader.config.callbackjs && uploader.config.callbackjs != '') {
                    if (typeof(uploader.config.callbackjs) === 'function') {
                        uploader.config.callbackjs(callbackObject);
                    } else {
                        //this was the old rubbish way, where the callback was a function name
                        this.executeFunctionByName(uploader.config.callbackjs, window, callbackObject);
                    }
                } else {
                    //by default we just poke the filename
                    uploader.pokeFilename(filename, uploader);
                }
            } else {
                //in the case of an iframeembed we will also post a message to the host, they can choose to handle it or not
                //The callback object above scan prob. be phased out. But not all integrations will use iframes either.
                var messageObject = {};
                messageObject.type = "filesubmitted";
                messageObject.mediaurl = uploader.config.s3root + uploader.config.s3filename;
                messageObject.mediafilename = uploader.config.s3filename;
                messageObject.sourcefilename = uploader.config.sourcefilename;
                messageObject.sourcemimetype = uploader.config.sourcemimetype;
                messageObject.s3root = uploader.config.s3root;
                messageObject.id = uploader.config.id;
                messageObject.updatecontrol = uploader.config.updatecontrol;
                if (uploader.config.transcribe) {
                    messageObject.transcripturl = uploader.config.s3root + uploader.config.s3filename + '.txt';
                    messageObject.transcriptfilename = uploader.config.s3filename + '.txt';
                }

                uploader.config.hermes.postMessage(messageObject);
            }
        },

        //after an upload handle the filename poke and callback call
        postProcessUpload: function (e, uploader) {
            var xhr = e.currentTarget;
            if (xhr.readyState == 4) {

                uploader.upskin.deactivateProgressSession();

                //deactivate premature leaving
                $(window).off('beforeunload', this.preventPrematureLeaving);

                if (xhr.status == 200) {
                    var filename = uploader.config.filename;
                    if (!filename) {
                        filename = uploader.extractFilename(xhr.responseText);
                    }
                    if (!filename) {
                        log.debug('upload failed #1');
                        log.debug(xhr);
                        return;
                    }

                    //Alert any listeners about the upload complete
                    //in an iframeembed we only  do this after conversion is complete. so we run a poll to check compl.
                    //in standard Moodle we have a placeholder file to deal with any slow conversions. so we don't poll
                    if (uploader.config.iframeembed) {
                        this.completeAfterProcessing(uploader, filename, 1000);
                    } else {
                        this.doUploadCompleteCallback(uploader, filename);
                    }

                    //alert the recorder that this was successful
                    this.alertRecorderSuccess(uploader.config.widgetid);

                } else {
                    log.debug('upload failed #3');
                    log.debug(xhr);
                    uploader.upskin.showMessage(M.util.get_string('recui_uploaderror', 'filter_poodll'), 'recui_uploaderror');

                    //alert the recorder that this failed
                    this.alertRecorderFailure(uploader.config.widgetid);

                } //end of if status 200
            }//end of if ready state 4

        },

        preventPrematureLeaving: function(){
            return M.util.get_string('recui_waitwaitstilluploading', 'filter_poodll');
        },

        // upload Media file to wherever
        uploadFile: function (filedata, sourcemimetype) {

            var xhr = new XMLHttpRequest();
            var config = this.config;
            var uploader = this;

            //get the file extension from the filetype
            var sourceext = this.fetchFileExtension(sourcemimetype);

            //is this an iframe embed
            if (typeof config.iframeembed == 'undefined') {
                config.iframeembed = false;
            }

            //are we using s3
            var using_s3 = config.using_s3;

            //Handle UI display of this upload
            this.upskin.initProgressSession(xhr);

            //Add a page unload check ..
            $(window).on('beforeunload', this.preventPrematureLeaving);

            //alert user that we are now uploading
            this.upskin.showMessage(M.util.get_string('recui_uploading', 'filter_poodll'), 'recui_uploading');

            //init sourcemimetype and sourcefilename
            uploader.config.sourcemimetype = sourcemimetype;
            uploader.config.sourcefilename = uploader.config.s3filename;

            xhr.onreadystatechange = function (e) {
                if (using_s3 && this.readyState === 4) {
                    if (config.iframeembed) {
                        uploader.update_filenames(uploader, sourceext);
                    } else {
                        //ping Moodle and inform that we have a new file
                        uploader.postprocess_s3_upload(uploader);
                    }
                }
                uploader.postProcessUpload(e, uploader);

            };

            if (using_s3) {
                xhr.open("put", config.posturl, true);
                xhr.setRequestHeader("Content-Type", 'application/octet-stream');
                xhr.send(filedata);
            } else {

                //We NEED to redo this bit of code ..
                //its duplicating!!!
                if (!(filedata instanceof Blob)) {
                    log.debug('filedata is not blob');
                    var params = "datatype=uploadfile";
                    //We must URI encode the filedata, because otherwise the "+" characters get turned into spaces
                    //spent hours tracking that down ...justin 20121012
                    params += "&paramone=" + encodeURIComponent(filedata);
                    params += "&paramtwo=" + sourceext;
                    params += "&paramthree=" + config.mediatype;
                    params += "&requestid=" + config.widgetid;
                    params += "&contextid=" + config.p2;
                    params += "&component=" + config.p3;
                    params += "&filearea=" + config.p4;
                    params += "&itemid=" + config.p5;

                    xhr.open("POST", config.posturl, true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.setRequestHeader("Cache-Control", "no-cache");
                    //xhr.setRequestHeader("Content-length", params.length);
                    //xhr.setRequestHeader("Connection", "close");
                    xhr.send(params);
                } else {
                    log.debug('filedata is blob');
                    //we have to base64 string the blob  before sending it
                    var reader = new window.FileReader();
                    reader.readAsDataURL(filedata);
                    reader.onloadend = function () {
                        var base64filedata = reader.result;
                        //log.debug(params);
                        var params = "datatype=uploadfile";
                        //We must URI encode the filedata, because otherwise the "+" characters get turned into spaces
                        //spent hours tracking that down ...justin 20121012
                        params += "&paramone=" + encodeURIComponent(base64filedata);
                        params += "&paramtwo=" + sourceext;
                        params += "&paramthree=" + config.mediatype;
                        params += "&requestid=" + config.widgetid;
                        params += "&contextid=" + config.p2;
                        params += "&component=" + config.p3;
                        params += "&filearea=" + config.p4;
                        params += "&itemid=" + config.p5;

                        xhr.open("POST", config.posturl, true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.setRequestHeader("Cache-Control", "no-cache");
                        // xhr.setRequestHeader("Content-length", params.length);
                        // xhr.setRequestHeader("Connection", "close");
                        xhr.send(params);
                    };//end of fileread on load end
                }//end of if blob
            }//end of if using_s3
        },

        // upload Media file to wherever
        uploadMultiPartFile: function (filedata, sourcemimetype) {

            var xhr = new XMLHttpRequest();
            var config = this.config;
            var uploader = this;

            //get the file extension from the filetype
            var sourceext = this.fetchFileExtension(sourcemimetype);

            //is this an iframe embed
            if (typeof config.iframeembed == 'undefined') {
                config.iframeembed = false;
            }

            //are we using s3
            var using_s3 = config.using_s3;

            //Handle UI display of this upload
            this.upskin.initProgressSession(xhr);

            //Add a page unload check ..
            $(window).on('beforeunload', this.preventPrematureLeaving);

            //alert user that we are now uploading
            this.upskin.showMessage(M.util.get_string('recui_uploading', 'filter_poodll'), 'recui_uploading');

            //init sourcemimetype and sourcefilename
            uploader.config.sourcemimetype = sourcemimetype;
            uploader.config.sourcefilename = uploader.config.s3filename;

            xhr.onreadystatechange = function (e) {
                if (using_s3 && this.readyState === 4) {
                    if (config.iframeembed) {
                        uploader.update_filenames(uploader, sourceext);
                    } else {
                        //ping Moodle and inform that we have a new file
                        uploader.postprocess_s3_upload(uploader);
                    }
                }
                uploader.postProcessUpload(e, uploader);

            };

            if (using_s3) {
                xhr.open("put", config.posturl, true);
                xhr.setRequestHeader("Content-Type", 'application/octet-stream');
                xhr.send(filedata);
            }//end of if using_s3
        },

        update_filenames: function (uploader, sourceext) {
            var config = uploader.config;

            //now its a bit hacky, but
            // only now do we know the true final file extension (ext) and mimetype of unconv. media
            // so we want to save that and if the user is NOT transcoding,
            //we want to change the s3filename from the default mp4/mp3 to whatever the mimetype inidicates, ie sourceext

            switch (config.mediatype) {
                case 'audio':
                    //source info
                    uploader.config.sourcefilename = config.s3filename.replace('.mp3', '.' + sourceext);
                    if (!config.transcode) {
                        uploader.config.s3filename = uploader.config.sourcefilename;
                        //do we need this, I think its old and noone uses it.
                        uploader.config.cloudfilename = uploader.config.s3filename;
                    }
                    break;
                case 'video':
                    uploader.config.sourcefilename = config.s3filename.replace('.mp4', '.' + sourceext);
                    if (!config.transcode) {
                        uploader.config.s3filename = uploader.config.sourcefilename;
                    }
                    break;
            }
        },

        postprocess_s3_upload: function (uploader) {
            var config = uploader.config;
            const formData = new FormData();
            formData.append("datatype", "handles3upload");
            formData.append("contextid", config.p2);
            formData.append("component", config.p3);
            formData.append("filearea", config.p4);
            formData.append("itemid", config.p5);
            formData.append("filename", config.filename);
            formData.append("mediatype", config.mediatype);
            //navigator beacon polyfill
            if (!navigator.sendBeacon) {
                navigator.sendBeacon = function (url, thedata) {
                    window.fetch(url, {method: 'POST', body: thedata, credentials: 'include'});
                };
            }
            //we use navigator beacon over xhr because there are times believe it or not, when a page load happens and that kills
            //the request. causing lost files
            navigator.sendBeacon(M.cfg.wwwroot + '/filter/poodll/poodllfilelib.php', formData);

        },

        //function to call the callback function with arguments
        executeFunctionByName: function (functionName, context, args) {

            //var args = Array.prototype.slice.call(arguments).splice(2);
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for (var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].call(this, args);
        },

        dataURItoBlob: function (dataURI, mimetype) {
            var byteString = atob(dataURI.split(',')[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], {type: mimetype});
        },//end of dataURItoBlob

        //some recorder skins call this directly, so we just pass it through to the upskin
        Output: function (msg) {
            this.upskin.showMessage(msg, 'recorderskinmsg');
        }
    };//end of returned object
});//total end
