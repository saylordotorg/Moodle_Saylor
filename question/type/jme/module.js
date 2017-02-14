// This is an external script, included here, and in one line, for performance.
/* PluginDetect v0.6.1 [ onWindowLoaded isMinVersion Java(OTF) Flash ] by Eric Gerds www.pinlady.net/PluginDetect */ if(!PluginDetect){var PluginDetect={getNum:function(b,c){if(!this.num(b)){return null}var a;if(typeof c=="undefined"){a=/[\d][\d\.\_,-]*/.exec(b)}else{a=(new RegExp(c)).exec(b)}return a?a[0].replace(/[\.\_-]/g,","):null},hasMimeType:function(c){if(PluginDetect.isIE){return null}var b,a,d,e=c.constructor==String?[c]:c;for(d=0;d<e.length;d++){b=navigator.mimeTypes[e[d]];if(b&&b.enabledPlugin){a=b.enabledPlugin;if(a.name||a.description){return b}}}return null},findNavPlugin:function(g,d){var a=g.constructor==String?g:g.join(".*"),e=d===false?"":"\\d",b,c=new RegExp(a+".*"+e+"|"+e+".*"+a,"i"),f=navigator.plugins;for(b=0;b<f.length;b++){if(c.test(f[b].description)||c.test(f[b].name)){return f[b]}}return null},AXO:window.ActiveXObject,getAXO:function(b,a){var f=null,d,c=false;try{f=new this.AXO(b);c=true}catch(d){}if(typeof a!="undefined"){delete f;return c}return f},num:function(a){return(typeof a!="string"?false:(/\d/).test(a))},compareNums:function(g,e){var d=this,c,b,a,f=window.parseInt;if(!d.num(g)||!d.num(e)){return 0}if(d.plugin&&d.plugin.compareNums){return d.plugin.compareNums(g,e)}c=g.split(",");b=e.split(",");for(a=0;a<Math.min(c.length,b.length);a++){if(f(c[a],10)>f(b[a],10)){return 1}if(f(c[a],10)<f(b[a],10)){return -1}}return 0},formatNum:function(b){if(!this.num(b)){return null}var a,c=b.replace(/\s/g,"").replace(/[\.\_]/g,",").split(",").concat(["0","0","0","0"]);for(a=0;a<4;a++){if(/^(0+)(.+)$/.test(c[a])){c[a]=RegExp.$2}}if(!/\d/.test(c[0])){c[0]="0"}return c[0]+","+c[1]+","+c[2]+","+c[3]},initScript:function(){var $=this,userAgent=navigator.userAgent;$.isIE=/*@cc_on!@*/false;$.IEver=$.isIE&&((/MSIE\s*(\d\.?\d*)/i).exec(userAgent))?parseFloat(RegExp.$1,10):-1;$.ActiveXEnabled=false;if($.isIE){var x,progid=["Msxml2.XMLHTTP","Msxml2.DOMDocument","Microsoft.XMLDOM","ShockwaveFlash.ShockwaveFlash","TDCCtl.TDCCtl","Shell.UIHelper","Scripting.Dictionary","wmplayer.ocx"];for(x=0;x<progid.length;x++){if($.getAXO(progid[x],1)){$.ActiveXEnabled=true;break}}$.head=typeof document.getElementsByTagName!="undefined"?document.getElementsByTagName("head")[0]:null}$.isGecko=!$.isIE&&typeof navigator.product=="string"&&(/Gecko/i).test(navigator.product)&&(/Gecko\s*\/\s*\d/i).test(userAgent)?true:false;$.GeckoRV=$.isGecko?$.formatNum((/rv\s*\:\s*([\.\,\d]+)/i).test(userAgent)?RegExp.$1:"0.9"):null;$.isSafari=!$.isIE&&(/Safari\s*\/\s*\d/i).test(userAgent)?true:false;$.onWindowLoaded(0)},init:function(c,a){if(typeof c!="string"){return -3}c=c.toLowerCase().replace(/\s/g,"");var b=this,d;if(typeof b[c]=="undefined"){return -3}d=b[c];b.plugin=d;if(typeof d.installed=="undefined"||a==true){d.installed=null;d.version=null;d.version0=null;d.getVersionDone=null;d.$=b}b.garbage=false;if(b.isIE&&!b.ActiveXEnabled){if(b.plugin!=b.java){return -2}}return 1},isMinVersion:function(g,e,c,b){var f=PluginDetect,d=f.init(g);if(d<0){return d}if(typeof e=="number"){e=e.toString()}if(typeof e!="string"){e="0"}if(!f.num(e)){return -3}e=f.formatNum(e);var a=-1,h=f.plugin;if(h.getVersionDone!=1){h.getVersion(c,b);if(h.getVersionDone==null){h.getVersionDone=1}}if(h.version!=null||h.installed!=null){if(h.installed<=0.5){a=h.installed}else{a=(h.version==null?0:(f.compareNums(h.version,e)>=0?1:-1))}}f.cleanup();return a;return -3},getVersion:function(e,b,a){return null},getInfo:function(f,c,b){var a={};return a},cleanup:function(){var a=this;if(a.garbage&&typeof window.CollectGarbage!="undefined"){window.CollectGarbage()}},isActiveXObject:function(b){},codebaseSearch:function(c){var e=this;if(!e.ActiveXEnabled){return null}if(typeof c!="undefined"){return e.isActiveXObject(c)}},dummy1:0}}PluginDetect.onDetectionDone=function(g,e,d,a){return -1};PluginDetect.onWindowLoaded=function(c){var b=PluginDetect,a=window;if(b.EventWinLoad===true){}else{b.winLoaded=false;b.EventWinLoad=true;if(typeof a.addEventListener!="undefined"){a.addEventListener("load",b.runFuncs,false)}else{if(typeof a.attachEvent!="undefined"){a.attachEvent("onload",b.runFuncs)}else{if(typeof a.onload=="function"){b.funcs[b.funcs.length]=a.onload}a.onload=b.runFuncs}}}if(typeof c=="function"){b.funcs[b.funcs.length]=c}};PluginDetect.funcs=[0];PluginDetect.runFuncs=function(){var b=PluginDetect,a;b.winLoaded=true;for(a=0;a<b.funcs.length;a++){if(typeof b.funcs[a]=="function"){b.funcs[a](b);b.funcs[a]=null}}};PluginDetect.java={mimeType:"application/x-java-applet",classID:"clsid:8AD9C840-044E-11D1-B3E9-00805F499D93",DTKclassID:"clsid:CAFEEFAC-DEC7-0000-0000-ABCDEFFEDCBA",DTKmimeType:"application/npruntime-scriptable-plugin;DeploymentToolkit",JavaVersions:[[1,9,2,25],[1,8,2,25],[1,7,2,25],[1,6,2,25],[1,5,2,25],[1,4,2,25],[1,3,1,25]],searchJavaPluginAXO:function(){var i=null,a=this,c=a.$,h=[],g,k=[1,5,0,14],j=[1,6,0,2],f=[1,3,1,0],e=[1,4,2,0],d=[1,5,0,7],b=false;if(!c.ActiveXEnabled){return null};if(c.IEver>=a.minIEver){h=a.searchJavaAXO(j,j,b);if(h.length>0&&b){h=a.searchJavaAXO(k,k,b)}}else{if(h.length==0){h=a.searchJavaAXO(f,e,false)}}if(h.length>0){i=h[0]}a.JavaPlugin_versions=[].concat(h);return i},searchJavaAXO:function(l,i,m){var n,f,h=this.$,p,k,a,e,g,j,b,q=[];if(h.compareNums(l.join(","),i.join(","))>0){i=l}i=h.formatNum(i.join(","));var o,d="1,4,2,0",c="JavaPlugin."+l[0]+""+l[1]+""+l[2]+""+(l[3]>0?("_"+(l[3]<10?"0":"")+l[3]):"");for(n=0;n<this.JavaVersions.length;n++){f=this.JavaVersions[n];p="JavaPlugin."+f[0]+""+f[1];g=f[0]+"."+f[1]+".";for(a=f[2];a>=0;a--){b="JavaWebStart.isInstalled."+g+a+".0";if(h.compareNums(f[0]+","+f[1]+","+a+",0",i)>=0&&!h.getAXO(b,1)){continue}o=h.compareNums(f[0]+","+f[1]+","+a+",0",d)<0?true:false;for(e=f[3];e>=0;e--){k=a+"_"+(e<10?"0"+e:e);j=p+k;if(h.getAXO(j,1)&&(o||h.getAXO(b,1))){q[q.length]=g+k;if(!m){return q}}if(j==c){return q}}if(h.getAXO(p+a,1)&&(o||h.getAXO(b,1))){q[q.length]=g+a;if(!m){return q}}if(p+a==c){return q}}}return q},minIEver:7,getFromMimeType:function(a){var h,f,c=this.$,j=new RegExp(a),d,k,i={},e=0,b,g=[""];for(h=0;h<navigator.mimeTypes.length;h++){k=navigator.mimeTypes[h];if(j.test(k.type)&&k.enabledPlugin){k=k.type.substring(k.type.indexOf("=")+1,k.type.length);d="a"+c.formatNum(k);if(typeof i[d]=="undefined"){i[d]=k;e++}}}for(f=0;f<e;f++){b="0,0,0,0";for(h in i){if(i[h]){d=h.substring(1,h.length);if(c.compareNums(d,b)>0){b=d}}}g[f]=i["a"+b];i["a"+b]=null}if(!/windows|macintosh/i.test(navigator.userAgent)){g=[g[0]]}return g},queryJavaHandler:function(){var b=PluginDetect.java,a=window.java,c;b.hasRun=true;try{if(typeof a.lang!="undefined"&&typeof a.lang.System!="undefined"){b.value=[a.lang.System.getProperty("java.version")+" ",a.lang.System.getProperty("java.vendor")+" "]}}catch(c){}},queryJava:function(){var c=this,d=c.$,b=navigator.userAgent,f;if(typeof window.java!="undefined"&&navigator.javaEnabled()&&!c.hasRun){if(d.isGecko){if(d.hasMimeType("application/x-java-vm")){try{var g=document.createElement("div"),a=document.createEvent("HTMLEvents");a.initEvent("focus",false,true);g.addEventListener("focus",c.queryJavaHandler,false);g.dispatchEvent(a)}catch(f){}if(!c.hasRun){c.queryJavaHandler()}}}else{if(/opera.9\.(0|1)/i.test(b)&&/mac/i.test(b)){}else{if(!c.hasRun){c.queryJavaHandler()}}}}return c.value},VENDORS:["Sun Microsystems Inc.","Apple Computer, Inc."],init:function(){var a=this,b=a.$;if(typeof a.app!="undefined"){a.delJavaApplets(b)}a.hasRun=false;a.value=[null,null];a.useTag=[2,2,2];a.app=[0,0,0,0,0,0];a.appi=3;a.queryDTKresult=null;a.OTF=0;a.BridgeResult=[[null,null],[null,null],[null,null]];a.JavaActive=[0,0,0];a.All_versions=[];a.DeployTK_versions=[];a.MimeType_versions=[];a.JavaPlugin_versions=[];a.funcs=[];var c=a.NOTF;if(c){c.$=b;if(c.javaInterval){clearInterval(c.javaInterval)}c.EventJavaReady=null;c.javaInterval=null;c.count=0;c.intervalLength=250;c.countMax=40}a.lateDetection=b.winLoaded;if(!a.lateDetection){b.onWindowLoaded(a.delJavaApplets)}},getVersion:function(f,l){var h,d=this,g=d.$,j=null,n=null,e=null,c=navigator.javaEnabled();if(d.getVersionDone==null){d.init()}var k;if(typeof l!="undefined"&&l.constructor==Array){for(k=0;k<d.useTag.length;k++){if(typeof l[k]=="number"){d.useTag[k]=l[k]}}}if(d.getVersionDone==0){if(!d.version||d.useAnyTag()){h=d.queryExternalApplet(f);if(h[0]){e=h[0];n=h[1]}}d.EndGetVersion(e,n);return}var i=d.queryDeploymentToolKit();if(typeof i=="string"&&i.length>0){j=i;n=d.VENDORS[0]}if(!g.isIE){var q,m,b,o,a;a=g.hasMimeType(d.mimeType);o=(a&&c)?true:false;if(d.MimeType_versions.length==0&&a){h=d.getFromMimeType("application/x-java-applet.*jpi-version.*=");if(h[0]!=""){if(!j){j=h[0]}d.MimeType_versions=h}}if(!j&&a){h="Java[^\\d]*Plug-in";b=g.findNavPlugin(h);if(b){h=new RegExp(h,"i");q=h.test(b.description)?g.getNum(b.description):null;m=h.test(b.name)?g.getNum(b.name):null;if(q&&m){j=(g.compareNums(g.formatNum(q),g.formatNum(m))>=0)?q:m}else{j=q||m}}}if(!j&&a&&/macintosh.*safari/i.test(navigator.userAgent)){b=g.findNavPlugin("Java.*\\d.*Plug-in.*Cocoa",false);if(b){q=g.getNum(b.description);if(q){j=q}}}if(j){d.version0=j;if(c){e=j}}if(!e||d.useAnyTag()){b=d.queryExternalApplet(f);if(b[0]){e=b[0];n=b[1]}}if(!e){b=d.queryJava();if(b[0]){d.version0=b[0];e=b[0];n=b[1];if(d.installed==-0.5){d.installed=0.5}}}if(d.installed==null&&!e&&o&&!/macintosh.*ppc/i.test(navigator.userAgent)){h=d.getFromMimeType("application/x-java-applet.*version.*=");if(h[0]!=""){e=h[0]}}if(!e&&o){if(/macintosh.*safari/i.test(navigator.userAgent)){if(d.installed==null){d.installed=0}else{if(d.installed==-0.5){d.installed=0.5}}}}}else{if(!j&&i!=-1){j=d.searchJavaPluginAXO();if(j){n=d.VENDORS[0]}}if(!j){d.JavaFix()}if(j){d.version0=j;if(c&&g.ActiveXEnabled){e=j}}if(!e||d.useAnyTag()){h=d.queryExternalApplet(f);if(h[0]){e=h[0];n=h[1]}}}if(d.installed==null){d.installed=e?1:(j?-0.2:-1)}d.EndGetVersion(e,n)},EndGetVersion:function(b,d){var a=this,c=a.$;if(a.version0){a.version0=c.formatNum(c.getNum(a.version0))}if(b){a.version=c.formatNum(c.getNum(b));a.vendor=(typeof d=="string"?d:"")}if(a.getVersionDone!=1){a.getVersionDone=0}},queryDeploymentToolKit:function(){var d=this,f=d.$,h,b,g=null,a=null;if((f.isGecko&&f.compareNums(f.GeckoRV,f.formatNum("1.6"))<=0)||f.isSafari||(f.isIE&&!f.ActiveXEnabled)){d.queryDTKresult=0}if(d.queryDTKresult!=null){return d.queryDTKresult}if(f.isIE&&f.IEver>=6){d.app[0]=f.instantiate("object",[],[]);g=f.getObject(d.app[0])}else{if(!f.isIE&&f.hasMimeType(d.DTKmimeType)){d.app[0]=f.instantiate("object",["type",d.DTKmimeType],[]);g=f.getObject(d.app[0])}}if(g){if(f.isIE&&f.IEver>=6){try{g.classid=d.DTKclassID}catch(h){}}try{a=g.jvms.getLength();if(a!=null&&a>0){var c;for(b=0;b<a;b++){c=g.jvms.get(a-1-b).version;if(!f.getNum(c)){continue}d.DeployTK_versions[b]=c}}}catch(h){}try{if(g.object&&g.readyState!=4){f.garbage=true;f.uninstantiate(d.app[0])}}catch(h){}}f.hideObject(g);d.queryDTKresult=d.DeployTK_versions.length>0?d.DeployTK_versions[0]:(a==0?-1:0);return d.queryDTKresult},queryExternalApplet:function(d){var c=this,e=c.$,h=c.BridgeResult,b=c.app,g=c.appi,a="&nbsp;&nbsp;&nbsp;&nbsp;";if(typeof d!="string"||!(/\.jar\s*$/).test(d)){return[null,null]}if(c.OTF<1){c.OTF=1}if(!e.isIE){if(e.isGecko&&!e.hasMimeType(c.mimeType)&&!c.queryJava()[0]){return[null,null]}}if(c.OTF<2){c.OTF=2}if(!b[g]&&c.canUseObjectTag()&&c.canUseThisTag(0)){b[1]=e.instantiate("object",[],[],a);b[g]=e.isIE?e.instantiate("object",["archive",d,"code","A.class","type",c.mimeType],["archive",d,"code","A.class","mayscript","true","scriptable","true"],a):e.instantiate("object",["archive",d,"classid","java:A.class","type",c.mimeType],["archive",d,"mayscript","true","scriptable","true"],a);h[0]=[0,0];c.query1Applet(g)}if(!b[g+1]&&c.canUseAppletTag()&&c.canUseThisTag(1)){b[g+1]=e.instantiate("applet",["archive",d,"code","A.class","alt",a,"mayscript","true"],["mayscript","true"],a);h[1]=[0,0];c.query1Applet(g+1)}if(e.isIE&&!b[g+2]&&c.canUseObjectTag()&&c.canUseThisTag(2)){b[g+2]=e.instantiate("object",["classid",c.classID],["archive",d,"code","A.class","mayscript","true","scriptable","true"],a);h[2]=[0,0];c.query1Applet(g+2)};var j,f=0;for(j=0;j<h.length;j++){if(b[g+j]||c.canUseThisTag(j)){f++}else{break}}if(f==h.length){c.getVersionDone=1}return c.getBR()},canUseAppletTag:function(){return((!this.$.isIE||navigator.javaEnabled())?true:false)},canUseObjectTag:function(){return((!this.$.isIE||this.$.ActiveXEnabled)?true:false)},useAnyTag:function(){var b=this,a;for(a=0;a<b.useTag.length;a++){if(b.canUseThisTag(a)){return true}}return false},canUseThisTag:function(c){var a=this,b=a.$;if(a.useTag[c]==3){return true}if(!a.version0||!navigator.javaEnabled()||(b.isIE&&!b.ActiveXEnabled)){if(a.useTag[c]==2){return true}if(a.useTag[c]==1&&!a.getBR()[0]){return true}}return false},getBR:function(){var b=this.BridgeResult,a;for(a=0;a<b.length;a++){if(b[a][0]){return[b[a][0],b[a][1]]}}return[b[0][0],b[0][1]]},delJavaApplets:function(b){var c=b.java.app,a;for(a=c.length-1;a>=0;a--){b.uninstantiate(c[a])}},query1Applet:function(g){var f,c=this,d=c.$,a=null,h=null,b=d.getObject(c.app[g]);try{if(b){a=b.getVersion()+" ";h=b.getVendor()+" ";if(a){c.BridgeResult[g-c.appi]=[a,h];d.hideObject(c.app[g])}if(d.isIE&&a&&b.readyState!=4){d.garbage=true;d.uninstantiate(c.app[g])}}}catch(f){}},NOTF:{isJavaActive:function(){}},append:function(e,d){for(var c=0;c<d.length;c++){e[e.length]=d[c]}},getInfo:function(){var o={};return o},JavaFix:function(){}};PluginDetect.flash={mimeType:["application/x-shockwave-flash","application/futuresplash"],progID:"ShockwaveFlash.ShockwaveFlash",classID:"clsid:D27CDB6E-AE6D-11CF-96B8-444553540000",getVersion:function(){var c=function(i){if(!i){return null}var e=/[\d][\d\,\.\s]*[rRdD]{0,1}[\d\,]*/.exec(i);return e?e[0].replace(/[rRdD\.]/g,",").replace(/\s/g,""):null};var j,g=this.$,h,f,b=null,a=null,d=null;if(!g.isIE){j=g.findNavPlugin("Flash");if(j&&j.description&&g.hasMimeType(this.mimeType)){b=c(j.description)}}else{for(f=15;f>2;f--){a=g.getAXO(this.progID+"."+f);if(a){d=f.toString();break}}if(d=="6"){try{a.AllowScriptAccess="always"}catch(h){return"6,0,21,0"}}try{b=c(a.GetVariable("$version"))}catch(h){}if(!b&&d){b=d}}this.installed=b?1:-1;this.version=g.formatNum(b);return true}};PluginDetect.div=null;PluginDetect.DOMbody=null;PluginDetect.uninstantiate=function(a){var c,d,b=this;if(!a){return}try{if(a[0]&&a[0].firstChild){a[0].removeChild(a[0].firstChild)}if(a[0]&&b.div){b.div.removeChild(a[0])}if(b.div&&b.div.childNodes.length==0){b.div.parentNode.removeChild(b.div);b.div=null;if(b.DOMbody&&b.DOMbody.parentNode){b.DOMbody.parentNode.removeChild(b.DOMbody)}b.DOMbody=null}a[0]=null}catch(c){}};PluginDetect.getObject=function(a){var b;try{if(a[0]&&a[0].firstChild){return a[0].firstChild}}catch(b){}return null};PluginDetect.hideObject=function(a){var b=this.getObject(a);if(b&&b.style){b.style.height="0"}};PluginDetect.instantiate=function(h,b,c,a){var q=function(d){var e=d.style;if(!e){return}e.border="0px";e.padding="0px";e.margin="0px";e.fontSize="5px";e.height="5px";if(d.tagName&&d.tagName.toLowerCase()=="div"){e.width="100%";e.display="block"}else{if(d.tagName&&d.tagName.toLowerCase()=="span"){e.width="1px";e.display="inline"}}};var j,k=document,g=this,p,m,f,i=(k.getElementsByTagName("body")[0]||k.body),o=k.createElement("span"),n,l="/";if(typeof a=="undefined"){a=""}p="<"+h+' width="1" height="1" ';for(n=0;n<b.length;n=n+2){p+=b[n]+'="'+b[n+1]+'" '}p+=">";for(n=0;n<c.length;n=n+2){p+='<param name="'+c[n]+'" value="'+c[n+1]+'" />'}p+=a+"<"+l+h+">";if(!g.div){g.div=k.createElement("div");if(i){try{if(i.firstChild&&typeof i.insertBefore!="undefined"){i.insertBefore(g.div,i.firstChild)}else{i.appendChild(g.div)}}catch(j){}}else{try{k.write("<div>o<"+l+"div>");i=(k.getElementsByTagName("body")[0]||k.body);i.appendChild(g.div);i.removeChild(i.firstChild)}catch(j){try{g.DOMbody=k.createElement("body");k.getElementsByTagName("html")[0].appendChild(g.DOMbody);g.DOMbody.appendChild(g.div)}catch(j){}}}q(g.div)}if(g.div&&g.div.parentNode&&g.div.parentNode.parentNode){g.div.appendChild(o);try{o.innerHTML=p}catch(j){}q(o);return[o]}return[null]};PluginDetect.initScript();


