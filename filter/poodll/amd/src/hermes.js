/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Hermes (the messenger) initialising');

    //posts messages back to the parent frame.
    //if it is allowed then we are good. Right?

    return {

        allowedURL: '',
        id: '',
        iframeembed: false,
        enabled: false,
        eventListeners: false,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //init
        init: function (id, allowedURL, iframeembed) {
            //the id tag is passed in initially passed in as $config->id from poodlltools::fetchAMDRecorderCode
            //or from data-id in cloudpodll iframe. This allows the receiving code to know which recorder generated event
            this.allowedURL = allowedURL;
            this.id = id;
            this.iframeembed = iframeembed;
            this.enabled = true;
            this.eventListeners = new Array();
            this.registerEvents();
        },

        disable: function () {
            this.enabled = false;
        },

        enable: function () {
            this.enabled = true;
        },

        //this registers handlers for message events of a type from around the recorder
        on: function(type,handler){
            var listener = new Object();
            listener.type = type;
            listener.handler = handler;
            this.eventListeners.push(listener);
        },

        //this handles the sending of message events recevied from parent around the recorder
        sendEvent: function(event) {
            for (var i = 0; i < this.eventListeners.length; i++) {
                if (event.type == this.eventListeners[i].type) {
                    this.eventListeners[i].handler(event);
                }
            }
        },

        //This posts a message to the parent page
        postMessage: function (messageObject) {
            if (!this.enabled) {
                return;
            }

            if (!messageObject.hasOwnProperty('type')) {
                log.debug('All message objects must have at least the "type" property');
                return;
            }
            if (this.iframeembed) {
                messageObject.id = this.id;
                window.parent.postMessage(messageObject, this.allowedURL);
            }
        },

        //We only listen to the message event from parent page
        //if its valid, we pass it on to registered handlers within the recorder
        registerEvents: function(){
            //register our receive message handler
            var that = this;
            window.addEventListener('message', function (e) {
                // Must be our parent
                if ((that.allowedURL==null) || that.allowedURL.indexOf(e.origin) !== 0) {
                    return;
                }

                //process data and if it is valid do the action
                var data = e.data;
                if (data && data.hasOwnProperty('id') &&
                    data.id == that.id && data.hasOwnProperty('type')) {
                    that.sendEvent(e.data);
                }
            });
        }
    };//end of returned object
});//total end
