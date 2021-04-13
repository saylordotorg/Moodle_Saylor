/**
 * CloudPoodll Loader module.
 *
 * @package mod_minilesson
 * @author  Justin Hunt - Poodll.com
 */
define(['jquery','core/log','https://cdn.jsdelivr.net/gh/justinhunt/cloudpoodll@latest/amd/build/cloudpoodll.min.js'], function($,log,CloudPoodll){
    return {
        callbacks: [],
        init: function (recorderid, thecallback) {
            var that = this;
            CloudPoodll.createRecorder(recorderid);
            this.callbacks.push({recorderid: recorderid, callback: thecallback});
            if(this.callbacks.length===1) {
                CloudPoodll.initEvents();
                CloudPoodll.theCallback = function (m) {
                    for (var i = 0; i < that.callbacks.length; i++) {
                        if (m.id === that.callbacks[i].recorderid) {
                            that.callbacks[i].callback(m);
                        }
                    }
                };
                $( "iframe" ).on("load",function(){
                    $(".mod_minilesson_recording_cont").css('background-image','none');
                });
            }
        }
}
});
