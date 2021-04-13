define(['jquery', 'core/log', 'mod_wordcards/modalformhelper'], function ($, log,mfh) {
    "use strict"; // jshint ;_;
    /*
    This file helps you get Polly URLs at runtime
     */

    log.debug('managewords helper: initialising');

    return {

        init: function(opts){
            var after_term_add = function(){};
            var after_term_edit = function(){};
            mfh.init('.mod_wordcards_item_row_addlink', opts['contextid'],after_term_add);
            mfh.init('.mod_wordcards_item_row_editlink', opts['contextid'],after_term_edit);
        }
    };//end of return value
});