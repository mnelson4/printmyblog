jQuery(document).ready(function(){
    // get the table of contents item
    var toc = jQuery('#pmb-toc-list');
    if(toc.length !== 0){
        // get all the H1s
        // jQuery('.pmb-posts .pmb-section').each(function(index){
        //     var section = jQuery(this);
        //     var section_id = section.attr('id');
        //     var title = jQuery(this).find('h1.entry-title').html();
        //     jQuery('#pmb-toc-list').append('<li><a href="#' + section_id + '">' + title + '</a></li>');
        // });

        // get all the depth-1 items
        // for each, find its pmb-title
        // add it to the list
        // then look for depth-2 items and add them as sub-items
    }

    // Pretty up the page
    pmb_dont_float();
    pmb_add_header_classes();
    // pmb_remove_hyperlinks();
    pmb_fix_wp_videos();
    pmb_resize_images(4);
    pmb_convert_youtube_videos_to_images();
    pmb_load_avada_lazy_images();
    pmb_replace_internal_links_with_page_refs_and_footnotes();
    pmb_create_toc_for_depth(jQuery('.pmb-print-page'), 1)
    jQuery(document).trigger('pmb_wrap_up');
});

/**
 * Search for PMB titles in the selector at the specified depth, and return them
 * @param jquery_obj
 * @param depth_to_look_for
 */
function pmb_find_articles_of_depth(selection, depth){
    return jQuery(selection ).find( ' .pmb-depth-' + depth);
}

function pmb_create_toc_for_depth(selection, depth){
    var articles = pmb_find_articles_of_depth(selection, depth);
    articles.each(function(index,element){
        // find its title
        var selection = jQuery(element);
        var title_element = jQuery(element).find('.pmb-title');
        var id = selection.attr('id');
        // if it's a PMB-core section, like title page or TOC, don't show it.
        if(id.indexOf('pmb-') !== -1){
            return;
        }
        var depth = parseInt(selection.attr('data-depth'));
        var height = parseInt(selection.attr('data-height'));
        var title_text = title_element.html();
        jQuery('#pmb-toc-list').append('<li class="pmb-toc-item pmb-toc-depth-' + depth + ' pmb-toc-height-' + height + '"><a href="#' + id + '">' + title_text + '</a></li>');

        // find its children
        pmb_create_toc_for_depth(selection.siblings('div'),depth + 1);
    });
}