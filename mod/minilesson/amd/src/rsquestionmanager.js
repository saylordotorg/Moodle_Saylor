/* jshint ignore:start */
define(['jquery', 'core/log','core/templates','mod_minilesson/definitions','mod_minilesson/modalformhelper',
        'mod_minilesson/modaldeletehelper','mod_minilesson/moveitemhelper','mod_minilesson/modalpreviewhelper',
        'mod_minilesson/duplicateitemhelper','mod_minilesson/datatables'],
    function($,  log, templates, def, mfh, mdh, mih, mph,dplh, datatables) {

    "use strict"; // jshint ;_;

    log.debug('RSQuestion manager: initialising');

    return {

        cmid: null,
        controls: null,
        rowIds: [],


        //pass in config
        init: function(props){
            var dd = this;
            dd.contextid=props.contextid;
            dd.tableid = props.tableid;

            dd.register_events();
            dd.process_html();
            dd.collate_rowids();
            dd.hide_useless_arrows();
        },

        process_html: function(){
            this.controls = [];
            this.controls.questionstable = datatables.getDataTable(this.tableid);
            this.controls.questionscontainer = $('#' + def.itemscontainer);
            this.controls.noquestionscontainer = $('#' + def.noitemscontainer);
            this.controls.movearrows=$('#' + def.movearrow);
        },

        //we maintain an array of datatable rowids, indexed by itemorder
        //we need this because moving, adding, deleting requires access to datatable rowid
        collate_rowids: function(){
            var dd = this;
            dd.rowIds=[];

            dd.controls.questionstable.rows().every( function ( rowindex, tableLoop, rowLoop ) {
                var itemorder = dd.controls.questionstable.cell({row:rowindex, column:0}).data();
                dd.rowIds[itemorder]=rowindex;
            } );
        },

        //we wont to show move arrows, but hide arrows the final down and first up
        hide_useless_arrows: function(){
          //first lets show all the arrows
          $('.mod_minilesson_item_move').attr('style','');

          //if no rows just get out of here.
          var rowcount=this.controls.questionstable.data().length;
          if(!rowcount || rowcount<1){return;}

          //hide bottom down arrow
          var bottomrowindex=this.rowIds[rowcount];
          var bottomtr = this.controls.questionstable.row(bottomrowindex).node();
          $(bottomtr).find('.mod_minilesson_item_move[data-direction="down"]').attr('style','visibility: hidden;');

          //hide top up arrow
          var toprowindex=this.rowIds[1];
          var toptr = this.controls.questionstable.row(toprowindex).node();
          $(toptr).find('.mod_minilesson_item_move[data-direction="up"]').attr('style','visibility: hidden;');
        },

        // we need to renumber rows when we remove one, so we start from there and renumber the next ones
        renumber_rows:function(fromorder) {

            var thetable= this.controls.questionstable;
            var rowcount = thetable.data().length;
            for(var itemorder =fromorder; itemorder<rowcount;itemorder++){
                var rowindex = this.rowIds[itemorder+1];
                thetable.cell({row:rowindex, column:0}).data(itemorder);
            }
        },

        move_row:function(itemid, direction) {
            var thetable= this.controls.questionstable;
            var therow = '#' + def.itemrow + '_' + itemid;
            var currentrow = thetable.row(therow);
            var currentindex = currentrow.index();
            var currentorder = parseInt(thetable.cell({row:currentindex, column:0}).data());


            var targetorder;
            if(direction=="up"){
                targetorder=currentorder-1;
            } else if(direction=="down"){
                targetorder=currentorder+1;
            }

            //should never arrive here pitching for out of range. But just in case
            if(targetorder<1){return;}
            var rowcount = thetable.data().length;
            if(targetorder>rowcount){return;}

            var targetindex = this.rowIds[targetorder];
            var from = thetable.cell({row:currentindex, column:0}).data();
            var to = thetable.cell({row:targetindex, column:0}).data();
            thetable.cell({row:currentindex, column:0}).data(to);
            thetable.cell({row:targetindex, column:0}).data(from);
            thetable.draw(false);
            this.collate_rowids();
            
        },
        register_events: function() {
          
            var dd = this;
          
            var qtypes =[def.qtype_dictation,def.qtype_dictationchat,def.qtype_page,
                def.qtype_speechcards,def.qtype_listenrepeat, def.qtype_multichoice, def.qtype_multiaudio];

            var after_questionmove= function(itemid, direction) {
                dd.move_row(itemid,direction);
                dd.hide_useless_arrows();
            };

            var after_questionedit= function(item, itemid) {
                var therow = '#' + def.itemrow + '_' + itemid;
                dd.controls.questionstable.cell($(therow + ' .c1')).data(decodeURIComponent(item.name));
            };
            var after_questionadd= function(item, itemid) {
                item.id = itemid;
                item.name = decodeURIComponent(item.name);
                item.index = dd.controls.questionstable.data().length+1;
                item.up = {'key': 't/up','component': 'moodle','title': 'up'};
                item.down = {'key': 't/down','component': 'moodle','title': 'down'};
                templates.render('mod_minilesson/itemlistitem',item).then(
                    function(html,js){
                        //add row move to the last page so we can see the new row if its off page
                        dd.controls.questionstable.row.add($(html)[0]).page('last').draw(false);
                        dd.collate_rowids();
                        dd.hide_useless_arrows();
                    }
                );
                dd.controls.noquestionscontainer.hide();
                dd.controls.questionscontainer.show();
            };
            var after_questionduplicate= function($resp) {

                var ret = JSON.parse($resp);
                var item={};
                item.id = ret.newitemid;
                item.name = decodeURIComponent(ret.newitemname);
                item.type = ret.type;
                item.typelabel = decodeURIComponent(ret.typelabel);
                item.index = dd.controls.questionstable.data().length+1;
                item.up = {'key': 't/up','component': 'moodle','title': 'up'};
                item.down = {'key': 't/down','component': 'moodle','title': 'down'};
                templates.render('mod_minilesson/itemlistitem',item).then(
                    function(html,js){
                        //add row move to the last page so we can see the new row if its off page
                        dd.controls.questionstable.row.add($(html)[0]).page('last').draw(false);
                        dd.collate_rowids();
                        dd.hide_useless_arrows();
                    }
                );
                dd.controls.noquestionscontainer.hide();
                dd.controls.questionscontainer.show();
            };
            var after_questiondelete= function(itemid) {
                log.debug('after question delete');
                var therow=dd.controls.questionstable.row('#' + def.itemrow + '_' + itemid);
                var itemorder=parseInt(therow.data()[0]);
                dd.renumber_rows(itemorder);
                therow.remove().draw(false);
                dd.collate_rowids();
                dd.hide_useless_arrows();
                var itemcount = dd.controls.questionstable.rows().count();
                if(!itemcount){
                    dd.controls.noquestionscontainer.show();
                    dd.controls.questionscontainer.hide();
                }
            };
            var after_questionpreview= function(itemid) {
                log.debug('after preview');
                //we want to remove the question from DOM ... its still there and on subsequent shows, id will match on 2 elements and question will fail to unhide
                $('#mod_minilesson_quiz_cont').remove();
            };

            //register ajax modal handler
            var editcallback=function(item, itemid){console.log(item);};
            var deletecallback=function(itemid){console.log(itemid);};
            var addcallback=function(itemid){console.log(itemid);};
            mfh.init('.' + def.component + '_addlink', dd.contextid,after_questionadd);
            //edit form helper
            mfh.init('.' + def.itemrow + '_editlink', dd.contextid,after_questionedit);
            //delete helpser
            mdh.init('.' + def.itemrow + '_deletelink', dd.contextid, 'deleteitem',after_questiondelete);
            //move helper
            mih.init('.' + def.movearrow , dd.contextid, after_questionmove);
            //preview helper
            mph.init('.' + def.itemrow + '_previewlink', dd.contextid, after_questionpreview);
            //duplicate item helper
            dplh.init('.' + def.itemrow + '_duplicatelink', dd.contextid, after_questionduplicate);
        }

    };//end of returned object
});//total end
