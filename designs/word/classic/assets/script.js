jQuery(document).ready(function(){
    // Pretty up the page
    pmb_standard_print_page_wrapup();
    pmb_default_align_center();

    pmb_replace_links_for_word(pmb_design_options['external_links'], pmb_design_options['internal_links']);
});

// wait until the images are loaded to try to resize them.
jQuery(window).on("load", function() {
    if(pmb_design_options.convert_videos === '1') {
        setTimeout(
            function () {
                pmb_convert_youtube_videos_to_images('simple');
            },
            2000
        );
        jQuery(document).on('pmb_done_processing_videos', function () {
            pmb_word_classic_after_videos();
        });
    } else {
        pmb_word_classic_after_videos();
    }

});

function pmb_word_classic_after_videos(){
    // pmb_resize_images(parseInt(pmb_design_options['image_size'],10));
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);
    jQuery(document).trigger('pmb_wrap_up');
}