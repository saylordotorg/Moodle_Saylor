/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('anim_horizontal_wave: initialising');

    return {

        analyser: null,
        cvs: null,
        cvsctx: null,
        drawparams: {
            wavColor: '#0',
            barColor: '#ad2323',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 10,
            shadowColor: '#fff',
            font: '18px Arial',
            textAlign: "center",
            textBaseline: 'middle'
        },


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },


        //init
        init: function (analyser, cvs) {
            this.cvs = cvs;
            this.cvsctx = cvs.getContext("2d");
            this.analyser = analyser;
            this.clear();
        },

        setDrawParam: function (paramkey, paramvalue) {
            this.drawparams[paramkey] = paramvalue;
        },

        clear: function () {
            this.cvsctx.clearRect(0, 0, this.cvs.width, this.cvs.height);
            this.cvsctx.lineWidth = 2;
            this.cvsctx.strokeStyle = this.drawparams.wavColor;
            this.cvsctx.beginPath();
            this.cvsctx.moveTo(0, this.cvs.height / 2);
            this.cvsctx.lineTo(this.cvs.width, this.cvs.height / 2);
            this.cvsctx.stroke();
        },

        start: function () {
            this.analyser.core.fftSize = 2048;
            var bufferLength = this.analyser.core.fftSize;
            var dataArray = new Uint8Array(bufferLength);
            var cwidth = this.cvs.width;
            var cheight = this.cvs.height;
            var canvasCtx = this.cvsctx;
            var analyser = this.analyser;
            this.clear();
            var that = this;

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

                canvasCtx.lineWidth = 2;
                canvasCtx.strokeStyle = that.drawparams.wavColor;

                canvasCtx.beginPath();

                var sliceWidth = cwidth * 1.0 / bufferLength;
                var x = 0;

                for (var i = 0; i < bufferLength; i++) {

                    var v = dataArray[i] / 128.0;
                    var y = v * cheight / 2;

                    if (i === 0) {
                        canvasCtx.moveTo(x, y);
                    } else {
                        canvasCtx.lineTo(x, y);
                    }

                    x += sliceWidth;
                }

                canvasCtx.lineTo(cwidth, cheight / 2);
                canvasCtx.stroke();
            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
