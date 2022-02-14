/**
 * Add a modal to manage question adding and editing to the page.
 *
 * @module     mod_minilesson/duplicateitemhelper
 * @class      duplicateitemhelper
 * @package    mod_minilesson
 * @copyright  2020 Justin Hunt <poodllsupport@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log','core/str', 'core/ajax'],
    function($, log,Str, Ajax) {

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

            //this will init on item click (better for lots of items)
            this.init(selector);
        };

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

        TheForm.prototype.direction = 'none';



        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @private
         * @return {Promise}
         */
        TheForm.prototype.init = function(selector) {

            var dd=this;

            $('body').on('click',selector,function(e) {
                //prevent it doing a real click (which will do the non ajax version of a click)
                e.preventDefault();
                dd.itemid=$(this).data('id');

                // Now we can continue...
                Ajax.call([{
                    methodname: 'mod_minilesson_duplicate_item',
                    args: {contextid: dd.contextid, itemid: dd.itemid},
                    done: dd.callback
                }]);


            });//end of on click

        };

        return /** @alias module:mod_minilesson/modalformhelper */ {
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