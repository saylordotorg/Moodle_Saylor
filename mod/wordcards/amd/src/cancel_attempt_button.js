define(['jquery', 'core/ajax', 'core/notification','core/modal_factory','core/str','core/modal_events', 'mod_wordcards/a4e'],
    function($, Ajax, Notification,ModalFactory, str, ModalEvents, a4e) {
  "use strict"; // jshint ;_;

  return {

    strings: {},

    init: function(opts) {

		//init strings
		this.init_strings();
		//register events
		this.register_events();

	},

	register_events: function() {
		var container = $('.mod_wordcards_cancelattempt_cont');
		var btn = container.find('.cancel_attempt_button');
		var that = this;
		btn.click(function(e) {
			e.preventDefault();
			var buttonhref= $(this).data('href');

			//confirm cancel and maybe proceed
			ModalFactory.create({
				type: ModalFactory.types.SAVE_CANCEL,
				title: that.strings.cancelattempttitle,
				body: that.strings.cancelattemptbody
			})
				.then(function(modal) {
					modal.setSaveButtonText(that.strings.cancelattempt);
					var root = modal.getRoot();
					root.on(ModalEvents.save, function() {
						window.location.href = buttonhref;
					});
					modal.show();
				});

		});
	},

    init_strings: function(){
        var that = this;
        // set up strings
        str.get_strings([
            {"key": "cancelattempttitle",       "component": 'mod_wordcards'},
            {"key": "cancelattemptbody",           "component": 'mod_wordcards'},
            {"key": "cancelattempt",           "component": 'mod_wordcards'}

        ]).done(function(s) {
            var i = 0;
            that.strings.cancelattempttitle = s[i++];
            that.strings.cancelattemptbody = s[i++];
            that.strings.cancelattempt = s[i++];
        });
    }

  }

});
