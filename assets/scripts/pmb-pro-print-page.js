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

    var html = '<html>' + jQuery('html').html() + '</html>';
    html = html
        .replaceAll('<script', '<disabled-script')
        .replaceAll('</script>','</disabled-script>')
        .replaceAll('<prince-script', '<script')
        .replaceAll('</prince-script>','</script>');
    html = _.unescape(html);
    dynamic_doc_attrs.document_content = html;
    console.log(html);


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
            jQuery('.pmb-pro-description').html(pmb_pro.translations.pro_description);
            window.location.href = download_url;
        },
        (error_message) => {
            // jQuery('.pmb-downloading-live-pdf').hide();
            // jQuery('.pmb-error-downloading-test-pdf').show();
            if(error_message === 'Socket error downloading document content from supplied url.'){
                error_message = pmb_pro.translations.socket_error;
            }
            pmb_stop_doing_button(jqelement);
            jQuery.ajax(
                pmb_pro.ajaxurl,
                {
                    'method': 'POST',
                    'data':{
                        'action':'pmb_report_error',
                        'error': error_message,
                        'project_id': pmb_pro.project_id,
                        'format': pmb_pro.format
                    }
                }
            );
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
                pmb_pro.ajaxurl,
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
            jQuery.ajax(
                pmb_pro.ajaxurl,
                {
                    'method': 'POST',
                    'data':{
                        'action':'pmb_report_error',
                        'error': error_message,
                        'project_id': pmb_pro.project_id,
                        'format': pmb_pro.format
                    }
                }
            );
            alert(error_message);
        }
    );
}

jQuery(document).ready(function(){
    var input = document.getElementById("pmb-print-with-browser");
    input.addEventListener("keyup", function(event) {
        // Number 13 is the "Enter" key on the keyboard
        if (event.keyCode === 13) {
            // Cancel the default action, if needed
            event.preventDefault();
            // Trigger the button element with a click
            document.getElementById("pmb-print-with-browser").click();
        }
    });
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
    jQuery('.pmb-screen-only').remove();
})