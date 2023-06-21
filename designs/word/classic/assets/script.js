// once doc conversion requested, process the HTML and trigger when we're ready.
jQuery(document).on('pmb_doc_conversion_requested', function(){
    // inform pmb-word__prmemium_only.js that we've handled this
    pmb_doc_conversion_request_handled = true;
    pmb_standard_print_page_wrapup();
    pmb_default_align_center();

    pmb_replace_links_for_word(pmb_design_options['external_links'], pmb_design_options['internal_links']);
    pmb_fix_headings_for_word_toc();
    // once done loading external images, tell ePub generator code that we're done.
    jQuery(document).on("pmb_external_resouces_loaded", function() {
        jQuery(document).trigger('pmb_doc_conversion_ready');
    });
    if(pmb_design_options.convert_videos === '1') {
        jQuery(document).on('pmb_done_processing_videos', function () {
            pmb_word_classic_after_videos();
            var erc = new PmbExternalResourceCacher();
            erc.replaceExternalImages();
        });
        pmb_convert_youtube_videos_to_images('simple');
    } else {
        pmb_word_classic_after_videos();
        var erc = new PmbExternalResourceCacher();
        erc.replaceExternalImages();
    }
});

function pmb_word_classic_after_videos(){
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
}