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
        $("#" + itemdata.uniqueid + "_container .minilesson_mc_wrong").show();
        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minilesson_mc_wrong").hide();
        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minilesson_mc_right").show();
        
        //highlight selected answers
        $("#" + itemdata.uniqueid + "_option" + checked).addClass('minilesson_mc_selected');


        var percent = checked == itemdata.correctanswer ? 100 : 0;
        
        $(".minilesson_nextbutton").prop("disabled", true);
        setTimeout(function() {
          $(".minilesson_nextbutton").prop("disabled", false);
          self.next_question(percent);
        }, 2000);
        
      });
      
    }
  };
});