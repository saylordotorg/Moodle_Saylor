/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Bar Progress: initialising');

    return {

        waveroot: null,

        playcanvas: null,
        context: null,
        startx: null,
        starty: null,
        barwidth: null,
        barheight: null,
        enabled: false,
        drawparams: {
            barColor: "#6DDC6F",
            textColor: '#fff',
            font: '12px Arial',
            textAlign: "center"
        },

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function (playcanvas) {
            //stash the key actors for calling from draw
            this.playcanvas = playcanvas.get(0);
            this.startx = 0;
            this.starty = 0;
            this.barwidth = this.playcanvas.width;
            this.barheight = this.playcanvas.height;
            this.context = this.playcanvas.getContext('2d');
        },
        test: function (analyser) {
            var testAnalyser = analyser;
            var bufferLength = testAnalyser.core.fftSize;
            var dataArray = new Uint8Array(bufferLength);
            console.log(testAnalyser);
        },

        readyBars: function () {

            var $waveRoot = $('.poodll_fresh_wave');
            var $waveProgress = $('<div class="poodll_fresh_progress_bar"><span></span></div>');
            var stickCount = $waveRoot.width() / 4;
            stickCount.toFixed();
            $waveRoot.empty();

            for (var i = 0; i < stickCount; i++) {
                var pointHeight = 8;
                var point = $(
                    '<div class="poodll_fresh_point">' +
                    '<div class="poodll_fresh_stick" style="height: ' + pointHeight + 'px"></div>' +
                    '</div>'
                )
                $waveRoot.append(point);
            }
            $waveRoot.append($waveProgress);

        },

        playBars: function (currentTime, duration) {
            var that = this;
            var $waveRoot = $('.poodll_fresh_wave');
            var $waveProgress = $('.poodll_fresh_progress_bar');
            var stickCount = $waveRoot.width() / 4;

            $waveRoot
                .attr('data-ct', currentTime)
                .attr('data-duration', duration);
            stickCount.toFixed();

            var pointsCollection = $('.poodll_fresh_point');
            $.each(pointsCollection,function(thepoint){
                if (pointsCollection[thepoint].children[0]) {
                    pointsCollection[thepoint].children[0].style.height = '4px';
                }
            });

            var draw = function () {
                var waveData = [];
                $.each(pointsCollection,function(thepoint){
                    var sampleData = Math.floor(Math.random() * 50 + 4);
                    waveData.push(sampleData);
                    var pointHeight = waveData[i] || 4;

                    // if (i < that.fetchCurrent() * stickCount ) {
                    pointsCollection[thepoint].children[0].style.height = pointHeight + 'px';
                });


                $waveProgress.css('left', that.fetchCurrent() * 100 + '%');
                var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
                    window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;

                if (that.fetchCurrent() < 1) {
                    console.log(that.fetchCurrent());
                    requestAnimationFrame(draw);
                }
            }//end of draw
            draw();
        },

        pauseBars: function () {

        },

        setDrawParam: function (paramkey, paramvalue) {
            this.drawparams[paramkey] = paramvalue;
        },

        clear: function () {
            this.context.clearRect(this.startx, this.starty, this.barwidth, this.barheight);
        },
        //this function to be overridden by calling class
        //0= 0% 1=100%
        fetchCurrent: function () {
            return 0;
        },

        //stop and clear
        stop: function () {
            this.enabled = false;
            this.clear();
        },
        //stop without clearing
        stopthere: function () {
            this.enabled = false;
        },

        start: function () {
            this.clear();
            this.enabled = true;
            var that = this;
            //set draw params, later could make this configurable
            this.context.textAlign = this.drawparams.textAlign;
            this.context.font = this.drawparams.font;
            var textcolor = this.drawparams.textColor;


            var draw = function () {

                if (!that.enabled) {
                    return;
                }
                that.clear();
                that.context.fillStyle = that.drawparams.barColor;
                that.context.fillRect(that.startx, that.starty, that.fetchCurrent() * that.barwidth, that.barheight);

                //draw text
                //this distorts unless we set the height and width of the canvas as canvas attributes, and NOT with CSS
                //that didn't really fit with this, so we use text in the page (ie not canvas)
                /*
                that.context.fillStyle = textcolor;
                that.context.fillText(parseInt(that.fetchCurrent() * 100) +'%',that.barwidth/2, that.barheight/2);
                */
                var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
                    window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
                requestAnimationFrame(draw);

            }//end of draw
            draw();
        }//end of enable
    };//end of returned object
});//total end
