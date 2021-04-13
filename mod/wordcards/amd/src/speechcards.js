/**
 * Matching module.
 *
 * @package mod_wordcards
 * @author  Justin Hunt - poodll.com
 * * (based on Paul Raine's APPs 4 EFL)
 */

define([
  'jquery',
  'jqueryui',
  'core/ajax',
  'core/log',
  'mod_wordcards/a4e',
  'mod_wordcards/cloudpoodllloader',
  'mod_wordcards/ttrecorder',
  'core/templates'
], function($, jqui, Ajax, log, a4e, cloudpoodll, ttrecorder, templates) {

  var app = {
    passmark: 75,
    pointer: 1,
    jsondata: null,
    props: null,
    dryRun: false,
    controls: {},
    region: 'useast1',

    init: function(props) {

      //pick up opts from html
      var theid = '#' + props.widgetid;
      this.dryRun = props.dryRun;
      this.nexturl = props.nexturl;
      this.modid = props.modid;
      this.region = props.region;
      var definitionscontrol = $(theid).get(0);
      if (definitionscontrol) {
        var jsondata = JSON.parse(definitionscontrol.value);
        $(theid).remove();
      } else {
        //if there is no config we might as well give up
        log.debug('No config found on page. Giving up.');
        return;
      }
      app.jsondata = jsondata;
      app.props = props;
      app.process(jsondata);


      a4e.register_events();
      a4e.init_audio(props.token,props.region,props.owner);

      this.init_controls();
      this.register_events();
    },
    init_controls: function() {
      app.controls.close_results = $("#wordcards-close-results");
      app.controls.results = $("#wordcards-results");
      app.controls.vocab_list = $("#wordcards-vocab-list");
      app.controls.the_list = $("#speechcards_thelist");
      app.controls.gameboard = $("#wordcards-gameboard");
      app.controls.time_counter = $("#wordcards-time-counter");
      app.controls.prev_button = $(".wordcards-speechcards_prevbutton");
      app.controls.next_button = $(".wordcards-speechcards_nextbutton");
      app.controls.standalonepushrecorder = $(".speechcards_standalonerecorder");
      app.controls.slider = $(".wordcards-poodllspeechcards_box");
    },
    do_next: function() {
      a4e.progress_dots(app.results, app.terms);
      app.clearStarRating();
      if (!app.is_end()) {
        app.move_card('>');
        app.update_header();
      } else {
        app.do_end();
      }
    },
    do_prev: function() {
      app.move_card('<');
      app.update_header();
    },

    move_card: function(direction){
        switch(direction){
            case '<':
                app.set_pointer(app.pointer - 1);
                app.controls.slider.toggle("slide",{direction:"right"});
                app.controls.slider.text(app.terms[app.pointer - 1].term);
                app.controls.slider.toggle("slide",{direction:"left"})
                app.update_header();
                break;
            case '>':
            default:
                app.set_pointer(app.pointer + 1);
                app.controls.slider.toggle("slide",{direction:"left"});
                app.controls.slider.text(app.terms[app.pointer - 1].term);
                app.controls.slider.toggle("slide",{direction:"right"});
                app.update_header();
        }
    },

    clearStarRating:function(){
      $("#wordcards-star-rating").html('· · ·');
    },
    
    register_events: function() {

      app.controls.prev_button.click(function() {
        app.do_prev();
      });
      app.controls.next_button.click(function() {

        //user has given up tryint pronounce it,update word as failed
        var failedword = app.terms[app.pointer - 1].term;
        app.check(false, failedword);
        app.do_next();

      });

      $('body').on('click', "#wordcards-close-results", function() {

        var total_time = app.timer.count;
        var url = app.nexturl.replace(/&amp;/g, '&') + "&localscattertime=" + total_time
        window.location.replace(url);

      });

      $('body').on('click', "#wordcards-try-again", function() {
        location.reload();
      });

      $('body').on('click', '#wordcards-start-button', function() {
        app.start();
      });

    },

    process: function(jsondata) {

      app.terms = jsondata.terms;
      app.has_images = jsondata.has_images;
      a4e.list_vocab("#vocab-list-inner", app.terms);

      //init components
      this.initComponents();

    },

    set_pointer:  function (newpointer) {
        app.pointer = newpointer;
    },

    initCards: function() {
        app.controls.slider.text(app.terms[app.pointer - 1].term);
    },

    initComponents: function() {

      var that =this;

      //The logic here is that on correct we transition.
      //on incorrect we do not. A subsequent nav button click then doesnt need to post a result
      var theCallback = function(message) {
        //console.log("triggered callback");
        console.log(message);

        switch (message.type) {
          case 'recording':

            break;

          case 'speech':
            var speechtext = message.capturedspeech;
            var cleanspeechtext = app.cleanText(speechtext);
            
            var spoken = cleanspeechtext;
            var correct = app.terms[app.pointer - 1].term;

            //Similarity check by character matching
            var similarity = app.similarity(spoken,correct);
            log.debug('JS similarity: ' + spoken + ':' + correct +':' + similarity);

            //Similarity check by direct-match/acceptable-mistranscription
            if (similarity >= app.passmark ||
                app.wordsDoMatch(cleanspeechtext, app.terms[app.pointer - 1]) ) {
                log.debug('local match:' + ':' + spoken +':' + correct);
                  app.showStarRating(100);
                  app.flagCorrectAndTransition(app.terms[app.pointer - 1]);
                  return;
            }

            //Similarity check by phonetics(ajax)
            app.checkByPhonetic(spoken,correct).then(function(similarity) {
              if (similarity===false) {
                  return $.Deferred().reject();
              }else{
                  log.debug('PHP similarity: ' + spoken + ':' + correct +':' + similarity);
                  app.showStarRating(similarity);
                  if(similarity>=app.passmark) {
                      app.flagCorrectAndTransition(similarity, app.terms[app.pointer - 1]);
                  }
              }//end of if check_by_phonetic result
             });//end of check by phonetic

        }//end of switch message type
      };

      //init the recorder
        var recid= 'wordcards-speechcards_pushrecorder';
        if(this.use_ttrecorder()) {
            //init tt recorder
            var opts = {};
            opts.uniqueid = recid;
            opts.callback = theCallback;
            ttrecorder.clone().init(opts);
        }else{
            //init cloudpoodll push recorder
            cloudpoodll.init(recid, theCallback);
        }

    },

    showStarRating: function(similarity){
        //how many stars code
        var stars = [true,true,true];
        if(similarity<1){
            stars=[true,true,false];
        }
        if(similarity<app.passmark){
            stars=[true,false,false];
        }
        if(similarity<0.5){
            stars=[false,false,false];
        }
        console.log(stars,similarity);

        //prepare stars html
        var code="";
        stars.forEach(function(star){
            if(star===true){
                code+='<i class="fa fa-star"></i>';
            }
            else{
                code+='<i class="fa fa-star-o"></i>';
            }
        });
        console.log(code);
        $("#wordcards-star-rating").html(code);
    },

    flagCorrectAndTransition: function(term){

        //update students word log if matched
        app.check(true, term);

        //transition if required
        if (app.is_end()) {
            app.update_header();
            setTimeout(function() {
                app.do_end();
            }, 700);
        } else {
            setTimeout(function() {
                app.do_next();
            }, 700);
        }

    },

    //this will return the promise, the result of which is an integer 100 being perfect match, 0 being no match
    checkByPhonetic: function(spoken, correct){

      return Ajax.call([{
            'methodname': 'mod_wordcards_check_by_phonetic',
            'args': {
                'spoken': spoken,
                'correct': correct,
                'language': app.props.language,
            }
        }])[0];

    },

    wordsDoMatch: function(wordheard, currentterm) {
      //lets lower case everything
       wordheard = app.cleanText(wordheard);
      currentterm.term = app.cleanText(currentterm.term);
      if (wordheard == currentterm.term) {
        return true;
      }
      if (!currentterm.alternates) {
        return false;
      }
      var awords = currentterm.alternates.split(',');
      var matched = false;
      $.each(awords, function(i, word) {
        if (app.cleanText(word.toLowerCase()) == wordheard) {
          //we return false to break out of the loop, not to tell the parent its unmatched
          matched = true;
          return false;
        }
      });
      return matched;
    },

    cleanText: function(text) {
        var lowertext = text.toLowerCase();
        var punctuationless = lowertext.replace(/['!"#$%&\\'()\*+,\-\.\/:;<=>?@\[\\\]\^_`{|}~']/g,"");
        var ret = punctuationless.replace(/\s+/g, " ").trim();
        return ret;
    },

    start: function() {
      app.results = [];
      a4e.shuffle(app.terms);
      app.controls.vocab_list.hide();
      app.controls.gameboard.show();
      app.controls.time_counter.text("00:00");
      app.clearStarRating();
      a4e.progress_dots(app.results, app.terms);
      app.timer = {
        interval: setInterval(function() {
          app.timer.update();
        }, 1000),
        count: 0,
        update: function() {
          app.timer.count++;
          app.controls.time_counter.text(a4e.pretty_print_secs(app.timer.count));
        }
      }
      app.update_header();
      this.initCards();
    },
    quit: function() {
      clearInterval(app.timer.interval);
      app.controls.gameboard.hide();
      app.controls.vocab_list.show();
    },

    do_end: function() {
      clearInterval(app.timer.interval);
      $("#wordcards-gameboard, #wordcards-quit-button, #wordcards-start-button").hide();
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
        activity: "speechcards"
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

    is_end: function() {
      if (app.pointer < app.terms.length) {
        return false;
      } else {
        return true;
      }
    },

    update_header: function() {

      var progress = {
        correct: app.results.filter(function(e) {
          return e.points > 0
        }).length / app.terms.length * 100,
        incorrect: app.results.filter(function(e) {
          return e.points == 0
        }).length / app.terms.length * 100
      }

    },

    check: function(correct, spokenwords) {
      var points = 1;
      if (correct == true) {
        //createjs.Sound.play('correct');
        points = 1;
      } else {
        points = 0;
        //createjs.Sound.play('incorrect');
      }
      $(".a4e-distractor").css('pointer-events', 'none');
      var result = {
        pointer: app.pointer,
        question: app.terms[app.pointer - 1]['definition'],
        selected: spokenwords,
        correct: app.terms[app.pointer - 1]['term'],
        points: points
      };

      $.each(app.results, (function(result) {
        if (app.pointer === result.pointer) {
          //something here to remove the old result
        }
      }));
      //finally add our result to the results
      app.results.push(result);

      //post results to server
      if (correct) {
        this.reportSuccess(app.terms[app.pointer - 1]['id']);
      } else {
        this.reportFailure(app.terms[app.pointer - 1]['id'], 0);
      }

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
    },

    similarity: function(s1, s2) {
      var longer = s1;
      var shorter = s2;
      if (s1.length < s2.length) {
        longer = s2;
        shorter = s1;
      }
      var longerLength = longer.length;
      if (longerLength == 0) {
        return 1.0;
      }
      return (longerLength - app.editDistance(longer, shorter)) / parseFloat(longerLength);
    },
    editDistance: function(s1, s2) {
      s1 = s1.toLowerCase();
      s2 = s2.toLowerCase();

      var costs = new Array();
      for (var i = 0; i <= s1.length; i++) {
        var lastValue = i;
        for (var j = 0; j <= s2.length; j++) {
          if (i == 0)
            costs[j] = j;
          else {
            if (j > 0) {
              var newValue = costs[j - 1];
              if (s1.charAt(i - 1) != s2.charAt(j - 1))
                newValue = Math.min(Math.min(newValue, lastValue),
                  costs[j]) + 1;
              costs[j - 1] = lastValue;
              lastValue = newValue;
            }
          }
        }
        if (i > 0)
          costs[s2.length] = lastValue;
      }
      return costs[s2.length];
    },

      mobile_user: function() {

          if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
              return true;
          } else {
              return false;
          }
      },

      chrome_user: function(){
          if(/Chrome/i.test(navigator.userAgent)) {
              return true;
          }else{
              return false;
          }
      },

      use_ttrecorder: function(){
          var ret =false;


          //check if language and region are ok
          switch(this.region){
              case 'tokyo':
              case 'useast1':
              case 'dublin':
              case 'sydney':
                  //ret = this.language.substr(0,2)==='en';
                  ret =true;
                  break;
              default:
                  ret = this.chrome_user();
          }
          return ret;
      },

  };

  return app;


});