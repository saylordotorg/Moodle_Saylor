/**
 * Add a create new group modal to the page.
 *
 * @module     mod_minilesson/modaldeletehelper
 * @class      modaldeletehelper
 * @package    mod_minilesson
 * @copyright  2020 Justin Hunt <poodllsupport@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/ajax','core/notification'],
    function($, log, Str, ModalFactory, ModalEvents, Ajax, Notification) {

        /**
         * Constructor
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @param {int} contextid
         * @param {String} formname The key/name of the form for this instance
         * @param {Object} callback The function to call after successful deletion (for UI updates)
         *
         * Each call to init gets it's own instance of this class.
         */
        var TheForm = function(selector, contextid, formname,callback) {
            this.contextid = contextid;
            this.formname = formname;
            this.callback = callback;

            //this will init on item click
            this.preinit(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        TheForm.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        TheForm.prototype.contextid = -1;

        /**
         * @var {string} itemname
         * @private
         */
        TheForm.prototype.itemname = '';

        /**
         * @var {int} itemid
         * @private
         */
        TheForm.prototype.itemid = -1;

        /**
         * @var {string} formname
         * @private
         */
        TheForm.prototype.formname = '';

        /**
         * @var {string} formname
         * @private
         */
        TheForm.prototype.formmessage = 'Really delete:';

        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @private
         * @return {Promise}
         */
        TheForm.prototype.preinit = function(selector) {
            var triggers = $(selector);
            var dd=this;
            Str.get_string(this.formname , 'mod_minilesson').then(function(title){dd.formtitle=title;});
            Str.get_string(this.formname + '_message' , 'mod_minilesson').then(function(message){dd.formmessage=message;});
            Str.get_string('deletebuttonlabel' , 'mod_minilesson').then(function(deletebuttonlabel){dd.deletebuttonlabel=deletebuttonlabel;});
            $('body').on('click',selector,function(e) {
                //prevent it doing a real click (which will do the non ajax version of a click)
                e.preventDefault();

                var clickedLink = $(e.currentTarget);
                var itemid = clickedLink.data('id');
                dd.itemid = itemid;
                var itemname = clickedLink.data('name');
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: dd.formtitle,
                    body: dd.formmessage + '<i>' + itemname + '</i>',
                })
                    .then(function(modal) {
                        dd.modal = modal;
                        dd.modal.setSaveButtonText(dd.deletebuttonlabel);
                        var root = dd.modal.getRoot();
                        root.on(ModalEvents.save, dd.submitForm.bind(dd));
                        dd.modal.show();
                        return dd.modal;
                    });

            });//end of on click

        };

        /**
         * This triggers a delete form ajax call
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        TheForm.prototype.submitForm = function(e) {
            e.preventDefault();
            var dd = this;
            log.debug('deleting:' + dd.formname );
            Ajax.call([{
                methodname: 'mod_minilesson_delete_item',
                args: {
                    itemid: dd.itemid,
                    contextid: dd.contextid,
                    formname: dd.formname
                },
                done: function (ajaxresult) {
                    var payloadobject = JSON.parse(ajaxresult);

                    if (payloadobject) {
                        log.debug(payloadobject);
                        switch(payloadobject.error) {
                            case false:
                               dd.callback(dd.itemid);
                                break;

                            case true:
                            default:
                                log.debug('that was an error: ');
                        }
                    }
                    dd.modal.hide();
                },
                fail: Notification.exception
            }]);
        };






        return /** @alias module:mod_minilesson/modaldeletehelper */ {
            // Public variables and functions.
            /**
             * Attach event listeners to initialise this module.
             *
             * @method init
             * @param {string} selector The CSS selector used to find nodes that will trigger this module.
             * @param {int} contextid The contextid for the course.
             * @param {string} formname The formname for the course.
             * @param {object} callback The callback on successful deletion (for ui updates)
             * @return {Promise}
             */
            init: function(selector, contextid, formname, callback) {
                return new TheForm(selector, contextid, formname, callback);
            }
        };
    });