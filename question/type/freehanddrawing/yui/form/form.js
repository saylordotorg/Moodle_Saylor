/**
 * This is JavaScript code that handles drawing on mouse events and painting pre-existing drawings.
 * @package    qtype
 * @subpackage freehanddrawing
 * @copyright  ETHZ LET <jacob.shapiro@let.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

YUI.add('moodle-qtype_freehanddrawing-form', function(Y) {
	var CSS = {
	},
	SELECTORS = {
			GENERICCANVAS: 'canvas[class="qtype_freehanddrawing_canvas"]',
			READONLYCANVAS: 'canvas[class="qtype_freehanddrawing_canvas readonly-canvas"]',
			FILEPICKER: '#id_qtype_freehanddrawing_image_file',
			FILEPICKERFIELDSET: 'fieldset[id$=qtype_freehanddrawing_drawing_background_image]',
			FILEPICKERFIELDSETANOTHER: 'fieldset[id$=qtype_freehanddrawing_drawing_background_image_selected]',
			DRAWINGRADIUS: '#id_radius',
			CHOOSEFILEBUTTON: 'input[name="qtype_freehanddrawing_image_filechoose"]',
			CHOOSEANOTHERFILEBUTTON: 'input[name="qtype_freehanddrawing_image_filechoose_another"]',
			ERASERBUTTON: 'img[class="qtype_freehanddrawing_eraser"]',
			ERASERTOOLBUTTON: 'img[class="qtype_freehanddrawing_eraser_tool"]',
			CONTAINERDIV: 'div[class="qtype_freehanddrawing_container_div"]',
			NOBACKGROUNDIMAGESELECTEDYET: 'div[class="qtype_freehanddrawing_no_background_image_selected_yet"]',
			CANVASTEXTAREAEDITMODE: 'textarea[name="qtype_freehanddrawing_textarea_id_0"]',
			CANVASTEXTAREATESTMODE: 'textarea[id="qtype_freehanddrawing_textarea_id_',
	};
	Y.namespace('Moodle.qtype_freehanddrawing.form');


	Y.Moodle.qtype_freehanddrawing.form = {


			canvasContext: new Array(),
			drawingRadius: new Array(),
            eraserToolOn: new Array(),
			emptyCanvasDataURL: new Array(),
			
			filepicker_change_sub: null,
			choose_new_image_file_click_sub: null,
			eraser_click_sub: null,
            eraser_tool_click_sub: null,
			canvas_mousedown_sub: null,
			canvas_touchstart_sub: null,
			canvas_touchmove_sub: null,
			canvas_touchend_sub: null,
			canvas_mouseup_sub: null,
			canvas_mouseout_sub: null,
			drawing_radius_change_sub: null,
			edit_mode: false,
				
			init: function(questionID, drawingRadius, correctAnswer, canvasInstanceID) {
				if (typeof correctAnswer != 'undefined' && correctAnswer != 'undefined') {
					// A correct answer is provided by the argument list--so this means the canvas is to be treated as READ ONLY
					this.drawingRadius[questionID] = drawingRadius;
					this.draw_correct_answer(questionID, correctAnswer, canvasInstanceID);
				} else {
					// No correct answer provided in argument list (although one might pre-exist in the textarea)
					// This means we allow drawing by the user.
					if (typeof questionID != 'undefined') {
						// A questionID should actually always be defined (even when it's a newly created question, in which case the question ID should be given as zero)
						// But better safe than sorry
						this.drawingRadius[questionID] = drawingRadius;
                        this.eraserToolOn[questionID] = false;
						this.emptyCanvasDataURL[questionID] = Y.one(SELECTORS.GENERICCANVAS).getDOMNode().toDataURL();
						this.create_canvas_context(questionID);
						if (questionID == 0) {
							// This is a question edit or "add new" form
							// Check if this is an edit form with a pre-existing (on the server) saved image:
							if (Y.one(SELECTORS.CHOOSEANOTHERFILEBUTTON) != null) {
								// So if there's a pre-existing background image
								// we'd like to hide the file-picker widget (until further notice... (click by 'choose another background'...)
								this.edit_mode = true;
								Y.one(SELECTORS.FILEPICKERFIELDSET).setStyles({display: 'none'});
								Y.delegate('click', function() { Y.one(SELECTORS.CHOOSEFILEBUTTON).simulate('click'); }, Y.config.doc, SELECTORS.CHOOSEANOTHERFILEBUTTON, this); 
							}
						}
					}
					
					if(!this.filepicker_change_sub) { 
						this.filepicker_change_sub = Y.delegate('change',    this.filepicker_change,     Y.config.doc, SELECTORS.FILEPICKER, this); 
					}
					if(!this.choose_new_image_file_click_sub) {
						this.choose_new_image_file_click_sub = Y.delegate('click', this.choose_new_image_file_click, Y.config.doc, SELECTORS.CHOOSEFILEBUTTON, this); 
					}
					if(!this.eraser_click_sub) { 
						this.eraser_click_sub =  Y.delegate('mouseup', this.eraser_click, Y.config.doc, SELECTORS.ERASERBUTTON, this);
					}
                    if (!this.eraser_tool_click_sub) {
						this.eraser_tool_click_sub =  Y.delegate('mouseup', this.eraser_tool_click, Y.config.doc, SELECTORS.ERASERTOOLBUTTON, this);
                    }
					if(!this.canvas_mousedown_sub) { 
						this.canvas_mousedown_sub = Y.delegate('mousedown', this.canvas_mousedown,  Y.config.doc, SELECTORS.GENERICCANVAS, this); 
					}
					if(!this.canvas_touchstart_sub) { 
						this.canvas_touchstart_sub = Y.delegate('touchstart', this.canvas_touchstart,  Y.config.doc, SELECTORS.GENERICCANVAS, this); 
					}
					if(!this.canvas_touchmove_sub) { 
						this.canvas_touchmove_sub = Y.delegate('touchmove', this.canvas_touchmove,  Y.config.doc, SELECTORS.GENERICCANVAS, this); 
					}
					if(!this.canvas_touchend_sub) { 
						this.canvas_touchend_sub = Y.delegate('touchend', this.canvas_touchend,  Y.config.doc, SELECTORS.GENERICCANVAS, this); 
					}
					if(!this.canvas_mouseup_sub) { 
						this.canvas_mouseup_sub =  Y.delegate('mouseup',   this.canvas_mouseup,    Y.config.doc, SELECTORS.GENERICCANVAS, this); 
					}
    				if(!this.canvas_mouseout_sub) { 
	    				this.canvas_mouseout_sub =  Y.delegate('mouseout',   this.canvas_mouseout,    Y.config.doc, SELECTORS.GENERICCANVAS, this); 
		    		}
					if(!this.drawing_radius_change_sub) { 
						this.drawing_radius_change_sub =  Y.delegate('change', this.drawing_radius_change, Y.config.doc, SELECTORS.DRAWINGRADIUS, this); 
					}
				}
	
	},
	
	eraser_click: function(e) {
		questionID = this.canvas_get_question_id(e.currentTarget);
		if (questionID == 0) {
			canvasNode = Y.one(SELECTORS.GENERICCANVAS);
		} else {
			Y.all(SELECTORS.GENERICCANVAS).each(function(node) {
				if (node.ancestor().getAttribute('class') == 'qtype_freehanddrawing_id_' + questionID) {
					canvasNode = node;
				}
			}.bind(this));
		}
		if (this.is_canvas_empty(questionID) == false) {
			if (confirm(M.util.get_string('are_you_sure_you_want_to_erase_the_canvas', 'qtype_freehanddrawing')) == true) {
				canvasNode.getDOMNode().width = canvasNode.getDOMNode().width;
				this.create_canvas_context(questionID, false);
			}
		}
	},
	
	eraser_tool_click: function(e) {
		questionID = this.canvas_get_question_id(e.currentTarget);
		if (questionID == 0) {
			canvasNode = Y.one(SELECTORS.GENERICCANVAS);
		} else {
			Y.all(SELECTORS.GENERICCANVAS).each(function(node) {
				if (node.ancestor().getAttribute('class') == 'qtype_freehanddrawing_id_' + questionID) {
					canvasNode = node;
				}
			}.bind(this));
		}
        if (this.eraserToolOn[questionID] == false) {
            this.eraserToolOn[questionID] = true;
            Y.one(SELECTORS.ERASERTOOLBUTTON).set('src', M.cfg.wwwroot + '/question/type/freehanddrawing/pix/Eraser-icon-active.png');
		    canvasNode.setStyles({ cursor: "url('" + M.cfg.wwwroot + '/question/type/freehanddrawing/pix/Eraser.cur' + "'), default", });
            this.canvasContext[questionID].globalCompositeOperation = 'destination-out';
        } else {
            this.eraserToolOn[questionID] = false;
            Y.one(SELECTORS.ERASERTOOLBUTTON).set('src', M.cfg.wwwroot + '/question/type/freehanddrawing/pix/Eraser-icon.png');
		    canvasNode.setStyles({ cursor: "url('" + M.cfg.wwwroot + '/question/type/freehanddrawing/pix/Brush.cur' + "'), default", });
            this.canvasContext[questionID].globalCompositeOperation = 'source-over';
        }
	},

	draw_correct_answer: function(questionID, correctAnswer, canvasInstanceID) {
		Y.all(SELECTORS.READONLYCANVAS).each(function(node) {
			if (node.ancestor().getAttribute('class') == 'qtype_freehanddrawing_id_' + questionID && node.ancestor().getData('canvas-instance-id') == canvasInstanceID) {
				canvasNode = node;
			}
		}.bind(this));
		
		if (typeof canvasNode != 'undefined') {
		
		canvasNode.setStyles({ cursor: 'auto', });
		
	
		this.canvasContext[canvasInstanceID] = canvasNode.getDOMNode().getContext('2d');
		
			var img = new Image();
			img.onload = function() {
				this.canvasContext[canvasInstanceID].drawImage(img, 0, 0);
			}.bind(this);
			img.src = correctAnswer;
		
		}
		
	},
	choose_new_image_file_click: function(e) {
		if (this.is_canvas_empty(0) == false) {
			if (confirm(M.util.get_string('are_you_sure_you_want_to_pick_a_new_bgimage', 'qtype_freehanddrawing')) == false) {
				Y.one('.file-picker.fp-generallayout').one('.yui3-button.yui3-button-close').simulate("click");	
			}
		}
	},
	
	
	
	get_drawing_radius: function(questionID) {
		if (questionID == 0) {
			this.drawingRadius[0] = Y.one(SELECTORS.DRAWINGRADIUS).get('value');
		}
		return this.drawingRadius[questionID];
	},
	
	
	
	is_canvas_empty: function(questionID) {
		if (questionID == 0) {
			canvasNode = Y.one(SELECTORS.GENERICCANVAS);
		} else {
			Y.all(SELECTORS.GENERICCANVAS).each(function(node) {
				if (node.ancestor().getAttribute('class') == 'qtype_freehanddrawing_id_' + questionID) {
					canvasNode = node;
				}
			}.bind(this));
		}		
		if (this.emptyCanvasDataURL[questionID] != 0 && canvasNode.getDOMNode().toDataURL() != this.emptyCanvasDataURL[questionID]) {
			return false;
		}
		return true;
	},
	filepicker_change: function(e) {
		
		if (this.edit_mode == true) {
			Y.one(SELECTORS.FILEPICKERFIELDSET).setStyles({display: 'block'});
			Y.one(SELECTORS.FILEPICKERFIELDSETANOTHER).setStyles({display: 'none'});
		}
		
		
		var imgURL = Y.one('#id_qtype_freehanddrawing_image_file').ancestor().one('div.filepicker-filelist a').get('href');
		var image = new Image();
		image.src = imgURL;
		image.onload = function () {
			Y.one(SELECTORS.GENERICCANVAS).setStyles({backgroundImage: "url('" + imgURL + "')", display: 'block'});
			Y.one(SELECTORS.ERASERBUTTON).setStyles({display: 'block'});
			Y.one(SELECTORS.ERASERTOOLBUTTON).setStyles({display: 'block'});
			Y.one(SELECTORS.CONTAINERDIV).setStyles({display: 'inline-block'});
			Y.one(SELECTORS.NOBACKGROUNDIMAGESELECTEDYET).setStyles({display: 'none'});
			
			Y.one(SELECTORS.GENERICCANVAS).getDOMNode().width = image.width;
			Y.one(SELECTORS.GENERICCANVAS).getDOMNode().height = image.height;
			this.emptyCanvasDataURL[0] = Y.one(SELECTORS.GENERICCANVAS).getDOMNode().toDataURL();
			this.create_canvas_context(0, false);
		}.bind(this);
	},
	create_canvas_context: function(questionID, applyTextArea) {
		if (typeof applyTextArea == 'undefined') {
			applyTextArea = true;
		}
		if (questionID == 0) {
			canvasNode = Y.one(SELECTORS.GENERICCANVAS);
		} else {
			Y.all(SELECTORS.GENERICCANVAS).each(function(node) {
				if (node.ancestor().getAttribute('class') == 'qtype_freehanddrawing_id_' + questionID) {
					canvasNode = node;
				}
			}.bind(this));
		}
		canvasNode.setStyles({ cursor: "url('" + M.cfg.wwwroot + '/question/type/freehanddrawing/pix/Brush.cur' + "'), default", });
		this.canvasContext[questionID] = canvasNode.getDOMNode().getContext('2d');
		this.canvasContext[questionID].lineWidth = this.get_drawing_radius(questionID);
		this.canvasContext[questionID].lineJoin = 'round';
		this.canvasContext[questionID].lineCap = 'round';
		this.canvasContext[questionID].strokeStyle = 'blue';
        this.canvasContext[questionID].globalCompositeOperation = 'source-over';

        textarea = this.canvas_get_textarea(canvasNode);
		if (textarea != null) {
			if (applyTextArea == false) {
				textarea.set('value', '');
			} else {
				if (textarea.get('value') != '') {
					var img = new Image();
					img.onload = function() {
						this.canvasContext[questionID].drawImage(img, 0, 0);
					}.bind(this);
					img.src = textarea.get('value');
				}
			}
		}
	},
	drawing_radius_change: function(e) {
		if (this.is_canvas_empty(0) == false) {
			if (confirm(M.util.get_string('are_you_sure_you_want_to_change_the_drawing_radius', 'qtype_freehanddrawing')) == true) {
				Y.one(SELECTORS.GENERICCANVAS).getDOMNode().width = Y.one(SELECTORS.GENERICCANVAS).getDOMNode().width;
				this.create_canvas_context(0, false);
			} else {
				Y.one(SELECTORS.DRAWINGRADIUS).set('selectedIndex', (this.drawingRadius));
			}
		} else {
			this.create_canvas_context(0);
		}
	},
	canvas_mousedown: function(e) {
        // --- To prevent the cursor from changing into text-edit mode: http://stackoverflow.com/questions/2659999/html5-canvas-hand-cursor-problems ---
        e.preventDefault();
        e.stopPropagation();
        // -----------------------
		questionID = this.canvas_get_question_id(e.currentTarget);
		this.canvasContext[questionID].beginPath();
		var offset = e.currentTarget.getXY();
		if (e.pageX - offset[0] < 0 || e.pageY - offset[1] < 0 || e.pageX - offset[0] > e.currentTarget.getDOMNode().width || e.pageY - offset[1] > e.currentTarget.getDOMNode().height) {
			// we got out of the boundaries of the canvas
			//this.canvas_mouseup(e);
		}
		else {
			this.canvasContext[questionID].moveTo(e.pageX - offset[0], e.pageY - offset[1]);

			// Added this so that single clicks would also generate something.
			this.canvasContext[questionID].beginPath();
			this.canvasContext[questionID].arc(e.pageX - offset[0], e.pageY - offset[1], /*seems to be arbitrary*/this.canvasContext[questionID].lineWidth/40/*not sure about this*/, 0, 2 * Math.PI, false);
			this.canvasContext[questionID].fillStyle = 'blue';
			this.canvasContext[questionID].fill();
			this.canvasContext[questionID].stroke();
			// ------------------------------------------------------------------  

			Y.on('mousemove', this.canvas_mousemove, e.currentTarget, this);
		}
	},
	canvas_touchstart: function(e) {
        // --- To prevent scrolling ---
        e.preventDefault();
        e.stopPropagation();
        this.canvas_mousedown(e);
	},
	canvas_touchmove: function(e) {
        // --- To prevent scrolling ---
        e.preventDefault();
        e.stopPropagation();
        this.canvas_mousemove(e);
	},
	canvas_touchend: function(e) {
        // --- To prevent scrolling ---
        e.preventDefault();
        e.stopPropagation();
        this.canvas_mouseup(e);
	},
	canvas_mousemove: function(e) {
		questionID = this.canvas_get_question_id(e.currentTarget);
		var offset = e.currentTarget.getXY();
		if (e.pageX - offset[0] < 0 || e.pageY - offset[1] < 0 || e.pageX - offset[0] > e.currentTarget.getDOMNode().width || e.pageY - offset[1] > e.currentTarget.getDOMNode().height) {
			// we got out of the boundaries of the canvas
			this.canvas_mouseup(e);
		}
		else {
			this.canvasContext[questionID].lineTo(e.pageX - offset[0], e.pageY - offset[1]);
			this.canvasContext[questionID].stroke();
		}
	},
	canvas_mouseout: function(e) {
		this.canvas_mouseup(e);
	},
	canvas_mouseup: function(e) {
		e.currentTarget.detach('mousemove', this.canvas_mousemove);
		this.canvas_get_textarea(e.currentTarget).set('value', e.currentTarget.getDOMNode().toDataURL());
	},
    canvas_get_textarea: function(node) {
        questionID = this.canvas_get_question_id(node);
        if (questionID == 0) {
                       return Y.one(SELECTORS.CANVASTEXTAREAEDITMODE);
        } else {
                       return Y.one(SELECTORS.CANVASTEXTAREATESTMODE+questionID+'"]');
        }
},

	canvas_get_question_id: function(node) {
		if (node.ancestor().getAttribute('class').indexOf('qtype_freehanddrawing_id') == -1) {
			return 0;
		} else {
			return node.ancestor().getAttribute('class').replace(/qtype_freehanddrawing_id_/gi, '');
		}
	},
};
}, '@VERSION@', {requires: ['node', 'event'] });
