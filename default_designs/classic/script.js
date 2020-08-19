jQuery(document).ready(function(){
    // get the table of contents item
    var toc = jQuery('#pmb-toc-list');
    if(toc.length !== 0){
        // get all the H1s
        jQuery('.pmb-posts .pmb-section').each(function(index){
            var section = jQuery(this);
            var section_id = section.attr('id');
            var title = jQuery(this).find('h1.entry-title').html();
            jQuery('#pmb-toc-list').append('<li><a href="#' + section_id + '">' + title + '</a></li>');
        });
    }

    // Pretty up the page
    pmb_dont_float();
    pmb_add_header_classes();
    // pmb_remove_hyperlinks();
    pmb_fix_wp_videos();
    pmb_resize_images(4);
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    pmb_replace_internal_links_with_page_refs();
    jQuery(document).trigger('pmb_wrap_up');
});