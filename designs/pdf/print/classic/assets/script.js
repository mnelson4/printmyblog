jQuery(document).ready(function(){
    // Pretty up the page
    pmb_dont_float();
    pmb_add_header_classes();
    pmb_fix_wp_videos();
    pmb_resize_images(pmb_classic_options['image_size']);
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    pmb_replace_internal_links_with_page_refs_and_footnotes(pmb_classic_options['external_links'], pmb_classic_options['internal_links']);
    new PmbToc();
    jQuery(document).trigger('pmb_wrap_up');
});

