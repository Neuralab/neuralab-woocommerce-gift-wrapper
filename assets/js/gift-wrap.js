jQuery( function( $ ) {
  $( '#gift-wrap-check' ).change( function() {
    $( 'body' ).trigger( 'update_checkout' );
  });
});
