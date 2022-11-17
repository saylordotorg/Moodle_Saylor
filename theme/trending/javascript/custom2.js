$.noConflict();
   jQuery(document).ready(function($) {
   
     /*=====
       ======= Home Page Slider Start============
   ============*/ 
    $(document).ready(function () {
       $('#camera_wrap_1').camera({
           thumbnails: false,
           pagination: false,
           loader: 'bar'
       });
    }); 
   /*=====
       ======= Home Page Slider End============
   ============*/
   
   
   
   /*=====
       ======= Home page Student Section Start============
   ============*/
   
   $("#colorful-background-image").colorfulTab({
       theme: "flatline",
       backgroundImage: "true",
       overlayColor: "#002F68",
       overlayOpacity: "0.8"
   });
   
   /*=====
       ======= Home page Student Section End============
   ============*/
   
   
    }); 