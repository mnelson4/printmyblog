jQuery(document).ready(function(){
    // Pretty up the page
    pmb_add_header_classes();
    // pmb_remove_hyperlinks();
    pmb_fix_wp_videos();
    //pmb_resize_images(4);
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    pmb_replace_internal_links_with_page_refs_and_footnotes('leave', 'parens');
    new PmbToc();
    jQuery(document).trigger('pmb_wrap_up');
});

