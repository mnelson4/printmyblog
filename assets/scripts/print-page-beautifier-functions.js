
function pmb_dont_float(){
    jQuery('.alignright').removeClass('alignright');
    jQuery('.alignleft').removeClass('alignleft');
}

function pmb_add_header_classes(){
    jQuery('.pmb-posts h1').addClass('pmb-header');
    jQuery('.pmb-posts h2').addClass('pmb-header');
    jQuery('.pmb-posts h3').addClass('pmb-header');
    jQuery('.pmb-posts h4').addClass('pmb-header');
    jQuery('.pmb-posts h5').addClass('pmb-header');
}

function pmb_remove_hyperlinks(){
    jQuery('.pmb-posts a').contents().unwrap();
}

function pmb_fix_wp_videos(){
    // Remove inline styles that dynamically set height and width on WP Videos.
    // They use some Javascript that doesn't get enqueued, so better to let the browser decide their dimensions.
    jQuery('div.wp-video').css({'width': '','min-width':'', 'height': '', 'min-height': ''});
}

function pmb_convert_youtube_videos_to_images() {
    jQuery('div.wp-block-embed__wrapper iframe[src*=youtube]').unwrap().end();
    var selection = jQuery('iframe[src*=youtube]');
    selection.replaceWith(function(index){
        var title = this.title;
        var src = this.src;
        var youtube_id = src.replace('https://www.youtube.com/embed/','');
        youtube_id = youtube_id.substring(0, youtube_id.indexOf('?'));
        var image_url = 'https://img.youtube.com/vi/' + youtube_id + '/0.jpg';
        var link = 'https://youtube.com/watch?v=' + youtube_id;
        return '<div class="pmb-youtube-video-replacement-wrapper">' +
            '<div class="pmb-youtube-video-replacement-header"><div class="pmb-youtube-video-replacement-icon">🎦</div>' +
            '<div class="pmb-youtube-video-replacement-text"><b class="pmb-youtube-video-title">' + title + '</b><br/><a href="' + link +'" target="_blank">' + link + '</a>' +
            '</div>' +
            '</div>' +
            '<img class="pmb-youtube-video-replacement" src="' + image_url + '">' +
            '</div>';
    });

};

function pmb_resize_images(image_size) {
    // Images that take up the entire page width are usually too big, so we usually want to shrink images and center them.
    // Plus, we want to avoid page breaks inside them. But tiny emojis shouldn't be shrunk, nor do we need to worry about
    // page breaks inside them. Images that are part of a gallery, or are pretty small and inline, also shouldn't be shrunk.
    // So first let's determine how tall the user requested the tallest image could be. Anything bigger than that
    // needs to be wrapped in a div (or figure) and resized.
    var desired_max_height = image_size * 100; // 1 inch is about 100 pixels.
    var wp_block_galleries = jQuery('.pmb-posts .wp-block-gallery');
    if(image_size === 0){
        // Remove all images, except emojis.
        jQuery('.pmb-posts img:not(.emoji)').remove();
        wp_block_galleries.remove();
    } else{
        var big_images = jQuery('.pmb-posts img:not(.emoji, div.tiled-gallery img, img.fg-image, img.size-thumbnail)').filter(function(){
            // only wrap images bigger than the desired maximum height in pixels.
            var element = jQuery(this);
            return element.height() > desired_max_height;
        });
        // Images that are bigger than this will get wrapped in a 'pmb-image' div or figure in order to avoid
        // pagebreaks inside them
        var wrap_threshold = 300;
        // Keep track of images that are already wrapped in a caption. We don't need to wrap them in a div.
        var big_images_without_figures = jQuery('.pmb-posts img').filter(function() {
            var element = jQuery(this);
            // If there's no figure, and the image is big enough, include it.
            if(element.parent('figure').length === 0 && element.height() > wrap_threshold){
                return true;
            }
            return false;
        });
        var figures_containing_a_big_image = jQuery('figure.wp-caption, figure.wp-block-image').filter(function(){
            var element = jQuery(this);
            // If there's a figure and the figure is big enough, include it.
            if(element.find('img').length && element.height() > wrap_threshold){
                return true;
            }
            return false;
        });
        figures_containing_a_big_image.addClass('pmb-image');
        big_images_without_figures.wrap('<div class="pmb-image"></div>');
        // Center the images inside pmb-images
        // figures_containing_a_big_image.add(big_images_without_figures).each(function() {
        //     var obj = jQuery(this);
        //     obj.css({
        //         'width': 'auto',
        //         'height': 'auto',
        //         'display': 'block',
        //         'margin-left': 'auto',
        //         'margin-right': 'auto'
        //     });
        // });
        big_images.each(function () {
            var obj = jQuery(this);
            // Modify the CSS here. We could have written CSS rules but the selector worked slightly differently
            // in CSS compared to jQuery.
            // Let's make the image smaller and centered
            obj.css({
                'max-height': desired_max_height,
                'max-width:': '100%',
            });
        });
        wp_block_galleries.each(function(){
            var obj = jQuery(this);
            // Galleries can't be resized by height (they just cut off
            // content underneath the set height). Need to use width.
            obj.css({
                'max-width': (desired_max_height * 1.25),
                'margin-right':'auto',
                'margin-left':'auto'
            });
        })
    }
}

function pmb_load_avada_lazy_images(){
    // Load Avada's lazy images (they took out images' "src" attribute and put it into "data-orig-src". Put it back.)
    jQuery('img[data-orig-src]').each(function(index,element){
        var jqelement = jQuery(this);
        jqelement.attr('src',jqelement.attr('data-orig-src'));
        jqelement.attr('srcset',jqelement.attr('data-orig-src'));
    });
}
