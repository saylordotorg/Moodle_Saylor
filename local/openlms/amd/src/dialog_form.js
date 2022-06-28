// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This code is based on core/fragment, it fetches form html markup and javascript
 * from legacy form page.
 *
 * This code is based on modal_form by Mitxel Moriana.
 *
 * @module     local_openlms/dialog_form
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/notification',
    'core/yui',
    'core/event',
    'core/str',
    'core/log'
], function($, ModalFactory, ModalEvents, Ajax, Notification, Y, Event, Str, Log) {
    var unloading = false;

    /**
     * Shows the legacy form inside a modal dialogue
     *
     * @param {Object} config Parameters for the list.
     *
     * @property {Object} config dialog form configuration
     * @property {Object} config.modalConfig Modal configuration.
     * @property {string} config.formUrl Legacy form URL.
     * @property {element} config.triggerElement trigger element for a modal form
     * @property {string} config.afterSubmit 'reload', 'redirect' on 'nothing' after successful form submit
     */
    var DialogForm = function(config) {
        this.config = config;
        this.config.modalConfig = this.config.modalConfig || {};
        this.config.modalConfig.type = ModalFactory.types.CANCEL;
        this.init();
    };

    /**
     * @var {Object} config
     */
    DialogForm.prototype.config = {};

    /**
     * @var {Modal} modal
     */
    DialogForm.prototype.modal = null;

    /**
     * Initialise the class.
     *
     * @private
     */
    DialogForm.prototype.init = function() {
        var requiredStrings = [
            {key: 'collapseall', component: 'moodle'},
            {key: 'expandall', component: 'moodle'}
        ];

        // Ensure strings required for shortforms are always available.
        M.util.js_pending('local_openlms_dialog_form_init');
        Str.get_strings(requiredStrings)
            .then(function() {
                // We don't attach trigger element to modal here to avoid MDL-70395.
                // We normally initialise DialogForm as result of some event
                // on trigger element, so new listener is not required.
                return ModalFactory.create(this.config.modalConfig);
            }.bind(this))
            .then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // We need to make sure that the modal already exists when we render the form. Some form elements
                // such as date_selector inspect the existing elements on the page to find the highest z-index.
                this.modal.setBody(this.getFormHtml(''));

                // Forms are big, we want a big modal.
                this.modal.setLarge();

                // After successful submit, when we press "Cancel" or close the dialogue by clicking on X in the top right corner.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    // Notify listeners that the form is about to be submitted (this will reset atto autosave).
                    Event.notifyFormSubmitAjax(this.modal.getRoot().find('form')[0], true);

                    // Destroy the modal.
                    this.modal.destroy();

                    // Reset form-change-checker.
                    this.resetDirtyFormState();

                    // Focus on the trigger element that actually launched the modal.
                    if (this.config.triggerElement !== null) {
                        this.config.triggerElement.focus();
                    }
                }.bind(this));

                // Add the class to the modal dialogue.
                this.modal.getModal().addClass('local_openlms-dialog_form');

                // NOTE: There does not seem to be an easy way to remove the footer,
                //       so for now it is just hidden using CSS hack.

                // We catch the press on non-submitting buttons in the forms.
                this.modal.getRoot().on('click', 'form input[type=submit][data-no-submit]', this.noSubmitButtonPressed.bind(this));

                // Special exception for Cancel button, no need to send the cancel data to server, just close the dialog.
                this.modal.getRoot().on('click', 'form input[type=submit][data-cancel]', this.cancelButtonPressed.bind(this));

                // We catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                this.onInit();

                this.modal.show();

                M.util.js_complete('local_openlms_dialog_form_init');
                return this.modal;
            }.bind(this))
            .fail(Notification.exception);
    };

    /**
     * On initialisation of a modal dialogue. Caller may override.
     */
    DialogForm.prototype.onInit = function() {
        // There is no need to register ModelEvents here, we use mforms buttons for everything.
    };

    /**
     * @param {string} formData serialised form data
     * @method getFormHtml
     * @private
     * @return {Promise}
     */
    DialogForm.prototype.getFormHtml = function(formData) {
        var promise = $.Deferred();
        M.util.js_pending('local_openlms_dialog_form_html');
        fetchFormHtml(this.config.formUrl, formData)
            .then(function(response) {
                if (response.dialog_form === 'render') {
                    promise.resolve(response.html, processCollectedJavascript(response.javascript));
                    M.util.js_complete('local_openlms_dialog_form_html');
                } else {
                    promise.reject(new Error('Dialog form rendering error'));
                }
                return null;
            }.bind(this))
            .fail(function(ex) {
                promise.reject(ex);
            }.bind(this));

        return promise.promise();
    };

    /**
     * On form submit. Caller may override
     *
     * @param {Object} response Response received from the form's "process" method
     * @return {Object}
     */
    DialogForm.prototype.onSubmitSuccess = function(response) {
        // By default this function does nothing.
        // Return here is irrelevant, it is only present to make eslint happy.
        return response;
    };

    /**
     * On form validation error. Caller may override
     *
     * @return {mixed}
     */
    DialogForm.prototype.onValidationError = function() {
        // By default this function does nothing.
        // Return here is irrelevant, it is only present to make eslint happy.
        return undefined;
    };

    /**
     * Reset "dirty" form state (warning if there are changes)
     */
    DialogForm.prototype.resetDirtyFormState = function() {
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
    };

    /**
     * Click on a "submit" button that is marked in the form as registerNoSubmitButton()
     *
     * @method submitButtonPressed
     * @private
     * @param {Event} e Form submission event.
     */
    DialogForm.prototype.noSubmitButtonPressed = function(e) {
        e.preventDefault();

        Event.notifyFormSubmitAjax(this.modal.getRoot().find('form')[0], true);

        // Add the button name to the form data and submit it.
        var formData = this.modal.getRoot().find('form').serialize(),
            el = $(e.currentTarget);
        formData = formData + '&' + encodeURIComponent(el.attr('name')) + '=' + encodeURIComponent(el.attr('value'));
        this.modal.setBody(this.getFormHtml(formData));
    };

    /**
     * Click on a "Cancel" form button.
     *
     * @method cancelButtonPressed
     * @private
     * @param {Event} e Form submission event.
     */
    DialogForm.prototype.cancelButtonPressed = function(e) {
        e.preventDefault();

        this.disableButtons();
        this.modal.hide();
    };

    /**
     * Validate form elements
     * @return {boolean} true if client-side validation has passed, false if there are errors
     */
    DialogForm.prototype.validateElements = function() {
        Event.notifyFormSubmitAjax(this.modal.getRoot().find('form')[0]);

        // Now the change events have run, see if there are any "invalid" form fields.
        var invalid = $.merge(
            this.modal.getRoot().find('[aria-invalid="true"]'),
            this.modal.getRoot().find('.error')
        );

        // If we found invalid fields, focus on the first one and do not submit via ajax.
        if (invalid.length) {
            invalid.first().focus();
            return false;
        }

        return true;
    };

    /**
     * Disable form buttons during form submission
     */
    DialogForm.prototype.disableButtons = function() {
        this.modal.getBody().find('form input[type=submit]').attr('disabled', true);
    };

    /**
     * Enable form buttons after form submission (on validation error)
     */
    DialogForm.prototype.enableButtons = function() {
        this.modal.getBody().find('form input[type=submit]').removeAttr('disabled');
    };

    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     */
    DialogForm.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();

        // If we found invalid fields, focus on the first one and do not submit via ajax.
        if (!this.validateElements()) {
            return;
        }
        this.disableButtons();

        // Convert all the form elements values to a serialised string.
        // Note that the button itself is not added, this may cause problems on
        // forms with multiple submit buttons.
        var formData = this.modal.getRoot().find('form').serialize();

        M.util.js_pending('local_openlms_dialog_form_submit');
        fetchFormHtml(this.config.formUrl, formData)
            .then(function(response) {
                if (response.dialog_form === 'render') {
                    // Form was not accepted, it could be either because validation failed.
                    var promise = $.Deferred();
                    promise.resolve(response.html, processCollectedJavascript(response.javascript));
                    this.modal.setBody(promise.promise());
                    this.enableButtons();
                    this.onValidationError();
                    M.util.js_complete('local_openlms_dialog_form_submit');
                } else if (response.dialog_form === 'submitted') {
                    // Form was submitted properly. Hide the modal and execute callback.
                    this.modal.hide();
                    this.onSubmitSuccess(response);
                    M.util.js_complete('local_openlms_dialog_form_submit');
                    // Now either reload page or follow the redirect from form.
                    if (this.config.afterSubmit === 'reload') {
                        window.location.reload();
                    } else if (this.config.afterSubmit === 'redirect') {
                        window.location = response.redirecturl;
                    } else {
                        // Value 'nothing' relies on overriding of onSubmitSuccess via mustache template.
                    }
                }
                return null;
            }.bind(this))
            .fail(function(exception) {
                // NOTE: do not enable buttons here, there is no correct way to recover
                // from unexpected errors.
                Notification.exception(exception);
            }.bind(this));
    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks
     * before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    DialogForm.prototype.submitForm = function(e) {
        e.preventDefault();
        this.modal.getRoot().find('form').submit();
    };

    /**
     * Converts the JS that was received from collecting JS requirements on the $PAGE so it can be added to the existing page.
     *
     * Copied from core/fragment
     *
     * @param {string} js
     * @return {string}
     */
    const processCollectedJavascript = function(js) {
        var jsNodes = $(js);
        var allScript = '';
        jsNodes.each(function(index, scriptNode) {
            scriptNode = $(scriptNode);
            var tagName = scriptNode.prop('tagName');
            if (tagName && (tagName.toLowerCase() === 'script')) {
                if (scriptNode.attr('src')) {
                    // We only reload the script if it was not loaded already.
                    var exists = false;
                    $('script').each(function(index, s) {
                        if ($(s).attr('src') === scriptNode.attr('src')) {
                            exists = true;
                        }
                        return !exists;
                    });
                    if (!exists) {
                        allScript += ' { ';
                        allScript += ' node = document.createElement("script"); ';
                        allScript += ' node.type = "text/javascript"; ';
                        allScript += ' node.src = decodeURI("' + encodeURI(scriptNode.attr('src')) + '"); ';
                        allScript += ' document.getElementsByTagName("head")[0].appendChild(node); ';
                        allScript += ' } ';
                    }
                } else {
                    allScript += ' ' + scriptNode.text();
                }
            }
        });
        return allScript;
    };


    /**
     * Loads an HTML fragment from legacy form URL.
     *
     * @method fetchFormHtml
     * @param {string} formUrl Legacy form URL.
     * @param {string} formData serialised form data
     * @return {Promise} JQuery promise object resolved when the fragment has been loaded.
     */
    var fetchFormHtml = function(formUrl, formData) {
        $(window).bind('beforeunload', function() {
            unloading = true;
        });

        var promise = $.Deferred();
        var settings = {
            type: 'POST',
            dataType: 'json',
            processData: false,
            async: true,
            headers: {'x-legacy-dialog-form-request': '1'},
            data: formData,
        };

        $.ajax(formUrl, settings)
            .done(function(response) {
                if (typeof response === 'undefined') {
                    promise.reject(new Error('Unreadable server response'));
                } else if (typeof response.error !== 'undefined') {
                    // Must be a Moodle ajax error.
                    promise.reject(response);
                } else if (typeof response.data !== 'undefined' && typeof response.data.dialog_form !== 'undefined') {
                    promise.resolve(response.data);
                } else {
                    promise.reject(new Error('Invalid server response'));
                }
            })
            .fail(function(jqXHR, textStatus, exception) {
                if (unloading) {
                    Log.error("Page unloaded.");
                    Log.error(exception);
                } else {
                    promise.reject(exception);
                }
            });

        return promise;
    };

    return DialogForm;
});
