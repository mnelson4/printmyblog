jQuery(document).ready(function(){
    // Pretty up the pageit
    pmb_standard_print_page_wrapup();
    if(pmb_design_options['default_alignment'] === 'center'){
        pmb_default_align_center();
    }
    pmb_convert_youtube_videos_to_images();
    pmb_replace_internal_links_with_page_refs_and_footnotes(pmb_design_options['external_links'], pmb_design_options['internal_links'],pmb_design_options['footnote_text'],  pmb_design_options['internal_footnote_text']);
    new PmbToc();
});

// wait until the images are loaded to try to resize them.
jQuery(window).on("load", function() {
    pmb_resize_images(parseInt(pmb_design_options.image_size,10));
    if(pmb_design_options.image_placement === 'dynamic-resize'){
        pmb_mark_for_dynamic_resize(parseInt(pmb_design_options.dynamic_resize_min));
    }
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
    jQuery(document).trigger('pmb_wrap_up');
});