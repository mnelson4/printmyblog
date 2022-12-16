jQuery(document).ready(function(){
    // Pretty up the page
    pmb_standard_print_page_wrapup();
    // There's not enough space for alignnone images to ever look good.
    pmb_default_align_center();
    pmb_replace_internal_links_with_page_refs_and_footnotes('leave', 'parens');
    new PmbToc();
});


// wait until the images are loaded to try to resize them.
jQuery(window).on("load", function() {
    setTimeout(
        function(){
            pmb_convert_youtube_videos_to_images('pretty', pmb_design_options.video_qr_codes);
        },
        2000
    );
    jQuery(document).on('pmb_done_processing_videos', function() {
        pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
        jQuery(document).trigger('pmb_wrap_up');
    });
});