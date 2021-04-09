define(['jquery','core/log','mod_solo/definitions', 'core/notification'],
    function($,log, def,notification) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('solo Toggle Selected: initialising');

    return{

        init: function(opts) {
            this.register_events(opts['container'],opts['item'],opts['updatecontrol'],opts['mode'], opts['maxchecks']);
            this.init_controls(opts['container'],opts['item'],opts['updatecontrol'],opts['mode']);
        },

        init_controls: function(container, item, control,mode){
            var selectedvalue = $('[name="' + control + '"]').val();
            if(selectedvalue) {
                //we could differentiate here between radio and checkbox modes, but split works for both
                var selections = selectedvalue.split(',');
                for(var i =0; i<selections.length;i++) {
                    $('.' + container + ' .' + item).filter('[data-id=' + selections[i] + ']').addClass('active');
                }
            }
        },

        register_events: function(container,item,control, mode, maxchecks){
            var thecontainer = $('.' + container);
            thecontainer.on('click', '.' + item, function(e) {

                //we set the new value AND trigger an event in case we are listening for one elsewhere
                var updatecontrol =  $('[name="' + control + '"]');
                var clickednode = $(this);
                var newdataid = clickednode.data('id');
                switch(mode){
                    case 'checkbox':

                        var currentvalues =updatecontrol.val().split(',');
                        if(currentvalues.includes(newdataid.toString())){
                            currentvalues.splice(currentvalues.indexOf(newdataid.toString()),1);
                        }else{
                            if(currentvalues.length < maxchecks) {
                                currentvalues.push(newdataid);
                            }else{
                                e.preventDefault();
                                return;
                            }
                        }

                        //clean up any empty values that somehow get in, then set it
                        currentvalues = currentvalues.filter(function(e){return e});
                        updatecontrol.val(currentvalues.join(','));
                        break;

                    case 'radio':
                    default:
                        updatecontrol.val(newdataid);

                }
               updatecontrol.trigger('change');
            });
        }

};//end of return value

});

