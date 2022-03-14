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

			var totalcards = $('.definition_flashcards_ul li').length;
			$(".definition_flashcards_ul li:gt(0)").hide();
            //set the first card as is_current
            $(".definition_flashcards_ul li:first").addClass('is-current');
			set_progress_info(1,totalcards);

			ef.click(function (d) {
				d.preventDefault();
				$('.definition_flashcards').fadeIn();
				$('.definition_grid').fadeOut();
				ef.addClass('btn-primary').removeClass('btn-outline-primary')
				eg.removeClass('btn-primary').addClass('btn-outline-primary')
			});
			eg.click(function (d) {
				d.preventDefault();
				$('.definition_flashcards').fadeOut();
				$('.definition_grid').fadeIn();
				eg.addClass('btn-primary').removeClass('btn-outline-primary')
				ef.removeClass('btn-primary').addClass('btn-outline-primary')
			});



			$('#Next').click(function () {
				var cr_index = $(".is-current").index() + 1;
                if(cr_index > (totalcards-1)){cr_index=0;}
				$('.definition_flashcards_ul li').slideUp(300);
                $('.definition_flashcards_ul li').removeClass("is-current");
                $('.definition_flashcards_ul li:eq(' + cr_index + ')').addClass("is-current");
				$('.definition_flashcards_ul li:eq(' + cr_index + ')').slideDown(300);

				//var curr_level_card = $('.curr_level_card').html();
				//$('.curr_level_card').html(parseInt(curr_level_card) + 1);

				set_progress_info(cr_index + 1,totalcards);

			});

			$('#Prev').click(function () {
				var cr_index = $(".is-current").index() - 1;
                if(cr_index <0){cr_index=(totalcards-1);}
				$('.definition_flashcards_ul li').slideUp(300);
                $('.definition_flashcards_ul li').removeClass("is-current");
                $('.definition_flashcards_ul li:eq(' + cr_index + ')').addClass("is-current");
				$('.definition_flashcards_ul li:eq(' + cr_index + ')').slideDown(300);
				var curr_level_card = $('.curr_level_card').html();
				$('.curr_level_card').html(parseInt(curr_level_card) - 1);
				set_progress_info(cr_index + 1 ,totalcards);

			});

			function set_progress_info(index,total) {
				$(".definition_flashcards .wc_cardsprogress").text(index + ' / ' + total);
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

          });
      });

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
