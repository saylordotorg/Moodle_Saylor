define(["jquery", "core/log"], function ($, log) {
    "use strict"; // jshint ;_;

    log.debug("poodll helper: initialising");

    return {


        init: function (opts) {
            var prquestion = function (evt) {
                if(evt && evt[1]=='filesubmitted'){
                    var filename = evt[2];
                    var updatecontrol = evt[3];
                    // post a custom event that a filter template might be interested in
                    var prquestionUploaded = new CustomEvent("prquestionUploaded", {details: evt});
                    document.dispatchEvent(prquestionUploaded);

                    //poke the filename
                    var upc = '';
                    if (typeof updatecontrol !== 'undefined' && updatecontrol !== '') {
                        upc = $('[id="' + updatecontrol + '"]');
                        //the code below used to work until odd chars in question id annoyed jquery 3
                        //upc = $('#' + uploader.config.updatecontrol);
                    }
                    if (upc.length < 1) {
                        upc = $('[id="' + updatecontrol + '"]', window.parent.document);
                    }
                    if (upc.length > 0) {
                        upc.get(0).value = filename;
                    } else {
                        log.debug('upload failed #2');
                        return false;
                    }
                    upc.trigger('change');
                }
            }; //end of callback function
            window.prquestion = prquestion;

        } //end of cp init

    };//end of return object
});