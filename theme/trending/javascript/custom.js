require(['jquery'], function ($) {
  $(document).ready(function () {


      /*=====
    ======= Stickyicons Section Start============
============*/
try {
var stickyNavHeight = $('#stickyicons').height();
$('#stickyicons').css({
    'margin-top': Math.ceil((stickyNavHeight / 2) * -1)
});
$('#stickyicons li').each(function() {
    var linkEl = $(this).find('a'),
        textEl = $(this).find('span.stickynavtext');
    linkEl.hover(function() {
        textEl.stop().animate({
            right: '100%'
        }, 300)
    }, function() {
        textEl.stop().animate({
            right: -500
        }, 150)
    })
});
} catch (ignore) {}
/*=====
    ======= Stickyicons Section End============
============*/

    /*=====
                ======= Home Page Category Start============
            ============*/

    try {
      var frontpageCategoryNames = $('#frontpage-category-names').html();
      if (typeof frontpageCategoryNames !== 'undefined' && frontpageCategoryNames !== null) {
        $('.defaultcategories > .container').prepend('<div id="frontpage-category-names">' + frontpageCategoryNames + '</div>');
        $('#region-main #frontpage-category-names').css({
          'display': 'none'
        });
      }

      var frontpageCategoryNames = $('#frontpage-category-names').html();
      if (typeof frontpageCategoryNames !== 'undefined' && frontpageCategoryNames !== null) {
        $('.customcategories > .container').prepend('<div id="frontpage-category-names">' + frontpageCategoryNames + '</div>');
        $('#region-main #frontpage-category-names').css({
          'display': 'none'
        });
      };
      if(typeof frontpageCategoryNames === "undefined"){
         $('.customcategories,.defaultcategories').css({
           'display' : 'none'
         });
      }
      if ($('#region-main #frontpage-category-names')) {
        $('#region-main #frontpage-category-names').remove();
      }

      if ($('#frontpage-category-names > h2'));
      $('#frontpage-category-names > h2').addClass('all');
      var elements = document.getElementsByClassName('all');
      if (elements) {
        for (var i = 0; i < elements.length; i++) {
          if (elements[i].innerHTML == 'Course categories') {
            elements[i].innerHTML = "Top Specializations";
            break;
          }
        }
      }

      $('#frontpage-category-names h2.all').after("<p id='categorytagline' class='tagline'></p>");
      var categoryTag = document.getElementById("categorytagline") ? document.getElementById("categorytagline").innerHTML = categorytagline : '';


      if ($(".customcategories > .container")) {
        $(".customcategories > .container").append("<a class='seeall btn btn-primary float-right' href='course/'>See All</a>");
      }

      $('.customcategories .subcategories:first').addClass('row');


      $(".customcategories .category[data-depth='1']").wrapAll("<div id='CustomCategories' class='owl-carousel owl-theme owl-loaded owl-drag'></div>");
      $(".customcategories .category").addClass("item");
      $(".owl-item:first").addClass("active");

      if ($('#CustomCategories')) {
        if ($('body').hasClass('dir-rtl') === true) {
          $('#CustomCategories').addClass('owl-rtl');
          $("#CustomCategories").owlCarousel({
            rtl: true,
            margin: 10,
            nav: true,
            loop: false,
            dots: false,
            responsiveClass: true,
            responsive: {
              0: {
                items: 1,
                nav: true
              },
              600: {
                items: 2,
                nav: true,
              },
              1000: {
                items: 4,
                nav: true,
              }
            }

          });
        } else {
          $("#CustomCategories").owlCarousel({
            rtl: false,
            margin: 10,
            nav: true,
            loop: false,
            dots: false,
            autoplay: true,
            responsiveClass: true,
            responsive: {
              0: {
                items: 1,
                nav: true
              },
              600: {
                items: 2,
                nav: true,
              },
              1000: {
                items: 4,
                nav: true,
              }
            }

          });
        }
      }


      // course customization start


      var customCateg = jQuery(".customcategories");
      if (customCateg) {
        var subCateg = customCateg && customCateg.find(".subcategories");
        var ownItem = subCateg && subCateg.find(".owl-item");
        var categoryItem = ownItem && ownItem.find('.category');
        categoryItem.each(function (index, obj) {
          var numOfCourse = jQuery(obj).find(".numberofcourse").eq(0);
          var orgContent = numOfCourse.html();
          var numContent = orgContent !== undefined ? orgContent.replace(/[\])}[{(]/g, '').trim() : '';
          var num = numContent !== "" ? parseInt(numContent) : '';
          var contentNode = jQuery(obj).find('.content');
          var course = num !== 1 ? "courses" : "course";
          if (num !== '') {
            jQuery("<span class='course-num'>" + num + " " + course + "</span>").insertAfter(contentNode)
          } else {
            jQuery("<span class='course-num'>0 course</span>").insertAfter(contentNode)
          }
          numOfCourse.addClass('hidden')
        });
      }

    } catch (ignore) {}


      /*=====
        ======= Home Page Category End============
    ============*/



/*=====
    ======= Home Page All Courses Start============
============*/
try{
      var availableCourse = $('#frontpage-available-course-list').html();
      if (typeof availableCourse !== 'undefined' && availableCourse !== null) {
        if ($('#allcourses > .container')) {
          $('#allcourses > .container').append('<div id="frontpage-available-course-list">' + availableCourse + '</div>');
        }
        if ($('#region-main #frontpage-available-course-list')) {
          $('#region-main #frontpage-available-course-list').remove();
        }

      };
      if ($('#frontpage-available-course-list').length === 0) {
        $('#page #allcourses').remove();
      }
      if ($('#frontpage-available-course-list > h2')) {
        $('#frontpage-available-course-list h2')[0].innerHTML = "Courses We Provide";
      }
if ($('#frontpage-available-course-list h2')) {
        $('#frontpage-available-course-list h2').after('<p id="allcoursestagline" class="tagline"></p>');
        var allCoursesTag = document.getElementById("allcoursestagline") ? document.getElementById("allcoursestagline").innerHTML = allcoursestagline : '';
    }
      var mainWrapper = $('.frontpage-course-list-all, .frontpage-course-list-enrolled');
      if (mainWrapper) {
        mainWrapper.each(function (ind, obj) {
          var coursebox = $(obj).find('.coursebox');
          if (coursebox) {
            coursebox.each(function (index, obj) {
              var courseimage = $(obj).find('.content .courseimage');
              var summaryNode = $(obj).find('.content .summary');
              var teacherNode = $(obj).find('.content ul.teachers');
              var moreContentNode = summaryNode && summaryNode.find(".morecontent span");
              var findDiv = $(obj).find('.info');
              if (courseimage.length > 0) {
                courseimage.insertBefore(findDiv);
              }
              if (findDiv && summaryNode) {
                findDiv.insertBefore(summaryNode);
              }
              if (teacherNode.length > 0 && moreContentNode.length > 0) {
                moreContentNode.append(teacherNode);
              }
            });
          }
        });
      }
    if ($("#allcourses .frontpage-course-list-all > .coursebox")) {
        $("#allcourses .frontpage-course-list-all > .coursebox").wrapAll("<div id='allCoursesCarousel' class='owl-carousel owl-theme owl-loaded owl-drag'></div>");
    }
    
    if ($(".frontpage-course-list-all > .coursebox")) {
        $(".frontpage-course-list-all > .coursebox").addClass("item");
    }
    
    if ($('body').hasClass('dir-rtl') === true) {
        $('#allCoursesCarousel').addClass('owl-rtl');
        $("#allCoursesCarousel").owlCarousel({
            rtl: true,
            margin: 10,
            nav: true,
            loop: false,
            dots: false,
            autoplay: true,
            responsiveClass: true,
                responsive: {
                  0: {
                    items: 1,
                    nav: true
                  },
                  600: {
                    items: 2,
                    nav: true,
                  },
                  1000: {
                    items: 4,
                    nav: true,
                  }
                }

    });
    }else{
        $("#allCoursesCarousel").owlCarousel({
            rtl: false,
            margin: 10,
            nav: true,
            loop: false,
            dots: false,
            autoplay: true,
            responsiveClass: true,
                responsive: {
                  0: {
                    items: 1,
                    nav: true
                  },
                  600: {
                    items: 2,
                    nav: true,
                  },
                  1000: {
                    items: 4,
                    nav: true,
                  }
                }


    });
    }

    $(".visitlink a > span").addClass("all");

    var elements = document.getElementsByClassName('all');
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].innerHTML == 'Course') {
            elements[i].innerHTML = "Enter";


        }
    }

    /* Paging Morelink */
    if ($('.paging-morelink > a')) {
        $('.paging-morelink > a').addClass('paging-morelink-link');
    }
    var elements = document.getElementsByClassName('paging-morelink-link');
    if (elements) {
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].innerHTML == 'All courses') {
                elements[i].innerHTML = "View all courses";
                break;
            }
        }
    }
    if ($(".paging-morelink-link")) {
        $(".paging-morelink-link").append(" <i class='fa fa-long-arrow-right' aria-hidden='true'></i>");
    }
    } catch (ignore) {}
