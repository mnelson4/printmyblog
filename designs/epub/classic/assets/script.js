jQuery(document).ready(function(){
    pmb_prevent_lazy_loading();
});

jQuery(document).on('pmb_doc_conversion_requested', function(){
    pmb_standard_print_page_wrapup();
    pmb_default_align_center();
    pmb_replace_internal_links_with_epub_file_links();
    pmb_add_alt_tags();
    // pmb_resize_images(parseInt(pmb_design_options['image_size'],10));
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);

    // once done loading external images, tell ePub generator code that we're done.
    jQuery(document).on("pmb_external_resouces_loaded", function() {
        jQuery(document).trigger('pmb_doc_conversion_ready');
    });
    if(pmb_design_options.convert_videos === '1'){
        // if processing videos, wait until that's done to replace images.
        jQuery(document).on('pmb_done_processing_videos', function(){
            var erc = new PmbExternalResourceCacher();
            erc.replaceExternalImages();
        });
        pmb_convert_youtube_videos_to_images('simple');
    } else {
        var erc = new PmbExternalResourceCacher();
        erc.replaceExternalImages();
    }
});
