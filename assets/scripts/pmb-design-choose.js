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
        pmb_open_modal(
            '#pmb-design-details-' + design_slug,
            {
                'buttons'       : [
                    {
                        "text": "Use This Design",
                        'class':'button button-primary',
                        'click': function () {
                            $('#pmb-design-form-' + design_slug).submit()
                        },
                    }
                ]
            }
        );
        // event.preventDefault();
        // var design_slug = event.currentTarget.getAttribute('data-design-slug');
        // var viewportWidth = $(window).width();
        // var viewportHeight = $(window).height();
        // jQuery('#pmb-design-details-' + design_slug).dialog({
        //     'dialogClass'   : 'wp-dialog',
        //     'modal'         : true,
        //     'autoOpen'      : true,
        //     'closeOnEscape' : true,
        //     'width':  viewportWidth * .9,
        //     'height'        : viewportHeight * .9,
        //     'buttons'       : [
        //         {
        //             "text": "Use This Design",
        //             'class':'button button-primary',
        //             'click': function () {
        //                 $('#pmb-design-form-' + design_slug).submit()
        //             },
        //         },
        //         {
        //             "text" : "Close Details",
        //             'class':'button',
        //             'click': function() {
        //                 $(this).dialog('close');
        //             }
        //         },
        //     ],
        //     open: function(event, ui)
        //     {
        //         var _this = $(this);
        //         $('.ui-widget-overlay').bind('click', function()
        //         {
        //             _this.dialog('close');
        //         });
        //     }
        // })
        // $info.dialog('open');
    });
});    