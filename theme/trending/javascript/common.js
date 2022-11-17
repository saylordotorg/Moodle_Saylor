  $(document).ready(function () {

if ($('body')) {
    $('body').addClass('fixed-nav');
  }


     /*
  ====== Navigation section start ======
*/

    try{
      var mainNav = $("#nav-main");
      var navNode = mainNav && mainNav.find(".navbar");
      var ulNode = navNode && navNode.find("ul.navbar-nav");
      if(ulNode.find("li.dropdown")){
        ulNode.find("li.dropdown").each(function(i, list){
            var divDrop = $(list).find("div.dropdown-menu");
            var divNode = document.createElement(divDrop[0].nodeName);
            var ul = document.createElement('ul');
          $(ul)[0].classList = "dropdown-list";
          $(ul).attr({
            "role" : "menu",
            "aria-labelledby" : "drop-down-menu"
          });
            ul.innerHTML = divDrop[0].innerHTML;
            divDrop[0].replaceWith(ul, divNode);
          $(list).find('div').remove();
        });
      }

      var ddItem = $("ul.dropdown-list a.dropdown-item");
      if(ddItem){
        for(var i = 0; i < ddItem.length; i+=1) {
          ddItem.slice(i, i+1).wrapAll("<li></li>");
        }
      }
    }catch(ignore){}

    try{
      if($("nav.navbar ul.navbar-nav.navigation")){
        $("nav.navbar ul.navbar-nav.navigation").addClass("main-menu theme-ddmenu");
      }
      if($("nav.navbar ul.navbar-nav")){
        $("nav.navbar ul.navbar-nav li.nav-item").removeClass("nav-item");
      }
     if ($("nav.navbar ul.navbar-nav.main-menu")) {
        $("nav.navbar ul.navbar-nav.main-menu").attr({
          "data-animtype": 2,
          "data-animspeed": 450
        });
        //$("nav.navbar ul.navbar-nav.main-menu li.dropdown ul.dropdown-list a.dropdown-item").removeClass("dropdown-item");
      }


    }catch(ignore){}

//<b class="mobile-arrow"></b>
    try{
      if($('#nav-main nav.navbar ul.main-menu li.dropdown')){
        $.each($('#nav-main nav.navbar ul.main-menu li.dropdown a[data-toggle="dropdown"]'), function( index, obj ) {
          var arrow = document.createElement('b');
          $(arrow)[0].classList = "mobile-arrow";
          obj.append(arrow);
        });
      }
    }catch(ignore){}
        
    try{

          var width = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
          
          if(width <= 768){
            if($('#nav-main nav.navbar ul.nav') && $('#nav-main nav.navbar ul.main-menu')){
              $('#nav-main nav.navbar ul.nav').insertBefore($('#nav-main nav.navbar ul.main-menu'));
            }
          }else{
            if($('#nav-main nav.navbar ul.nav') && $('#nav-main nav.navbar ul.main-menu')){
              $('#nav-main nav.navbar ul.main-menu').insertBefore($('#nav-main nav.navbar ul.nav'));
            }
          }

        $(window).on('resize', function() {
          var width = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
          if(width <= 768){
            if($('#nav-main nav.navbar ul.nav') && $('#nav-main nav.navbar ul.main-menu')){
              $('#nav-main nav.navbar ul.nav').insertBefore($('#nav-main nav.navbar ul.main-menu'));
            }
          }else{
            if($('#nav-main nav.navbar ul.nav') && $('#nav-main nav.navbar ul.main-menu')){
              $('#nav-main nav.navbar ul.main-menu').insertBefore($('#nav-main nav.navbar ul.nav'));
            }
          }
      });
    }catch(ignore){}

/*
  ====== Navigation section end ======
*/

  try {
    $('#page-header .mini-block').before("<p id='internalbannertagline' class='tagline'></p>");
    var categoryTag = document.getElementById("internalbannertagline") ? document.getElementById("internalbannertagline").innerHTML = internalbannertagline : '';
  } catch (ignore) {}

/*
  ====== More section start ======
*/

  try {
      // Configure/customize these variables.
      var showChar = 55; // How many characters are shown by default
      var ellipsestext = "";
      var moretext = "...More";
      var lesstext = "...Less";


      $('.course_category_tree .inner-con').each(function (index, obj) {
        var tHTML = "";
        var teachers = $(obj).find('.teachers');
        if (teachers.length > 0) {
          var tHTML = teachers.html();
        }
        if ($(this).children('.teachers')) {
          $(this).children('.teachers').remove();
        }
        var content = $(this).html();

        if (content.length > showChar) {
          content = strip(content);

          function strip(html) {
            var tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText;
          }
          var c = content.substr(0, showChar);
          var h = content.substr(showChar, content.length - showChar);

          var html = c + '<span class="moreellipses">' + ellipsestext + ' </span><span class="morecontent"><span>' + h + "<ul class='teachers'>" + tHTML + "</ul>" + '</span>  <a href="" class="morelink">' + moretext + '</a></span>';

          $(this).html(html);
          var teachersNode = $(this).children('.morecontent').children('span').children('.teachers');
          if (teachersNode) {
            var tInnerHTML = teachersNode.html();
            if (tInnerHTML === "") {
              teachersNode.remove();
            }
          }

        }

      });

      $(".morelink").click(function () {
        if ($(this).hasClass("less")) {
          $(this).removeClass("less");
          $(this).html(moretext);
        } else {
          $(this).addClass("less");
          $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
      });
      
    } catch (ignore) {}

    try{
      $(".visitlink a > span").addClass("all");

      var elements = document.getElementsByClassName('all');
      for (var i = 0; i < elements.length; i++) {
          if (elements[i].innerHTML == 'Course') {
              elements[i].innerHTML = "Enter";
          }
      }

    }catch(ignore){}

  });

