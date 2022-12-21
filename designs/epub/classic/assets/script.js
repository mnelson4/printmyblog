var pmb_window_load_fired = false;
var pmb_done_document_ready_tasks = false;

jQuery(document).ready(function(){
    console.log('PMB says document ready ');
    // Pretty up the page
    pmb_standard_print_page_wrapup();
    pmb_default_align_center();
    pmb_replace_internal_links_with_epub_file_links();
    pmb_add_alt_tags();
    pmb_done_document_ready_tasks = true;
    pmb_epub_maybe_process_after_images_ready();
});

// wait until the images are loaded to try to resize them.
jQuery(window).on("load", function() {
    console.log('PMB says window loaded ');
    pmb_window_load_fired = true;
    pmb_epub_maybe_process_after_images_ready();
});

/**
 * We can't be certain is document ready or window load will be called first (see https://stackoverflow.com/questions/47405458/which-come-first-document-load-or-window-load)
 * so we need to make sure this is called after they've both ran (because on document ready we disable lazy-loading,
 * and on window load we know non-lazy-loaded images are all available, so we need to wait for both before processing
 * those images.)
 */
function pmb_epub_maybe_process_after_images_ready(){
    // only execute once window loaded (images loaded) and PMB's done all the ready tasks.
    // We can't be certain on what order those will be executed so this gets called twice.
    if(! pmb_window_load_fired || !pmb_done_document_ready_tasks){
        return;
    }
    // pmb_resize_images(parseInt(pmb_design_options['image_size'],10));
    pmb_change_image_quality(pmb_design_options.image_quality, pmb_design_options.domain);

    jQuery(document).on("pmb_external_resouces_loaded", function() {
        jQuery(document).trigger('pmb_wrap_up');
    });
    if(pmb_design_options.convert_videos === '1'){
        // wait a couple seconds for Elementor videos to convert from DIVs to iframes
        setTimeout(
            function(){
                // if processing videos, wait until that's done to replace images.
                jQuery(document).on('pmb_done_processing_videos', function(){
                    var erc = new PmbExternalResourceCacher();
                    erc.replaceExternalImages();
                });
                pmb_convert_youtube_videos_to_images('simple');
            },
            2000
        );

    } else {
        var erc = new PmbExternalResourceCacher();
        erc.replaceExternalImages();
    }
}