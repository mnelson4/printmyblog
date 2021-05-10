jQuery(document).ready(function(){
    jQuery('.pmb_spin_on_click').on('click', function() {
        pmb_doing_button(jQuery(this));
    });
    jQuery('.pmb-hover[data-help]').hover(function() {
        var that = jQuery(this),
            help = '<p>' + that.attr('data-help') + '</p>',
            options = {
                content: help,
                position: {
                    edge: isRtl ? 'right' : 'left',
                    align: 'center',
                    of: that
                },
                document: {body: that}
            };

        var pointer = that.pointer(options).pointer('open');
        that.closest('tr, p').mouseleave(function () {
            pointer.pointer('close');
        });
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
