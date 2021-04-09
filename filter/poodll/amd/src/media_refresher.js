/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Media refresher: initialising');

    return {

        placeholderduration: null,
        mediaduration: null, //audio or video tag
        alertconverted: null,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function (filename, placeholderduration, mediaduration, alertconverted) {
            //debugger;
            this.placeholderduration = placeholderduration;
            this.mediaduration = mediaduration;
            this.alertconverted = alertconverted;
            this.check_updates(filename, 'firstpass');
        },

        should_be_checked: function (filename) {
            //check this is an unconverted recording that we need to track

            log.debug('mediaduration: ' + this.mediaduration);
            log.debug('placeholderduration: ' + this.placeholderduration);
            log.debug('fixed to one mediaduration: ' + parseFloat(this.mediaduration).toFixed(1));
            log.debug('fixed to one placeholderduration: ' + parseFloat(this.placeholderduration).toFixed(1));

            //if any of these numbers is not numeric we kill it
            if (!$.isNumeric(this.placeholderduration)) {
                return false;
            }
            if (!$.isNumeric(this.mediaduration)) {
                return false;
            }
            //if the two numbers are equivalent to one decimal place we credit it
            //firefox calcs mp3 size diff to chrome, but they seem same to 1 place ... now anyway
            if (parseFloat(this.mediaduration).toFixed(1) != parseFloat(this.placeholderduration).toFixed(1)) {
                return false;
            }
            //this is a bogus check.
            //later we only want to check filenames that look like poodll recorded ones
            if (filename == '') {
                return false;
            }
            return true;
        },

        check_updates: function (filename, checktype) {
            //checktype:firstpass - if have a task then we keep checking till there is no task
            //then we know its finished. Those checks are the 'secondpass'
            this.should_be_checked(filename);
            //check this is a recording that we need to track
            if (checktype == 'firstpass' && !this.should_be_checked(filename)) {
                return;
            }

            //set up our ajax request
            var xhr = new XMLHttpRequest();
            var that = this;

            //set up our handler for the response
            xhr.onreadystatechange = function (e) {
                if (this.readyState === 4) {
                    if (xhr.status == 200) {

                        //get a yes or forgetit or tryagain
                        var payload = xhr.responseText;
                        var payloadobject = JSON.parse(payload);
                        if (payloadobject) {
                            switch (payloadobject.code) {

                                case 'mediaready':
                                    that.alertconverted();
                                    break;
                                case 'stillwaiting':
                                    setTimeout(function () {
                                        that.check_updates(filename, 'secondpass')
                                    }, 15000);
                                    break;
                                case 'notask':
                                    if (checktype == 'secondpass') {
                                        that.alertconverted();
                                    }
                                    break;
                                case 'notloggedin':
                                default:
                                //just stop trying in this case
                                //the task is long ago processed or its not a rec. or something
                            }
                        }
                    } else {
                        log.debug('Not 200 response:' + xhr.status);
                    }
                }
            };

            //log.debug(params);
            var params = "filename=" + filename;
            xhr.open("POST", M.cfg.wwwroot + '/filter/poodll/ajaxmediaquery.php', true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.send(params);
        }
    };//end of returned object
});//total end
