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


});