/*=====
    ======= Home Page All Courses End============
============*/ 


    /*=====
    =======About Us Section Start============
    ============*/
try{
  var firstCollaspse = $("#about #collapseOne1") ? $("#about #collapseOne1").addClass('show') : "";
}catch(ignore){}
    /*=====
    =======About Us Section End============
    ============*/

/*=====
    ======= Home page Site News Start============
============*/
    try{
    if ($('#site-news-forum > h2')) {
        $('#site-news-forum > h2').addClass('newsheading');
    }

    var elements = document.getElementsByClassName('newsheading');
    if (elements) {
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].innerHTML == 'Site announcements') {
                elements[i].innerHTML = "Special Announcements";
                break;
            }
        }
    }
    if ($('h2.newsheading')) {
        $('h2.newsheading').after('<p id="sitenewstagline" class="tagline"></p>');
        var siteNewsTag = document.getElementById("sitenewstagline") ? document.getElementById("sitenewstagline").innerHTML = sitenewstagline : '';
    }

    var siteNewsForum = $('#site-news-forum').html();
    if (typeof siteNewsForum !== 'undefined' && siteNewsForum !== null) {
        if ($('#news')) {
            $('#news > .container').append('<div id="site-news-forum">' + siteNewsForum + '</div>');
        }
        if ($('#region-main #site-news-forum')) {
            $('#region-main #site-news-forum').outerHTML= "";
        }

    };
    if ($('#site-news-forum').length === 0) {
        if ($('#page #news')) {
            $('#page #news').outerHTML= "";
        }
    }

    if ($('#page-content #site-news-forum').length !== 0) {
        $('#page-content #site-news-forum').remove();
      }

        var _mainDiv = $(".author");
        if (_mainDiv) {
            for (var i = 0; i < _mainDiv.length; i++) {    
                if(_mainDiv[i].childNodes[2]){
                    if(_mainDiv[i].childNodes[2].nodeValue){
                        var _info = _mainDiv[i].childNodes[2].nodeValue; 
                        var _infoMain = _info.slice(3) ? _info.slice(3) : '';
                        if(_infoMain !== ''){
                            var _anchorEl = _mainDiv[i].childNodes[1];
                            if(_anchorEl){
                                $("<div class='content wst'>" + _infoMain + "</div>").insertAfter(_anchorEl);
                                
                            }
                            if(_mainDiv[i].childNodes[3]){
                                _mainDiv[i].childNodes[3].nodeValue = '';
                                
                                
                            }
                            if(_mainDiv[i].childNodes[0]){
                                _mainDiv[i].childNodes[0].nodeValue = '';
                                
                                
                            }
                        }
                    }
                }
            }
        }

    // removing a tag
    if (jQuery("#site-news-forum > a")) {

        jQuery("#site-news-forum > a").each(function(index, obj) {
            var attr = this.getAttribute('id').substring(0, 1);
            if (attr === 'p') {
                this.outerHTML= "";
            }
        });

    }
    }catch(ignore){}
