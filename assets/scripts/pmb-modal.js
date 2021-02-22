function pmb_open_modal(content_selector, extra_args)
{
    var viewportWidth = jQuery(window).width();
    var viewportHeight = jQuery(window).height();
    var args = {
        'dialogClass'   : 'wp-dialog',
        'draggable'     : false,
        'modal'         : true,
        'autoOpen'      : true,
        'closeOnEscape' : true,
        'width'         : viewportWidth * .9,
        'height'        : viewportHeight * .9,
        'buttons'       : [
            {
                "text" : "Close",
                'class':'button',
                'click': function() {
                    jQuery(this).dialog('close');
                }
            },
        ],
        'open': function(event, ui)
        {
            var _this = jQuery(this);
            jQuery('.ui-widget-overlay').bind('click', function()
            {
                _this.dialog('close');
            });
        }
    };
    var combined_args = Object.assign({}, args, extra_args);
    jQuery(content_selector).dialog(combined_args);
}