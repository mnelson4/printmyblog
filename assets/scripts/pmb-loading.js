jQuery(document).ready(function () {
    var querystring_args = pmb_get_querystring_vars();
    var data = pmb_loading.generate_ajax_data;
    jQuery.ajax({
        url:pmb_loading.pmb_ajax,
        method:'POST',
        data:data,
        dataType:'json',
        success:(response) => {
            // could fetch more, but for now it all just happens in one request
            if (typeof (response) === 'object' && typeof (response.url) === 'string') {
                window.location.replace(response.url);
            }
        },
        converters: pmb_jquery_ajax_converters,
    }).fail((jqXhr, text_message, text_status) => {
            // the server dhould have already recorded the error
            alert(pmb_loading.translations.error_generating);
        }
    );
});

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