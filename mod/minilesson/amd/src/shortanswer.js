define(['jquery', 'core/log', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper'], function($, log, def, polly) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson ShortAnswer: initialising');

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
      
    }
  };
});