define(['jquery', 'core/log', 'mod_solo/dependencyloader', 'theme_boost/popover'], function ($, log) {
    "use strict"; // jshint ;_;

    /*
    This file is to manage the transcript popover
     */

    log.debug('solo Popover helper: initialising');

    return {

        lastitem: false,
        dispose: false, //Bv4 = dispose  Bv3 = destroy
        transcripttitle: M.util.get_string('heard','mod_solo'),

        init: function () {
            this.register_events();
        },

        register_events: function () {
           return;
        },

        //different bootstrap/popover versions have a different word for "dispose" so this method bridges that.
        //we can not be sure what version is installed
        disposeWord: function () {
            if (this.dispose) {
                return this.dispose;
            }
            var version = '3';
            if ($.fn.popover.Constructor.hasOwnProperty('VERSION')) {
                version = $.fn.popover.Constructor.VERSION.charAt(0);
            }
            switch (version) {
                case '4':
                    this.dispose = 'dispose';
                    break;
                case '3':
                default:
                    this.dispose = 'destroy';
                    break;
            }
            return this.dispose;
        },

        remove: function (item) {
            if (item) {
                $(item).popover(this.disposeWord());
            } else if (this.lastitem) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem = false;
            }
        },

        isShowing: function(item){
            if(this.lastitem === item) {
                return true;
            }else{
                return false;
            }
        },

        addTranscript: function (item, transcript) {

            //if we are already showing this item then dispose of it, set last item to null and go home
            if (this.lastitem === item) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem = false;
                return;
            }

            //dispose of previous popover, and remember this one
            if (this.lastitem) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem = false;
            }
            this.lastitem = item;

            //lets add the popover
            $(item).popover({
                title: this.transcripttitle,
                content: transcript,
                trigger: 'manual',
                placement: 'top'
            });
            $(item).popover('show');
        }

    };//end of return value
});