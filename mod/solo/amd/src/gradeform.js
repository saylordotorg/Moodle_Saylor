/**
 * Add a create new group modal to the page.
 *
 * @module     mod_solo/gradeform
 * @class      GradeForm
 * @package    mod_solo
 * @copyright  PoodLL
 * @copyright  Based on: 2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log','core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
    function($, log ,Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

        /**
         * Constructor
         *
         * @param {String} selector used to find triggers for the new grade modal.
         * @param {int} contextid
         * @param {String} grademethod (simple or rubric)
         *
         * Each call to init gets it's own instance of this class.
         */
        const GradeForm = function(selector, contextid, grademethod) {
            this.contextid = contextid;
            this.grademethod = grademethod;
            this.preinit(selector);
        };

        /**
         * @let {Modal} modal
         * @private
         */
        GradeForm.prototype.modal = null;

        /**
         * @let {int} contextid
         * @private
         */
        GradeForm.prototype.contextid = -1;

        /**
         * @let {int} contextid
         * @private
         */
        GradeForm.prototype.grademethod = 'simple';


        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new grade modal.
         * @private
         * @return {Promise}
         */
        GradeForm.prototype.preinit = function(selector) {
            var triggers = selector;
            var that = this;

            Str.get_string('dopopupgrade', 'mod_solo').then(function(title){that.formtitle=title;});

            // Fetch the title string.
            $(triggers).on('click', function(e){
                e.preventDefault();

                that.studentid = $(this).attr('data-student-id');
                that.cmid = $(this).attr('data-cm-id');

                // Create the modal.
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: that.formtitle + $(this).attr('data-student-name'),
                    body: that.getBody()
                }).then(function(modal) {
                    // Keep a reference to the modal.
                    that.modal = modal;

                    // Forms are big, we want a big modal.
                    that.modal.setLarge();

                    // We want to reset the form every time it is opened.
                    that.modal.getRoot().on(ModalEvents.hidden, function () {
                        that.modal.setBody(that.getBody());
                    }.bind(that));

                    // We want to hide the submit buttons every time it is opened.
                    that.modal.getRoot().on(ModalEvents.shown, function () {
                        that.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                    });


                    // We catch the modal save event, and use it to submit the form inside the modal.
                    // Triggering a form submission will give JS validation scripts a chance to check for errors.
                    that.modal.getRoot().on(ModalEvents.save, that.submitForm.bind(that));
                    // We also catch the form submit event and use it to submit the form with ajax.
                    that.modal.getRoot().on('submit', 'form', that.submitFormAjax.bind(that));
                    that.modal.show();
                    return that.modal;
                });//end of then(function(modal)
            });//end of click event
        };


        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new grade modal.
         * @private
         * @return {Promise}
         */
        GradeForm.prototype.init = function(selector) {
            var triggers = selector;
            this.studentid = $(selector).attr('data-student-id');
            this.cmid = $(selector).attr('data-cm-id');

            // Fetch the title string.
            return Str.get_string('creategroup', 'core_group').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: this.getBody()
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // Forms are big, we want a big modal.
                this.modal.setLarge();

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody());
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));


                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                return this.modal;
            }.bind(this));
        };

        /**
         * @method getBody
         * @private
         * @return {Promise}
         */
        GradeForm.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }
            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata)};
            params.studentid = this.studentid;
            params.cmid = this.cmid;
            var theform = 'simple_grade_form';
            if(this.grademethod==='rubric'){
                theform = 'rubric_grade_form';
            }


            return Fragment.loadFragment(
                'mod_solo',
                theform,
                this.contextid,
                params);
        };

        /**
         * @method handleFormSubmissionResponse
         * @private
         * @return {Promise}
         */
        GradeForm.prototype.handleFormSubmissionResponse = function() {
            this.modal.hide();

            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });

         //   $("[data-original-student]").trigger('change');

            var element = $(".card[data-original-student='" + this.studentid + "']");
            var promises = Ajax.call([
                { methodname: 'mod_solo_get_grade_submission', args: {  userid: this.studentid, cmid: this.cmid } }]);

            promises[0].done(function(response) {
                log.debug('promises-done');
                log.debug(response);
                if(response && response.response[0]) {
                    log.debug(response.response[0]);
                    log.debug(element);
                    $(element).find('.chatgrade').html(response.response[0].grade);
                    $(element).find('.chatfeedback').html(response.response[0].feedback);
                }
            }).fail(function(ex) {
                // @todo do something with the exception
            });


        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         * @return {Promise}
         */
        GradeForm.prototype.handleFormSubmissionFailure = function(data) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody(data));
            $("[data-original-student]").trigger('change');
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         * @private
         * @param {Event} e Form submission event.
         */
        GradeForm.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var changeEvent = document.createEvent('HTMLEvents');
            changeEvent.initEvent('change', true, true);

            // Prompt all inputs to run their validation functions.
            // Normally this would happen when the form is submitted, but
            // since we aren't submitting the form normally we need to run client side
            // validation.
            this.modal.getRoot().find(':input').each(function(index, element) {
                element.dispatchEvent(changeEvent);
            });

            // Now the change events have run, see if there are any "invalid" form fields.
            var invalid = $.merge(
                this.modal.getRoot().find('[aria-invalid="true"]'),
                this.modal.getRoot().find('.error')
            );

            // If we found invalid fields, focus on the first one and do not submit via ajax.
            if (invalid.length) {
                invalid.first().focus();
                return;
            }

            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form').serialize();
            var methodName ='mod_solo_submit_simple_grade_form';
            if(this.grademethod==='rubric'){
                methodName ='mod_solo_submit_rubric_grade_form';
            }

            // Now we can continue...
            Ajax.call([{
                methodname: methodName,
                args: {
                    contextid: this.contextid,
                    jsonformdata: JSON.stringify(formData),
                    studentid: parseInt(this.studentid) ? parseInt(this.studentid) : 0,
                    cmid: parseInt(this.cmid) ? parseInt(this.cmid) : 0
                },
                done: this.handleFormSubmissionResponse.bind(this, formData),
                fail: this.handleFormSubmissionFailure.bind(this, formData)
            }]);

            this.modal.hide();
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        GradeForm.prototype.submitForm = function(e) {
            e.preventDefault();
            this.modal.getRoot().find('form').submit();
        };

        return /** @alias module:mod_solo/gradeform */ {
            // Public variables and functions.
            /**
             * Attach event listeners to initialise this module.
             *
             * @method init
             * @param {string} selector The CSS selector used to find nodes that will trigger this module.
             * @param {int} contextid The contextid for the course.
             * @param {String} grademethod simple or rubric
             * @return {Promise}
             */
            init: function(selector, contextid, grademethod) {
                return new GradeForm(selector, contextid, grademethod);
            }
        };
    });