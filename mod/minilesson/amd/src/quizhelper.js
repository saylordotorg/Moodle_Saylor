define(['jquery', 'core/log', 'mod_minilesson/definitions', 'core/templates', 'core/ajax',
    'mod_minilesson/dictation', 'mod_minilesson/dictationchat', 'mod_minilesson/multichoice','mod_minilesson/multiaudio',
        'mod_minilesson/speechcards', 'mod_minilesson/listenrepeat',
        'mod_minilesson/page','mod_minilesson/smartframe','mod_minilesson/shortanswer'],
  function($, log, def, templates, Ajax, dictation, dictationchat, multichoice, multiaudio, speechcards, listenrepeat, page, smartframe, shortanswer) {
    "use strict"; // jshint ;_;

    /*
    This file is to manage the quiz stage
     */

    log.debug('MiniLesson Quiz helper: initialising');

    return {

      //original spliton_regexp: new RegExp(/([,.!?:;" ])/, 'g'),
      spliton_regexp: new RegExp(/([!"# $%&'()。「」、*+,-.\/:;<=>?@[\]^_`{|}~])/, 'g'),
      //nopunc is diff to split on because it does not match on spaces
      nopunc_regexp: new RegExp(/[!"#$%&'()。「」、*+,-.\/:;<=>?@[\]^_`{|}~]/,'g'),
      nonspaces_regexp: new RegExp(/[^ ]/,'g'),

      controls: {},
      submitbuttonclass: 'mod_minilesson_quizsubmitbutton',
      stepresults: [],

      init: function(quizcontainer, activitydata, cmid, attemptid,polly) {
        this.quizdata = activitydata.quizdata;
        this.region = activitydata.region;
        this.ttslanguage = activitydata.ttslanguage;
        this.controls.quizcontainer = quizcontainer;
        this.attemptid = attemptid;
        this.courseurl = activitydata.courseurl;
        this.cmid = cmid;
        this.reattempturl = activitydata.reattempturl;
        this.backtocourse = activitydata.backtocourse;
        this.ds_only = activitydata.ds_only;
        this.prepare_html();
        this.init_questions(this.quizdata,polly);
        this.register_events();
        this.start_quiz();
      },

      prepare_html: function() {

        // this.controls.quizcontainer.append(submitbutton);
        this.controls.quizfinished=$("#mod_minilesson_quiz_finished");

      },

      init_questions: function(quizdata, polly) {
        var dd = this;
        $.each(quizdata, function(index, item) {
          switch (item.type) {
            case def.qtype_dictation:
              dictation.clone().init(index, item, dd, polly);
              break;
            case def.qtype_dictationchat:
              dictationchat.clone().init(index, item, dd, polly);
              break;
            case def.qtype_multichoice:
              multichoice.clone().init(index, item, dd);
              break;
            case def.qtype_multiaudio:
                multiaudio.clone().init(index, item, dd);
                break;
            case def.qtype_speechcards:
              //speechcards init needs to occur when it is visible. lame.
              // so we do that in do_next function, down below
              speechcards.clone().init(index, item, dd);
              break;
            case def.qtype_listenrepeat:
              listenrepeat.clone().init(index, item, dd);
              break;

             case def.qtype_page:
                  page.clone().init(index, item, dd);
                  break;

              case def.qtype_smartframe:
                  smartframe.clone().init(index, item, dd);
                  break;

              case def.qtype_shortanswer:
                  shortanswer.clone().init(index, item, dd);
                  break;
          }

        });

        //TTS in question headers
          $("audio.mod_minilesson_itemttsaudio").each(function(){
              var that=this;
              polly.fetch_polly_url($(this).data('text'), $(this).data('ttsoption'), $(this).data('voice')).then(function(audiourl) {
                  $(that).attr("src", audiourl);
              });
          });

      },

      register_events: function() {
        $('.' + this.submitbuttonclass).on('click', function() {
          //do something
        });
      },
      render_quiz_progress:function(current,total){
        var array = [];
        for(var i=0;i<total;i++){
          array.push(i);
        }

        if(total<6) {
            var slice = array.slice(0, 5);
            var linestyles = "width: " + (100 - 100 / slice.length) + "%; margin-left: auto; margin-right: auto";
            var html = "<div class='minilesson_quiz_progress_line' style='" + linestyles + "'></div>";

            slice.forEach(function (i) {
                html += "<div class='minilesson_quiz_progress_item " + (i == current ? 'minilesson_quiz_progress_item_current' : '') + " " + (i < current ? 'minilesson_quiz_progress_item_completed' : '') + "'>" + (i + 1) + "</div>";
            });
        }else {
             if(current > total-6){
                 var slice = array.slice(total-5, total-1);
             }else{
                 var slice = array.slice(current, current + 4);
             }

              //if first item is visible then no line trailing left of item 1
              if(current==0){
                  var linestyles = "width: 80%; margin-left: auto; margin-right: auto";
              }else {
                  var linestyles = "width: " + (100 - 100 / (2 *slice.length)) + "%; margin-left: 0";
              }
            var html = "<div class='minilesson_quiz_progress_line' style='" + linestyles + "'></div>";
              slice.forEach(function (i) {
                  html += "<div class='minilesson_quiz_progress_item " + (i == current ? 'minilesson_quiz_progress_item_current' : '') + " " + (i < current ? 'minilesson_quiz_progress_item_completed' : '') + "'>" + (i + 1) + "</div>";
              });
              //end marker
            html += "<div class='minilesson_quiz_progress_finalitem'>" + (total) + "</div>";
          }

        html+="";
        $(".minilesson_quiz_progress").html(html);

      },

      do_next: function(stepdata){
        var dd = this;
        //get current question
        var currentquizdataindex =   stepdata.index;
        var currentitem = this.quizdata[currentquizdataindex];

        //in preview mode do no do_next
        if(currentitem.preview===true){return;}

        //post grade
        dd.report_step_grade(stepdata);
        //hide current question
        var theoldquestion = $("#" + currentitem.uniqueid + "_container");
        theoldquestion.hide();
        //show next question or End Screen
        if (dd.quizdata.length > currentquizdataindex+1) {
          var nextindex = currentquizdataindex+ 1;
          var nextitem = this.quizdata[nextindex];
            //show the question
            $("#" + nextitem.uniqueid + "_container").show();
          //any per question type init that needs to occur can go here
          switch (nextitem.type) {
              case def.qtype_speechcards:
                  //speechcards.init(nextindex, nextitem, dd);
                  break;
              case def.qtype_dictation:
              case def.qtype_dictationchat:
              case def.qtype_multichoice:
              case def.qtype_multiaudio:
              case def.qtype_listenrepeat:
              case def.qtype_smartframe:
              case def.qtype_shortanswer:
              default:
          }//end of nextitem switch

        } else {

          var results = dd.stepresults.filter(function(e){return e.hasgrade;});
          var correctitems = 0;
          var totalitems = 0;
          results.forEach(function(result,i){
            result.index=i+1;
            result.title=dd.quizdata[i].title;
            correctitems += result.correctitems;
            totalitems += result.totalitems;
          });
          var totalpercent = Math.round((correctitems/totalitems)*100);
          console.log(results,correctitems,totalitems,totalpercent);
          var finishedparams ={results:results,total:totalpercent, courseurl: this.courseurl};
          if(this.reattempturl!=''){finishedparams.reattempturl = this.reattempturl;}
          if(this.backtocourse!=''){finishedparams.backtocourse = true;}
          templates.render('mod_minilesson/quizfinished',finishedparams).then(
              function(html,js){
                  dd.controls.quizfinished.html(html);
                  dd.controls.quizfinished.show();
                  templates.runTemplateJS(js);
              }
          );

        }

        this.render_quiz_progress(stepdata.index+1,this.quizdata.length);

          //we want to destroy the old question in the DOM also because iframe/media content might be playing
          theoldquestion.remove();
        
      },

      report_step_grade: function(stepdata) {
        var dd = this;

        //store results locally
        this.stepresults.push(stepdata);

        //push results to server
        Ajax.call([{
          methodname: 'mod_minilesson_report_step_grade',
          args: {
            cmid: dd.cmid,
            step: JSON.stringify(stepdata),
          }
        }]);
      },



      start_quiz: function() {
        $("#" + this.quizdata[0].uniqueid + "_container").show();
        this.render_quiz_progress(0,this.quizdata.length);
      },

      //this function is overridden by the calling class
      onSubmit: function() {
        alert('quiz submitted. Override this');
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

        //this will always be true these days
        use_ttrecorder: function(){
            return true;
        },
        is_ds_only: function(){
          return this.ds_only;
        },

        //text comparison functions follow===============

        similarity: function(s1, s2) {
            //we remove spaces because JP transcript and passage might be different. And who cares about spaces anyway?
            s1 = s1.replace(/\s+/g, '');
            s2 = s2.replace(/\s+/g, '');

            var longer = s1;
            var shorter = s2;
            if (s1.length < s2.length) {
                longer = s2;
                shorter = s1;
            }
            var longerLength = longer.length;
            if (longerLength == 0) {
                return 100;
            }
            return 100 * ((longerLength - this.editDistance(longer, shorter)) / parseFloat(longerLength));
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

        cleanText: function(text) {
            var lowertext = text.toLowerCase();
            var punctuationless = lowertext.replace(this.nopunc_regexp,"");
            var ret = punctuationless.replace(/\s+/g, " ").trim();
            return ret;
        },

        //this will return the promise, the result of which is an integer 100 being perfect match, 0 being no match
        checkByPhonetic: function(passage, transcript, passagephonetic, language) {
            return Ajax.call([{
                'methodname': 'mod_minilesson_check_by_phonetic',
                'args': {
                    'spoken': transcript,
                    'correct': passage,
                    'language': language,
                    'phonetic': passagephonetic,
                    'region': this.region,
                    'cmid': this.cmid
                }
            }])[0];

        },

       comparePassageToTranscript: function (passage,transcript,passagephonetic, language){
          return Ajax.call([{
               methodname: 'mod_minilesson_compare_passage_to_transcript',
               args: {
                   passage: passage,
                   transcript: transcript,
                   alternatives: '',
                   phonetic: passagephonetic,
                   language: language,
                   region: this.region,
                   cmid: this.cmid
               }
           }])[0];
       }
    }; //end of return value
  });
