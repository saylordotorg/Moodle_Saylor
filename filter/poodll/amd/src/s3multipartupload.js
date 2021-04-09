/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: s3multipartupload initialising');

    return {

        PART_SIZE: 10 * 1024 * 1024, // Minimum part size defined by aws s3 is 5 MB(5 * 1024 * 1024), maximum 5 GB (5 * 1024 * 1024 * 1024)
        SERVER_LOC: '?', // Location of the server
        completed: false,
        file: null,
        fileInfo: null,
        sendBackData: null,
        uploadXHR: [],
        partURLs: [],
        // Progress monitoring
        byterate: [],
        lastUploadedSize: [],
        lastUploadedTime: [],
        loaded: [],
        total: [],


        init: function (file) {
            this.completed = false;
            this.file = file;
            this.fileInfo = {
                name: this.file.name,
                type: this.file.type,
                size: this.file.size,
                lastModifiedDate: this.file.lastModifiedDate
            };
            this.sendBackData = null;
            this.uploadXHR = [];
            // Progress monitoring
            this.byterate = [];
            this.lastUploadedSize = [];
            this.lastUploadedTime = [];
            this.loaded = [];
            this.total = [];

        },

        /**
         * Creates the multipart upload
         */
        createMultipartUpload: function () {

            //The REST API we are calling
            var functionname = 'local_cpapi_fetch_multipartupload_details';

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
                                //if all good, then lets do the upload
                            } else {
                                that.partURLs = payloadobject.partURLs;
                                that.config.allowedURL = payloadobject.allowedURL;
                                that.config.posturl = payloadobject.postURL;
                                that.config.filename = payloadobject.filename;
                                that.config.s3filename = payloadobject.s3filename;
                                that.config.s3root = payloadobject.s3root;
                                that.config.cloudfilename = payloadobject.shortfilename;
                                that.config.cloudroot = payloadobject.shortroot;

                                //do the upload
                                that.sendAll();
                            }
                        } else {
                            log.debug('error:' + payloadobject.message);
                        }
                    } else {
                        log.debug('Not 200 response:' + xhr.status);
                    }
                }
            };

            //prepare our upload parts
            var parts = this.prepareParts();


            //log.debug(params);
            var xhrparams = "wstoken=" + this.config.wstoken
                + "&wsfunction=" + functionname
                + "&moodlewsrestformat=" + this.config.moodlewsrestformat
                + "&mediatype=" + this.config.mediatype
                + '&parts=' + parts;

            var serverurl = M.cfg.wwwroot + "/webservice/rest/server.php";
            xhr.open("POST", serverurl, true);
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(xhrparams);
        },

        /**
         * Creates the multipart upload
         */
        doMultipartUpload: function () {

            //The REST API we are calling
            var functionname = 'local_cpapi_fetch_multipartupload_urls';

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
                                //if all good, then lets do the upload
                            } else {
                                that.partURLs = payloadobject.partURLs;
                                that.config.allowedURL = payloadobject.allowedURL;
                                that.config.posturl = payloadobject.postURL;
                                that.config.filename = payloadobject.filename;
                                that.config.s3filename = payloadobject.s3filename;
                                that.config.s3root = payloadobject.s3root;
                                that.config.cloudfilename = payloadobject.shortfilename;
                                that.config.cloudroot = payloadobject.shortroot;

                                //do the upload
                                that.sendAll();
                            }
                        } else {
                            log.debug('error:' + payloadobject.message);
                        }
                    } else {
                        log.debug('Not 200 response:' + xhr.status);
                    }
                }
            };

            //prepare our upload parts
            var parts = this.prepareParts();


            //log.debug(params);
            var xhrparams = "wstoken=" + this.config.wstoken
                + "&wsfunction=" + functionname
                + "&moodlewsrestformat=" + this.config.moodlewsrestformat
                + "&mediatype=" + this.config.mediatype
                + '&parts=' + parts;

            var serverurl = M.cfg.wwwroot + "/webservice/rest/server.php";
            xhr.open("POST", serverurl, true);
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(xhrparams);
        },


/**
 * Call this function to start uploading to server
 */
start: function() {
    this.createMultipartUpload();
},

/** private */
prepareParts: function() {
    var blobs = this.blobs = [];
    this.partURLs = [];
    var start = 0;
    var parts =[];
    var end, blob;
    var partNum = 0;

    //create blobs and determine each blob file size
    while(start < this.file.size) {
        end = Math.min(start + this.PART_SIZE, this.file.size);
        filePart = this.file.slice(start, end);
        // this is to prevent push blob with 0Kb
        if (filePart.size > 0)
            blobs.push(filePart);
        start = this.PART_SIZE * ++partNum;
    }
    //return blob details so we can use them when uploading
    for (var i = 0; i < blobs.length; i++) {
        blob = blobs[i];
        parts[i]={};
        parts[i].partNumber= i+1;
        parts[i].contentLength = blob.size;
    }
    return parts;
},


 /*
uploadParts: function() {
    var blobs = this.blobs = [], promises = [];
    var start = 0;
    var parts =0;
    var end, blob;
    var partNum = 0;

    while(start < this.file.size) {
        end = Math.min(start + this.PART_SIZE, this.file.size);
        filePart = this.file.slice(start, end);
        // this is to prevent push blob with 0Kb
        if (filePart.size > 0)
            blobs.push(filePart);
        start = this.PART_SIZE * ++partNum;
    }
    //fetch and store the presigned  upload URLs
    for (var i = 0; i < blobs.length; i++) {
        blob = blobs[i];
        promises.push(this.uploadXHR[i]=$.post(this.SERVER_LOC, {
            command: 'part',
            sendBackData: this.sendBackData,
            partNumber: i+1,
            contentLength: blob.size
        }));
    }
    $.when.apply(null, promises)
        .then(this.sendAll.bind(this), this.onServerError)
        .done(this.onPrepareCompleted);
},
*/

