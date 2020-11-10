// custom.js
jQuery(function($) {
    // var $info = $("#modal-content");
    // $info.dialog({
    //     'dialogClass'   : 'wp-dialog',
    //     'modal'         : true,
    //     'autoOpen'      : false,
    //     'closeOnEscape' : true,
    //     'buttons'       : {
    //         "Close": function() {
    //             $(this).dialog('close');
    //         }
    //     }
    // });
    $(".pmb-design-details-opener").click(function(event) {
        event.preventDefault();
        var design_slug = event.currentTarget.getAttribute('data-design-slug');
        jQuery('#pmb-design-details-' + design_slug).dialog({
            'dialogClass'   : 'wp-dialog',
            'modal'         : true,
            'autoOpen'      : true,
            'closeOnEscape' : true,
            'buttons'       : [
                {
                    "text": "Use This Design",
                    'class':'button button-primary',
                    'click': function () {
                        $('#pmb-design-form-' + design_slug).submit()
                    },
                },
                {
                    "text" : "Close Details",
                    'class':'button',
                    'click': function() {
                        $(this).dialog('close');
                    }
                },
            ],
            'width': "80%",
            open: function(event, ui)
            {
                var _this = $(this);
                $('.ui-widget-overlay').bind('click', function()
                {
                    _this.dialog('close');
                });
            }
        })
        // $info.dialog('open');
    });
});    