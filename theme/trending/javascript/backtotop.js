require(['jquery'], function ($) {
  $(document).ready(function () {

    /*=====
        ======= Back to top Start============
    ============*/
    try {
      jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > 100) {
          jQuery('.scrollup').fadeIn();
        } else {
          jQuery('.scrollup').fadeOut();
        }
      });

      jQuery('.scrollup').click(function () {
        jQuery("html, body").animate({
          scrollTop: 0
        }, 600);
        return false;
      })

      $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
          $('#backtotop ').fadeIn();
        } else {
          $('#backtotop').fadeOut();
        }
      });
      $('#backtotop a').click(function (e) {
        e.preventDefault();
        $("html, body").animate({
          top: 0
        });
        return false;
      });
    } catch (ignore) {}
    /*=====
        ======= Back to top End============
    ============*/


  });
});
