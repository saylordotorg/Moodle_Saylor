define(['jquery','core/log','theme_boost/tether'], function($,log,Tether) {
    "use strict"; // jshint ;_;

/*
This file is a dependency of loader that is called by popover to ensure correct things are loaded in the right sequence
 */

    log.debug('MiniLesson deps: initialising');

    window.Tether = Tether;
    return{ };//end of return value
});