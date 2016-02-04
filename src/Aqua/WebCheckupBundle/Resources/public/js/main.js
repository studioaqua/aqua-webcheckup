jQuery.noConflict();

(function( $ ) {
  $('#form_webcheckup').submit(function( event ) {
    //event.preventDefault();
    console.log('submit check.');
    console.log(event);
    $('#form_checkup').prop('disabled', true);
    NProgress.start();
    console.log(NProgress);
  });
})( jQuery );