/*=====
    ======= Home page Site News End============
============*/    


/*=====
    ======= Home page My Courses Start============
============*/

    if ($('.frontpage-course-list-enrolled')) {
        $('.frontpage-course-list-enrolled').parent().addClass("mycourses");
    }

    if ($('.mycourses > h2')) {
        $('.mycourses > h2').addClass('mycoursesheading');
    }

    var elements = document.getElementsByClassName('mycoursesheading');
    if (elements) {
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].innerHTML == 'My courses') {
                elements[i].innerHTML = "Enrolled Courses";
                break;
            }
        }
    }

    if ($('.mycourses > h2')) {
        $('.mycourses > h2').after('<p id="enrolledcoursestagline" class="tagline"></p>');
        var enrolledCoursesTag = document.getElementById("enrolledcoursestagline") ? document.getElementById("enrolledcoursestagline").innerHTML = enrolledcoursestagline : '';
    }

    var myCourses = $('.mycourses').html();
    if (typeof myCourses !== 'undefined' && myCourses !== null) {
        if ($('#enrolledcourses > .container')) {
            $('#enrolledcourses > .container').append('<div id="frontpage-course-list" class="mycourses">' + myCourses + '</div>');
        }
        if ($('#region-main .mycourses')) {
            $('#region-main .mycourses').remove();
        }

    };
    if ($('.mycourses').length === 0) {
        $('#page #enrolledcourses').remove();
    }



    $('.frontpage-course-list-enrolled .coursebox').addClass('col-lg-3 col-md-6 col-sm-12');
    $('.frontpage-course-list-enrolled').addClass('clearfix');

    $(function() {
        var frontpageCourseListEnrolled = $('.frontpage-course-list-enrolled');
        var totalPageWidth = $(frontpageCourseListEnrolled).width();
        var courseBoxWidth = frontpageCourseListEnrolled.find('.coursebox:first').outerWidth(true);
        $('.frontpage-course-list-enrolled > .coursebox').addClass('tst');
        var allBoxes = frontpageCourseListEnrolled.find('.coursebox');
        var totalBoxes = allBoxes.length;
        var boxesPerRow = Math.floor(totalPageWidth / courseBoxWidth);
        var temp2, temp3, shadowPAGE = $('<div class="shadow-frontpage-course-list-enrolled row"></div>');
        for (temp2 = 0; temp2 < boxesPerRow; temp2++) {
            shadowPAGE.append('<div class="content-column col-lg-3 col-md-6 col-sm-12 content-column-' + temp2 + '"></div>');
        }
        for (temp2 = 0, temp3 = 0; temp2 < totalBoxes; temp2++, temp3 = (temp3 < (boxesPerRow - 1) ? temp3 + 1 : 0)) {
            shadowPAGE.find('.content-column-' + temp3).append($(allBoxes[temp2]).clone());
        }
        shadowPAGE.append('<div class="clear"></div>');
        frontpageCourseListEnrolled.html(shadowPAGE);
    });

    $(function() {


        $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').children().each(function() {
            var tagClass = $(this);
            // collum 1
            if (tagClass.hasClass('content-column-0')) {
                var divs = $('.content-column-0 > .coursebox');
                for (var i = 0; i < divs.length; i += 4) {
                    divs.slice(i, i + 4).wrapAll('<div class="color-wrapper-1"></div>');
                };
                $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').each(function(i, v) {
                    $(v).attr('id', 'color-wrapper-' + (i + 1)).find('div.color-wrapper-1').each(function(idx, val) {
                        $(val).children().each(function(index, element) {
                            if (element.parentNode.children.length <= 1) {
                                $(element).addClass('main-color-1');
                            } else {
                                $(element).addClass('color-' + (++index));
                            }
                        });
                    });
                });
            };
            // collum 2
            //for reverse
            if (tagClass.hasClass('content-column-1')) {
                var divs = $('.content-column-1 > .coursebox');
                for (var i = 0; i < divs.length; i += 4) {
                    divs.slice(i, i + 4).wrapAll('<div class="color-wrapper-2"></div>');
                };
                $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').each(function(i, v) {

                    $(v).attr('id', 'color-wrapper-' + (i + 1)).find('div.color-wrapper-2').each(function(idx, val) {

                        $($(val).children().get().reverse()).each(function(index, element) {

                            if (element.parentNode.children.length <= 1) {
                                $(element).addClass('main-color-2');
                            } else {
                                $(element).addClass('color-' + (++index));
                            }

                        });
                    });
                });
            };
            // collum 3
            if (tagClass.hasClass('content-column-2')) {
                var divs = $('.content-column-2 > .coursebox');
                for (var i = 0; i < divs.length; i += 4) {
                    divs.slice(i, i + 4).wrapAll('<div class="color-wrapper-3"></div>');
                };
                $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').each(function(i, v) {

                    $(v).attr('id', 'color-wrapper-' + (i + 1)).find('div.color-wrapper-3').each(function(idx, val) {

                        $(val).children().each(function(index, element) {

                            if (element.parentNode.children.length <= 1) {
                                $(element).addClass('main-color-3');
                            } else {
                                $(element).addClass('color-' + (++index));
                            }
                        });
                    });
                });
            };
            // collum 4
            //for reverse
            if (tagClass.hasClass('content-column-3')) {
                var divs = $('.content-column-3 > .coursebox');
                for (var i = 0; i < divs.length; i += 4) {
                    divs.slice(i, i + 4).wrapAll('<div class="color-wrapper-4"></div>');
                };
                $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').each(function(i, v) {
                    $(v).attr('id', 'color-wrapper-' + (i + 1)).find('div.color-wrapper-4').each(function(idx, val) {
                        $($(val).children().get().reverse()).each(function(index, element) {
                            if (element.parentNode.children.length <= 1) {
                                $(element).addClass('main-color-4');
                            } else {
                                $(element).addClass('color-' + (++index));
                            }
                        });
                    });
                });
            };
            // collum 5
            if (tagClass.hasClass('content-column-4')) {
                var divs = $('.content-column-4 > .coursebox');
                for (var i = 0; i < divs.length; i += 4) {
                    divs.slice(i, i + 4).wrapAll('<div class="color-wrapper-5"></div>');
                };
                $('.frontpage-course-list-enrolled .shadow-frontpage-course-list-enrolled').each(function(i, v) {
                    $(v).attr('id', 'color-wrapper-' + (i + 1)).find('div.color-wrapper-5').each(function(idx, val) {
                        $(val).children().each(function(index, element) {
                            $(element).addClass('color-' + (++index));
                        });
                    });
                });
            };


        });
    });
/*=====
    ======= Home page My Courses End============
============*/



    /*=====
        ======= More Less Content============
    ============*/

    try {
      // Configure/customize these variables.
      var showChar = 55; // How many characters are shown by default
      var ellipsestext = "";
      var moretext = "...More";
      var lesstext = "...Less";


      $('.frontpage-course-list-all .inner-con, .customcategories .categorydescription').each(function (index, obj) {
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


/*=====
            ======= More Less Content End============
        ============*/



  });

});
