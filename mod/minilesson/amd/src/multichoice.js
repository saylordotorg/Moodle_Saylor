define(['jquery', 'core/log', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper'], function($, log, def, polly) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson Multichoice: initialising');

  return {

    //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
     },

    init: function(index, itemdata, quizhelper) {
      if(itemdata.hasOwnProperty('audiocontent')) {
          this.prepare_audio(itemdata);
      }
      this.register_events(index, itemdata, quizhelper);
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
    register_events: function(index, itemdata, quizhelper) {
      
      var self = this;
      self.index = index;
      self.quizhelper = quizhelper;
      var theplayer = $("#" + itemdata.uniqueid + "_player");
      
      $("#" + itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
        self.next_question(0);
      });
      
      $("#" + itemdata.uniqueid + "_container .minilesson_mc_response").on('click', function(e) {
        //if disabled =>just return (already answered)
          if($("#" + itemdata.uniqueid + "_container .minilesson_mc_response").hasClass('minilesson_mc_disabled')){
            return;
          }

          //get selected item index
          var checked = $(this).data('index');

          //disable the answers, cos its answered
          $("#" + itemdata.uniqueid + "_container .minilesson_mc_response").addClass('minilesson_mc_disabled');

        //reveal answers
        $("#" + itemdata.uniqueid + "_container .minilesson_mc_unanswered").hide();
        $("#" + itemdata.uniqueid + "_container .minilesson_mc_wrong").show();

        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minilesson_mc_wrong").hide();
        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minilesson_mc_right").show();


        //if answers were dots for audio content, show them
          if(itemdata.hasOwnProperty('audiocontent')) {
              for (var i = 0; i < itemdata.sentences.length; i++) {
                  var theline = $("#" + itemdata.uniqueid + "_option" + (i + 1));
                  $("#" + itemdata.uniqueid + "_option" + (i + 1) + ' .minilesson_sentence').text(itemdata.sentences[i].sentence);
              }
          }

        
        //highlight selected answers
        $("#" + itemdata.uniqueid + "_option" + checked).addClass('minilesson_mc_selected');


        var percent = checked == itemdata.correctanswer ? 100 : 0;
        
        $(".minilesson_nextbutton").prop("disabled", true);
        setTimeout(function() {
          $(".minilesson_nextbutton").prop("disabled", false);
          self.next_question(percent);
        }, 2000);
        
      });

      //play audio if we are doing this as an audio player thingy
        //this will use the multichoice audio content template
        $("#" + itemdata.uniqueid + "_container .minilesson_mc_audioplayer").on('click', function(e) {
            //if disabled =>just return (already answered)
            if($("#" + itemdata.uniqueid + "_container .minilesson_mc_audioplayer").hasClass('minilesson_mc_disabled')){
                return;
            }

            //audio play requests
            if (!self.playing) {
                var el = this;
                self.playing = true;
                theplayer.attr('src', $(this).attr('data-src'));
                theplayer[0].play();
                theplayer[0].onended = function() {
                    $(el).find(".minilesson_mc_playstate").removeClass("fa-spin fa-spinner").addClass("fa-play");
                    self.playing = false;
                };
                $(el).find(".minilesson_mc_playstate").removeClass("fa-play").addClass("fa-spin fa-spinner");
            }else{
                theplayer[0].pause();
                theplayer[0].currentTime=0;
                $("#" + itemdata.uniqueid + "_container .minilesson_mc_playstate").removeClass("fa-spin fa-spinner").addClass("fa-play");
                self.playing = false;
            }
        });
      
    },

      prepare_audio: function(itemdata) {
          // debugger;
          $.each(itemdata.sentences, function(index, sentence) {
              polly.fetch_polly_url(sentence.sentence, itemdata.voiceoption, itemdata.usevoice).then(function(audiourl) {
                  $("#" + itemdata.uniqueid + "_audioplayer" + (index+1)).attr("data-src", audiourl);
              });
          });
      },
  };
});