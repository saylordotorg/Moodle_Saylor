define(['jquery', 'jqueryui', 'core/log', 'core/ajax', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper',
  'mod_minilesson/cloudpoodllloader','mod_minilesson/ttrecorder'
], function($, jqui, log, Ajax, def, polly, cloudpoodll, ttrecorder) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson Speechcards: initialising');

  return {

      //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
      },

    init: function(index, itemdata, quizhelper) {

      this.init_app(index, itemdata, quizhelper);
    },

    init_app: function(index, itemdata, quizhelper) {

      console.log(itemdata);

      var app = {
        passmark: 90,
        pointer: 1,
        jsondata: null,
        props: null,
        dryRun: false,
        language: 'en-US',
        terms: [],
        phonetics: [],
        displayterms: [],
        results: [],
        controls: {},

        init: function() {

          //init terms
          for (var i = 0; i < itemdata.sentences.length; i++) {
            app.terms[i] = itemdata.sentences[i].sentence;
            app.phonetics[i] = itemdata.sentences[i].phonetic;
            app.displayterms[i] = itemdata.sentences[i].prompt;
          }
          app.language = itemdata.language;

          this.init_controls();
          this.initComponents();
          this.register_events();
        },

        init_controls: function() {
          app.controls = {};
          app.controls.star_rating = $("#" + itemdata.uniqueid + "_container .minilesson_star_rating");
          app.controls.next_button = $("#" + itemdata.uniqueid + "_container .minilesson-speechcards_nextbutton");
          app.controls.slider = $("#" + itemdata.uniqueid + "_container .minilesson_speechcards_target_phrase");
        },
        next_question: function() {
          var stepdata = {};
          stepdata.index = index;
          stepdata.hasgrade = true;
          stepdata.totalitems = app.terms.length;
          stepdata.correctitems = app.results.filter(function(e){return e.points>0;}).length;
          stepdata.grade = Math.round((stepdata.correctitems/stepdata.totalitems)*100);
          quizhelper.do_next(stepdata);
        },
        register_events: function() {

          $("#" + itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
            app.next_question();
          });

          app.controls.next_button.click(function() {
            //user has given up ,update word as failed
            app.check(false);

            //transition if required
            if (app.is_end()) {
              setTimeout(function() {
                app.do_end();
              }, 200);
            } else {
              setTimeout(function() {
                app.do_next();
              }, 200);
            }

          });
        },

        initComponents: function() {

          var theCallback = function(message) {

            switch (message.type) {
              case 'recording':

                break;

              case 'speech':
                log.debug("speech at speechcards");
                var speechtext = message.capturedspeech;
                var spoken_clean  = quizhelper.cleanText(speechtext);
                var correct_clean = quizhelper.cleanText(app.terms[app.pointer - 1]);
                var correctphonetic = app.phonetics[app.pointer - 1];
log.debug('speechtext:',speechtext);
log.debug('spoken:',spoken_clean);
log.debug('correct:',correct_clean);
                //Similarity check by character matching
                var similarity_js = quizhelper.similarity(spoken_clean, correct_clean);
                log.debug('JS similarity: ' + spoken_clean + ':' + correct_clean + ':' + similarity_js);

                //Similarity check by direct-match/acceptable-mistranscription
                if (similarity_js >= app.passmark ||
                  app.wordsDoMatch(spoken_clean, correct_clean)) {
                  log.debug('local match:' + ':' + spoken_clean + ':' + correct_clean);
                  app.showStarRating(100);
                  app.flagCorrectAndTransition();
                  return;
                }

                //Similarity check by phonetics(ajax)
                quizhelper.checkByPhonetic(correct_clean, spoken_clean, correctphonetic, app.language).then(function(similarity_php) {
                  if (similarity_php === false) {
                    return $.Deferred().reject();
                  } else {
                    log.debug('PHP similarity: ' + spoken_clean + ':' + correct_clean + ':' + similarity_php);

                    if (similarity_php >= app.passmark) {
                        app.showStarRating(similarity_php);
                        app.flagCorrectAndTransition();
                    }else{
                        //show the greater of the ratings
                        app.showStarRating(Math.max(similarity_js,similarity_php));
                    }
                  } //end of if check_by_phonetic result
                }); //end of check by phonetic

            } //end of switch message type
          };



         if(quizhelper.use_ttrecorder()) {
             //init tt recorder
             var opts = {};
             opts.uniqueid = itemdata.uniqueid;
             opts.callback = theCallback;
             opts.stt_guided=quizhelper.is_stt_guided();
             ttrecorder.clone().init(opts);
         }else{
             //init cloudpoodll push recorder
             cloudpoodll.init('minilesson-recorder-speechcards-' + itemdata.id, theCallback);
         }


          //init progress dots
          app.progress_dots(app.results, app.terms);

          app.initSlider();


        },

        initSlider: function() {
          app.controls.slider.text(app.displayterms[app.pointer - 1]);
          app.controls.slider.show();
        },

        writeCurrentTerm: function() {
            app.controls.slider.toggle("slide",{direction:"left"});
            app.controls.slider.text(app.displayterms[app.pointer - 1]);
            app.controls.slider.toggle("slide",{direction:"right"})
        },

        flagCorrectAndTransition: function() {

          //update students word log if matched
          app.check(true);

          //transition if required
          if (app.is_end()) {
            setTimeout(function() {
              app.do_end();
            }, 700);
          } else {
            setTimeout(function() {
              app.do_next();
            }, 700);
          }

        },

        wordsDoMatch: function(phraseheard, currentphrase) {
          //lets lower case everything
          phraseheard = quizhelper.cleanText(phraseheard);
          currentphrase = quizhelper.cleanText(currentphrase);
          if (phraseheard == currentphrase) {
            return true;
          }
          return false;
        },


        showStarRating: function(similarity) {
          //how many stars code
          var stars = [true, true, true];
          if (similarity < app.passmark) {
            stars = [true, true, false];
          }
          if (similarity < .75) {
            stars = [true, false, false];
          }
          if (similarity < 0.5) {
            stars = [false, false, false];
          }

          //prepare stars html
          var code = "";
          stars.forEach(function(star) {
            if (star === true) {
              code += '<i class="fa fa-star"></i>';
            } else {
              code += '<i class="fa fa-star-o"></i>';
            }
          });

          app.controls.star_rating.html(code);
        },

        check: function(correct) {
          var points = 1;
          if (correct == true) {
            points = 1;
          } else {
            points = 0;
          }
          var result = {
            points: points
          };
          app.results.push(result);
        },

        do_next: function() {
          app.pointer++;
          app.progress_dots(app.results, app.terms);
          app.clearStarRating();
          if (!app.is_end()) {
            app.writeCurrentTerm();
          } else {
            app.do_end();
          }
        },

        clearStarRating: function() {
          app.controls.star_rating.html('· · ·');
        },

        do_end: function() {
          app.next_question();
        },

        is_end: function() {
          //pointer is 1 based but array is, of course, 0 based
          if (app.pointer <= app.terms.length) {
            return false;
          } else {
            return true;
          }
        },

        progress_dots: function(results, terms) {

          var code = "";
          var color = "";
          terms.forEach(function(o, i) {
            color = "darkgray";
            if (results[i] !== undefined) {
              if (results[i].points) {
                color = "green";
              } else {
                color = "red";
              }
            }
            code += '<i style="color:' + color + ';" class="fa fa-circle"></i>';
          });

          $("#" + itemdata.uniqueid + "_container .minilesson_progress_dots").html(code);

        },
      }; //end of app definition
      app.init();

    } //end of init_App


  }; //end of return value
});