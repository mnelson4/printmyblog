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
