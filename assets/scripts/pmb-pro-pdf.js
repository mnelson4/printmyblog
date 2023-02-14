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
    var jq_html = jQuery('html');
    var html = '<html class="' + jq_html[0].className + '">' + jq_html.html() + '</html>';

    // don't send script tags, as we don't want Prince to execute Javascript we've already executed in the browser
    var open_tag = '<script';
    var close_tag = '</script>';
    var open_tag_pos = html.indexOf(open_tag);
    var close_tag_pos = html.indexOf(close_tag);

    while(open_tag_pos !== -1){
        var pre_script = html.substring(0,open_tag_pos);
        var post_script = html.substring(close_tag_pos + close_tag.length);
        html =  pre_script + post_script;
        open_tag_pos = html.indexOf(open_tag);
        close_tag_pos = html.indexOf(close_tag);
    }

    // unleash the Javascript for Prince!!
    html = html
        .replaceAll('<prince-script', '<script')
        .replaceAll('</prince-script>','</script>');

    // jQuery escaped the contents of the Prince script when we fetched it, so un-escaped it using underscore.js
    var open_tag_pos = html.indexOf('<script');
    var close_tag_pos = html.indexOf(close_tag);
    var pre_script = html.substring(0,open_tag_pos);
    var script_contents = html.substring(open_tag_pos + open_tag.length + 1, close_tag_pos);
    script_contents = _.unescape(script_contents);
    var post_script = html.substring(close_tag_pos + close_tag.length);
    html =  pre_script + open_tag + '>' + script_contents + close_tag + post_script;

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
            jQuery('.pmb-download-live').removeClass('pmb-disabled');
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
                        'action':'pmb_reduce_credits',
                        '_wpnonce':pmb_pro.pmb_nonce,
                    }
                }
            );
            jQuery('.pmb-pro-description').hide();
            jQuery('.pmb-pro-after-pro').show();
        },
        (error_message) => {
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
                        'format': pmb_pro.format,
                        '_wpnonce':pmb_pro.pmb_nonce,
                    }
                }
            );
            alert(error_message);
        }
    );
}

/**
 * Callbacks that listen for document.pmb_doc_conversion_requested should set pmb_doc_conversion_request_handled to TRUE immediately, otherwise
 * we'll assume no callback was set and so we'll just proceed with converting the file.
 * @type {boolean}
 */
var pmb_doc_conversion_request_handled = false;
/**
 * Keeps track of if we've finished preparing the entire print page. (So we don't process stuff over and over again if the print button
 * gets pressed again.)
 * @type boolean
 */
var pmb_pro_page_rendered = false;
jQuery(document).on('ready', function(){
    var download_test_button = jQuery('.pmb-download-test');
    setTimeout(function(){
            pmb_stop_doing_button(download_test_button);
        },
        2000
    );
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
    download_test_button.click(function(event){
        var jqelement = jQuery(event.currentTarget);
        pmb_doing_button(jqelement);
        if(pmb_pro_page_rendered){
            pmb_generate_test_doc(jqelement);
        } else {
            // wait for the design to call document.pmb_doc_conversion_ready (and to set pmb_doc_conversion_request_handled
            // to true)  before proceeding with converting HTML to ePub
            jQuery(document).on('pmb_doc_conversion_ready', function(){
                pmb_generate_test_doc(jqelement);
            });
            jQuery(document).trigger('pmb_doc_conversion_requested');
            // trigger document.pmb_wrap_up for legacy code.
            jQuery(document).trigger('pmb_wrap_up');
            pmb_pro_page_rendered = true;
        }
    });
    jQuery('.pmb-download-live').click(function(event){
        var jqelement = jQuery(event.currentTarget);
        pmb_generate_live_doc(jqelement);
        pmb_doing_button(jqelement);
    });
    jQuery('#pmb-print-with-browser').click(function(event){
        if(pmb_pro_page_rendered){
            window.print();
        } else {
            var jqelement = jQuery(event.currentTarget);
            pmb_doing_button(jqelement);
            // wait for the design to call document.pmb_doc_conversion_ready (and to set pmb_doc_conversion_request_handled
            // to true)  before proceeding with converting HTML to ePub
            jQuery(document).on('pmb_doc_conversion_ready', function(){
                window.print();
                setTimeout(
                    function(){
                        pmb_stop_doing_button(jqelement);
                    },
                    2000
                );
            });
            jQuery(document).trigger('pmb_doc_conversion_requested');
            // trigger document.pmb_wrap_up for legacy code.
            jQuery(document).trigger('pmb_wrap_up');
            pmb_pro_page_rendered = true;
        }

    });
});