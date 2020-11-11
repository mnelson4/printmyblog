jQuery(document).ready(function(){
    // Pretty up the page
    pmb_add_header_classes();
    if(pmb_design_options['default_alignment'] === 'center'){
        pmb_default_align_center();
    }
    // pmb_remove_hyperlinks();
    pmb_fix_wp_videos();
    pmb_resize_images(pmb_design_options['image_size']);
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    pmb_replace_internal_links_with_page_refs_and_footnotes(pmb_design_options['external_links'], pmb_design_options['internal_links']);
    new PmbToc();
    jQuery(document).trigger('pmb_wrap_up');
});

