define(['jquery', 'core/ajax', 'core/notification','core/modal_factory','core/str','core/modal_events', 'mod_wordcards/a4e'],
    function($, Ajax, Notification,ModalFactory, str, ModalEvents, a4e) {
  "use strict"; // jshint ;_;

  return {

    strings: {},

    init: function(opts) {

        var that = this;

        //init strings
        this.init_strings();

        //pick up opts from html
        var theid = '#' + opts['widgetid'];
        var propscontrol = $(theid).get(0);
        if (propscontrol) {
            var props = JSON.parse(propscontrol.value);
            this.props =props;
            $(theid).remove();
        } else {
            //if there is no config we might as well give up
            log.debug('No config found on page. Giving up.');
            return;
        }

/* flashcards code start */
			var ef = $(".event_flashcards");
			var eg = $(".event_grid");

			$('.ProgressBar-step').on('click', function () {
				if ($(this).hasClass("is-current")) {
					return false;
				}
				$('.ProgressBar-step').removeClass("is-current").removeClass('is-complete');
				$(this).addClass("is-current");
				var str = $(this).index();
				if (str > 0) {
					for (var i = 0; i <= str; i++) {
						$('.ProgressBar')
							.find('li:eq(' + i + ')')
							.addClass('is-complete');
					}
				}
				$('.definition_flashcards_ul li').slideUp(300);
				$('.definition_flashcards_ul')
					.find('li:eq(' + str + ')')
					.slideDown(300);
				$('.is-current').removeClass('is-complete');
				check_prev_level();
				check_next_level();

			});
			
			if (window.matchMedia("(max-width: 767px)").matches) {

				var definition_flashcards_ul = $('.definition_flashcards_ul li').length;
				$('.wrapper_pr').append('<div class="mb_nav"><span class="curr_level_card">1</span> / <span class="tot_level_card">' + definition_flashcards_ul + '</span></div>');
				$('.ProgressBar').hide();
			}
			$(".definition_flashcards_ul li:gt(0)").hide();
			var $bar = $(".ProgressBar");
			$bar.children("li:first").addClass('is-current');
			check_prev_level();
			check_next_level();
			ef.click(function (d) {
				d.preventDefault();
				$('.definition_flashcards').show();
				$('.definition_grid').hide();
			});
			eg.click(function (d) {
				d.preventDefault();
				$('.definition_flashcards').hide();
				$('.definition_grid').show();
			});

			function check_prev_level() {

				var $bar = $(".ProgressBar");
				if ($bar.children("li:first").hasClass('is-current') === true) {
					$('#Prev').attr('disabled', 'disabled').addClass('add_opacity_level');
					return true;
				}

				$('#Prev').removeAttr('disabled');
				$('#Prev').removeAttr('disabled').removeClass('add_opacity_level');
			}

			$('#Next').click(function () {
				var cr_index = $(".is-current").index() + 1;
				$('.definition_flashcards_ul li').slideUp(300);
				$('.definition_flashcards_ul li:eq(' + cr_index + ')').slideDown(300);
				var curr_level_card = $('.curr_level_card').html();
				$('.curr_level_card').html(parseInt(curr_level_card) + 1);

				var $bar = $(".ProgressBar");
				if ($bar.children(".is-current").length > 0) {
					$bar.children(".is-current").removeClass("is-current").addClass("is-complete").next().addClass("is-current");
				} else {
					$bar.children().first().addClass("is-current");
				}
				check_prev_level();
				check_next_level();
			});

			$('#Prev').click(function () {
				var cr_index = $(".is-current").index() - 1;
				$('.definition_flashcards_ul li').slideUp(300);
				$('.definition_flashcards_ul li:eq(' + cr_index + ')').slideDown(300);
				var curr_level_card = $('.curr_level_card').html();
				$('.curr_level_card').html(parseInt(curr_level_card) - 1);
				var $bar = $(".ProgressBar");
				if ($bar.children(".is-current").length > 0) {
					$bar.children(".is-current").removeClass("is-current").prev().removeClass("is-complete").addClass("is-current");
				} else {
					$bar.children(".is-complete").last().removeClass("is-complete").addClass("is-current");
				}
				check_prev_level();
				check_next_level();
			});

			function check_next_level() {
				var $bar = $(".ProgressBar");
				if ($bar.children("li:last").hasClass('is-current') === true) {
					$('#Next').attr('disabled', 'disabled').addClass('add_opacity_level');
					return true;
				}
				$('#Next').removeAttr('disabled').removeClass('add_opacity_level');
			}
			/* flashcards code end */
      var container = $('#definitions-page-' + opts['widgetid']),
        modid = props.modid,
        canmanage = props.canmanage,
        canattempt = props.canattempt,
        btn = container.find('.definitions-next');

       //set up audio
       a4e.register_events();
       a4e.init_audio(props.token,props.region,props.owner);

      function seenAll() {
        return container.find('.term').length === container.find('.term.term-seen').length
      }

      container.on('click', '.term-seen-action', function(e) {
        e.preventDefault();

        var termNode = $(this).parents('.term').first();
        var termId = termNode.data('termid');

        //On the clicked (and visible) node add loading
        termNode.addClass('term-loading');
        Ajax.call([{
            'methodname': 'mod_wordcards_mark_as_seen',
            'args': {
              'termid': termId
            }
          }])[0].then(function(result) {
            if (!result) {
              return $.Deferred().reject();
            }

          //since we have two nodes (grid and flashcards) for a single term,
			// and the user might toggle between the grid and flashcards view we need to update both
			//so the old termNode.addClass('term-seen') is no good. it would only update one
          //  termNode.addClass('term-seen');
			$('.definition_flashcards [data-termid="' + termId + '"]').addClass('term-seen')
			$('.definition_grid [data-termid="' + termId + '"]').addClass('term-seen')
          })
          .fail(Notification.exception)
          .always(function() {
          	//remove loading from  node which loading was applied to
            termNode.removeClass('term-loading');
            if (seenAll()) {
              btn.prop('disabled', false);
            }
          });
      });

      // Teachers can jump to the next steps.
      if (!seenAll() && !canmanage) {
        btn.prop('disabled', true);
      }

      btn.click(function(e) {
        e.preventDefault();
        var buttonhref= $(this).data('href');

        //f its not a reattempt ... proceed
        if($(this).data('action')!=='reattempt') {
            window.location.href = buttonhref;
            return;
        }

        //if its a reattempt, confirm and proceed
          ModalFactory.create({
              type: ModalFactory.types.SAVE_CANCEL,
              title: that.strings.reattempttitle,
              body: that.strings.reattemptbody
          })
          .then(function(modal) {
              modal.setSaveButtonText(that.strings.reattempt);
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
            {"key": "reattempttitle",       "component": 'mod_wordcards'},
            {"key": "reattemptbody",           "component": 'mod_wordcards'},
            {"key": "reattempt",           "component": 'mod_wordcards'}

        ]).done(function(s) {
            var i = 0;
            that.strings.reattempttitle = s[i++];
            that.strings.reattemptbody = s[i++];
            that.strings.reattempt = s[i++];
        });
    }

  }

});
