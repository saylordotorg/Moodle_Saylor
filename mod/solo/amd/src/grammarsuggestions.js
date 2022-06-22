define(['jquery', 'core/log','mod_solo/definitions','core/str','core/ajax','core/notification','mod_solo/correctionsmarkup'],
    function ($, log, def, str, Ajax, notification,correctionsmarkup) {
    "use strict"; // jshint ;_;
    /*
    This file does small report
     */

    log.debug('Grammar suggestions: initialising');

    return {
        //controls
        controls: {},
        ready: false,
        activityid: 0,
        checking: '... checking ...',
        nosuggestions: 'No suggestions',

        //init the module
        init: function(activityid){
            this.activityid=activityid;
            this.ready=false;
            this.init_strings();
            this.register_controls();
            this.register_events();
        },

        init_strings: function(){
          var that =this;
          str.get_string('checking','mod_solo').done(function(s){that.checking=s;});
          str.get_string('nosuggestions','mod_solo').done(function(s){that.nosuggestions=s;});
        },

        //load all the controls so we do not have to do it later
        register_controls: function(){
            this.controls.grammarsuggestionscont = $('#' + def.grammarsuggestionscont);
            this.controls.checkgrammarbutton = $('.' + def.checkgrammarbutton);
            this.controls.selftranscript = $("textarea[name='selftranscript']");
        },

        //attach the various event handlers we need
        register_events: function() {
            var that = this;
            that.controls.checkgrammarbutton.click(function(e){
                that.check_grammar(that);
                return false;
            });
        },//end of register events

        check_grammar: function (that) {

            //do the check
            var text = that.controls.selftranscript.val();
            //but quit if its empty
            if(!text || text==='' || text.trim()===''){
                return;
            }
            that.controls.grammarsuggestionscont.text(that.checking);
            Ajax.call([{
                methodname: 'mod_solo_check_grammar',
                args: {
                    activityid: that.activityid,
                    text: text

                },
                done: function (ajaxresult) {

                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        if(payloadobject.grammarerrors.length<3){
                            //hacky but fast way to flag no errors
                            that.controls.grammarsuggestionscont.text(that.nosuggestions);
                        }else{
                            that.controls.grammarsuggestionscont.html(payloadobject.suggestions);
                            var opts = [];
                            opts['sessionerrors'] = payloadobject.grammarerrors;
                            opts['sessionmatches'] = payloadobject.grammarmatches;
                            correctionsmarkup.init(opts);
                        }

                    }else{
                        //something went wrong, user does not really need to know details
                        that.controls.grammarsuggestionscont.text(that.nosuggestions);
                        log.debug('result not fetched');
                    }

                },
                fail: notification.exception
            }]);
        },

    };//end of return value
});