function pmb_generate_doc_from_html(is_preview, success_callback, failure_callback){
    var use_middleman = parseInt(pmb_pro.use_pmb_central_for_previews) || ! is_preview;
    if(use_middleman){
        var authorization_data = pmb_pro.license_data;
    } else {
        var authorization_data = 'YOUR_API_KEY_HERE';
    }

    var dynamic_doc_attrs = JSON.parse(JSON.stringify(pmb_pro.doc_attrs));
    // if this a preview, always override whether its a test request or not.
    if(is_preview){
        dynamic_doc_attrs.test = true;
    }

    dynamic_doc_attrs.document_content = '<html>' + jQuery('html').html() + '</html>';


    var server_communicator = new PmbAsyncPdfCreation(
        use_middleman,
        authorization_data,
        dynamic_doc_attrs,
        (response) => {
            console.log(response);
        },
        success_callback,
        failure_callback
    );
    server_communicator.begin();
}

/**
 * Generates the test document from the current page, downloads it, and enables downloading the paid PDF
 * @param jqelement
 */
function pmb_generate_test_doc(jqelement) {
    pmb_generate_doc_from_html(
        true,
        (download_url) => {

            pmb_stop_doing_button(jqelement);
            jQuery('.pmb-download-live').removeClass('pmb-pro-disabled');
            window.location.href = download_url;
        },
        (error_message) => {
            // jQuery('.pmb-downloading-live-pdf').hide();
            // jQuery('.pmb-error-downloading-test-pdf').show();
            if(error_message === 'Socket error downloading document content from supplied url.'){
                error_message = pmb_generate.translations.socket_error;
            }
            pmb_stop_doing_button(jqelement);
            alert(error_message);
        }
    );
}

/**
 * Generates the paid document from the current page and downloads it
 * @param jqelement
 */
function pmb_generate_live_doc(jqelement) {
    pmb_generate_doc_from_html(
        false,
        (download_url) => {
            pmb_stop_doing_button(jqelement);
            window.location.href = download_url;
            jQuery.ajax(
                ajaxurl,
                {
                    'method': 'POST',
                    'data':{
                        'action':'pmb_reduce_credits'
                    }
                }
            );
            jQuery('.pmb-pro-description').hide();
            jQuery('.pmb-pro-after-pro').show();
        },
        (error_message) => {
            // jQuery('.pmb-downloading-live-pdf').hide();
            // jQuery('.pmb-error-downloading-test-pdf').show();
            if(error_message === 'Socket error downloading document content from supplied url.'){
                error_message = pmb_generate.translations.socket_error;
            }
            pmb_stop_doing_button(jqelement);
            alert(error_message);
        }
    );
}

jQuery(document).ready(function(){
    jQuery('.pmb-download-test').click(function(event){
        var jqelement = jQuery(event.currentTarget);
        pmb_generate_test_doc(jqelement);
        pmb_doing_button(jqelement);
    });
    jQuery('.pmb-download-live').click(function(event){
        var jqelement = jQuery(event.currentTarget);
        if(! jqelement.hasClass('pmb-pro-disabled')) {
            pmb_generate_live_doc(jqelement);
            pmb_doing_button(jqelement);
        }
    });
})