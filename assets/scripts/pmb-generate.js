jQuery(document).ready(function () {
    jQuery('.pmb-generate').click(function (event) {
        event.preventDefault();
        var querystring_args = pmb_get_querystring_vars();
        var format_slug = event.currentTarget.getAttribute('data-format');
        var format_options_selection = jQuery('.pmb-generate-options-for-' + format_slug);
        jQuery.post(
            ajaxurl,
            {
                action: 'pmb_project_status',
                ID: querystring_args['ID'],
                format: format_slug,
            },
            (response) => {
                // could fetch more, but for now it all just happens in one request
                if (typeof (response) === 'object' && typeof (response.url) === 'string') {
                    // setup the buttons to send the HTML to DocRaptor or PmbCentral
                    var preview_button = format_options_selection.find('.pmb-download-preview');
                    preview_button.attr('data-html-url', response.url);
                    preview_button.addClass('button-primary');
                    format_options_selection.find('.pmb-download-live').attr('data-html-url', response.url);
                    format_options_selection.find('.pmb-after-generation').show();
                    format_options_selection.find('.pmb-generate').hide();
                    format_options_selection.find('.pmb-view-html').attr('href', response.url);
                    format_options_selection.find('.pmb-previous-generation-info').hide();
                }
            },
            'json').fail((event) => {
                alert(pmb_generate.translations.error_generating);
            }
        );
    });
    jQuery('.pmb-download-preview').click(function (event) {
        jQuery('.pmb-after-download-preview>div').hide();
        jQuery('.pmb-downloading-test-pdf').show();
        event.preventDefault();
        var format = event.currentTarget.getAttribute('data-format');
        pmb_open_modal(
            '.pmb-download-preview-dialog-' + format,
        );
        pmb_generate_doc(
            event.currentTarget,
            (download_url) => {
                jQuery('.pmb-downloading-test-pdf').hide();
                jQuery('.pmb-success-download-test-pdf').show();
                window.location.href = download_url;
            },
            (error_message) => {
                jQuery('.pmb-downloading-test-pdf').hide();
                jQuery('.pmb-error-downloading-test-pdf').show();
                if(error_message === 'Socket error downloading document content from supplied url.'){
                    error_message = pmb_generate.translations.socket_error;
                }
                alert(error_message);
            }
        );
    });

    jQuery('.pmb-download-live').click(function (event) {
        jQuery('.pmb-success-download-test-pdf').hide();
        jQuery('.pmb-downloading-live-pdf').show();
        event.preventDefault();

        pmb_generate_doc(
            event.currentTarget,
            (download_url) => {
                // ok! reduce credits then
                var previous_remaining_credits = parseInt(jQuery('.pmb-credits-remaining').text());
                jQuery('.pmb-credits-remaining').text(previous_remaining_credits - 1);
                jQuery.ajax(
                    ajaxurl,
                    {
                        'method': 'POST',
                        'data':{
                            'action':'pmb_reduce_credits'
                        }
                    }
                );
                jQuery('.pmb-downloading-live-pdf').hide();
                jQuery('.pmb-after-download-actual-success').show();
                window.location.href = download_url;
            },
            (error_message) => {
                jQuery('.pmb-downloading-live-pdf').hide();
                jQuery('.pmb-error-downloading-test-pdf').show();
                if(error_message === 'Socket error downloading document content from supplied url.'){
                    error_message = pmb_generate.translations.socket_error;
                }
                alert(error_message);
            }
        );
    });
});

function pmb_generate_doc(currentTarget, success_callback, failure_callback){
    var format = currentTarget.getAttribute('data-format');
    var html_url = currentTarget.getAttribute('data-html-url');
    var is_preview = currentTarget.getAttribute('data-preview');
    var use_middleman = is_preview && pmb_generate.use_pmb_central_for_previews;
    if(use_middleman){
        var authorization_data = pmb_generate.license_data;
    } else {
        var authorization_data = 'YOUR_API_KEY_HERE';
    }
    if (format === 'digital_pdf') {
        var media = 'screen';
    } else {
        var media = 'print';
    }

    var dynamic_doc_attrs = JSON.parse(JSON.stringify(pmb_generate.doc_attrs));
    // if this a preview, always override whether its a test request or not.
    if(is_preview){
        dynamic_doc_attrs.test = true;
    }
    dynamic_doc_attrs.document_url = html_url;
    dynamic_doc_attrs.prince_options.media = media;


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
 * from https://stackoverflow.com/questions/4656843/get-querystring-from-url-using-jquery
 * @returns {[]}
 */
function pmb_get_querystring_vars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}