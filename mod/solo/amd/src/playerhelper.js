define(["jquery", "mod_solo/conversationconstants"], function($, constants) {

    return {
        controls: {},

        init: function(mediatype){
           this.initControls(mediatype);
           this.registerEvents();
        },

        initControls: function(mediatype){
            if(mediatype === constants.mediatype_video) {
                this.controls.mediaPlayer = $(constants.videoplayer);
            }else{
                this.controls.mediaPlayer = $(constants.audioplayer);
            }
            this.controls.playBtn = $(constants.playbutton);
            this.controls.prevBtn = $(constants.prevbutton);
            this.controls.nextBtn = $(constants.nextbutton);
            this.controls.progressBar = $(constants.progressbar);
            this.controls.progressLine = $(constants.progressline);
            this.controls.greenProgress = $(constants.greenprogress);
            this.controls.progressMarker = $(constants.progressmarker);
            this.controls.mediaDuration = 0;
            this.controls.timeTotal = $(constants.timetotal);
            this.controls.timeCurrent = $(constants.timecurrent);
        },

        toTimeString: function (date) {
            var date = date.toTimeString().split(' ')[0];
            date = date.slice(3, 8);
            return date;
        },

        moveSlider: function(event) {

        },

        stepBack: function() {
            var current = this.controls.mediaPlayer[0].currentTime;
            if (current > 5) {
                current -= 5;
            } else {
                current = 0;
            }
            this.controls.mediaPlayer[0].currentTime = current;
        },
        stepForward: function() {
            var current = this.controls.mediaPlayer[0].currentTime;
            var duration = this.controls.mediaPlayer[0].duration;
            if (current <= (duration - 5)) {
                current += 5;
            } else {
                current = duration;
            }
            this.controls.mediaPlayer[0].currentTime = current;
        },

        getCoords: function(elem) {
            var box = elem.getBoundingClientRect();

            return {
                top: box.top + pageYOffset,
                left: box.left + pageXOffset
            };

        },

        registerEvents: function(){

            var c = this.controls;
            var that = this;

            c.playBtn.click(function(e) {
                e.preventDefault();
                var readyState = c.mediaPlayer[0].readyState;

                if (readyState == '4') {
                    if (c.playBtn.hasClass('playing')) {
                        c.mediaPlayer[0].pause();
                    } else {
                        c.mediaPlayer[0].play();
                    }

                    c.playBtn.toggleClass('playing');
                } else {
                    alert('No media..');
                }



            });

            c.prevBtn.click(function(e) {
                e.preventDefault();
                that.stepBack();
            });

            c.nextBtn.click(function(e) {
                e.preventDefault();
                that.stepForward();
            });

            c.progressLine.click(function(e) {
                e.preventDefault();

                var $target = $(e.target);

                if ($target.hasClass('progress-marker')) {
                    return false;
                }

                var progressVal = e.offsetX;
                var barWidth = c.progressLine.width();
                var positionRatio = progressVal / barWidth;
                var progressCurrent = positionRatio * 100;

                c.greenProgress.width(progressCurrent + '%');
                c.progressMarker.css(
                    {
                        left: progressCurrent + '%',
                        opacity: 1
                    }
                );

                c.mediaPlayer[0].currentTime = c.mediaDuration * positionRatio;

                var durationCurrent = new Date(0, 0, 0, 0, 0, 0, c.mediaDuration * positionRatio * 1000);
                c.timeCurrent.text(that.toTimeString(durationCurrent));

            });

            c.mediaPlayer.on('loadeddata', function(e) {
                c.mediaDuration = c.mediaPlayer[0].duration;
                if(!isNaN(c.mediaDuration) && isFinite(c.mediaDuration)){
                    var duration = new Date(0, 0, 0, 0, 0, 0, c.mediaDuration * 1000);
                    c.timeTotal.text(that.toTimeString(duration));
                }

                c.timeCurrent.text('0:00');

                //init progress marker
                c.progressMarker.css(
                    {
                        left: '0%',
                        opacity: 1
                    }
                );

                //add the timeupdate event. If you add this before now, it fails cos no media was loaded
                c.mediaPlayer.on('timeupdate', function (ex) {
                    // Update progress bar
                    var video = c.mediaPlayer[0];
                    var durationRatio = video.currentTime / video.duration;
                    var leftPosition = durationRatio * 100 + '%';

                    c.greenProgress.width(leftPosition);
                    c.progressMarker.css(
                        {
                            left: leftPosition,
                            opacity: 1

                        }
                    );

                    // Update playback duration
                    var currentTime = new Date(0, 0, 0, 0, 0, 0, video.currentTime * 1000);
                    currentTime = that.toTimeString(currentTime);
                    c.timeCurrent.text(currentTime);

                });
            });

            c.mediaPlayer.on('durationchange', function(e) {
                c.mediaDuration = c.mediaPlayer[0].duration;
                if(!isNaN(c.mediaDuration) && isFinite(c.mediaDuration)){
                    var duration = new Date(0, 0, 0, 0, 0, 0, c.mediaDuration * 1000);
                    c.timeTotal.text(that.toTimeString(duration));
                }
            });



            var sliderElem = c.progressLine[0];
            var thumbElem = c.progressMarker[0];

            thumbElem.onmousedown = function(e) {

                var sliderCoords = that.getCoords(sliderElem);

                document.onmousemove = function(e) {
                    var newLeft = e.pageX - sliderCoords.left;

                    if (newLeft < 0) {
                        newLeft = 0;
                    }
                    var rightEdge = sliderElem.offsetWidth;

                    if (newLeft > rightEdge) {
                        newLeft = rightEdge;
                    }

                    thumbElem.style.left = newLeft + 'px';
                    c.greenProgress.width(newLeft + 'px');

                    c.mediaPlayer[0].currentTime = c.mediaDuration * (newLeft / rightEdge);

                    var durationCurrent = new Date(0, 0, 0, 0, 0, 0, c.mediaDuration * (newLeft / rightEdge) * 1000);
                    c.timeCurrent.text(that.toTimeString(durationCurrent));

                }

                document.onmouseup = function(e) {
                    document.onmousemove = document.onmouseup = null;
                };

            };

            thumbElem.ondragstart = function() {
                return false;
            };

        }//end of register events
}//end of returned object

});