/**
 * Sends all the created upload parts in a loop
 */
sendAll: function() {
    var blobs = this.blobs;
    var length = blobs.length;
    for (var i = 0; i < length; i++) {
        this.sendToS3(partURLs[i], blobs[i], i);
    }
},

/**
 * Used to send each uploadPart
 * @param  theurl the upload url
 * @param  blob blob  data bytes
 * @param  int index part index (base zero)
 */
sendToS3: function(theurl, blob, index) {
    var self = this;
    var size = blob.size;
    var request = self.uploadXHR[index] = new XMLHttpRequest();

    //handle a result from request object
    request.onreadystatechange = function() {
        if (request.readyState === 4) { // 4 is DONE
            // self.uploadXHR[index] = null;
            if (request.status !== 200) {
                self.updateProgress();
                self.onS3UploadError(request);
                return;
            }
            self.updateProgress();
        }
    };

    //handle an  on progress event from the upload of the request
    request.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            self.total[index] = size;
            self.loaded[index] = e.loaded;
            if (self.lastUploadedTime[index])
            {
                var time_diff=(new Date().getTime() - self.lastUploadedTime[index])/1000;
                if (time_diff > 0.005) // 5 miliseconds has passed
                {
                    var byterate=(self.loaded[index] - self.lastUploadedSize[index])/time_diff;
                    self.byterate[index] = byterate;
                    self.lastUploadedTime[index]=new Date().getTime();
                    self.lastUploadedSize[index]=self.loaded[index];
                }
            }
            else
            {
                self.byterate[index] = 0;
                self.lastUploadedTime[index]=new Date().getTime();
                self.lastUploadedSize[index]=self.loaded[index];
            }
            // Only send update to user once, regardless of how many
            // parallel XHRs we have (unless the first one is over).
            if (index==0 || self.total[0]==self.loaded[0])
                self.updateProgress();
        }
    };
    //send the request !!
    request.open('PUT', theurl, true);
    request.send(blob);

}, //end of sendToS3

/**
 * Abort multipart upload
 */
cancel: function() {
    var self = this;
    for (var i=0; i<this.uploadXHR.length; ++i) {
        this.uploadXHR[i].abort();
    }
    $.post(self.SERVER_LOC, {
        command: 'abort',
        sendBackData: self.sendBackData
    }).done(function(data) {

    });
},

/**
 * Complete multipart upload
 */
completeMultipartUpload: function() {
    var self = this;
    if (this.completed) return;
    this.completed=true;
    $.post(self.SERVER_LOC, {
        command: 'complete',
        sendBackData: self.sendBackData
    }).done(function(data) {
        self.onUploadCompleted(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        self.onServerError('complete', jqXHR, textStatus, errorThrown);
    });
},

/**
 * Track progress, propagate event, and check for completion
 */
updateProgress: function() {
    var total=0;
    var loaded=0;
    var byterate=0.0;
    var complete=1;
    for (var i=0; i<this.total.length; ++i) {
        loaded += +this.loaded[i] || 0;
        total += this.total[i];
        if (this.loaded[i]!=this.total[i])
        {
            // Only count byterate for active transfers
            byterate += +this.byterate[i] || 0;
            complete=0;
        }
    }
    if (complete)
        this.completeMultipartUpload();
    total=this.fileInfo.size;
    this.onProgressChanged(loaded, total, byterate);
},

// Overridable events:

/**
 * Overrride this function to catch errors occured when communicating to your server
 *
 * @param {type} command Name of the command which failed,one of 'CreateMultipartUpload', 'SignUploadPart','CompleteMultipartUpload'
 * @param {type} jqXHR jQuery XHR
 * @param {type} textStatus resonse text status
 * @param {type} errorThrown the error thrown by the server
 */
onServerError: function(command, jqXHR, textStatus, errorThrown) {},

/**
 * Overrride this function to catch errors occured when uploading to S3
 *
 * @param XMLHttpRequest xhr the XMLHttpRequest object
 */
onS3UploadError: function(xhr) {},

/**
 * Override this function to show user update progress
 *
 * @param {type} uploadedSize is the total uploaded bytes
 * @param {type} totalSize the total size of the uploading file
 * @param {type} speed bytes per second
 */
onProgressChanged: function(uploadedSize, totalSize, bitrate) {},

/**
 * Override this method to execute something when upload finishes
 *
 */
onUploadCompleted: function(serverData) {},
/**
 * Override this method to execute something when part preparation is completed
 *
 */
onPrepareCompleted: function() {}

};//end of return object
});//utterly the end