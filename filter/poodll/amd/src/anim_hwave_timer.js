/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('anim_horizontal_wave_ra: initialising');

    return {

        analyser: null,
        cvs: null,
        cvsctx: null,
        sounddetected: false,
        displaytime: '00:00:00',
        drawparams: {
            wavColor: "#CCCCCC",
            textColor: "#000000",
            lineWidth: 2,
            font: '30px Georgia',
            textAlign: "center"
        },

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },


        //init
        init: function (analyser, cvs) {
            this.cvs = cvs;
            this.cvsctx = cvs.getContext("2d");
            this.cvsctx.textAlign = this.drawparams.textAlign;
            this.analyser = analyser;
        },

        setDrawParam: function (paramkey, paramvalue) {
            this.drawparams[paramkey] = paramvalue;
        },

        clear: function () {
            this.cvsctx.clearRect(0, 0, this.cvs.width, this.cvs.height);
            this.cvsctx.lineWidth = 2;
            this.cvsctx.strokeStyle = this.drawparams.textColor;
            this.cvsctx.beginPath();
            this.drawTime(this.cvsctx, this.displaytime, this.cvs.width, this.cvs.height);
            this.cvsctx.stroke();
        },

        drawTime: function (ctx, displaytime, cwidth, cheight) {
            ctx.fillStyle = this.drawparams.textColor;
            ctx.font = this.drawparams.font;
            ctx.fillText(displaytime, cwidth / 2, (cheight / 4) * 3);
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

                var lineheight = (cheight / 4) * 3;

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
                    var y = v * lineheight / 2;

                    if (i === 0) {
                        canvasCtx.moveTo(x, y);
                    } else {
                        //canvasCtx.lineTo(x, oldy);
                        canvasCtx.lineTo(x, y);

                    }
                    oldy = y;
                    x += sliceWidth;
                }

                canvasCtx.lineTo(cwidth, lineheight / 2);
                //draw thetime
                that.drawTime(canvasCtx, that.displaytime, cwidth, cheight);
                canvasCtx.stroke();

            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
