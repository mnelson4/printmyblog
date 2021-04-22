jQuery(document).ready(function(){
    jQuery('.pmb_spin_on_click').on('click', function() {
        pmb_doing_button(jQuery(this));
    });
});

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