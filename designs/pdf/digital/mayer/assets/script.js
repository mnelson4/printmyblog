jQuery(document).ready(function(){
    // Pretty up the page
    pmb_dont_float();
    pmb_add_header_classes();
    // pmb_remove_hyperlinks();
    pmb_fix_wp_videos();
    //pmb_resize_images(4);
    jQuery('.pmb-posts img:not(.emoji, div.tiled-gallery img, img.fg-image, img.size-thumbnail)').filter(function(){
        // only wrap images bigger than the desired maximum height in pixels.
        var element = jQuery(this);
        return element.height() > desired_max_height;
    });
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    //pmb_replace_internal_links_with_page_refs_and_footnotes(pmb_classic_options['external_links'], pmb_classic_options['internal_links']);
    new PmbToc();
    jQuery(document).trigger('pmb_wrap_up');
});

