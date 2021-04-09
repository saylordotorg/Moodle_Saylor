define(["jquery"], function($) {
  return {

      //borrowed and altered from : https://github.com/dsslimshaddy/vtt-to-json/blob/master/index.js
      convertVttToJson: function(vttString) {

           var current = {};
           var sections = [];
          var tidyexp = /<\/?[^>]+(>|$)/g;
           var start =  false;
           var that = this;

           var vttArray = vttString.split('\n');
            vttArray.forEach(function(line, index){

                var nicerline = line.replace(tidyexp, "");

                if (nicerline === " "){
                    //do what?
                } else if (nicerline == "") {
                        //do what?
                } else if (line.indexOf('-->') !== -1 ) {
                    start = true;

                    if (current.start ) {
                        sections.push(that.clone(current));
                    }

                    current = {
                        start: that.timeString2ms(line.split("-->")[0].trimRight().split(" ").pop()),
                        end: that.timeString2ms(line.split("-->")[1].trimLeft().split(" ").shift()),
                        part: ''
                    }
                } else {
                    if (start){
                        if (sections.length !== 0) {
                            if (sections[sections.length - 1].part.replace(tidyexp, "") === line.replace(nicerline, "")) {
                                //do what?
                            } else {
                                //if current part is empty, just set it
                                if (current.part.length === 0) {
                                    current.part = line;
                                //if its partyly full, add to it
                                } else {
                                    current.part = current.part +  " " +  line;
                                }
                            }
                        } else {
                            current.part = line;
                            sections.push(that.clone(current));

                            //clear the current section ( so it is not added again)
                            //this means very first transcription can not have two lines ...nor last one ...ok
                            current.start = false;
                            current.end = false;
                            current.part = '';
                        }
                    }
                }
        });

         //if we got to the end but we have an unpushed current, we push that to our return array here
          // If it's the last line of the transcriptions
          if (current.start) {
              sections.push(that.clone(current));
          }

        sections.forEach(function(section){
            section.part = section.part.replace(tidyexp, "")
        });
        return(sections);
    },

      //borrowed and altered from https://github.com/linclark/vtt-json/blob/0.0.x/lib/vtt-serializer.js
    convertJsonToVtt: function(thejson){
          var ret = "WEBVTT";
          ret +='\n\n';
          var that = this;
          $.each(thejson,function(index,item) {
              var start = that.ms2TimeString(item.start);
              var end = that.ms2TimeString(item.end);
              ret += start + ' --> ' + end + '\n';
              ret += item.part + '\n';
              ret +='\n';
          });
        return ret;
    },

      //we use this to make sure that the string we will pass for conversion to ms, is well formed
      validateTimeString: function(rawstring){
        if(!rawstring){return false;}
        rawstring=rawstring.trim();
        var bits = rawstring.split(':');
        if(bits.length!==3){return false;}
         var reg = new RegExp('^[0-9]+$');
        for(var bit=0; bit <=3;bit++){
            var strbit = bits[bit];

            switch(bit){
                //hours
                case 0:
                    if(!reg.test(strbit)){
                        return false;
                    }
                    var hours = parseInt(strbit);
                    if(hours <0 || hours > 23){return false;}
                    break;

                //mins
                case 1:
                    if(!reg.test(strbit)){
                        return false;
                    }
                    var mins = parseInt(strbit);
                    if(mins <0 || mins > 59){return false;}
                    break;
                case 2:
                    var secondbits = strbit.split('.');
                    if(secondbits.length != 2){
                        return false;
                    }
                    var secs = secondbits[0];
                    var millisecs = secondbits[1];

                    //test seconds
                    if(!reg.test(secs)){
                        return false;
                    }
                    var secs = parseInt(secs);
                    if(secs <0 || secs > 59){return false;}

                    //test millisecs
                    if(!reg.test(millisecs)){
                        return false;
                    }
                    var millisecs = parseInt(millisecs);
                    if(millisecs <0 || millisecs > 999){return false;}
                    break;
            }
          }
         return true;
      },

    // helpers
    //largely incomprehensible time functions from here...
    //   http://codereview.stackexchange.com/questions/45335/milliseconds-to-time-string-time-string-to-milliseconds
        timeString2ms: function(a,b){// time(HH:MM:SS.mss) // optimized
            //console.log(a);
            //console.log(this.validateTimeString(a));
            return a=a.split('.'), // optimized
                b=a[1]*1||0, // optimized
                a=a[0].split(':'),
            b+(a[2]?a[0]*3600+a[1]*60+a[2]*1:a[1]?a[0]*60+a[1]*1:a[0]*1)*1e3 // optimized
        },

      ms2TimeString: function(a,k,s,m,h){
        return k=a%1e3, // optimized by konijn
                s=a/1e3%60|0,
                m=a/6e4%60|0,
                h=a/36e5%24|0,
            (h?(h<10?'0'+h:h)+':':'00:')+ // optimized
            (m<10?0:'')+m+':'+  // optimized
            (s<10?0:'')+s+'.'+ // optimized
            (k<100?k<10?'00':0:'')+k // optimized
        },

        clone: function(obj) {
            if (null == obj || "object" != typeof obj) return obj;
            var copy = obj.constructor();
            for (var attr in obj) {
                if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
            }
            return copy;
        }

    }
});
