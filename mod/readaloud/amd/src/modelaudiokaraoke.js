define(['jquery', 'core/log', 'mod_readaloud/definitions'], function($, log, def) {
  "use strict"; // jshint ;_;
  /*
  This file runs preview and shadow and L-and-R modes, highlighting text as the player reaches it.
   */

  log.debug('Model Audio Karaoke: initialising');

  return {
    controls: {},
    breaks: [],
    endwordnumber: 0,
    currentstartbreak: false,
    modeling: false,

    //class definitions
    cd: {
      audioplayerclass: def.audioplayerclass,
      wordplayerclass: def.wordplayerclass,
      wordclass: def.wordclass,
      spaceclass: def.spaceclass,
      endspaceclass: def.endspaceclass,
      passagecontainer: def.passagecontainer,
      activesentence: def.activesentence,
      stopbutton: 'mod_readaloud_button_stop',
      playbutton: 'mod_readaloud_button_play'
    },

    //init the module
    init: function(opts) {
      if (opts.breaks) {
        var breaks = JSON.parse(opts.breaks);
        this.set_breaks(breaks);
      }
      if (opts.modeling) {
        this.modeling = true;
      }
      if (opts.audioplayerclass) {
        this.cd.audioplayerclass = opts.audioplayerclass;
      }

      //register the controls
      this.register_controls();

      //register the end word number
      this.endwordnumber = this.controls.eachword.length;

      //register the events
      this.register_events();
    },

    set_breaks: function(breaks) {
      this.breaks = breaks;
      this.sort_breaks();
      this.number_breaks();
    },

    sort_breaks: function() {
      this.breaks.sort(function(a, b) {
        return a.audiotime - b.audiotime;
      });
    },

    number_breaks: function(){
      var that=this;
        for (var i = 0; i < that.breaks.length; i++) {
           that.breaks[i].breaknumber=i+1;
        }
    },

    pause_audio: function() {
      this.controls.audioplayer[0].pause();
    },

    play_audio: function() {
      this.controls.audioplayer[0].play();
    },

    get_audio_time: function() {
      return this.controls.audioplayer[0].currentTime;
    },

    set_audio_time: function(newtime) {
      this.controls.audioplayer[0].currentTime=newtime;
    },

    fetch_audio_url: function() {
      return this.controls.audioplayer.attr('src');
    },

    //load all the controls so we do not have to do it later
    register_controls: function() {
      this.controls.audioplayer = $('#' + this.cd.audioplayerclass);
      this.controls.eachword = $('.' + this.cd.wordclass);
      this.controls.eachspace = $('.' + this.cd.spaceclass);
      this.controls.eachwordorspace = $('.' + this.cd.spaceclass + ',.' + this.cd.wordclass);
      this.controls.passagecontainer = $("." + this.cd.passagecontainer);
      this.controls.stopbutton = $('#' + this.cd.stopbutton);
      this.controls.playbutton = $('#' + this.cd.playbutton);
    },

    //attach the various event handlers we need
    register_events: function() {
      var that = this;

      // Get the audio element
      var aplayer = this.controls.audioplayer[0];

      this.controls.playbutton.on('click', function() {
        aplayer.play();
      });

      this.controls.stopbutton.on('click', function() {
        aplayer.pause();
        aplayer.currentTime=0;
      });

      //if we are not modeling we want to jump to the clicked location
      //if we are modeling the meaning of a click is to place a marker, so we do not want to jump
      this.controls.eachwordorspace.on('click', function() {
        if (!that.modeling) {
          var wordnumber = parseInt($(this).attr('data-wordnumber'));
          var nearest_start_break = false;
          for (var i = 0; i < that.breaks.length; i++) {
            if (that.breaks[i].wordnumber < wordnumber) {
              nearest_start_break = that.breaks[i];
            } else {
              //exit the loop;
              break;
            }
          }
          if (!nearest_start_break) {
            //start from beginning OR do nothing
          } else {
            aplayer.pause();
            aplayer.currentTime = nearest_start_break.audiotime;
            aplayer.play();
          }
        } //end of if not modeling
      }); //end of eachwordorspace


      var timeupdate = function() {
        var currentTime = aplayer.currentTime;
        var startbreak = false;
        var nextbreak = false;
        for (var i = 0; i < that.breaks.length; i++) {

          //if this is the last marked break (ie flow till end)
          if (currentTime >= that.breaks[i].audiotime && i + 1 === that.breaks.length) {
            startbreak = that.breaks[i];
            nextbreak = {
              wordnumber: that.endwordnumber + 1,
              audiotime: 0
            };
            //if its just between two breaks (yay)
          } else if (currentTime >= that.breaks[i].audiotime && currentTime < that.breaks[i + 1].audiotime) {
            startbreak = that.breaks[i];
            nextbreak = that.breaks[i + 1];
            break;
            //this is the first section
          } else if (i === 0 && currentTime < that.breaks[i].audiotime && currentTime > 0) {
            startbreak = {
              wordnumber: 0,
              audiotime: 0,
              breaknumber: 0,
            };
            nextbreak = that.breaks[i];

          }
        }
        //if the current break changed since last time, we go in here
        // (on first time through we want to flag  "changed" so that is why a false current startbreak goes to "changed"
        //in the special case that we reached the end of the passage we need to raise the eevent
        var islastbreak = aplayer.ended && nextbreak.audiotime===0;
        if (that.currentstartbreak === false || startbreak.wordnumber !== that.currentstartbreak.wordnumber || islastbreak) {
          var finishedsentence = $('.' + that.cd.activesentence).text();
          that.previousstartbreak = that.currentstartbreak;
          that.currentstartbreak = startbreak;
          that.controls.eachword.removeClass(that.cd.activesentence);
          that.controls.eachspace.removeClass(that.cd.activesentence);
          if (startbreak !== false && nextbreak !== false) {
            for (var thewordnumber = startbreak.wordnumber + 1; thewordnumber <= nextbreak.wordnumber; thewordnumber++) {
              $('#' + that.cd.spaceclass + '_' + thewordnumber).addClass((that.cd.activesentence));
              $('#' + that.cd.wordclass + '_' + thewordnumber).addClass((that.cd.activesentence));
            }
          }
          that.on_reach_audio_break(finishedsentence, that.previousstartbreak, that.currentstartbreak, that.breaks);
        }
      };

      //Player events (onended, onpause, ontimeupdate)
      var ended = function() {
        that.controls.eachword.removeClass(that.cd.activesentence);
        that.controls.eachspace.removeClass(that.cd.activesentence);
        that.currentstartbreak = false;
      };


      aplayer.onended = ended;
      aplayer.onpause = ended;
      aplayer.ontimeupdate = timeupdate;
    }, //end of register events


    on_reach_audio_break: function(sentence, oldbreak, newbreak, breaks) {
      log.debug(sentence);
      log.debug(oldbreak);
      log.debug(newbreak);
    }

  }; //end of return value
});