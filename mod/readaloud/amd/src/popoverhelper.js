define(['jquery', 'core/log', 'mod_readaloud/definitions', 'mod_readaloud/dependencyloader', 'theme_boost/popover'], function ($, log, def) {
    "use strict"; // jshint ;_;

    /*
    This file is to
     */

    log.debug('Read Aloud Popover helper: initialising');

    return {

        lastitem: false,
        okbuttonclass: def.okbuttonclass,
        ngbuttonclass: def.ngbuttonclass,
        quickgradecontainer: def.quickgradecontainerclass,
        quickgradetitle: M.util.get_string('quickgrade', def.component),
        transcripttitle: M.util.get_string('transcript', def.component),
        oklabel: M.util.get_string('ok', def.component),
        nglabel: M.util.get_string('ng', def.component),
        dispose: false, //Bv4 = dispose  Bv3 = destroy

        init: function () {
            this.register_events();
        },

        register_events: function () {
            $(document).on('click', '.' + this.okbuttonclass, this.onAccept);
            $(document).on('click', '.' + this.ngbuttonclass, this.onReject);
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

        addQuickGrader: function (item) {

            //dispose of previous popover, and remember this one
            if (this.lastitem && this.lastitem !== item) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem = false;
            }
            this.lastitem = item;
            var that = this;

            var thefunc = function () {
                var wordnumber = $(this).attr("data-wordnumber");
                var okbutton = "<button type='button' class='btn " + that.okbuttonclass + "' data-wordnumber='" + wordnumber + "'><i class='fa fa-check'></i> " + that.oklabel + "</button>";
                var ngbutton = "<button type='button' class='btn " + that.ngbuttonclass + "' data-wordnumber='" + wordnumber + "'><i class='fa fa-close'></i> " + that.nglabel + "</button>";
                var container = "<div class='" + that.quickgradecontainerclass + "'>" + okbutton + ngbutton + "</div>";
                return container;
            };

            //lets add the popover
            $(item).popover({
                title: this.quickgradetitle,
                content: thefunc,
                trigger: 'manual',
                placement: 'top',
                html: true
            });
            $(item).popover('show');
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
        },

        //these two functions are overridden by the calling class
        onAccept: function () {
            alert($(this).attr('data-wordnumber'));
        },
        onReject: function () {
            alert($(this).attr('data-wordnumber'));
        }

    };//end of return value
});