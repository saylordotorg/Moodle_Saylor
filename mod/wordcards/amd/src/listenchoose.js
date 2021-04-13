/**
 * Matching module.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - poodll.com
 * * (based on Paul Raine's APPs 4 EFL)
 */

define([
  'jquery',
  'core/ajax',
  'core/log',
  'mod_wordcards/a4e',
  'mod_wordcards/pollyhelper',
  'core/templates'
], function($, Ajax, log, a4e, polly, templates) {

  var app = {
    dryRun: false,
    audio: false,
    ttslanguage: 'en-US',

    init: function(props) {

      //pick up opts from html
      var theid = '#' + props.widgetid;
      this.dryRun = props.dryRun;
      this.nexturl = props.nexturl;
      this.modid = props.modid;
      var configcontrol = $(theid).get(0);
      if (configcontrol) {
        var matchingdata = JSON.parse(configcontrol.value);
        $(theid).remove();
      } else {
        //if there is no config we might as well give up
        log.debug('No config found on page. Giving up.');
        return;
      }

      app.process(matchingdata);

      a4e.register_events();
      a4e.init_audio(props.token,props.region,props.owner);
      polly.init(props.token, props.region, props.owner);
      app.ttslanguage = props.ttslanguage;

      this.register_events();
    },

    register_events: function() {

      // Get the audio element
      var aplayer = $("#dictation_player");
      $("#listen-button").click(function() {
          if (app.audio) {
              aplayer.attr('src', app.audio);
              aplayer[0].play();
          } else {
              polly.fetch_polly_url(app.tts, 'text', app.ttsvoice);
          }

      });

      //play what was returned in polly.fetch_polly_url
      polly.onnewpollyurl = function(theurl) {
          aplayer.attr('src', theurl);
          aplayer[0].play();
      };

      $('body').on('click', "#wordcards-close-results", function() {

        var total_time = app.timer.count;
        var url = app.nexturl.replace(/&amp;/g, '&') + "&localscattertime=" + total_time
        window.location.replace(url);

      });

      $('body').on('click', "#wordcards-try-again", function() {
        location.reload();
      });

      $("body").on('click', '.a4e-distractor', function(e) {
        app.check($(this).data('correct'), this);
      });

      $('body').on('click', '#wordcards-start-button', function() {
        app.start();
      });

    },

    process: function(json) {

      app.terms = json.terms;
      app.has_images = json.has_images;
      a4e.list_vocab("#vocab-list-inner", app.terms);
    },
    start: function() {
      app.results = [];
      a4e.shuffle(app.terms);
      app.pointer = 0;
      $("#wordcards-vocab-list, #wordcards-start-button").hide();
      $("#wordcards-gameboard").show();
      $("#wordcards-time-counter").text("00:00");
      app.timer = {
        interval: setInterval(function() {
          app.timer.update();
        }, 1000),
        count: 0,
        update: function() {
          app.timer.count++;
          $("#wordcards-time-counter").text(a4e.pretty_print_secs(app.timer.count));
        }
      }
      app.next();
    },
    quit: function() {
      clearInterval(app.timer.interval);
      $("#wordcards-gameboard").hide();
      $("#wordcards-vocab-list, #wordcards-start-button").show();
    },

    end: function() {
      clearInterval(app.timer.interval);
      $("#wordcards-gameboard, #wordcards-start-button").hide();
      $("#wordcards-results").show();

      //template data
      var tdata = [];
      tdata['results'] = app.results;
      tdata['total'] = app.terms.length;
      tdata['totalcorrect'] = a4e.calc_total_points(app.results);
      var total_time = app.timer.count;
      if (total_time == 0) {
        tdata['prettytime'] = '00:00';
      } else {
        tdata['prettytime'] = a4e.pretty_print_secs(total_time);
      }
      templates.render('mod_wordcards/feedback', tdata).then(
        function(html, js) {
          $("#results-inner").html(html);
        }
      );

      var data = {
        results: app.results,
        activity: "match_select"
      };

      console.log(data);

      Ajax.call([{
          methodname: 'mod_wordcards_report_step_grade',
          args: {
              modid: app.modid,
              correct: tdata['totalcorrect']
          }
      }]);


    },
    next: function() {
      
      a4e.progress_dots(app.results, app.terms);

        app.tts = app.terms[app.pointer]['term'];

        if (app.terms[app.pointer]['ttsvoice']) {
            app.ttsvoice = app.terms[app.pointer]['ttsvoice'];
        } else {
            app.ttsvoice = 'auto';
        }

        if (app.terms[app.pointer]['audio']) {
            app.audio = app.terms[app.pointer]['audio'];
        } else {
            app.audio = false;
        }
        $("#listen-button").trigger("click");

      $("#wordcards-input").html(app.get_distractors());

    },

    check: function(correct, clicked) {
      var points = 0;
      if (correct == true) {
        //createjs.Sound.play('correct');
        points = 1;
      } else {
        //createjs.Sound.play('incorrect');
      }
      $(".a4e-distractor").css('pointer-events', 'none');
      
      var result = {
        question: app.terms[app.pointer]['definition'],
        selected: $(clicked).text(),
        correct: app.terms[app.pointer]['term'],
        points: points
      };
      
      app.results.push(result);

      var background = correct == true ? 'a4e-correct' : 'a4e-incorrect';
      $(clicked).addClass(background).append("<i style='color:" + (correct ? 'green' : 'red') + ";margin-left:5px;' class='fa fa-" + (correct ? 'check' : 'times') + "'></i>").parent().addClass('a4e-click-disabled');

      if (!correct) {
        $(".a4e-distractor[data-correct='true']").addClass('a4e-correct').append("<i style='color:green;margin-left:5px;' class='fa fa-check'></i>");
      }

      //post results to server
      if (correct) {
        this.reportSuccess(app.terms[app.pointer]['id']);
      } else {
        this.reportFailure(app.terms[app.pointer]['id'], $(clicked).data('id'));
      }

      app.pointer++;
      if (!correct) {
        setTimeout(function() {
          if (app.pointer < app.terms.length) {
            app.next();
          } else {
            app.end();
          }
        }, 1500)
      } else {
        setTimeout(function() {
          if (app.pointer < app.terms.length) {
            app.next();
          } else {
            app.end();
          }
        }, 1000)
      }
    },

    get_distractors: function() {
      var distractors = app.terms.slice(0);
      var answer = app.terms[app.pointer]['term'];
      distractors.splice(app.pointer, 1);
      a4e.shuffle(distractors);
      distractors = distractors.slice(0, 4);
      distractors.push(app.terms[app.pointer]);
      a4e.shuffle(distractors);
      var options = [];
      $.each(distractors, function(i, o) {
        var is_correct = o['term'] == answer;
        var term_id = o['id'];
        options.push('<li data-id="' + term_id + '" data-correct="' + is_correct.toString() + '" class="list-group-item a4e-distractor a4e-noselect">' + o['term'] + '</li>');
      });
      var code = '<ul class="list-group a4e-distractors">' + options.join('') + '</ul>';
      return code;
    },

    reportFailure: function(term1id, term2id) {
      if (this.dryRun) {
        return;
      }

      Ajax.call([{
        methodname: 'mod_wordcards_report_failed_association',
        args: {
          term1id: term1id,
          term2id: term2id
        }
      }]);
    },

    reportSuccess: function(termid) {
      if (this.dryRun) {
        return;
      }

      Ajax.call([{
        methodname: 'mod_wordcards_report_successful_association',
        args: {
          termid: termid
        }
      }]);
    }
  };

  return app;

});