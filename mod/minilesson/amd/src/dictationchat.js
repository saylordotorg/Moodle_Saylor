define(['jquery', 'core/log', 'core/ajax', 'mod_minilesson/definitions', 'mod_minilesson/pollyhelper'], function($, log, ajax, def, polly) {
  "use strict"; // jshint ;_;

  log.debug('MiniLesson dictation chat: initialising');

  return {

      //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
      },

      usevoice: 'Amy',

      init: function(index, itemdata, quizhelper) {
      var self = this;
      self.itemdata = itemdata;
      self.quizhelper = quizhelper;
      self.index = index;
      self.register_events();
      self.setvoice();
      self.getItems();

    },
    next_question:function(){
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

      $("#" + self.itemdata.uniqueid + "_container .dictate_start_btn").on("click", function() {
        self.start();
      });

      $("#" + self.itemdata.uniqueid + "_container .dictate_listen_btn").on("click", function() {
        self.items[self.game.pointer].audio.load();
        self.items[self.game.pointer].audio.play();
      });

      //on skip button click
      $("#" + self.itemdata.uniqueid + "_container .dictate_skip_btn").on("click", function() {
        //disable buttons
        $("#" + self.itemdata.uniqueid + "_container .dictate_ctrl-btn").prop("disabled", true);
        //reveal prompt
        $("#" + self.itemdata.uniqueid + "_container .dictate_speech.dictate_teacher_left").text(self.items[self.game.pointer].prompt + "");
        //reveal answers
        //reveal the answer
        $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord").each(function() {
          var realidx = $(this).data("realidx");
          var dictate_targetWord = self.items[self.game.pointer].dictate_targetWords[realidx];
          $(this).val(dictate_targetWord);
        });


        //move on after 3s
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

      
      $("#" + self.itemdata.uniqueid + "_container .dictate_check_btn").on("click", function() {
        self.check_answer();
      });

      //listen for enter key
      $("#" + self.itemdata.uniqueid + "_container").on("keydown",".dictate_targetWord", function(e) {
        if (e.which == 13) {
          self.check_answer();
        }
      });

      //auto nav between inputs
      $("#" + self.itemdata.uniqueid + "_container").on("keyup",".dictate_targetWord", function(e) {

        //move focus between textboxes
        //log.debug(e);
        var target = e.srcElement || e.target;
        var maxLength = parseInt(target.attributes["maxlength"].value, 10);
        var myLength = target.value.length;
        var key = e.which;
        if (myLength >= maxLength) {
          var next = $(this);
          while (next = next.parent().next().children('input.dictate_targetWord')) {
            if (next.length === 1){
              next.focus();
              break;
            }else{
              break;

            }
          }
          // Move to previous field if empty (user pressed backspace or delete)
        } else if (( key == 8 || key == 46 ) && myLength === 0) {
          var previous = $(this);
          while (previous = previous.parent().prev().children('input.dictate_targetWord')) {
            if (previous.length === 1) {
              previous.focus();
              break;
            }else{
              break;
            }
          }
        }
      });
      
    },

    game: {
      pointer: 0
    },

    check_answer: function(){
      var self = this;
      var passage = self.items[self.game.pointer].target;
      var transcriptArray = [];

      $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord").each(function() {
        transcriptArray.push($(this).val().trim() == "" ? "|" : $(this).val().trim());
      });

      var transcript = transcriptArray.join(" ");

      self.getComparison(passage, transcript, function(comparison) {
        self.gotComparison(comparison, transcript);
      });
    },
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
          dictate_targetWords: target.sentence.trim().split(self.quizhelper.spliton_regexp).filter(function(e) {
            return e !== "";
          }),
          target: target.sentence,
          prompt: target.prompt,
          displayprompt: target.displayprompt,
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
              return e.audio == null
            }).length == 0) {
            self.appReady();
          } else {
            console.log(self.items);
          }
        });

      });

    },
    appReady: function() {
      var self = this;
      $("#" + self.itemdata.uniqueid + "_container .dictate_not_loaded").hide();
      $("#" + self.itemdata.uniqueid + "_container .dictate_loaded").show();
      $("#" + self.itemdata.uniqueid + "_container .dictate_start_btn").prop("disabled", false);
    },
    gotComparison: function(comparison, typed) {

      var self = this;

      $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord").removeClass("dictate_correct dictate_incorrect");
      $("#" + self.itemdata.uniqueid + "_container .dictate_feedback").removeClass("fa fa-check fa-times");

      var allCorrect = comparison.filter(function(e) {
        return !e.matched;
      }).length == 0;

      if (allCorrect) {

        $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord").addClass("dictate_correct").prop("disabled",true);
        $("#" + self.itemdata.uniqueid + "_container .dictate_feedback").addClass("fa fa-check");
        $("#" + self.itemdata.uniqueid + "_container .dictate_speech.dictate_teacher_left").text(self.items[self.game.pointer].target + "");

        self.items[self.game.pointer].answered = true;
        self.items[self.game.pointer].correct = true;
        self.items[self.game.pointer].typed = typed;

        $("#" + self.itemdata.uniqueid + "_container .dictate_ctrl-btn").prop("disabled", true);
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
          if (!obj.matched) {
            $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord[data-idx='" + obj.wordnumber + "']").addClass("dictate_incorrect");
            $("#" + self.itemdata.uniqueid + "_container .dictate_feedback[data-idx='" + obj.wordnumber + "']").addClass("fa fa-times");
          } else {
            $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord[data-idx='" + obj.wordnumber + "']").addClass("dictate_correct").prop("disabled",true);
            $("#" + self.itemdata.uniqueid + "_container .dictate_feedback[data-idx='" + obj.wordnumber + "']").addClass("fa fa-check");
          }
        });

        $("#" + self.itemdata.uniqueid + "_container .dictate_reply_" + self.game.pointer).effect("shake", function() {
          $("#" + self.itemdata.uniqueid + "_container .dictate_ctrl-btn").prop("disabled", false);
        });

      }

      $("#" + self.itemdata.uniqueid + "_container .dictate_targetWord.dictate_correct").each(function() {
        var realidx = $(this).data("realidx");
        var dictate_targetWord = self.items[self.game.pointer].dictate_targetWords[realidx];
        $(this).val(dictate_targetWord);
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
    getComparison: function(passage, transcript, callback) {
        var self = this;

        $(".dictate_ctrl-btn").prop("disabled", true);
        var phonetic ='';//we do not want a phonetic match in dictation.
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

      $("#" + self.itemdata.uniqueid + "_container .dictate_results").html("TOTAL<br/>" + numCorrect + "/" + totalNum).show();
      
      $(".minilesson_nextbutton").prop("disabled",true);
      setTimeout(function() {
        $(".minilesson_nextbutton").prop("disabled",false);
        self.next_question();
      }, 2000);
      

    },
    start: function() {
      var self = this;

      $("#" + self.itemdata.uniqueid + "_container .dictate_ctrl-btn").prop("disabled", true);

      self.items.forEach(function(item) {
        item.spoken = "";
        item.answered = false;
        item.correct = false;
      });

      self.game.pointer = 0;

      $("#" + self.itemdata.uniqueid + "_container .dictate_game").show();
      $("#" + self.itemdata.uniqueid + "_container .dictate_start_btn").hide();
      $("#" + self.itemdata.uniqueid + "_container .dictate_mainmenu").hide();
      $("#" + self.itemdata.uniqueid + "_container .dictate_controls").show();

      self.nextPrompt();

    },
    nextPrompt: function() {

      var self = this;
      var target = self.items[self.game.pointer].target;
      var prompt = self.items[self.game.pointer].prompt;
      var displayprompt = self.items[self.game.pointer].displayprompt;

      var nopunc = prompt.replace(self.quizhelper.nopunc_regexp,"");
      var dots = nopunc.replace(self.quizhelper.nonspaces_regexp, '•');
      var code = "<div class='dictate_prompt dictate_prompt_" + self.game.pointer + "' style='display:none;'>";

      code += "<i class='fa fa-graduation-cap dictate_speech-icon-left'></i>";
      code += "<div style='margin-left:90px;' class='dictate_speech dictate_teacher_left'>";
      code += dots;
      code += "</div>";
      code += "</div>";

      $("#" + self.itemdata.uniqueid + "_container .dictate_game").html(code);
      $(".dictate_ctrl-btn").prop("disabled", false);

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

      $("#" + self.itemdata.uniqueid + "_container .dictate_title").html(progress);
      $(".dictate_prompt_" + self.game.pointer).toggle("slide", {
        direction: 'left'
      });

      self.nextReply();

    },
    nextReply: function() {
      var self = this;
      var target = self.items[self.game.pointer].target;
      var code = "<div class='dictate_reply dictate_reply_" + self.game.pointer + "' style='display:none;'>";
      code += "<i class='fa fa-user dictate_speech-icon-right'></i>";
      var dictate_targetWordsCode = "";
      var idx = 1;
      self.items[self.game.pointer].dictate_targetWords.forEach(function(word, realidx) {
        if (!word.match(self.quizhelper.spliton_regexp)) {
          dictate_targetWordsCode += "<ruby><input type='text' maxlength='" + word.length + "' size='" + (word.length + 1) + "' class='dictate_targetWord' data-realidx='" + realidx + "' data-idx='" + idx + "'><rt><i data-idx='" + idx + "' class='dictate_feedback'></i></rt></ruby>";
          idx++;
        } else {
          dictate_targetWordsCode += word;
        }
      });
      code += "<div style='margin-right:90px;' class='dictate_speech dictate_right'>" + dictate_targetWordsCode + "</div>";
      code += "</div>";
      $("#" + self.itemdata.uniqueid + "_container .dictate_game").append(code);
      $(".dictate_reply_" + self.game.pointer).toggle("slide", {
        direction: 'right'
      });
      $("#" + self.itemdata.uniqueid + "_container .dictate_ctrl-btn").prop("disabled", false);
      setTimeout(function() {
        $(".dictate_targetWord").first().focus();
        $("#" + self.itemdata.uniqueid + "_container .dictate_listen_btn").trigger('click');
      }, 500);
    }

  };
});