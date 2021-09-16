/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: utils initialising');

    return {
        timeouthandles: [],

        // Call Upload file from drawingboard a, first handle autosave bits and pieces
        WhiteboardUploadHandler: function (recid, wboard, opts, theuploader) {
            // Save button disabling a little risky db perm. fails publish "startdrawing" after mode change
            var savebutton = $('#' + recid + '_btn_upload_whiteboard')[0];
            savebutton.disabled = true;
            clearTimeout(this.timeouthandles[recid]);
            //call the file upload
            var cvs = this.getCvs(recid, wboard, opts);
            this.pokeVectorData(recid, wboard, opts);
            theuploader.uploadFile(cvs.toDataURL(), 'image/png');
        },
        getCvs: function (recid, wboard) {
            if (recid.indexOf('drawingboard_') == 0) {
                var cvs = wboard.canvas;
            } else {
                var cvs = wboard.canvasForExport();
            }//end of of drawing board
            return cvs;
        },

        escapeColon: function (thestring) {
            return thestring.replace(/:/, '\\:');
        },

        pokeVectorData: function (recid, wboard, opts) {
            var vectordata = "";
            if (recid.indexOf('drawingboard_') == 0) {
                vectordata = JSON.stringify(wboard.history, null, 2);
            } else {
                //only LC has vector data it seems
                vectordata = JSON.stringify(wboard.getSnapshot());
            }//end of of drawing board

            //need to do the poke here
            if (typeof opts['vectorcontrol'] !== 'undefined' && opts['vectorcontrol'] !== '') {
                //the moodle question has a colon in the field ids, so we need to escape that away
                $('#' + this.escapeColon(opts['vectorcontrol'])).val(vectordata);
                log.debug('Vectorcontrol:' + opts['vectorcontrol']);
                //  log.debug('Vectordata:' + vectordata );
            }
            //end of poke vectordata
        },


        _concatenateWavBlobs: function (blobs, callback) {



            //fetch our header
            var self=this;
            var allbytes = []; //this will be an array of arraybuffers
            var loadedblobs = 0;
            var totalbytes = 0;
            // fetch the blob data
            var lng = blobs.length;
            for (var i = 0; i < lng; i++) {
                //we run the filereader inside an an immediately executing function
                //so that we can keep track of the index of the blob being read.
                //in edge and IE they all get read simulatenously and the order of concatenation
                //could not be relied on with : allbytes.push(audiodata); so we did: allbytes[index]=audiodata;
                (function (index) {
                    var fileReader = new FileReader();
                    fileReader.onload = function () {
                        //load blob into arraybuffer
                        var ab = this.result;

                        //remove header and add audiodata to the all data array
                        //the slice is from(inclusive) to end(exclusive)
                        var audiodata = ab.slice(44);
                        totalbytes += audiodata.byteLength;
                        //allbytes.push(audiodata);
                        allbytes[index] = audiodata;
                        loadedblobs++;

                        //finally add the header and do callback if at end
                        if (loadedblobs == lng) {
                            //get header from last blob, and just adjust the data length
                            var header = ab.slice(0, 44);
                            var headerview = new DataView(header);
                            headerview.setUint32(40, totalbytes, true);
                            allbytes.unshift(header);

                            //make our final binary blob and pass it to callback
                            var wavblob = new Blob(allbytes, {type: 'audio/wav'});
                            log.debug(totalbytes);
                            log.debug(allbytes);
                            callback(wavblob);
                        }
                    };
                    fileReader.readAsArrayBuffer(blobs[i]);
                })(i);

            }//end of i loop

        }, //end of concatenateWavBlobs



        _simpleConcatenateBlobs: function (blobs, type) {
            return new Blob(blobs, {'type': type});
        },

        doConcatenateBlobs: function (theblobs, thecallback) {
           // var mimetype = 'audio/ogg';
            //can be of format: "audio/ogg; codecs=opus"
            //still works though
           var mimetype =theblobs[0].type;
            switch (mimetype) {
                case 'audio/wav':
                case 'audio/pcm':
                    // mediastreamrecorder adds a header to each wav blob,
                    // we remove them and combine audiodata and new header
                    this._concatenateWavBlobs(theblobs, thecallback);
                    break;
                case 'audio/ogg':
                case 'audio/webm':
                case 'video/webm':
                case 'video/mp4':
                case 'audio/mp4':
                case 'audio/mp3':
                case 'audio/m4a':
                default:
                    var concatenatedBlob = this._simpleConcatenateBlobs(theblobs, mimetype);
                    thecallback(concatenatedBlob);
                    break;
            }// end of switch case
        },


        bytesToSize: function (bytes) {
            var k = 1000;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0) {
                return '0 Bytes';
            }
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(k)), 10);
            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },

        // below function via: http://goo.gl/6QNDcI
        getTimeLength: function (milliseconds) {
            var data = new Date(milliseconds);
            return data.getUTCHours() + " hours, " + data.getUTCMinutes() + " minutes and " + data.getUTCSeconds() + " second(s)";
        },

        has_mediarecorder: function(){
          return typeof MediaRecorder !== 'undefined';
        },

        can_html5_record: function(mediatype){
          if( navigator && navigator.mediaDevices
            && navigator.mediaDevices.getUserMedia){
              if(mediatype=='audio'){
                  return true;
              }else{
                  return this.has_mediarecorder();
              }
          }else{
              return false;
          }
        },

        is_edge: function () {
            return navigator.userAgent.indexOf('Edge') > -1;
        },

        is_chrome: function () {
            var isChromium = window.chrome,
                winNav = window.navigator,
                vendorName = winNav.vendor,
                isOpera = winNav.userAgent.indexOf("OPR") > -1,
                isIEedge = winNav.userAgent.indexOf("Edge") > -1,
                isIOSChrome = winNav.userAgent.match("CriOS");

            if (isIOSChrome) {
                return true;
            } else if (
                isChromium !== null &&
                typeof isChromium !== "undefined" &&
                vendorName === "Google Inc." &&
                isOpera === false &&
                isIEedge === false
            ) {
                return true;
            } else {
                return false;
            }
        },

        is_safari: function () {
            return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        },

        is_ios: function () {
            return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        },

        is_opera: function () {
            return (typeof opera !== 'undefined' && navigator.userAgent && navigator.userAgent.indexOf('OPR/') !== -1);
        },

        is_android: function () {
            var ua = window.navigator.userAgent;
            var isAndroid = (ua.indexOf("android") > -1) || (ua.indexOf("Android") > -1); //&& ua.indexOf("mobile");
            return isAndroid;
        },

        is_ie: function () {
            var ms_ie = false;
            var ua = window.navigator.userAgent;
            var old_ie = ua.indexOf('MSIE ');
            var new_ie = ua.indexOf('Trident/');
            var edge = ua.indexOf('Edge/');
            if ((old_ie > -1) || (new_ie > -1) || (edge > -1)) {
                ms_ie = true;
            }
            return ms_ie;
        },

        parseQueryString: function (url) {
            var urlParams = {};
            url.replace(
                new RegExp("([^?=&]+)(=([^&]*))?", "g"),
                function ($0, $1, $2, $3) {
                    urlParams[$1] = $3;
                }
            );

            return urlParams;
        }
    };//end of return object
});