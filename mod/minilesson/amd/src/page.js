define(['jquery', 'core/log', 'mod_minilesson/definitions'], function($, log, def) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('MiniLesson Page: initialising');

  return {

      //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
      },


      init: function(index, itemdata, quizhelper) {
      this.register_events(index, itemdata, quizhelper);
    },

    prepare_html: function(itemdata) {
      //do something
    },

    register_events: function(index, itemdata, quizhelper) {
      //When click next button , report and leave it up to parent to eal with it.
      $("#" + itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
        var stepdata = {};
          stepdata.index = index;
          stepdata.hasgrade = false;
          stepdata.totalitems=0;
          stepdata.correctitems=0;
          stepdata.grade = 0;
        quizhelper.do_next(stepdata);
      });
    }
  }; //end of return value
});