define(["jquery", "core/log"], function ($, log) {
    "use strict"; // jshint ;_;

    log.debug("poodll helper: initialising");

    return {


        init: function (opts) {
            var that =this;
            var prquestion = function (evt) {

                if(evt){
                    switch(evt[1]) {
                        case 'filesubmitted':
                            var filename = evt[2];
                            var updatecontrol = evt[3];
                            // post a custom event that a filter template might be interested in
                            var prquestionUploaded = new CustomEvent("prquestionUploaded", {details: evt});
                            document.dispatchEvent(prquestionUploaded);

                            //if opts safe save
                            if(opts.safesave==1) {
                                var nextbtn = $('.submitbtns .mod_quiz-next-nav');
                                nextbtn.removeClass('qtype_poodllrecording_disabledbtn');
                                nextbtn.removeAttr('disabled', 'disabled');
                                //deactivate premature leaving
                                $(window).off('beforeunload', that.preventPrematureLeaving);
                            }

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
                            break;
                        case 'started':
                            //if opts safe save
                            if(opts.safesave==1) {
                                var savebtn = $('.submitbtns .mod_quiz-next-nav');
                                savebtn.addClass('qtype_poodllrecording_disabledbtn');
                                savebtn.attr('disabled', 'disabled');
                                //Add a page unload check ..
                                $(window).on('beforeunload', that.preventPrematureLeaving);
                            }

                            break;
                    }
                }
            }; //end of callback function
            window.prquestion = prquestion;

        }, //end of init

        preventPrematureLeaving: function(e){
            log.debug('preventPrematureLeaving has been called');
            e.preventDefault();
            e.returnValue = "Your recording has not been uploaded. Cancel to stay on this page.";
            return e.returnValue;
        },

    };//end of return object
});