define(['jquery', 'jqueryui', 'core/log'], function ($, jqui, log) {
    "use strict"; // jshint ;_;

    log.debug('Poodll util drag drop: initialising');

    return {

        theelement: false,
        uploadfunction: false,
        uploader: false,
        started: false,
        filebutton: false,

        init: function (element, uploadfunction, ip) {
            this.theelement = element;
            this.uploadfunction = uploadfunction;
            this.uploader = ip.uploader;
            this.filebutton = ip.controlbar.filebutton;

            if (this.supports_dragdrop(element)) {
                log.debug('registering drag drop');
                this.register_dragdrop();
                this.do_visuals();
            } else {
                log.debug('drag drop not supported');
            }
        },
        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },
        supports_dragdrop: function (element) {
            return ('draggable' in element) || ('ondragstart' in element && 'ondrop' in element)
        },

        do_visuals: function () {
            this.theelement.addClass("poodll-can-do-drag-drop");
        },

        turnoff_dragdrop: function () {
            if (!this.theelement) {
                return;
            }
            this.theelement.removeClass("poodll-can-do-drag-drop");
            this.theelement.removeClass('is-poodll-drag-over');
            //replace events with a simple DD canceller (otherwise if we just remove them, and they DD they will leave the page)
            this.theelement.off('drag dragstart dragend dragover dragenter dragleave drop');
            this.theelement.on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        },

        register_dragdrop: function () {

            var that = this;

            this.theelement.on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (that.started) {
                    return;
                }
            }).on('dragover dragenter', function () {
                that.theelement.addClass('is-poodll-drag-over');
            }).on('dragleave dragend drop', function () {
                that.theelement.removeClass('is-poodll-drag-over');
            }).on('drop', function (e) {
                if (that.started) {
                    return;
                }

                var files = e.originalEvent.dataTransfer.files;
                that.uploadfunction(files, that.uploader);

                //remove events
                that.turnoff_dragdrop();
                that.filebutton.off('change');
                that.filebutton.attr('disabled', true);
                that.started = true;

            });
        },

    }
});