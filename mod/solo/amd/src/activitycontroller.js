/* jshint ignore:start */
define(['jquery', 'core/log','mod_solo/definitions'],
    function($, log, def) {

    "use strict"; // jshint ;_;

    log.debug('Activity controller: initialising');

    return {

        cmid: null,
        activitydata: null,
        recorderid: null,
        controls: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function(props){
            var dd = this.clone();

            //pick up opts from html
            var theid='#amdopts_' + props.widgetid;
            var configcontrol = $(theid).get(0);
            if(configcontrol){
                dd.activitydata = JSON.parse(configcontrol.value);
                $(theid).remove();
            }else{
                //if there is no config we might as well give up
                log.debug('solo Controller: No config found on page. Giving up.');
                return;
            }

            dd.cmid = props.cmid;
            dd.process_html();
            dd.register_events();
        },

        register_events: function() {
            var dd = this;

        },

        process_html: function(){
            var opts = this.activitydata;
            //these css classes/ids are all passed in from php in
            //renderer.php::fetch_activity_amd
            var controls ={
                hider: $('.' + opts['hider']),
            };
            this.controls = controls;
        }

    };//end of returned object
});//total end
