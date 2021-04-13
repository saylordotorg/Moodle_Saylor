define(['jquery','core/log','mod_minilesson/definitions','https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.3.1/qrcode.min.js'], function($,log, def, qrcode) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('MiniLesson QRCode helper: initialising');

    return{
        //pass in config, amd set up table
        init: function(props){
            var that =this;
            $(props.selector).each(function(){
                var qrcode = that.fetch(props.size,props.margin,$(this).text());
                $(this).html(qrcode);
                $(this).show();
            });
        },

        fetch: function(size,margin,thetext){
            var typeNumber = 0; //the higher the number ... the more data
            var errorCorrectionLevel = 'L';
            var qr = qrcode(typeNumber, errorCorrectionLevel);
            qr.addData(thetext);
            qr.make();
            var theqrcode = qr.createImgTag(size,margin);
            return theqrcode;
        }


    };//end of return value
});