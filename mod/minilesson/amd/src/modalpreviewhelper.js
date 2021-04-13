/**
 * Add a modal to manage question adding and editing to the page.
 *
 * @module     mod_minilesson/modalpreviewhelper
 * @class      modalpreviewhelper
 * @package    mod_minilesson
 * @copyright  2020 Justin Hunt <poodllsupport@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log','core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
    function($, log,Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

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
        var TheForm = function(selector, contextid, callback) {
            this.contextid = contextid;
            this.callback = callback;

            //this will init on page load (good if just one or two items)
            //this.init(selector);

            //this will init on item click (better for lots of items)
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
         * @var {int} itemid
         * @private
         */
        TheForm.prototype.itemid = -1;



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

            $('body').on('click',selector,function(e) {
                //prevent it doing a real click (which will do the non ajax version of a click)
                e.preventDefault();

                dd.itemid=$(this).data('id');

                ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                 //   title: dd.questiontitle,
                    body: dd.getBody({})
                }).then(function (modal) {
                    // Keep a reference to the modal.
                    dd.modal = modal;
                    //Str.get_string(dd.formname , 'mod_minilesson').then(function(title){dd.formtitle=title;dd.modal.setTitle(dd.formtitle);});


                    //questions are big, we want a big modal.
                    dd.modal.setLarge();


                    // We want to call our callback to do any housekeeping after the form is closed
                    dd.modal.getRoot().on(ModalEvents.hidden, function() {
                        dd.callback();
                    }.bind(dd));

                    // We want to hide the next button every time it is opened.
                    dd.modal.getRoot().on(ModalEvents.shown, function () {
                        //hide next button and progress bar
                        dd.modal.getRoot().append('<style>.minilesson_nextbutton { display: none ! important; }</style>');
                        dd.modal.getRoot().append('<style>.minilesson_quiz_progress { display: none ! important; }</style>');
                    });


                    dd.modal.show();
                    return dd.modal;
                });

            });//end of on click

        };


        /**
         * @method getBody
         * @private
         * @return {Promise}
         */
        TheForm.prototype.getBody = function() {

            // Get the content of the modal.
            var params = {itemid: this.itemid};
            return Fragment.loadFragment('mod_minilesson', 'preview', this.contextid, params);

        };





        return /** @alias module:mod_minilesson/modalpreviewhelper */ {
            // Public variables and functions.
            /**
             * Attach event listeners to initialise this module.
             *
             * @method init
             * @param {string} selector The CSS selector used to find nodes that will trigger this module.
             * @param {int} contextid The contextid for the course.
             * @param {function} callback The callback.
             * @return {Promise}
             */
            init: function(selector, contextid, callback) {
                return new TheForm(selector, contextid, callback);
            }
        };
    });