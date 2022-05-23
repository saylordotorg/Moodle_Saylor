define(['jquery', 'core/log'], function ($,  log) {
    "use strict"; // jshint ;_;

    log.debug('Poodll Dialog: initialising');

    return {
        dlgbox: null,
        headertext: '',

        init: function () {

        },
        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        setHeader: function (headertext) {
            this.headertext = headertext;
        },
        setContent: function (content) {
            this.clear();
            this.dlgbox.append(content);
        },
        clear: function () {
            this.dlgbox.children().last().remove();
        },

        open: function () {
            this.dlgbox.slideDown(400);
        },
        close: function () {
            var self = this;
            this.dlgbox.slideUp(400);
        },

        onclose: function () {

        },

        test: function () {
            log.debug('hio');
        },
        fetch_dialogue_box: function (dialogtype) {
            var ret = '<div class="poodll_dialogue_box poodll_dialogue_box_' + dialogtype + ' hide"><div class="poodll_close_modal">';
            ret += '<i class="fa fa-window-close" aria-hidden="true"></div></i>';
            ret += '<div class="poodll_dialogue_content"><strong>' + this.headertext + '</strong></div>';
            ret += '<div class="will_be_cleared"></div></div>';
            return ret;
        },
        set_dialogue_box: function (dlgbox) {
            this.dlgbox = dlgbox;
            var that = this;

            dlgbox.find('.poodll_close_modal').click(function () {
                that.close();
            });
        },
    }
});