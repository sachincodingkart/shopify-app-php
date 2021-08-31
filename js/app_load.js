jQuery(document).ready(function () {

  // Load the content of proxy file (proxy_frontend) inside the appfrontend-div to store front

  if (jQuery(".appfrontend-div").length == 0) {
    jQuery('body').append("<div class='appfrontend-div'></div>");
    jQuery('.appfrontend-div').load('/proxy_frontend/proxy_frontend.php');
  }

});