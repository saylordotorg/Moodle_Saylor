jQuery(document).ready(function($){
    $('.theme-ddmenu').each(function() {
        var menuList = $(this),
            animType = menuList.data('animtype'),
            animSpeed = menuList.data('animspeed'),
            mobileArr = menuList.find('.mobile-arrow');
        menuList.superfish({
            popUpSelector: 'ul',
            hoverClass: 'mb2ctm-hover',
            animation: animType == 2 ? {
                height: 'show'
            } : {
                opacity: 'show'
            },
            speed: animSpeed,
            speedOut: 'fast',
            cssArrows: false
        });
        menuOnHover(menuList);
        $(window).on('resize', function() {
            menuOnHover(menuList)
        });
        mobileArr.click(function(e) {
            e.preventDefault();
            $(this).parent().siblings('ul').slideToggle(250);
            $(this).toggleClass('active')
        })

        function menuOnHover(list) {
            var w = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
            if (w <= 768) {
                list.removeClass('sf-js-enabled');
                list.removeClass('desk-menu');
                list.addClass('mobile-menu')
            } else {
                list.addClass('sf-js-enabled');
                list.removeClass('mobile-menu');
                list.addClass('desk-menu');
                list.find('.mobile-arrow').removeClass('active');
                list.find('.mobile-arrow').parent().siblings('ul').hide()
            }
        }
    })

    function customAccordion(header, content) {
        header.click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            content.slideToggle(250)
        })
    };
    $(document).on('click', '.show-menu', function(e) {
        e.preventDefault();
        var menuList = $(this).parent().parent().parent().find('.theme-ddmenu');
        menuList.slideToggle(250)
    });
    
    /*=====
            ======= fixed navigation start======*/
    
    setTimeout(function() {
        makeFixedNavigation()
    }, 10);
    $(window).scroll(function() {
        makeFixedNavigation()
    })

    function makeFixedNavigation() {
        var win = $(window),
            offsetEl = $('.fixed-nav-element');
        if (offsetEl.length != 0 && $('body').hasClass('fixed-nav')) {
            var fixedEl = $('#nav-main'),
                fixedElWrap = fixedEl.parent(),
                elOffset = offsetEl.offset().top,
                fixElHeight = fixedEl.outerHeight(true);
            if (win.scrollTop() > elOffset) {
                fixedEl.addClass('navbar-fixed-top');
                offsetEl.css({
                    height: fixElHeight
                })
            } else {
                fixedEl.removeClass('navbar-fixed-top');
                offsetEl.css({
                    height: 0
                })
            }
        }
    };
    
    /*=====
            ======= fixed navigation end======*/
    
    /*=====
======= Message App Drawer Close Button Start======*/


    if(($(".drawer").length === 1)){
        if($(".drawer .close-btn").length === 0){
            $(".drawer").prepend("<div class='close-btn'>x</div>") 
        }
    }

    if($(".drawer .close-btn").length === 1){
        $(".drawer .close-btn").on("click", function(){
            $(".drawer").addClass("hidden");
            $(".drawer").attr({
                "aria-expanded":"false",
                "aria-hidden":"true"
            });
        });
    }

/*=====
======= Message App Drawer Close Button End======*/

});

