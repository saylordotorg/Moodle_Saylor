
<script type="text/javascript" src="<?php
                                    global $PAGE; if($PAGE->pagelayout !== 'frontpage')
                                        echo $CFG->wwwroot.'/theme/trending/javascript/jquery.min.js';
                                    ?>"></script>
<script type="text/javascript">
   var categorytagline = "<?= $categorytagline ?>";
   var allcoursestagline = "<?= $allcoursestagline ?>";
   var enrolledcoursestagline = "<?= $enrolledcoursestagline ?>";
    var sitenewstagline = "<?= $sitenewstagline ?>";
    var internalbannertagline = "<?= $internalbannertagline ?>";
   
   
   /* Search Courses */
      $(document).ready(function() {
          
        /*=====
        ======= For Main Calendar Section Start============
       ============*/
         if($("body#page-calendar-view .controls .calendar-controls")){
        $("body#page-calendar-view .controls .calendar-controls").addClass("clearfix");
         }
       /*=====
        ======= For Main Calendar Section End============
       ============*/
   
      if($("body.pagelayout-frontpage").length > 0){
        if ($('#frontpage-category-combo').length === 0) {
             $('#page #page-content').css({
                 'display': 'none'
             });
         }
          
         if ($('#coursesearch').length === 0) {
             $('#page .newsearch').css({
                 'display': 'none'
             });
         }
          
           if ($('#frontpage-category-names').length === 0) {
             $('#page .defaultcategories').css({
                 'display': 'none'
             });
         }
          
          if ($('#frontpage-category-names').length === 0) {
             $('#page .customcategories').css({
                 'display': 'none'
             });
         } 
      }

        /*=====
    ======= Course Category Inner Section Start ============
============*/

var courseIndexCate = $('#page-course-index-category .course_category_tree > .content');
var pagination = courseIndexCate.find("nav.pagination");
var currentLocation = window.location;
var page = currentLocation.search.split("&page=");
var pageNum = parseInt(page[1]);
if(courseIndexCate.length > 0 ){
  var courses = courseIndexCate.find('.courses');
  var courseBox = courses.find('.coursebox');
  var courseBoxLen = courseBox.length;
  if(courseBox){
    if(courseBoxLen > 10){
      courseBox.each(function(index, obj){
    
        var panelImg = $(obj).find('.panel-image') ? $(obj).find('.panel-image').css('display', 'none') : '';
        var info = $(obj).find('.panel-body .info');
        if(info){
          $(info).insertBefore($(obj).find('.panel-image'));
        }
    if($(obj).find('.panel-image').children().length === 0){
      $(obj).find('.content').addClass("max-width");
    }else{
      $(obj).find('.content').addClass("mleft");
    }
      });
    }else{
      courseBox.each(function(index, obj){
        if($(obj).find('.panel-image').children().length === 0){
          $(obj).find('.panel-image').addClass("hidden");
          $(obj).find('.content').addClass("mleft");
        }else{
           $(obj).find('.panel-image').removeClass("hidden");
           $(obj).find('.content').removeClass("mleft");
        }
      });
    }
    
  }
}

/*
  === for pagination in course page excluding first page
*/

if(pagination.length !== 0 ){

  if(pageNum !== 0){
    
  if(courseIndexCate.length > 0 ){
  
    var courses2 = courseIndexCate.find('.courses');
    var courseBox2 = courses2.find('.coursebox');
    var courseBoxLen2 = courseBox2.length;
      if(courseBox2){
          if(courseBoxLen2 > 0){
            courseBox2.each(function(index, obj){
              var panelImg2 = $(obj).find('.panel-image') ? $(obj).find('.panel-image').css('display', 'none') : '';
              var info2 = $(obj).find('.panel-body .info');
              if(info2){
                $(info2).insertBefore($(obj).find('.panel-image'));
              }
            });
          }
        
      }
    }
    
  }
}

/*=====
    ======= Course Category Inner Section End ============
============*/

      });
</script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/trending/javascript/backtotop.js"></script>

