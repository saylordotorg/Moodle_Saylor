define(['jquery', 'core/config', 'core/notification'], function($, config, notification) {
    return {
        init: function() {
            $(".algebra_answer").on('input paste keyup', null, null, function() {
                // Convert answer id to valid javascript name.
                var id = $(this).attr('id');
                var display = id.replace(':', '_');
                var params = {
                    vars: $('#' + display + '_vars').html(),
                    expr: $(this).val(),
                    sesskey: config.sesskey,
                };
                $.post(config.wwwroot + '/question/type/algebra/ajax.php', params, null, 'json')
                    .done(function(data) {
                        // Replace TeX form in page.
                        var displaydiv = $('#' + display + '_display');
                        displaydiv.html("<span class=\"filter_mathjaxloader_equation\">" + data +"</span>");
                        // Notify the filters about the modified node.
                        require(['core/event'], function(event) {
                            event.notifyFilterContentUpdated(displaydiv);
                        });
                    })
                    .fail(function(jqXHR, status, error) {
                        notification.exception(error);
                    });
            });
        }
    };
});