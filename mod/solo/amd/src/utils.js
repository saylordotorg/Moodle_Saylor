define(['jquery','core/log','mod_solo/definitions'], function($,log, def) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('solo Utils: initialising');

    return{
        string_the_object: function(obj){
            return btoa(unescape(encodeURIComponent(JSON.stringify(obj))))
        },
        object_the_string: function(str){

            return JSON.parse(decodeURIComponent(escape(atob(str))));
        },
        is_JSON_string: function(str) {
            try {
                JSON.parse(atob(str));
            } catch (e) {
                return false;
            }
            return true;
        },

    };//end of return value
});