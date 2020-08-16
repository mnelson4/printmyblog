jQuery(document).ready(function(){
    // get the table of contents item
    var toc = jQuery('#pmb-toc-list');
    if(toc.length !== 0){
        // get all the H1s
        var h1s = jQuery('.pmb-posts h1').each(function(index){
            jQuery('#pmb-toc-list').append('<li><a href="' + this.id + '">' + this.innerText + '</a></li>');
        })
    }


});