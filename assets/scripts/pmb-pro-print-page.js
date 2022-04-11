/**
 * Functions that does a bunch of standard wrap-up function calls done by pretty well all designs.
 */
function pmb_standard_print_page_wrapup(){
    pmb_remove_unsupported_content();
    pmb_add_header_classes();
    pmb_fix_wp_videos();
    pmb_load_avada_lazy_images();
    pmb_reveal_dynamic_content();
    pmb_check_project_size('#pmb-print-page-warnings');
}
/**
 * Checks if the project is really big, in which case suggests either reducing splitting it up or reducing image quality
 * @var string warning_element_selector jQuery selector indicating where to place the warning if there is one.
 */
function pmb_check_project_size(warning_element_selector){
    //check for really, really big printouts
    var many_articles = jQuery('article').length > 100;
    // don't warn about many images if they've already reduced their quality
    var many_images = jQuery('img').length > 1 &&
        (typeof pmb_design_options == 'object' &&
            typeof pmb_design_options.image_quality === 'string' &&
            ['', 'uploaded'].includes(pmb_design_options.image_quality)
        );

    var warning_text = false;
    switch(true){
        case many_articles:
            warning_text = pmb_pro.translations.many_articles;
            break;
        case many_images:
            warning_text = pmb_pro.translations.many_images;
            break;
    }
    if(warning_text){
        var warning_element = jQuery(warning_element_selector);
        warning_element.append(warning_text);
        warning_element.show();

    }
}