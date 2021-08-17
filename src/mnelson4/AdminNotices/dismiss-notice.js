jQuery(document).ready( function(){
    var dismissBtn  = document.querySelector( '.wptrt-notice .notice-dismiss' );

    // Add an event listener to the dismiss button.
    dismissBtn.addEventListener( 'click', function( event ) {
        var notice = jQuery(event.currentTarget).parents('.wptrt-notice');
        var data_id = notice.attr('data-id');
        var data_nonce = notice.attr('data-nonce');
        var httpRequest = new XMLHttpRequest(),
            postData    = '';

        // Build the data to send in our request.
        // Data has to be formatted as a string here.
        postData += 'id=' + data_id;
        postData += '&action=wptrt_dismiss_notice';
        postData += '&nonce=' + data_nonce;

        httpRequest.open( 'POST', ajaxurl );
        httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
        httpRequest.send( postData );
    });

});