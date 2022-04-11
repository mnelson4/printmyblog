jQuery(document).ready(function(){
    // Pretty up the page
    pmb_standard_print_page_wrapup();
    pmb_default_align_center();
    if(pmb_design_options.convert_videos === '1'){
        pmb_convert_youtube_videos_to_images();
    }
    pmb_replace_internal_links_with_epub_file_links();
});

// wait until the images are loaded to try to resize them.
jQuery(window).on("load", function() {
    // pmb_resize_images(parseInt(pmb_design_options['image_size'],10));
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
    jQuery(document).trigger('pmb_wrap_up');
});