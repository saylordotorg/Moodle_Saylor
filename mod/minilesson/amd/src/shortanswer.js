define(['jquery', 'core/log', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper','mod_minilesson/cloudpoodllloader',
      'mod_minilesson/ttrecorder'],
    function($, log, def, polly,cloudpoodll, ttrecorder) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson ShortAnswer: initialising');

  return {
    passmark: 90,//lower this if it often doesnt match (was 85)

    //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
     },

    init: function(index, itemdata, quizhelper) {
   //   this.prepare_audio(itemdata);
      this.register_events(index, itemdata, quizhelper);
      this.init_components(index, itemdata, quizhelper);
    },
    next_question: function(percent) {
      var self = this;
      var stepdata = {};
      stepdata.index = self.index;
      stepdata.hasgrade = true;
      stepdata.totalitems = 1;
      stepdata.correctitems = percent>0?1:0;
      stepdata.grade = percent;
      self.quizhelper.do_next(stepdata);
    },

    /* NOT NEEDED */
    prepare_audio: function(itemdata) {
      // debugger;
      $.each(itemdata.sentences, function(index, sentence) {
        polly.fetch_polly_url(sentence.sentence, itemdata.voiceoption, itemdata.usevoice).then(function(audiourl) {
          $("#" + itemdata.uniqueid + "_option" + (index+1)).attr("data-src", audiourl);
        });
      });
    },
    
    register_events: function(index, itemdata, quizhelper) {
      
      var self = this;
      self.index = index;
      self.quizhelper = quizhelper;
      
      $("#" + itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
        self.next_question(0);
      });
      
      $("#" + itemdata.uniqueid + "_container ." + itemdata.uniqueid + "_option").on('click', function(e) {
        
        $("." + itemdata.uniqueid + "_option").prop("disabled", true);
        $("." + itemdata.uniqueid + "_fb").html("<i style='color:red;' class='fa fa-times'></i>");
        $("." + itemdata.uniqueid + "_option" + itemdata.correctanswer + "_fb").html("<i style='color:green;' class='fa fa-check'></i>");
        
        var checked = $('input[name='+itemdata.uniqueid+'_options]:checked').data('index');
        var percent = checked == itemdata.correctanswer ? 100 : 0;
        
        $(".minilesson_nextbutton").prop("disabled", true);
        setTimeout(function() {
          $(".minilesson_nextbutton").prop("disabled", false);
          self.next_question(percent);
        }, 2000);
        
      });
      
    },

    init_components: function(index, itemdata, quizhelper) {
      var app= this;
      var sentences = itemdata.sentences;//sentence & phonetic
      //clean the text of any junk
      for(var i=0;i<sentences.length;i++){
          sentences[i].originalsentence= sentences[i].sentence
          sentences[i].sentence=quizhelper.cleanText(sentences[i].sentence);
      }

      var theCallback = async function(message) {

        switch (message.type) {
          case 'recording':

            break;

          case 'speech':
            log.debug("speech at shortanswer");
            var speechtext = message.capturedspeech;
            var cleanspeechtext = quizhelper.cleanText(speechtext);
            var spoken = cleanspeechtext;

            log.debug('speechtext:',speechtext);
            log.debug('cleanspeechtext:',spoken);
            var matched=false;
            var percent=0;

            //Similarity check by direct-match/acceptable-mistranscriptio
            for(var x=0;x<sentences.length;x++){
              //if this is the correct answer index, just move on
              if(sentences[x].sentence===''){continue;}
              var similar = quizhelper.similarity(spoken, sentences[x].sentence);
              log.debug('JS similarity: ' + spoken + ':' + sentences[x].sentence + ':' + similar);
              if (similar >= app.passmark ||
                  app.spokenIsCorrect(quizhelper, cleanspeechtext, sentences[x].sentence)) {
                  percent = app.process_accepted_response(itemdata, x);
                  matched=true;
                  break;
              }//end of if similarity
            }//end of for x

            //Similarity check by phonetics(ajax)
            //this is an expensive call since it goes out to the server and possibly to the cloud
            if(!matched) {
              for (x = 0; x < sentences.length; x++) {
                var similarity = await quizhelper.checkByPhonetic(sentences[x].sentence, spoken, sentences[x].phonetic, itemdata.language);
                if (!similarity || similarity < app.passmark) {
                  //keep looking
                } else {
                  matched = true;
                  log.debug('PHP similarity: ' + spoken + similarity);
                  percent = app.process_accepted_response(itemdata, x);
                  break;
                }
              }//end of Similarity check by phonetics(ajax) loop
            }

            //we do not do a passage match check , but this is how we would ..
              if(!matched ) {
                for (x = 0; x < sentences.length; x++) {
                  var ajaxresult = await quizhelper.comparePassageToTranscript(sentences[x].sentence, spoken, sentences[x].phonetic, itemdata.language);
                  var result = JSON.parse(ajaxresult);
                  var haserror=false;
                  for (var i=0;i<result.length;i++){
                    if(result[i].matched===false){haserror=true;break;}
                  }
                  if(!haserror){
                    percent = app.process_accepted_response(itemdata, x);
                    matched=true;
                    break;
                  }
                }
              }

              //if we got a match then process it
            if(matched){
              //proceed to next question
              $(".minilesson_nextbutton").prop("disabled", true);
              setTimeout(function () {
                $(".minilesson_nextbutton").prop("disabled", false);
                app.next_question(percent);
              }, 2000);
              return;
            }else{
              //shake the screen
              $("#" + itemdata.uniqueid + "_correctanswer").effect("shake");
            }
        } //end of switch message type
      }; //end of callback declaration

      //init TT recorder
      var opts = {};
      opts.uniqueid = itemdata.uniqueid;
      log.debug('sa uniqueid:' + itemdata.uniqueid);
      opts.callback = theCallback;
      opts.ds_only=quizhelper.is_ds_only();
      ttrecorder.clone().init(opts);

    } ,//end of init components

    spokenIsCorrect: function(quizhelper, phraseheard, currentphrase) {
      //lets lower case everything
      phraseheard = quizhelper.cleanText(phraseheard);
      currentphrase = quizhelper.cleanText(currentphrase);
      if (phraseheard === currentphrase) {
        return true;
      }
      return false;
    },

    process_accepted_response: function(itemdata, sentenceindex){
      var percent = sentenceindex >= 0 ? 100 : 0;
      //TO DO .. disable TT recorder here
      //disable TT recorder

      if(percent > 0) {
        //turn dots into text (if they were dots)
        if (parseInt(itemdata.show_text) === 0) {
          for (var i = 0; i < itemdata.sentences.length; i++) {
            var theline = $("#" + itemdata.uniqueid + "_option" + (i + 1));
            $("#" + itemdata.uniqueid + "_option" + (i + 1) + ' .minilesson_sentence').text(itemdata.sentences[i].sentence);
          }
        }

        //hightlight successgit cm
        var  answerdisplay =  $("#" + itemdata.uniqueid + "_correctanswer");
        answerdisplay.text(itemdata.sentences[sentenceindex].originalsentence);
        answerdisplay.addClass("minilesson_success");
      }

      return percent;

    },

  };
});