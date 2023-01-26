// once doc conversion requested, process the HTML and trigger when we're ready.
jQuery(document).on('pmb_doc_conversion_requested', function(){
    pmb_doc_conversion_request_handled = true;
    pmb_standard_print_page_wrapup();
    // Pretty up the page
    if(pmb_design_options['default_alignment'] === 'center'){
        pmb_default_align_center();
    }
    pmb_replace_internal_links_with_page_refs_and_footnotes('footnote', 'footnote', pmb_design_options['footnote_text'], pmb_design_options['internal_footnote_text']);
    new PmbToc();
    pmb_pdf_plugin_fixups();
    jQuery(document).on('pmb_done_processing_videos', function() {
        pmb_resize_images(400);
        pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
        jQuery(document).trigger('pmb_doc_conversion_ready');
    });
    pmb_convert_youtube_videos_to_images('pretty', pmb_design_options.video_qr_codes);
});

