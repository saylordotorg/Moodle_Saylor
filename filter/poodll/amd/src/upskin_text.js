/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('upskin_text: initialising');

    return {
        element: null,
        cvs: null,
        cvsctx: null,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //init
        init: function (config, element, cvs, messagearea) {
            this.config = config;
            this.element = element;
            this.cvs = cvs;
            this.messagearea = messagearea;
        },

        initControls: function () {
            //add html and events to recorder here
        },

        //create a progress bar
        initProgressSession: function (xhr) {
            var self = this;
            self.percent = 0;
            var uploadingmessage = M.util.get_string('recui_uploading', 'filter_poodll');
            // Handle event
            xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    self.percent = e.loaded / e.total;
                    self.showMessage(uploadingmessage + '(' + Math.floor(self.percent * 100) + '%)');
                }
            });
        },

        deactivateProgressSession: function () {
            log.debug('deactivating session');
        },

        showMessage: function (msg, msgid) {
            this.messagearea.html(msg);
        }//End of send message
    };//end of returned object
});//total end
