define(['jquery','core/log','mod_solo/cloudpoodllloader'], function($,log, cloudpoodll) {
    "use strict"; // jshint ;_;
/*
This file sets up the cloud poodll recorder and passes on events to registered handlers
 */

    log.debug('Recorder helper: initialising');

    return{

        status: 'stopped',

        init: function(opts,on_recording_start,
            on_recording_end,
            on_media_processing,
            on_speech,
            on_file_submitted,
            on_upload_details){

            var that = this;
            cloudpoodll.init(opts['recorderid'],

                function(message){
                    //console.log(message);
                    switch(message.type){
                        case 'recording':
                            if(message.action==='started'){
                                that.startbuttonclick();
                                on_recording_start(message);

                            }else if(message.action==='stopped'){
                                that.stopbuttonclick();
                                on_recording_end(message);
                            }
                            break;
                        case 'awaitingprocessing':
                            //awaitingprocessing fires often, but we only want to post once
                            if(that.status!='posted') {
                                on_media_processing(message);
                            }
                            that.status='posted';
                            break;

                        case 'filesubmitted':
                            on_file_submitted();
                            break;

                        case 'speech':
                            on_speech(message);
                            break;

                        case 'uploaddetails':
                            on_upload_details(message)
                            break;

                        case 'error':
                            alert('PROBLEM:' + message.message);
                            break;
                    }
                }
            );
        },
        stopbuttonclick: function(){
            this.status='stopped';
            //do something
        },
        startbuttonclick: function(){
            this.status='started';
           //do something
        }

    };//end of return value
});