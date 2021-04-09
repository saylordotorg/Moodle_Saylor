define(['jquery', 'core/log', 'filter_poodll/dlg_poodll', 'filter_poodll/utils_amd'], function ($, log, dialog, utils) {

    log.debug('download dialog: initialising');


    return {
        dlg: null,
        instanceprops: null,
        pmr: null,
        dlgbox: null,

        init: function (pmr, instanceprops) {
            this.dlg = dialog.clone();
            this.dlg.setHeader('Download');
            this.instanceprops = instanceprops;
            this.pmr = pmr;
        },
        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        fetch_blob_url: function () {
            var concatenatedBlob = utils.simpleConcatenateBlobs(this.instanceprops.blobs, this.instanceprops.blobs[0].type);
            var mediaurl = URL.createObjectURL(concatenatedBlob);
            return mediaurl;
        },

        fetch_dialogue_box: function () {
            //this returns html that will be set to the DOM
            return this.dlg.fetch_dialogue_box('download');
        },
        set_dialogue_box: function (dlgbox) {
            //this is the jquery object that is the dlgbox in the DOM
            this.dlgbox = dlgbox;
            this.dlg.set_dialogue_box(dlgbox);

        },
        open: function () {
            var self = this;
            var ip = this.instanceprops;
            var ext = '';
            //make a filename with a 6 digit random number.
            var nowdatetime = (new Date()).toISOString().replace(/[^0-9]/g, "");
            var filename = ip.config.mediatype + '_' + nowdatetime;
            //(Math.round(Math.random() * 899999,0) + 100000);
            utils.doConcatenateBlobs(this.instanceprops.blobs, function (concatenatedBlob) {

                //get blob type
                switch (concatenatedBlob.type) {
                    case 'audio/wav':
                    case 'audio/pcm':
                        ext = '.wav';
                        break;
                    case 'audio/ogg':
                        ext = '.ogg';
                        break;
                    case 'audio/webm':
                    case 'video/webm':
                        ext = '.webm';
                        break;

                    default:
                        var bits = concatenatedBlob.type.split('/');
                        if (bits.length > 1) {
                            ext = '.' + bits[1];
                        } else {
                            //if we ever get here we will just have to save an extension-less file.
                        }
                }
                filename = filename + ext;

                var theurl = URL.createObjectURL(concatenatedBlob);
                var displaytext = M.util.get_string('recui_downloadfile', 'filter_poodll');
                var content = '<div class="filter_poodll_dlgdownload_content">';
                content += '<a href="' + theurl + '" download="' + filename + '" class="btn btn-secondary">' + displaytext + '</a>';
                content += '</div>';
                //set the html to the dialog and DOM
                self.dlg.setContent(content);
                //register events for the select boxes
                self.registerEvents();
                //open the dialog
                self.dlg.open();
            });
        },

        registerEvents: function () {
            //we have no events
        }
    }

});