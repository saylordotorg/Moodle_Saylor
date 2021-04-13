define(['jquery', 'core/log', 'core/ajax', 'mod_readaloud/definitions', 'mod_readaloud/cloudpoodllloader', 'mod_readaloud/ttrecorder'],
    function($, log, ajax, def, cloudpoodll, ttrecorder) {
  "use strict"; // jshint ;_;

  log.debug('Readaloud listen and repeat: initialising');

  return {

    activated: false,
    currentSentence: "",
    language: "en-US",
    currentAudioStart: 0,
    currentAudioStop: 0,
    mak: null,
    controls: {},
    results: [],
    cmid: 0,

    init: function(props) {

      var self = this;
      self.cmid = props.cmid;
      self.mak = props.modelaudiokaraoke;
      self.language = props.language;
      self.region = props.region;

      //recorder stuff
      var recid = 'readaloud_pushrecorder';
      var theCallback =function(message) {
          switch (message.type) {
              case 'recording':
                  break;

              case 'speech':
                  self.getComparison(
                      self.cmid,
                      self.currentSentence,
                      message.capturedspeech,
                      function(comparison) {
                          self.gotComparison(comparison, message);
                      }
                  );
                  break;
          }
      };

      if(self.use_ttrecorder()) {
            //init tt recorder
            var opts = {};
            opts.uniqueid = recid;
            opts.callback = theCallback;
            ttrecorder.clone().init(opts);
      }else{
            //init cloudpoodll push recorder
            cloudpoodll.init(recid, theCallback);
      }



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
      self.controls.playbutton = $('#mod_readaloud_landr_modalplay');
      self.controls.skipbutton = $('#mod_readaloud_landr_modalskip');
      self.controls.finishedbutton = $("#mod_readaloud_landr_modalfinished");
      self.audiourl = self.mak.fetch_audio_url();
      self.controls.hiddenplayer.attr('src', self.audiourl);

    },

    register_mak: function() {
      var self = this;

      self.mak.on_reach_audio_break = function(sentence, oldbreak, newbreak, breaks) {
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
        self.currentAudioStart = oldbreak.audiotime;
        self.currentAudioEnd = newbreak.audiotime;

        log.debug(sentence);
        log.debug(oldbreak);
        log.debug(newbreak);

        //pause audio while we do our thing
        if (oldbreak.breaknumber == 0 && newbreak == false) {
          // do nothing
        } else {
          // detect last line
          if (newbreak.breaknumber == breaks[breaks.length - 1].breaknumber) {
            self.controls.finishedbutton.show();
            self.controls.skipbutton.hide();
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

      }

    },

    register_events: function() {

      var self = this;

      self.controls.playbutton.on('click', function(e) {
        self.controls.hiddenplayer[0].currentTime = self.currentAudioStart;
        self.controls.hiddenplayer[0].play();
      });

      self.controls.skipbutton.on('click', function(e) {
        self.controls.container.modal('hide');
        if (self.controls.hiddenplayer[0].playing) {
          self.controls.hiddenplayer[0].pause();
        }
        self.controls.hiddenplayer[0].currentTime = self.currentAudioStart;
        self.mak.play_audio();
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

    spliton: new RegExp('([,.!?:;" ])', 'g'),

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
            setTimeout(function(){self.controls.skipbutton.trigger('click')},600);
        }
      })

    },
    getComparison: function(cmid, passage, transcript, callback) {
      var self = this;

      ajax.call([{
        methodname: 'mod_readaloud_compare_passage_to_transcript',
        args: {
          cmid: cmid,
          passage: passage,
          transcript: transcript,
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
          console.log(err);
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
      },

      use_ttrecorder: function(){
          var ret =false;


          //check if language and region are ok
          switch(this.region){
              case 'tokyo':
              case 'useast1':
              case 'dublin':
              case 'sydney':
                  //ret = this.language.substr(0,2)==='en';
                  ret =true;
                  break;
              default:
                  ret = this.chrome_user();
          }
          return ret;
      },


  };
});