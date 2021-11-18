jQuery(document).ready(function(){
    jQuery('.pmb_spin_on_click').on('click', function() {
        pmb_doing_button(jQuery(this));
    });
    pmb_setup_hover_helps();
});

function pmb_setup_hover_helps(){
    jQuery('.pmb-hover').hover(function() {
        var that = jQuery(this);
        if(typeof(that.attr('data-help')) !== 'undefined'){
            var help = '<p>' + that.attr('data-help') + '</p>';
        } else {
            var help = '<p>' + that.children('.pmb-hover-help').html() + '</p>';
        }
        if(typeof(that.attr('data-hover-edge')) !== 'undefined'){
            var edge = that.attr('data-hover-edge');
        } else {
            var edge = 'left';
        }
        var options = {
            content: help,
            position: {
                edge: edge,
                align: 'center',
                of: that
            },
            document: {body: that}
        };

        var pointer = that.pointer(options).pointer('open');
        that.closest('.pmb-hover').mouseleave(function () {
            pointer.pointer('close');
        });
    });
}

/**
 *
 * @param jqelement
 */
function pmb_doing_button(jqelement){
    var current_html = jqelement.html();
    jqelement.append('<div class="pmb-spinner-container"><div class="pmb-spinner"></div></div>')
    jqelement.addClass('pmb-pro-disabled');
}

function pmb_stop_doing_button(jqelement){
    jqelement.children('.pmb-spinner-container').remove();
    jqelement.removeClass('pmb-pro-disabled');
}

// converters for passing to jQuery ajax requests.
var pmb_jquery_ajax_converters = {
    'text json': function(result) {
        let new_result = result;
        // Sometimes other plugins echo out junk before the start of the real JSON response.
        // So we need to chop off all that extra stuff.
        do{
            // Find the first spot that could be the beginning of valid JSON...
            var start_of_json = Math.min(
                new_result.indexOf('{'),
                new_result.indexOf('['),
                new_result.indexOf('true'),
                new_result.indexOf('false'),
                new_result.indexOf('"')
            );
            // Remove everything before it...
            new_result = new_result.substring(start_of_json);
            try{
                // Try to parse it...
                let i = jQuery.parseJSON(new_result);
                // If that didn't have an error, great. We found valid JSON!
                return i;
            }catch(error){
                // There was an error parsing that substring. So let's chop off some more and keep hunting for valid JSON.
                // Chop off the character that made this look like it could be valid JSON, and then continue iterating...
                new_result = new_result.substring(1);
            }
        }while(start_of_json !== false && new_result.length);
        // Never found any valid JSON. Throw the error.
        throw "No JSON found in AJAX response using custom JSON parser.";
    }
};