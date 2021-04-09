define(["jquery","mod_solo/conversationconstants"], function($, constants) {

    //Preview helper manipulates the audio/video and passing on media events and info to other parts of app

  return {

      controls: {},
      mediatype: constants.mediatype_audio,

      init: function(){
            this.initControls();
      },

      initControls: function(){
          this.controls.videoplayer = $(constants.videoplayer);
          this.controls.audioplayer = $(constants.audioplayer);
          this.controls.root = $(constants.root);
          if(this.mediatype===constants.mediatype_audio){
              this.controls.mediaplayer = this.controls.audioplayer
          }else {
              this.controls.mediaplayer = this.controls.videoplayer
          }
          this.controls.mediaplayer.show();
      },



      fetchCurrentTime: function(){
         return Math.floor(1000 * this.controls.mediaplayer[0].currentTime);//milliseconds
      },

      setMediaURL: function(mediaurl){
        if(!mediaurl | mediaurl.trim()==''){return;}
        switch(this.mediatype){
            case constants.mediatype_audio:
                this.mediatype=constants.mediatype_audio;
                this.controls.mediaplayer.off('timeupdate');
                this.controls.mediaplayer = this.controls.audioplayer;
                this.controls.root.addClass('player-audio');
                break;

            default:
                this.mediatype=constants.mediatype_video;
                this.controls.mediaplayer.off('timeupdate');
                this.controls.mediaplayer = this.controls.videoplayer;
                this.controls.root.removeClass('player-audio');
        }
        this.controls.mediaplayer.attr("src",mediaurl);
        this.controls.mediaplayer[0].load();
      }
    }
});
