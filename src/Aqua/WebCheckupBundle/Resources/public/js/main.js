jQuery.noConflict();

(function( $ ) {
  $( "#form_webcheckup" ).submit(function( event ) {
    console.log(event);
    $('#form_checkup').prop('disabled', true);
    NProgress.start();
  });
})( jQuery );
