define(['jquery', 'core/log','mod_solo/definitions','core/str','core/ajax','core/notification'], function ($, log, def, str, Ajax, notification) {
    "use strict"; // jshint ;_;
    /*
    This file does small report
     */

    log.debug('Small Report Helper: initialising');

    return {
        //controls
        controls: {},
        ready: false,
        remotetranscribe: false,
        attemptid: 0,
        checking: '... checking ...',
        secstillcheck: 'Checking again in: ',
        notgradedyet: 'Your reading has not been evaluated yet.',
        evaluated: 'Your reading has been evaluated.',

        //init the module
        init: function(attemptid){
            this.attemptid=attemptid;
            this.ready=false;
            this.init_strings();
            this.register_controls();
            this.register_events();
            if(!this.ready && this.attemptid){
                this.check_for_results(this,15);
            }
        },

        init_strings: function(){
          var that =this;
          str.get_string('checking','mod_solo').done(function(s){that.checking=s;});
          str.get_string('secs_till_check','mod_solo').done(function(s){that.secstillcheck=s;});
          str.get_string('notgradedyet','mod_solo').done(function(s){that.notgradedyet=s;});
          str.get_string('evaluatedmessage','mod_solo').done(function(s){that.evaluated=s;});
        },

        //load all the controls so we do not have to do it later
        register_controls: function(){
            this.controls.placeholdertext = $('.' + def.smallreportplaceholdertext);
            this.controls.placeholderspinner = $('.' + def.smallreportplaceholderspinner);
        },

        //attach the various event handlers we need
        register_events: function() {
            var that = this;
        },//end of register events

        check_for_results: function (that, seconds) {

            //decrement 1 s. At 15 seconds do the check
            seconds=seconds-1;
            if(seconds>0){
                setTimeout(that.check_for_results,1000,that,seconds);
                var formattedSeconds = (" 0" + seconds).slice(-2);
                that.controls.placeholdertext.html(that.secstillcheck + '<br>' + formattedSeconds );
                return;
            }

            //do the check
            that.controls.placeholdertext.text(that.checking);
            that.controls.placeholderspinner.show();
            Ajax.call([{
                methodname: 'mod_solo_check_for_results',
                args: {
                    attemptid: that.attemptid
                },
                done: function (ajaxresult) {
                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        switch (payloadobject.ready) {
                            case true:
                                location.reload();
                                break;

                            case false:
                            default:
                                log.debug('result not fetched');
                                setTimeout(that.check_for_results,1000,that,15 );
                                that.controls.placeholdertext.text(that.secstillcheck + seconds);
                        }
                    }
                    that.controls.placeholderspinner.hide();
                },
                fail: notification.exception
            }]);
        },

    };//end of return value
});