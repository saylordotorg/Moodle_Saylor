define(['jquery','core/log','mod_minilesson/definitions','mod_minilesson/dependencyloader','theme_boost/popover'], function($,log,def) {
    "use strict"; // jshint ;_;

/*
This file is to
 */

    log.debug('Popover helper: initialising');

    return{

        lastitem: false,
        okbuttonclass: def.okbuttonclass,
        ngbuttonclass: def.ngbuttonclass,
        msvclosebuttonclass: def.msvclosebuttonclass,
        s_buttonclass: def.s_buttonclass,
        m_buttonclass: def.m_buttonclass,
        v_buttonclass: def.v_buttonclass,
        correctbuttonclass: def.correctbuttonclass,
        errorbuttonclass: def.errorbuttonclass,
        selfcorrectbuttonclass: def.selfcorrectbuttonclass,
        msvgradebuttonclass: def.msvgradebutton,
        msvcontainerclass: def.msvcontainerclass,
        msvbuttonsbox: def.msvbuttonsbox,
        quickgradecontainer: def.quickgradecontainerclass,
        stateerror:  def.stateerror,
        statecorrect:  def.statecorrect,
        stateselfcorrect:  def.stateselfcorrect,
        quickgradetitle: M.util.get_string('quickgrade',def.component),
        transcripttitle: M.util.get_string('transcript',def.component),
        msvtitle: M.util.get_string('msv',def.component),
        oklabel: M.util.get_string('ok',def.component),
        nglabel: M.util.get_string('ng',def.component),
        msvcloselabel: M.util.get_string('msvcloselabel',def.component),
        s_label: M.util.get_string('s_label',def.component),
        m_label: M.util.get_string('m_label',def.component),
        v_label: M.util.get_string('v_label',def.component),
        errorlabel: M.util.get_string('error',def.component),
        correctlabel: M.util.get_string('correct',def.component),
        selfcorrectlabel: M.util.get_string('selfcorrect',def.component),
        dispose: false, //Bv4 = dispose  Bv3 = destroy

        init: function(){
            this.register_events();
        },

        register_events: function() {
            var that = this;

            //on close button push
            $(document).on('click','.' + this.okbuttonclass,this.onAccept);
            $(document).on('click','.' + this.ngbuttonclass,this.onReject);
            $(document).on('click','.' + this.msvclosebuttonclass,this.onMSVClose);

            //on msv button push
            $(document).on('click','.' + this.msvgradebuttonclass,
                function(){
                //if we are not in correct mode, then toggle the s or m or v button
                    if($('input[name="msvitem"]:checked').val()===that.statecorrect){return;}
                    if($(this).attr('data-checked')=='0'){
                        $(this).attr('data-checked','1');
                    }else{
                        $(this).attr('data-checked','0');
                    }
            });

            //on msv dialog correct/error/selfcorrect
           // $(document).on('click','.' + that.msvcontainerclass + ' .btn-secondary',
            $(document).on('change','.msvtoggleform',
                function(){
                    //turn off s and m and v buttons
                    if($('input[name="msvitem"]:checked').val() ===that.statecorrect) {
                        $('.' + that.s_buttonclass).attr('data-checked','0');
                        $('.' + that.m_buttonclass).attr('data-checked','0');
                        $('.' + that.v_buttonclass).attr('data-checked','0');
                    }
                }
            );
        },

        //different bootstrap/popover versions have a different word for "dispose" so this method bridges that.
        //we can not be sure what version is installed
        disposeWord: function(){
            if(this.dispose){return this.dispose;}
            var version ='3';
            if($.fn.popover.Constructor.hasOwnProperty('VERSION')){
                version = $.fn.popover.Constructor.VERSION.charAt(0);
            }
            switch(version){
                case '4':
                    this.dispose='dispose';
                    break;
                case '3':
                default:
                    this.dispose='destroy';
                    break;
            }
            return this.dispose;
        },

        remove: function(item){
          if(item) {
              $(item).popover(this.disposeWord());
          }else if(this.lastitem) {
              $(this.lastitem).popover(this.disposeWord());
              this.lastitem=false;
          }
        },

        addQuickGrader: function(item){

            //dispose of previous popover, and remember this one
            if(this.lastitem && this.lastitem !== item) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem=false;
            }
            this.lastitem = item;
            var that = this;

            var thefunc = function(){
                var wordnumber = $(this).attr("data-wordnumber");
                var okbutton = "<button type='button' class='btn " + that.okbuttonclass + "' data-wordnumber='" + wordnumber + "'><i class='fa fa-check'></i> " + that.oklabel + "</button>";
                var ngbutton = "<button type='button' class='btn " + that.ngbuttonclass + "' data-wordnumber='" + wordnumber + "'><i class='fa fa-close'></i> " + that.nglabel + "</button>";
                var container = "<div class='" + that.quickgradecontainerclass +  "'>" + okbutton + ngbutton + "</div>";
                return container;
            };

            //lets add the popover
            $(item).popover({
                title: this.quickgradetitle,
                content: thefunc,
                trigger: 'manual',
                placement: 'top',
                html: true
            });
            $(item).popover('show');
        },

        addMSVGrader: function(item,currentmsv){

            //dispose of previous popover, and remember this one
            if(this.lastitem && this.lastitem !== item) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem=false;
            }
            this.lastitem = item;
            var that = this;

            //close button


            var thefunc = function(){
                var wordnumber = $(this).attr("data-wordnumber");

                //toggle button
                var toggle = '<form class="msvtoggleform">';
                toggle += '<div class="btn-group btn-group-toggle" data-toggle="buttons">';

                //correct
                var checked = currentmsv.state===that.statecorrect ? 'checked' : '';
                var active = currentmsv.state===that.statecorrect ? 'active' : '';
                toggle +=    '<label for="'+ that.correctbuttonclass +'" class="btn btn-secondary  ' + active + '">';
                toggle +=    '<input id="'+ that.correctbuttonclass +'" name="msvitem" type="radio" ' + checked + ' value="' + that.statecorrect + '">';
                toggle +=    that.correctlabel + '</label>';

                //error
                checked = currentmsv.state===that.stateerror ? 'checked' : '';
                active = currentmsv.state===that.stateerror ? 'active' : '';
                toggle +=    '<label for="'+ that.errorbuttonclass +'" class="btn btn-secondary ' + active + '">';
                toggle +=    '<input id="'+ that.errorbuttonclass +'" name="msvitem" type="radio" ' + checked + '  value="' + that.stateerror  + '">';
                toggle +=    that.errorlabel + '</label>';

                //selfcorrect
                checked = currentmsv.state===that.stateselfcorrect ? 'checked' : '';
                active = currentmsv.state===that.stateselfcorrect ? 'active' : '';
                toggle +=    '<label for="'+ that.selfcorrectbuttonclass +'" class="btn btn-secondary ' + active + '">';
                toggle +=    '<input id="'+ that.selfcorrectbuttonclass +'" name="msvitem" type="radio" ' + checked + ' value="' + that.stateselfcorrect + '">';
                toggle +=     that.selfcorrectlabel + '</label>';

                toggle +=    '</div>';
                toggle +=    '</form>';

                var msvbuttons = "<button type='button' data-checked='" + currentmsv.msv.m +"' class='btn " + that.m_buttonclass + ' ' + that.msvgradebuttonclass + "'>" + that.m_label + "</button>";
                msvbuttons += "<button type='button' data-checked='" + currentmsv.msv.s +"' class='btn " + that.s_buttonclass + ' ' + that.msvgradebuttonclass + "'>" + that.s_label + "</button>";
                msvbuttons+= "<button type='button' data-checked='" + currentmsv.msv.v +"' class='btn " + that.v_buttonclass + ' ' + that.msvgradebuttonclass + "'>" + that.v_label + "</button>";
                var msvbuttonsdiv = "<div class='" + that.msvbuttonsbox + "'>" + msvbuttons +"</div>";

                var closebutton = "<button type='button' class='btn " + that.msvclosebuttonclass + "' data-wordnumber='" + wordnumber + "'>" + that.msvcloselabel + "</button>";
                var container = "<div class='" + that.msvcontainerclass +  "'>" + toggle + msvbuttonsdiv + closebutton + "</div>";
                return container;
            };

            //lets add the popover
            $(item).popover({
                title: this.msvtitle,
                content: thefunc,
                trigger: 'manual',
                placement: 'top',
                html: true
            });
            $(item).popover('show');
        },

        fetchMSVResults: function(){
            var ret={};
            ret.msv={};
            ret.msv.m=$('.' + this.m_buttonclass).attr('data-checked');
            ret.msv.s=$('.' + this.s_buttonclass).attr('data-checked');
            ret.msv.v=$('.' + this.v_buttonclass).attr('data-checked');
            ret.state=$('input[name="msvitem"]:checked').val();
            return ret;
        },

        addTranscript: function(item,transcript){

            //if we are already showing this item then dispose of it, set last item to null and go home
            if(this.lastitem == item) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem = false;
                return;
            }

            //dispose of previous popover, and remember this one
            if(this.lastitem) {
                $(this.lastitem).popover(this.disposeWord());
                this.lastitem=false;
            }
            this.lastitem = item;

            //lets add the popover
            $(item).popover({
                title: this.transcripttitle,
                content: transcript,
                trigger: 'manual',
                placement: 'top'
            });
            $(item).popover('show');
        },

        //these two functions are overridden by the calling class
        onAccept: function(){alert($(this).attr('data-wordnumber'));},
        onReject: function(){alert($(this).attr('data-wordnumber'));},
        onMSVClose: function(){alert($(this).attr('data-wordnumber'));}

    };//end of return value
});