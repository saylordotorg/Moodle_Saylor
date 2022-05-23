define(['jquery','core/log'],  function($,log) {
  "use strict"; // jshint ;_;

  return {

  	  useanimatecss: true,

	  init: function(opts){
		this.useanimatecss=opts.useanimatecss;
	  },


	  do_animate: function(theelement, animation, inout){
  	  	  var that =this;

		  // We create a Promise and return it
		  var p =new Promise(function(resolve, reject) {

			  //if animate css is turned on
			  if(that.useanimatecss){
				  var animationName = 'animate__' + animation;

				  // When the animation ends, we clean the classes and resolve the Promise
				  function handleAnimationEnd(event) {
					  event.stopPropagation();
					  theelement.removeClass('animate__animated ' + animationName);
					  if(inout==='out'){theelement.hide();}
					  resolve('Animation ended');
				  }
				  theelement.one('animationend', handleAnimationEnd);
				  if(inout==='in'){theelement.show();}
				  theelement.addClass('animate__animated ' + animationName);

			  //if animate css is turned off
			  }else {

				  switch(inout){
					  case 'in':
						  theelement.slideDown(400,function(){resolve('Animation ended');});
						  break;
					  case 'out':
						  theelement.slideUp(400,function(){resolve('Animation ended');});
						  break;
					  default:
					  	 //do nothing special
						  resolve('Animation ended');
				  }
			  }
		  });
		  return p;
	  },

  }

});
