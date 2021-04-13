define(['jquery', 'core/log', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper'], function($, log, def, polly) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson Quiz helper: initialising');

  return {

    playing: false,

      //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
      },


      init: function(index, itemdata, quizhelper, polly) {

      this.prepare_audio(itemdata, polly);
      this.register_events(index, itemdata, quizhelper);

    },

    prepare_html: function(itemdata) {
      //do something
    },

    prepare_audio: function(itemdata) {
      $.each(itemdata.sentences, function(index, sentence) {
        polly.fetch_polly_url(sentence.displaysentence, itemdata.voiceoption, itemdata.usevoice).then(function(audiourl) {
          $("#" + itemdata.uniqueid + "_container .dictationplayer_" + index + " .dictationtrigger").attr("data-src", audiourl);
        });
      });
    },

    register_events: function(index, itemdata, quizhelper) {

      var self = this;

      var theplayer = $("#" + itemdata.uniqueid + "_player");

      //key events in text box
      $("#" + itemdata.uniqueid + "_container .poodlldictationinput input").on("input", function(e) {

        var index = $(this).data("index");
        var correct = itemdata.sentences[index].sentence.trim().toLowerCase();
        var typed = $(this).val().trim().toLowerCase();
        $("#"+itemdata.uniqueid+"_container .dictationplayer_"+index+"_chars").html(typed.length);
        if (correct == typed) {
          $("#"+itemdata.uniqueid+"_container .dictate-feedback[data-index='" + index + "']").removeClass("fa-times").addClass("fa-check").css("color","green").show();
        } else {
          $("#"+itemdata.uniqueid+"_container .dictate-feedback[data-index='" + index + "']").removeClass("fa-check").addClass("fa-times").css("color","red").show();
        }

      });

      //audio play requests
      $("#" + itemdata.uniqueid + "_container .dictationtrigger").on('click', function(e) {
        if (!self.playing) {
          var el = this;
          self.playing = true;
          theplayer.attr('src', $(this).attr('data-src'));
          theplayer[0].play();
          theplayer[0].onended = function() {
            $(el).find(".fa").removeClass("fa-spin fa-spinner").addClass("fa-play");
            self.playing = false;
          }
          $(el).find(".fa").removeClass("fa-play").addClass("fa-spin fa-spinner");
        }
      });

      //When click next button , report and leave it up to parent to eal with it.
      $("#" + itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
        var stepdata = {};
        var correct = $('#' + itemdata.uniqueid + '_container .dictate-feedback.fa-check').length;
        var total = $('#' + itemdata.uniqueid + '_container .dictate-feedback').length;
        var grade = Math.round(correct / total, 2) * 100;
        stepdata.index = index;
        stepdata.hasgrade = true;
        stepdata.grade = grade;
        stepdata.totalitems=total;
        stepdata.correctitems=correct;
        stepdata.grade = grade;
        quizhelper.do_next(stepdata);
      });
    }
  }; //end of return value
});