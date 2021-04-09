define(['jquery','core/log','mod_solo/definitions','core/ajax', 'core/notification'], function($,log, def, ajax, notification) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('solo Updated Selected Topic: initialising');

    return{

        init: function(opts) {

            this.register_events(opts['activityid']);

        },

        register_events: function(activityid){
            var container = $('.' + def.topicscontainer);
            container.on('click', '.' + def.topiccheckbox, function(e) {
             //   e.preventDefault();

                var topicNode = $(this);
                var topicid = topicNode.data('topicid');

                topicNode.addClass('term-loading');
                ajax.call([{
                    'methodname': 'mod_solo_toggle_topic_selected',
                    'args': {
                        'topicid': topicid,
                        'activityid': activityid,
                    }
                }])[0].then(function(result) {
                    if (!result) {
                        return $.Deferred().reject();
                    }
                    topicNode.addClass('term-seen');
                })
                    .fail(notification.exception)
                    .always(function() {
                        topicNode.removeClass('term-loading');
                    });
            });
        },


    all_selected: function() {
        var container = $('.' + defs.topicscontainer);
        return container.find('.' + defs.topiccheckbox).length === container.find('.term.term-seen').length
    }

};//end of return value
});

