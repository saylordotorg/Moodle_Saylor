$(document).ready(function () {
  try {
    /*
        >>> login page customization
    */
    var cardBody = $(".card-body").eq(0) ? $(".card-body").eq(0).prependTo($("div[role='main']")) : '';
    if ($("div[role='main'] .card-body .row.justify-content-md-center")) {
      $("div[role='main'] .card-body .row.justify-content-md-center").addClass('hidden');
    }
    if ($("div[role='main'] .row.justify-content-center")) {
      $("div[role='main'] .row.justify-content-center").addClass('hidden');
    }
    if ($("#page-login-index")) {
      var headerNode = $("#page-login-index h2.card-header.text-center") ? $("#page-login-index h2.card-header.text-center").addClass("hidden") : "";
      /*
               >>> login Box
           */
      var loginTmp = [];
      var loginNode = "<div class='login-main-wrapper'></div>";
      loginTmp.push("<div class='login-main-wrapper'>");
      loginTmp.push("<div class='login-wrapper col-xl-4 col-md-6'>");
      loginTmp.push("<div class='login-guest-wrapper'>");
      loginTmp.push("</div>");
      loginTmp.push("</div>");
      loginTmp.push("<div class='signup-wrapper col-xl-8 col-md-6'>");
      loginTmp.push("<div class='signup-heading'>");
      loginTmp.push("<h2 class='heading'>Is this your first time here?</h2>");
      loginTmp.push("<span class='signup-desc'>For full access to this site, you first need to create an account.</span>");
      loginTmp.push("</div>");
      loginTmp.push("</div>");
      loginTmp.push("</div>");
      var loginSiteNode = $(loginNode)[0].innerHTML = loginTmp.join('');
      var mainRole = $("#page-login-index .container div[role='main']") ? $("#page-login-index .container div[role='main']").append(loginSiteNode) : '';
      var loginForm = $("form#login") ? $("form#login").clone(true).prependTo($('.login-wrapper')) : '';
      var loginGuestNode = ($(".col-md-5").eq(1) && $('.login-guest-wrapper')) ? $('.login-guest-wrapper').append($(".col-md-5").eq(1).html()) : '';
      var signUpForm = $("form#signup") ? $("form#signup").clone(true).appendTo($('.signup-wrapper')) : '';
      var cardBody2 = $("div[role='main'] > .card-body") ? $("div[role='main'] > .card-body").prependTo($(".login-wrapper")) : '';
      var justifyContent = $(".login-main-wrapper .card-body .row.justify-content-md-center.hidden") ? $(".login-main-wrapper .card-body .row.justify-content-md-center.hidden").remove() : '';
    }
  } catch (ignore) {}
    
  try{
        
      if($(".login-main-wrapper .signup-wrapper form#signup").length === 0){
         var signup =  $(".signup-wrapper") ? $(".signup-wrapper").addClass("hidden") : "";
         var regionBoxM = $("#region-main-box") ? $("#region-main-box").removeClass("col-xl-12") : "";
         var regionBox = $("#region-main-box") ? $("#region-main-box").addClass("col-xl-6 col-md-6 mauto") : "";
         var loginWrapper = $(".login-wrapper") ? $(".login-wrapper").removeClass("col-md-6") : "";
         var loginWrapper2 = $(".login-wrapper") ? $(".login-wrapper").addClass("col-xl-12 col-md-12") : "";
      }else{
         var signup2 =  $(".signup-wrapper") ? $(".signup-wrapper").removeClass("hidden") : "";
         var regionBox2 = $("#region-main-box") ? $("#region-main-box").removeClass("col-xl-6 col-md-6 mauto") : "";
         var regionBoxA = $("#region-main-box") ? $("#region-main-box").addClass("col-xl-12") : "";
         var loginWrapper2 = $(".login-wrapper") ? $(".login-wrapper").removeClass("col-md-6") : "";
         var loginWrapper3 = $(".login-wrapper") ? $(".login-wrapper").removeClass("col-xl-12 col-md-12") : "";
      }
      
  }catch(ignore){}
    
});
