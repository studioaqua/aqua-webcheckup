+function ($) {
  $( "#form_webcheckup" ).submit(function( event ) {
    $('#form_checkup').prop('disabled', true);
    NProgress.start();
  });
}(jQuery);
