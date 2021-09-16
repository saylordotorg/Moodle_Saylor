define(['jquery', 'core/log', 'core/ajax', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper', 'mod_minilesson/cloudpoodllloader','mod_minilesson/ttrecorder'],
    function($, log, ajax, def, polly, cloudpoodll, ttrecorder) {
  "use strict"; // jshint ;_;

  log.debug('MiniLesson listen and repeat: initialising');

  return {

      //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
      },

      init: function(index, itemdata, quizhelper) {
        var self = this;
        var theCallback = function(message) {

            switch (message.type) {
                case 'recording':
                    break;

                case 'speech':
                    log.debug("speech at listen_repeat");
                    self.getComparison(
                        self.items[self.game.pointer].target,
                        message.capturedspeech,
                        self.items[self.game.pointer].phonetic,
                        function(comparison) {
                            self.gotComparison(comparison, message);
                        }
                    );
                    break;

            }

        };

        if(quizhelper.use_ttrecorder()) {
            var opts = {};
            opts.uniqueid = itemdata.uniqueid;
            opts.callback = theCallback;
            ttrecorder.clone().init(opts);
        }else{
            //init cloudpoodll push recorder
            cloudpoodll.init('minilesson-recorder-listenrepeat-' + itemdata.id, theCallback);
        }

        self.itemdata = itemdata;
        self.quizhelper = quizhelper;
        self.index = index;
        self.register_events();
        self.setvoice();
        self.getItems();
    },

    next_question:function(percent){
      var self=this;
      var stepdata = {};
      stepdata.index = self.index;
      stepdata.hasgrade = true;
      stepdata.totalitems=self.items.length;
      stepdata.correctitems=self.items.filter(function(e) {return e.correct;}).length;
      stepdata.grade = Math.round((stepdata.correctitems/stepdata.totalitems)*100);
      self.quizhelper.do_next(stepdata);
    },
    register_events: function() {

      var self = this;

      $("#" + self.itemdata.uniqueid + "_container .minilesson_nextbutton").on('click', function(e) {
        self.next_question();
      });

      $("#" + self.itemdata.uniqueid + "_container .landr_start_btn").on("click", function() {
        self.start();
      });

      $("#" + self.itemdata.uniqueid + "_container .landr_listen_btn").on("click", function() {
        self.items[self.game.pointer].audio.load();
        self.items[self.game.pointer].audio.play();
      });

      $("#" + self.itemdata.uniqueid + "_container .landr_skip_btn").on("click", function() {
        $("#" + self.itemdata.uniqueid + "_container .landr_ctrl-btn").prop("disabled", true);
        $("#" + self.itemdata.uniqueid + "_container .landr_speech.landr_teacher_left").text(self.items[self.game.pointer].target + "");
        setTimeout(function() {
          if (self.game.pointer < self.items.length - 1) {
            self.items[self.game.pointer].answered = true;
            self.items[self.game.pointer].correct = false;
            self.game.pointer++;
            self.nextPrompt();
          } else {
            self.end();
          }
        }, 3000);
      });
      
    },
    

    game: {
      pointer: 0
    },
    usevoice: '',
    setvoice: function() {
            var self = this;
            self.usevoice = self.itemdata.usevoice;
            self.voiceoption=self.itemdata.voiceoption;
            return;
    },
    getItems: function() {
      var self = this;
      var text_items = self.itemdata.sentences;

      self.items = text_items.map(function(target) {
        return {
          landr_targetWords: target.sentence.trim().split(self.quizhelper.spliton_regexp).filter(function(e) {
            return e !== "";
          }),
          target: target.sentence,
          prompt: target.displaysentence,
          phonetic: target.phonetic,
          typed: "",
          answered: false,
          correct: false,
          audio: null
        };
      }).filter(function(e) {
        return e.target !== "";
      });


      $.each(self.items, function(index, item) {
        polly.fetch_polly_url(item.prompt,  self.voiceoption, self.usevoice).then(function(audiourl) {
          item.audio = new Audio();
          item.audio.src = audiourl;
          if (self.items.filter(function(e) {
              return e.audio === null
            }).length === 0) {
            self.appReady();
          }
        });
      });



    },
    appReady: function() {
      var self = this;
      $("#" + self.itemdata.uniqueid + "_container .landr_not_loaded").hide();
      $("#" + self.itemdata.uniqueid + "_container .landr_loaded").show();
      $("#" + self.itemdata.uniqueid + "_container .landr_start_btn").prop("disabled", false);
    },
    gotComparison: function(comparison, typed) {
      var self = this;

      $("#" + self.itemdata.uniqueid + "_container .landr_targetWord").removeClass("landr_correct landr_incorrect");
      $("#" + self.itemdata.uniqueid + "_container .landr_feedback").removeClass("fa fa-check fa-times");

      var allCorrect = comparison.filter(function(e){return !e.matched;}).length==0;
      
      if (allCorrect && comparison && comparison.length>0) {
        
        $("#" + self.itemdata.uniqueid + "_container .landr_targetWord").addClass("landr_correct");
        $("#" + self.itemdata.uniqueid + "_container .landr_feedback").addClass("fa fa-check");
        $("#" + self.itemdata.uniqueid + "_container .landr_speech.landr_teacher_left").text(self.items[self.game.pointer].target + "");

        self.items[self.game.pointer].answered = true;
        self.items[self.game.pointer].correct = true;
        self.items[self.game.pointer].typed = typed;

        $("#" + self.itemdata.uniqueid + "_container .landr_ctrl-btn").prop("disabled", true);
        if (self.game.pointer < self.items.length - 1) {
          setTimeout(function() {
            self.game.pointer++;
            self.nextPrompt();
          }, 3000);
        } else {
          setTimeout(function() {
            self.end();
          }, 3000);
        }

      } else {

        comparison.forEach(function(obj) {
          if(!obj.matched){
            $("#" + self.itemdata.uniqueid + "_container .landr_targetWord[data-idx='" + obj.wordnumber + "']").addClass("landr_incorrect");
            $("#" + self.itemdata.uniqueid + "_container .landr_feedback[data-idx='" + obj.wordnumber + "']").addClass("fa fa-times");
          } else {
            $("#" + self.itemdata.uniqueid + "_container .landr_targetWord[data-idx='" + obj.wordnumber + "']").addClass("landr_correct");
            $("#" + self.itemdata.uniqueid + "_container .landr_feedback[data-idx='" + obj.wordnumber + "']").addClass("fa fa-check");
          }
        });

        $("#" + self.itemdata.uniqueid + "_container .landr_reply_" + self.game.pointer).effect("shake", function() {
          $("#" + self.itemdata.uniqueid + "_container .landr_ctrl-btn").prop("disabled", false);
        });

      }

      $("#" + self.itemdata.uniqueid + "_container .landr_targetWord.landr_correct").each(function() {
        var realidx = $(this).data("realidx");
        var landr_targetWord = self.items[self.game.pointer].landr_targetWords[realidx];
        $(this).val(landr_targetWord);
      });

    },
    getWords: function(thetext) {
      var self = this;
      var checkcase = false;
      if (checkcase == 'false') {
        thetext = thetext.toLowerCase();
      }
      var chunks = thetext.split(self.quizhelper.spliton_regexp).filter(function(e) {
        return e !== "";
      });
      var words = [];
      for (var i = 0; i < chunks.length; i++) {
        if (!chunks[i].match(self.quizhelper.spliton_regexp)) {
          words.push(chunks[i]);
        }
      }
      return words;
    },
    getComparison: function(passage, transcript, phonetic, callback) {
      var self = this;
      
      $(".landr_ctrl-btn").prop("disabled", true);
      self.quizhelper.comparePassageToTranscript(passage,transcript,phonetic,self.itemdata.language).then(function(ajaxresult) {
            var payloadobject = JSON.parse(ajaxresult);
            if (payloadobject) {
                callback(payloadobject);
            } else {
                callback(false);
            }
       });

    },
    end: function() {
      var self = this;

      var numCorrect = self.items.filter(function(e) {
        return e.correct;
      }).length;

      var totalNum = self.items.length;

      $("#" + self.itemdata.uniqueid + "_container .landr_results").html("TOTAL<br/>" + numCorrect + "/" + totalNum).show();
      
      $(".minilesson_nextbutton").prop("disabled",true);
      setTimeout(function() {
        
        $(".minilesson_nextbutton").prop("disabled",false);
        self.next_question();
        
      }, 2000);

    },
    start: function() {
      var self = this;

      $("#" + self.itemdata.uniqueid + "_container .landr_ctrl-btn").prop("disabled", true);
      $("#" + self.itemdata.uniqueid + "_container .landr_speakbtncontainer").show();

      self.items.forEach(function(item) {
        item.spoken = "";
        item.answered = false;
        item.correct = false;
      });

      self.game.pointer = 0;

      $("#" + self.itemdata.uniqueid + "_container .landr_game").show();
      $("#" + self.itemdata.uniqueid + "_container .landr_start_btn").hide();
      $("#" + self.itemdata.uniqueid + "_container .landr_mainmenu").hide();
      $("#" + self.itemdata.uniqueid + "_container .landr_controls").show();

      self.nextPrompt();

    },
    nextPrompt: function() {
      var showText = parseInt(this.itemdata.show_text);
      var self = this;

      var target = self.items[self.game.pointer].target;
      var code = "<div class='landr_prompt landr_prompt_" + self.game.pointer + "' style='display:none;'>";

      code += "<i class='fa fa-graduation-cap landr_speech-icon-left'></i>";
      code += "<div style='margin-left:90px;' class='landr_speech landr_teacher_left'>";
      if(!showText){
        var nopunc = target.replace(self.quizhelper.nopunc_regexp,"");
        var dots = nopunc.replace(self.quizhelper.nonspaces_regexp, '•');
        code += dots;
      } else{
        code += target;
      }
      code += "</div>";
      code += "</div>";

      $("#" + self.itemdata.uniqueid + "_container .landr_game").html(code);
      $(".landr_ctrl-btn").prop("disabled", false);

      var color;

      var progress = self.items.map(function(item, idx) {
        color = "gray";
        if (self.items[idx].answered && self.items[idx].correct) {
          color = "green";
        } else if (self.items[idx].answered && !self.items[idx].correct) {
          color = "red";
        }
        return "<i style='color:" + color + "' class='fa fa-circle'></i>";
      }).join(" ");

      $("#" + self.itemdata.uniqueid + "_container .landr_title").html(progress);
      $(".landr_prompt_" + self.game.pointer).toggle("slide", {
        direction: 'left'
      });

      self.nextReply();

    },
    nextReply: function() {
      var self = this;
      var target = self.items[self.game.pointer].target;
      var code = "<div class='landr_reply landr_reply_" + self.game.pointer + "' style='display:none;'>";
      code += "<i class='fa fa-user landr_speech-icon-right'></i>";
      var landr_targetWordsCode = "";
      var idx = 1;
      self.items[self.game.pointer].landr_targetWords.forEach(function(word, realidx) {
        if (!word.match(self.quizhelper.spliton_regexp)) {
          landr_targetWordsCode += "<ruby><input disabled type='text' maxlength='" + word.length + "' size='" + (word.length + 1) + "' class='landr_targetWord' data-realidx='" + realidx + "' data-idx='" + idx + "'><rt><i data-idx='" + idx + "' class='landr_feedback'></i></rt></ruby>";
          idx++;

        } else {
          landr_targetWordsCode += word;
        }
      });
      code += "<div style='margin-right:90px;' class='landr_speech landr_right'>" + landr_targetWordsCode + "</div>";
      code += "</div>";
      $("#" + self.itemdata.uniqueid + "_container .landr_game").append(code);
      $(".landr_reply_" + self.game.pointer).toggle("slide", {
        direction: 'right'
      });
      $("#" + self.itemdata.uniqueid + "_container .landr_ctrl-btn").prop("disabled", false);
      if(!self.quizhelper.mobile_user()){
        setTimeout(function(){
          $("#" + self.itemdata.uniqueid + "_container .landr_listen_btn").trigger('click');
        },1000);
      }
    }

  };
});