M.qtype_jme = {
    useJSME : true,
    reload_limit : 10,
    editor_displayed : false,
    topnode : null,
    applet_name : null,
    Y : null,
    insert_applet : function(Y, toreplaceid, appletid, name, topnode,
                                                                    appleturl, feedback, readonly, width, height, appletoptions){
        var javaparams = ['jme', Y.one(topnode + ' input.jme').get('value')];
        var jmeoptions = new Array();
        this.applet_name = name;
        this.topnode = topnode;
        this.Y = Y;

        if (appletoptions) {
            jmeoptions[jmeoptions.length] = appletoptions;
        }
        if (readonly) {
            jmeoptions[jmeoptions.length] = "depict";
        }
        if (jmeoptions.length !== 0) {
            javaparams[javaparams.length] = "options";
            javaparams[javaparams.length] = jmeoptions.join(',');
        }

        this.show_java(toreplaceid, appletid, name, appleturl,
                       width, height, 'JME.class', javaparams, jmeoptions);
    },
    update_inputs : function() {
        var Y = this.Y,
        name = this.applet_name,
        topnode = this.topnode;
        if (!this.editor_displayed) {
            this.show_error(Y, topnode);
        } else {
            var inputdiv = Y.one(topnode);
            inputdiv.ancestor('form').on('submit', function (){
                Y.one(topnode + ' input.answer').set('value', this.find_applet(name).smiles());
                Y.one(topnode + ' input.jme').set('value', this.find_applet(name).jmeFile());
                Y.one(topnode + ' input.mol').set('value', this.find_applet(name).molFile())
            }, this);
        }
    },
    show_error : function (Y, topnode) {
        var errormessage = '<span class ="javascriptwarning">' +
            M.util.get_string('enablejavascript', 'qtype_jme') +
            '</span>';
        Y.one(topnode + ' .ablock').insert(errormessage, 1);
    },
    /**
     * Gets around problem in ie6 using name
     */
    find_applet : function (appletname) {
        var applets = this.useJSME ? document.jsapplets : document.applets;
        for (appletno in applets) {
            if (applets[appletno].name == appletname) {
                return applets[appletno];
            }
        }
        return null;
    },

    nextappletid : 1,
    javainstalled : -99,
    doneie6focus : 0,
    doneie6focusapplets : 0,
    // Note: This method is also called from mod/audiorecorder.
    show_java : function (id, appletid, name, java, width, height, appletclass, javavars, jmeoptions) {

        if (this.javainstalled == -99 ) {
            this.javainstalled = PluginDetect.isMinVersion(
                'Java', 1.5, 'plugindetect.getjavainfo.jar', [0, 2, 0]) == 1;
        }
        var warningspan = document.getElementById(id);
        warningspan.innerHTML = '';
        if (!this.javainstalled && !this.useJSME) {
            return false;
        }

        // Ensure the JSME code is loaded properly. IE 8 struggles.
        if (typeof JSApplet !== 'object' && this.reload_limit) {
            setTimeout(function(){M.qtype_jme.show_java(id, appletid, name,
                            java, width, height, appletclass, javavars, jmeoptions)}, 100);
            this.reload_limit--;
            return false;
        }

        var elementname = this.useJSME ? "div" : "applet";
        var newApplet = document.createElement(elementname);
        newApplet.setAttribute('code', appletclass);
        newApplet.setAttribute('archive', java);
        newApplet.setAttribute('name', name);
        newApplet.setAttribute('width', width);
        newApplet.setAttribute('height', height);
        newApplet.setAttribute('tabIndex', -1);
        newApplet.setAttribute('mayScript', true);
        newApplet.setAttribute('id', appletid);
        // In case applet supports the focushack system, we
        // pass in its id as a parameter.
        javavars[javavars.length] = 'focushackid';
        javavars[javavars.length] = newApplet.id;
        for (var i = 0; i < javavars.length; i += 2) {
            var param = document.createElement('param');
            param.name = javavars[i];
            param.value = javavars[i + 1];
            newApplet.appendChild(param);
        }
        warningspan.appendChild(newApplet);

        if (this.useJSME) {
            //Instantiate a new JSME:
            //arguments: HTML id, width, height (must be string not number!).
            jsmeApplet = new JSApplet.JSME(appletid, width + 'px', height + 'px', {
                // Optional parameters.
                "options" : jmeoptions.join(',')
            });
            jsmeApplet.name = name;
            // If molecule data is supplied display it.
            if (javavars[1]) {
                jsmeApplet.readMolecule(javavars[1]);
            }
            // Create document.jsapplets array if it doesn't exist.
            if (!document.jsapplets) {
                document.jsapplets = [];
            }
            document.jsapplets[document.jsapplets.length] = jsmeApplet;
        }

        if(document.body.className.indexOf('ie6') != -1 && !this.doneie6focus) {
            var fixFocus = function() {
                if (document.activeElement && document.activeElement.nodeName.toLowerCase() == elementname) {
                    setTimeout(fixFocus, 100);
                    this.doneie6focus = 1;
                    this.doneie6focusapplets ++;
                    window.focus();
                } else {
                    this.doneie6focus++;
                    if(this.doneie6focus == 2 && this.doneie6focusapplets > 0) {
                        // Focus one extra time after applet gets it.
                        window.focus();
                    }
                    if(this.doneie6focus < 50) {
                        setTimeout(fixFocus, 100);
                    }
                }
            };
            window.arghApplets = 0;
            setTimeout(fixFocus, 100);
            this.doneie6focus = 1;
        }

        this.editor_displayed = true;

        this.update_inputs();

        return this.editor_displayed;
    }
}
