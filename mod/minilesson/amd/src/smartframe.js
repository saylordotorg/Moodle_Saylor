define(['jquery', 'core/log', 'mod_minilesson/definitions'], function($, log, def) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson SmartFrame: initialising');

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
      stepdata.totalitems = 10;
      stepdata.correctitems = percent / 10;
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

      //listen for the grades
      window.addEventListener("message", function(event) {
        log.debug('messageevent',event);

            //if its not really our smartframe host get out of here
            if (event.origin !== itemdata.smartframehost) {
                return;
            }
            //pass back results and transition
            var gradesdata = event.data;
            //each instance of teacher tools itemtype will catch this event, so we need to filter the ones we handle to just this instanc
           //thats why we pass the itemdata around
            if(typeof gradesdata != 'undefined' && gradesdata.itemid == itemdata.id) {
                var thepercent = gradesdata.percent;

                $(".minilesson_nextbutton").prop("disabled", true);
                setTimeout(function () {
                    $(".minilesson_nextbutton").prop("disabled", false);
                    self.next_question(gradesdata.percent);
                }, 500);
            }
          }, false
      );
      
    }
  };
});