define(['jquery', 'core/log', 'core/ajax', 'mod_readaloud/definitions', 'mod_readaloud/cloudpoodllloader', 'mod_readaloud/ttrecorder'],
    function($, log, ajax, def, cloudpoodll, ttrecorder) {
  "use strict"; // jshint ;_;

  log.debug('Readaloud listen and repeat: initialising');

  return {

    activated: false,
    currentSentence: "",
    currentPhonetic: "",
    language: "en-US",
    currentAudioStart: 0,
    currentAudioStop: 0,
    oldBreak: {},
    newBreak: {},
    mak: null,
    controls: {},
    results: [],
    phonetics: [],
    cmid: 0,
    ttr: {},

    init: function(props) {

      var self = this;
      self.cmid = props.cmid;
      self.mak = props.modelaudiokaraoke;
      self.language = props.language;
      self.region = props.region;
      self.phonetics = props.phonetics;
      self.stt_guided = props.stt_guided;
      self.shadow = false;//props.shadow;
      self.ttr={};

      //recorder stuff
      var theCallback =function(message) {
          switch (message.type) {
            case 'recordingstarted':
              if (self.controls.shadowplaycheckbox.is(":checked")) {
                self.shadow = true;
                log.debug('shadow is true');
                self.controls.playbutton.trigger('click');
              }else{
                log.debug('shadow is false');
                self.shadow=false;
              }
              //hide the self model player (though it may not be here) because we do not want it playing old stuff into mic
              self.controls.playselfbutton.hide();

              break;

            case 'recordingstopped':
                  if (self.shadow === true){
                    self.controls.hiddenplayer[0].pause();
                  }
                  if(self.stt_guided || self.ttr.usebrowserrec===false){
                    self.controls.playselfbutton.show();
                  }
                  break;

              case 'speech':

                  self.getComparison(
                      self.cmid,
                      self.currentSentence,
                      message.capturedspeech,
                      self.currentPhonetic,
                      function(comparison) {
                          self.gotComparison(comparison, message);
                      }
                  );
                  break;
          }
      };

        //init tt recorder
      var opts = {};
      opts.uniqueid = 'readaloud_ttrecorder';
      opts.stt_guided = self.stt_guided;
      opts.callback = theCallback;
      opts.shadow = true;
      self.ttr = ttrecorder.clone();
      self.ttr.init(opts);


      self.prepare_controls();
      self.register_events();
      self.register_mak();
    },

    activate: function() {
      this.results = [];
      this.activated = true;
    },
    deactivate: function() {
      if (this.mak.controls.audioplayer[0].playing) {
        this.mak.controls.audioplayer[0].pause();
      }
      this.activated = false;
    },

    prepare_controls: function() {
      var self = this;
      self.controls.container = $('#' + def.landrcontainer);
      self.controls.hiddenplayer = $('#mod_readaloud_landr_hiddenplayer');
      self.controls.hiddenselfplayer = $('#mod_readaloud_landr_hiddenselfplayer');
      self.controls.playbutton = $('#mod_readaloud_landr_modalplay');
      self.controls.shadowplaycheckbox = $('#mod_readaloud_landr_shadow');
      self.controls.skipbutton = $('#mod_readaloud_landr_modalskip');
      self.controls.finishedbutton = $("#mod_readaloud_landr_modalfinished");
      self.controls.playselfbutton = $("#mod_readaloud_landr_modalplayself");
      self.audiourl = self.mak.fetch_audio_url();
      self.controls.hiddenplayer.attr('src', self.audiourl);

    },

    register_mak: function() {
      var self = this;

      self.mak.on_reach_audio_break = function(sentence, oldbreak, newbreak, breaks) {
       // log.debug(breaks);
        //do not get involved if we are not active
        //model audio karaoke is used elsewhere (shadow and preview) as well
        if (!self.activated) {
          return;
        }

        // sentence contains the target text
        //empty strings are none of our concern
        if (sentence.trim() === '') {
          return;
        }

        self.currentSentence = sentence;
        self.oldBreak = oldbreak;
        self.newBreak = newbreak;
        self.currentAudioStart = oldbreak.audiotime;
        self.currentAudioEnd = newbreak.audiotime;

        if(self.currentAudioStart===self.currentAudioEnd){
            //This is a special case where the end of the audio has been reached in MAK, and there is now no next break
            self.currentAudioEnd=self.controls.hiddenplayer[0].duration;
        }

          if(self.phonetics.length>newbreak.wordnumber-1){
              var startpos = oldbreak.wordnumber;
              if(startpos<0){startpos=0;}
              var endpos = newbreak.wordnumber;

              /*
              * break=0: wordnumber 0 start = 0, end = 9: jssplit returns 0-8
              * break=1: wordnumber 9 start = 9, end = 18: jssplit returns 9-17
              * break=2: wordnumber 18 start = 18, end = 99: jssplit returns 18-98
               */
              self.currentPhonetic = self.phonetics.slice(startpos,endpos).join(' ');
          }else{
              self.currentPhonetic  = '';
          }

        //pause audio while we do our thing
        if (oldbreak.breaknumber == 0 && newbreak == false) {
          // do nothing
        } else {
          // detect last line
          if (oldbreak.breaknumber == breaks[breaks.length - 1].breaknumber) {
            self.controls.finishedbutton.show();
            self.controls.skipbutton.hide();
            self.oldBreak.isfinalbreak=true;
          } else {
            self.controls.finishedbutton.hide();
            self.controls.skipbutton.show();
          }
          self.mak.pause_audio();
          self.controls.container.modal('show');
          $("#mod_readaloud_modal_target_phrase").html(sentence.split(/ /).map(function(e, i) {
            return '<div class="mod_readaloud_modal_target_word" data-index="' + i + '">' + e + '</div>';
          }));
        }

      };

    },

    register_events: function() {

      var self = this;

      self.controls.playbutton.on('click', function(e) {
        if (!self.controls.hiddenplayer[0].paused) {
          self.controls.hiddenplayer[0].pause();
        }else {
          self.controls.hiddenplayer[0].currentTime = self.currentAudioStart;
          self.controls.hiddenplayer[0].play();
        }
      });

      self.controls.playselfbutton.on('click',function(e){
        if (!self.controls.hiddenselfplayer[0].paused) {
          self.controls.hiddenselfplayer[0].pause();
        }else {
          self.controls.hiddenselfplayer.attr('src', self.ttr.audio.dataURI);
          self.controls.hiddenselfplayer[0].play();
        }
      });

      self.controls.skipbutton.on('click', function(e) {
        self.controls.container.modal('hide');

        //hide the self model player because when we show page again we dont want it enabled
        self.controls.playselfbutton.hide();

        //we might get here from a 100% score on final break on the modal (it calls the skip button
        //so we check if its finished or not. Otherwise it will return to the first break and start playing
        if(self.oldBreak.isfinalbreak) {
          self.mak.controls.audioplayer[0].currentTime = 0;
        }else{
          if (self.controls.hiddenplayer[0].playing) {
            self.controls.hiddenplayer[0].pause();
          }
          self.controls.hiddenplayer[0].currentTime = self.currentAudioStart;
          //if the main page player has gone a bit forward, we can lose the first part of our next break, so we adjust that if we have to
          //we dont want it hitting the same break repeatedly though, so we inc by .1 to get clear of that
          log.debug('mak audio time', self.mak.get_audio_time());
          log.debug('self current audio end', self.currentAudioEnd);
          if(self.mak.get_audio_time() > self.currentAudioEnd+.1){
            self.mak.set_audio_time(self.currentAudioEnd+.1);
          }
            self.mak.play_audio();
        }
      });

      self.controls.finishedbutton.on('click', function() {
        self.controls.container.modal('hide');
        self.mak.controls.audioplayer[0].currentTime = 0;
      });

      self.controls.hiddenplayer[0].ontimeupdate = function() {
        if (self.controls.hiddenplayer[0].currentTime >= self.currentAudioEnd) {
          self.controls.hiddenplayer[0].pause();
        }
      };

    },

   // spliton: new RegExp('([,.!?:;" ])', 'g'),
      spliton: new RegExp(/([!"# $%&'()。「」、*+,-.\/:;<=>?@[\]^_`{|}~])/, 'g'),

    gotComparison: function(comparison, typed) {
     if(!comparison){return;}
      var self = this;
      var thisClass;
      var wordsmatched=0;
      $(".mod_readaloud_modal_target_word").removeClass("mod_readaloud_modal_target_word_correct mod_readaloud_modal_target_word_incorrect");

      comparison.forEach(function(word, idx) {

        if( word.matched) {
            thisClass = "mod_readaloud_modal_target_word_correct" ;
            wordsmatched++;
        }else{
            thisClass = "mod_readaloud_modal_target_word_incorrect";
        }
        $(".mod_readaloud_modal_target_word[data-index='" + idx + "']").addClass(thisClass);
        if(comparison.length == wordsmatched){
            setTimeout(function(){self.controls.skipbutton.trigger('click');},600);
        }
      });

    },
    getComparison: function(cmid, passage, transcript,passagephonetic, callback) {
      var self = this;

      ajax.call([{
        methodname: 'mod_readaloud_compare_passage_to_transcript',
        args: {
          cmid: cmid,
          passage: passage,
          transcript: transcript,
          passagephonetic: passagephonetic,
          language: self.language
        },
        done: function(ajaxresult) {
          var payloadobject = JSON.parse(ajaxresult);
          if (payloadobject) {
            callback(payloadobject);
          } else {
            callback(false);
          }
        },
        fail: function(err) {
          log.debug(err);
        }
      }]);

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
      }
  };
});