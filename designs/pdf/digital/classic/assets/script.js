// once doc conversion requested, process the HTML and trigger when we're ready.
jQuery(document).on('pmb_doc_conversion_requested', function(){
    pmb_doc_conversion_request_handled = true;
    // Pretty up the page
    pmb_standard_print_page_wrapup();
    if(pmb_design_options['default_alignment'] === 'center'){
        pmb_default_align_center();
    }
    pmb_replace_internal_links_with_page_refs_and_footnotes(pmb_design_options['external_links'], pmb_design_options['internal_links'], pmb_design_options['footnote_text'], pmb_design_options['internal_footnote_text']);
    new PmbToc();
    pmb_pdf_plugin_fixups();
    pmb_convert_youtube_videos_to_images('pretty');
    jQuery(document).on('pmb_done_processing_videos', function() {
        pmb_resize_images(parseInt(pmb_design_options['image_size'],10));
        if(pmb_design_options.image_placement === 'dynamic-resize'){
            pmb_mark_for_dynamic_resize(parseInt(pmb_design_options.dynamic_resize_min));
        }
        pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
        jQuery(document).trigger('pmb_doc_conversion_ready');
    });
});