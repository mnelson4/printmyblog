jQuery(document).ready(function(){
    pmb_standard_print_page_wrapup();
    // Pretty up the page
    if(pmb_design_options['default_alignment'] === 'center'){
        pmb_default_align_center();
    }
    pmb_replace_internal_links_with_page_refs_and_footnotes('footnote', 'footnote', pmb_design_options['footnote_text'], pmb_design_options['internal_footnote_text']);
    new PmbToc();
    pmb_pdf_plugin_fixups();
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
        pmb_resize_images(400);
        pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
        jQuery(document).trigger('pmb_wrap_up');
    });
});

