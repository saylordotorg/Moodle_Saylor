YUI.add('moodle-atto_generico-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_generico
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_generico-button
 */

/**
 * Atto text editor generico plugin.
 *
 * @namespace M.atto_generico
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_generico';
var LOGNAME = 'atto_generico';

var CSS = {
        INPUTSUBMIT: 'atto_media_urlentrysubmit',
        INPUTCANCEL: 'atto_media_urlentrycancel',
        KEYBUTTON: 'atto_generico_templatebutton',
        HEADERTEXT: 'atto_generico_headertext',
        INSTRUCTIONSTEXT: 'atto_generico_instructionstext',
        TEMPLATEVARIABLE: 'atto_generico_templatevariable',
        FLAVORCONTROL: 'flavorcontrol'
    },
    SELECTORS = {
        FLAVORCONTROL: '.flavorcontrol'
    };

var FIELDSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            '<h4 class="' + CSS.HEADERTEXT + '">{{headertext}} {{name}}</h4>' +
            '<div class="' + CSS.INSTRUCTIONSTEXT + '">{{instructions}}</div>' +
        '</div>';

var BUTTONSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            '<h4 class="' + CSS.HEADERTEXT + '">{{headertext}}</h4>' +
        '</div>';
        
var BUTTONTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="atto_generico_buttons mdl-align">' +
            '<button class="' + CSS.KEYBUTTON + '_{{templateindex}}">{{name}}</button>' +
        '</div>';
		
var FIELDTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">{{variable}}' +
            '&nbsp;<input type="text" class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}} atto_generico_field" value="{{defaultvalue}}"></input>' +
        '</div>';
var SELECTCONTAINERTEMPLATE = '' +
            '<div id="{{elementid}}_{{innerform}}" class="mdl-align">{{variable}}</div>';
			
var SELECTTEMPLATE = '' +
            '<select class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}} atto_generico_field"></select>';

var OPTIONTEMPLATE ='' +
		'<option value="{{option}}">{{option}}</option>';

var SUBMITTEMPLATE = '' +
  '<form class="atto_form">' +
   '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
	'<button class="' + CSS.INPUTSUBMIT +'">{{inserttext}}</button>' +
    '</div>' +
	'</form>';

Y.namespace('M.atto_generico').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    initializer: function() {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')){
            return;
        }


        if(this.get('customicon')) {
            var iconurl = decodeURIComponent(this.get('customicon'));
            var iconname = 'atto_generico';
            // Add the generico icon/buttons
            this.addButton({
                iconurl: iconurl,
                buttonName: iconname,
                callback: this._displayDialogue,
                callbackArgs: iconname
            });

        }else{
            // Add the generico icon/buttons
            var iconname = 'iconone';
            this.addButton({
                icon: 'ed/' + iconname,
                iconComponent: 'atto_generico',
                buttonName: iconname,
                callback: this._displayDialogue,
                callbackArgs: iconname
            });
        }
    },


     /**
     * Display the generico dialog
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function(e, clickedicon) {
        e.preventDefault();
        var width=400;


        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            focusAfterHide: clickedicon
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }
        
        //create content container
        var bodycontent =  Y.Node.create('<div></div>');
        
        //create and append header
        var template = Y.Handlebars.compile(BUTTONSHEADERTEMPLATE),
            	content = Y.Node.create(template({
                headertext: M.util.get_string('chooseinsert', COMPONENTNAME)
            }));
         bodycontent.append(content);

        //get button nodes
        var buttons = this._getButtonsForKeys(clickedicon);

        
         Y.Array.each(buttons, function(button) {  	 
            //loop start
                bodycontent.append(button);
            //loop end
        }, bodycontent);
     

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

	    /**
     * Display the chosen generico template form
     *
     * @method _displayDialogue
     * @private
     */
    _showTemplateForm: function(e,templateindex) {
        e.preventDefault();
        var width=400;

		
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px'
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }

        //get fields , 1 per variable
        var fields = this._getTemplateFields(templateindex);
        var instructions = this.get('instructions')[templateindex];
            instructions = decodeURIComponent(instructions);
	
		//get header node. It will be different if we have no fields
		if(fields && fields.length>0){
			var useheadertext  = M.util.get_string('fieldsheader', COMPONENTNAME);
		}else{
			var useheadertext =  M.util.get_string('nofieldsheader', COMPONENTNAME);
		}
		var template = Y.Handlebars.compile(FIELDSHEADERTEMPLATE),
            	content = Y.Node.create(template({
            	    key: this.get('keys')[templateindex],
                    name: this.get('names')[templateindex],
                    headertext: useheadertext,
                    instructions: instructions
            }));
        var header = content;
		
		//set container for our nodes (header, fields, buttons)
        var bodycontent =  Y.Node.create('<div></div>');
        
        //add our header
         bodycontent.append(header);
        
        //add fields
         Y.Array.each(fields, function(field) {  	 
            //loop start
                bodycontent.append(field);
            //loop end
        }, bodycontent);
     
     	//add submit button
     	var submitbuttons = this._getSubmitButtons(templateindex);
     	bodycontent.append(submitbuttons)

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

  /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getSubmitButtons
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getSubmitButtons: function(templateindex) {
  
        var template = Y.Handlebars.compile(SUBMITTEMPLATE),
        	
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                inserttext:  M.util.get_string('insert', COMPONENTNAME)
            }));
     
		content.one('.' + CSS.INPUTSUBMIT).on('click', this._doInsert, this, templateindex);
        return content;
    },


   /**
     * Return a field (yui node) for each variable in the template
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getTemplateFields: function(templateindex) {
    
    	var allcontent=[];
    	var thekey=this.get('keys')[templateindex];
       var thename=this.get('names')[templateindex];
    	var thevariables=this.get('variables')[templateindex];
    	var thedefaults=this.get('defaults')[templateindex];
    	
    	//defaults array 
    	//var defaultsarray=this._getDefArray(thedefaults);
		var defaultsarray=thedefaults;
		
    	 Y.Array.each(thevariables, function(thevariable, currentindex) { 	 
            //loop start
			if((thevariable in defaultsarray) && defaultsarray[thevariable].indexOf('|')>-1){
			
				var containertemplate = Y.Handlebars.compile(SELECTCONTAINERTEMPLATE),
					content = Y.Node.create(containertemplate({
					elementid: this.get('host').get('elementid'),
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			
				var selecttemplate = Y.Handlebars.compile(SELECTTEMPLATE),
					selectbox = Y.Node.create(selecttemplate({
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			
				var opts = defaultsarray[thevariable].split('|');
				var htmloptions="";
				var opttemplate = Y.Handlebars.compile(OPTIONTEMPLATE);
				Y.Array.each(opts, function(opt, optindex) {
					var optcontent = Y.Node.create(opttemplate({
							option: opt
						}));
					selectbox.appendChild(optcontent);
				});
				content.appendChild(selectbox);
				
			}else{
			
				 var template = Y.Handlebars.compile(FIELDTEMPLATE),
					content = Y.Node.create(template({
					elementid: this.get('host').get('elementid'),
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			}
			
			
            allcontent.push(content);
            //loop end
        }, this);


        return allcontent;
    },


     /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getButtonsForKeys: function(clickedicon) {
    
    	var allcontent=[];
    	 Y.Array.each(this.get('names'), function(thename, currentindex) {
            //loop start
             var template = Y.Handlebars.compile(BUTTONTEMPLATE),
            	content = Y.Node.create(template({
            	elementid: this.get('host').get('elementid'),
                name: thename,
                templateindex: currentindex
            }));
            this._form = content;
            content.one('.' + CSS.KEYBUTTON + '_' + currentindex).on('click', this._showTemplateForm, this,currentindex);
            allcontent.push(content);
            //loop end
        }, this);

        return allcontent;
    },
    
    _getDefArray: function(thedefaults){
    	//defaults array 
    	var defaultsarray=[];
    	var defaultstemparray = thedefaults.match(/([^=,]*)=("[^"]*"|[^,"]*)/g);//thedefaults.split(',');
    	Y.Array.each(defaultstemparray, function(defset){
    		//loop start
    		var defsetarray = defset.split('=');
    		if(defsetarray && defsetarray.length>1){
    			defaultsarray[defsetarray[0]] = defsetarray[1].replace(/"/g,'');
    		}
    	 //loop end
        }, this);
        return defaultsarray;
    
    },

    /**
     * Inserts the users input onto the page
     * @method _getDialogueContent
     * @private
     */
    _doInsert : function(e,templateindex){
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();
        
        var retstring = "{GENERICO:type=";
        var thekey = this.get('keys')[templateindex];
        var thename = this.get('names')[templateindex];
        var thevariables=this.get('variables')[templateindex];
        var thedefaults=this.get('defaults')[templateindex];
        var theend=this.get('ends')[templateindex];
      //  var defaultsarray=this._getDefArray(thedefaults);
          var defaultsarray=thedefaults;
        
        //add key to return string
        retstring += '"' + thekey + '"';
        
        //add variables to return string
         Y.Array.each(thevariables, function(variable, currentindex) {
        //loop start
        	var thefield = Y.one('.' + CSS.TEMPLATEVARIABLE + '_' + currentindex);
        	var thevalue = thefield.get('value');
        	if(thevalue && thevalue!=defaultsarray[variable]){
        		retstring += ',' + variable + '="' + thevalue + '"';
        	}
        //loop end
        }, this);
        
        //close out return string
        retstring += "}";
        
        //add an end tag, if we need to
        if(theend){
        	retstring += '<br/>{GENERICO:type="' + thekey + '_end"}';
        }
        

        this.editor.focus();
        this.get('host').insertContentAtFocusPoint(retstring);
        this.markUpdated();

    }
}, { ATTRS: {
    disabled: {
        value: false
    },

    keys: {
        value: null
    },

    names: {
        value: null
    },

	variables: {
        value: null
    },

    defaults: {
        value: null
    }
    ,
    instructions: {
        value: null
    },
    customicon: {
        value: null
    },
    ends: {
        value: null
    }
 }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
