/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('anim_horizontal_wave_mic: initialising');

    return {

        analyser: null,
        cvs: null,
        cvsctx: null,
        sounddetected: false,
        imgone: null,
        imgoneloaded: false,
        drawparams: {
            micColor: '#CCCCCC',
            wavColor: "#CCCCCC",
            lineWidth: 2
        },

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        setDrawParam: function (paramkey, paramvalue) {
            this.drawparams[paramkey] = paramvalue;
        },


        //init
        init: function (analyser, cvs) {
            this.cvs = cvs;
            this.cvsctx = cvs.getContext("2d");
            this.cvsctx.textAlign = "center";
            this.analyser = analyser;

            //init images
            //listen for load to ensure initial display
            var that = this;
            this.imgone = new Image();
            this.imgone.addEventListener('load', function () {
                that.imgoneloaded = true;
            });
            this.imgone.src = M.cfg.wwwroot + '/filter/poodll/pix/svg/mic.svg';
        },

        clear: function () {
            if (!this.imgoneloaded) {
                var that = this;
                this.imgone.addEventListener('load', function () {
                    that.imgoneloaded = true;
                    that.clear();
                });
                return;
            }
            this.cvsctx.clearRect(0, 0, this.cvs.width, this.cvs.height);

            this.cvsctx.beginPath();
            this.cvsctx.lineWidth = this.drawparams.lineWidth;
            this.cvsctx.strokeStyle = this.drawparams.wavColor;
            this.cvsctx.moveTo(0, this.cvs.height / 2);
            this.cvsctx.lineTo(this.cvs.width, this.cvs.height / 2);
            this.cvsctx.stroke();

            // this.cvsctx.beginPath();
            //  this.cvsctx.strokeStyle = this.drawparams.micColor;
            this.drawMic(this.cvsctx, this.cvs.width, this.cvs.height, this.imgone);
            //  this.cvsctx.stroke();
        },

        drawMic: function (ctx, cwidth, cheight, mic) {
            var x = (cwidth - mic.width) / 2;
            var y = (cheight - mic.height) / 2;
            ctx.drawImage(mic, x, y);
        },

        start: function () {
            this.analyser.core.fftSize = 2048;
            var bufferLength = this.analyser.core.fftSize;
            var dataArray = new Uint8Array(bufferLength);
            var cwidth = this.cvs.width;
            var cheight = this.cvs.height;
            var canvasCtx = this.cvsctx;
            var analyser = this.analyser;
            var that = this;
            this.clear();

            var draw = function () {

                var drawVisual = requestAnimationFrame(draw);

                //cancel out if the theinterval is null
                if (!analyser.theinterval) {
                    return;
                }

                analyser.core.getByteTimeDomainData(dataArray);

                //filling is rubbish, we just clear it
                //canvasCtx.fillStyle = 'rgb(200, 200, 200)';
                //canvasCtx.fillRect(0, 0, cwidth, cheight);
                canvasCtx.clearRect(0, 0, cwidth, cheight);

                canvasCtx.lineWidth = that.drawparams.lineWidth;
                canvasCtx.strokeStyle = that.drawparams.wavColor;

                canvasCtx.beginPath();

                var sliceWidth = cwidth * 2.0 / bufferLength;
                var x = 0;
                var oldy = 0;

                //we check if we could capture sound here
                if (bufferLength > 0) {
                    var level = dataArray[bufferLength - 1];
                    if (level != 128) {
                        that.sounddetected = true;
                    }
                }

                for (var i = 0; i < bufferLength; i++) {

                    var v = dataArray[i] / 128.0;
                    var y = v * cheight / 2;

                    if (i === 0) {
                        canvasCtx.moveTo(x, y);
                    } else {
                        //canvasCtx.lineTo(x, oldy);
                        canvasCtx.lineTo(x, y);

                    }
                    oldy = y;
                    x += sliceWidth;
                }
                canvasCtx.lineTo(cwidth, cheight / 2);
                canvasCtx.stroke();

                //draw a microphone
                that.drawMic(canvasCtx, cwidth, cheight, that.imgone);


            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
