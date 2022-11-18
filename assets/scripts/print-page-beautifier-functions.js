/**
 * Removes content Prince XML and DocRaptor don't know how to handle properly, no "noscript" tags.
 */
function pmb_remove_unsupported_content(){
    // remove "noscripts" because we actually executed Javascript in the browser, then turn JS off for DocRaptor
    // (but Javascript was executed, so no need to do noscript tags)
    jQuery('noscript').remove();
    // Don't stack columns vertically
    jQuery('.wp-block-columns').addClass('is-not-stacked-on-mobile');
    // remove empty divs, as one of them at the start will cause an empty first page (happens when WooCommerce active)
    jQuery("body>div").filter(function() {
        return jQuery.trim(jQuery(this).html()) === "";
    }).remove();
    // prevent MathJax-LaTeX (https://wordpress.org/plugins/mathjax-latex/) from adding a page empty pages
    // and remove any totally empty divs
    setTimeout(function(){
        jQuery('body>*').each(function(){
            var element = jQuery(this);
            if(this.innerHTML === ''){
                element.remove();
            }
        });
    },
        2000 // just a guess that MathJax-LaTeX will be done by now
    );
}

/**
 * Fix common issues with folks using protocols that don't work (often these don't work on their website either,
 * or have warnings, but we'll cut them some slack and fix them here.)
 */
function pmb_fix_protocols(){
    // remove all the broken images and links etc
    jQuery('[src^="file:"]').remove();
    jQuery('[href^="file:"]').contents().unwrap();
    // DocRaptor handles "//" like "/" which means it thinks you're trying to access resources on its server
    // and has an error. So change those to the old way of specifying links.
    jQuery('[src^="//"]').each(function(index, element){
        element.setAttribute("src", location.protocol + element.getAttribute('src'));
    });
    jQuery('[href^="//"]').each(function(index, element){
        element.setAttribute("href", location.protocol + element.getAttribute('href'));
    });

    var correct_protocol = window.location.protocol;
    var incorrect_protocol = 'http:';
    if( correct_protocol === 'http:'){
        incorrect_protocol = 'https:';
    }
    jQuery('[src^="' + incorrect_protocol + '//' + window.location.host + '"]').each(function(index, element){
        element.setAttribute("src", element.getAttribute('src').replace(incorrect_protocol, correct_protocol));
    });
    jQuery('[href^="' + incorrect_protocol + '//' + window.location.host + '"]').each(function(index, element){
        element.setAttribute("href", element.getAttribute('href').replace(incorrect_protocol, correct_protocol));
    });
}

function pmb_dont_float(){
    jQuery('.alignright').removeClass('alignright');
    jQuery('.alignleft').removeClass('alignleft');
}

/**
 * Forces all the images with no alignment to instead be aligned in the center.
 */
function pmb_default_align_center(){
    // take care of classic images that had align none on them.
    jQuery('img.alignnone,figure.alignnone').removeClass('alignnone').addClass('aligncenter');
    // take care of Gutenberg images
    jQuery('figure:not(.alignleft,.alignright,.aligncenter,.alignwide,.alignfull)').addClass('aligncenter');
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

function pmb_resize_images(desired_max_height) {
    // Images that take up the entire page width are usually too big, so we usually want to shrink images and center them.
    // Plus, we want to avoid page breaks inside them. But tiny emojis shouldn't be shrunk, nor do we need to worry about
    // page breaks inside them. Images that are part of a gallery, or are pretty small and inline, also shouldn't be shrunk.
    // So first let's determine how tall the user requested the tallest image could be. Anything bigger than that
    // needs to be wrapped in a div (or figure) and resized.
    var wp_block_galleries = jQuery('.pmb-posts .wp-block-gallery:not(.pmb-dont-resize)');
    if(desired_max_height === 0){
        // Remove all images, except emojis.
        jQuery('.pmb-posts img:not(.emoji)').remove();
        wp_block_galleries.remove();
    } else{
        var big_images_in_figures = jQuery('.pmb-posts figure:not(.pmb-dont-resize) img:not(.emoji, div.tiled-gallery img, img.fg-image, img.size-thumbnail)').filter(function(){
            // only wrap images bigger than the desired maximum height in pixels.
            var element = jQuery(this);
            // ignore images in columns. If they get moved by prince-snap they can disappear
            if(element.parents('.wp-block-columns').length !== 0){
                return false;
            }
            return element.height() > desired_max_height;
        });
        // Images that are bigger than this will get wrapped in a 'pmb-image' div or figure in order to avoid
        // pagebreaks inside them
        var wrap_threshold = 300;
        // Keep track of images that are already wrapped in a caption. We don't need to wrap them in a div.
        var big_images_without_figures = jQuery('.pmb-posts img:not(.pmb-dont-resize)').filter(function() {
            var element = jQuery(this);
            // ignore images in columns. If they get moved by prince-snap they can disappear
            if(element.parents('.wp-block-columns').length !== 0){
                return false;
            }
            // If there's no figure, and the image is big enough, include it.
            if(element.parents('figure').length === 0
                && element.parents('div.wp-caption').length === 0
                && element.height() > wrap_threshold){
                return true;
            }
            return false;
        });
        var figures_containing_a_big_image = jQuery('figure.wp-caption:not(.pmb-dont-resize), figure.wp-block-image:not(.pmb-dont-resize), div.wp-caption:not(.pmb-dont-resize)').filter(function(){
            var element = jQuery(this);
            // ignore images in columns. If they get moved by prince-snap they can disappear
            // also don't resize images inside galleries. They just get messed up
            if(element.parents('.wp-block-columns, .wp-block-gallery').length !== 0){
                return false;
            }
            // If there's a figure and the figure is big enough, include it.
            if(element.find('img').length && element.height() > wrap_threshold){
                return true;
            }
            return false;
        });
        figures_containing_a_big_image.addClass('pmb-image');
        figures_containing_a_big_image.css({
            'width':'auto'
        });
        pmb_force_resize_image = function (index, element) {
            var obj = jQuery(element);
            // Modify the CSS here. We could have written CSS rules but the selector worked slightly differently
            // in CSS compared to jQuery.
            // Let's make the image smaller and centered
            obj.css({
                'max-height': desired_max_height,
                'max-width:': '100%',
                'width':'auto',
            });
        };
        big_images_without_figures.wrap('<div class="pmb-image"></div>');
        big_images_without_figures.each(pmb_force_resize_image);
        big_images_in_figures.each(pmb_force_resize_image);
        // wp_block_galleries.each(function(){
        //     var obj = jQuery(this);
        //     // Galleries can't be resized by height (they just cut off
        //     // content underneath the set height). Need to use width.
        //     obj.css({
        //         'max-width': (desired_max_height * 1.25),
        //         'margin-right':'auto',
        //         'margin-left':'auto'
        //     });
        // });
    }

    // test resizing full-size images to smaller equivalents. Eg
    // https://i0.wp.com/caravana2021.wpcomstaging.com/wp-content/uploads/2021/11/img_76282.jpeg
    // to
    // https://i0.wp.com/caravana2021.wpcomstaging.com/wp-content/uploads/2021/11/img_76282-1024x768.jpeg
}

/**
 * Gets content ready for Prince JS to do the dynamic resize.
 * For that, we mainly need to add the CSS class "pmb-dynamic-resize" onto blocks.
 * @param min_image_size
 */
function pmb_mark_for_dynamic_resize(min_image_size){
    // don't add the CSS class a second time, of course
    jQuery('img').each(function(index, element){
        // Don't try to resize trickier items like columns or YouTube videos
        var jqe = jQuery(element);
        if(jqe.parents('.wp-block-columns, .wp-block-embed-youtube, .wp-block-gallery, .gallery, .pmb-dont-dynamic-resize').length > 0){
            return;
        }
        if(element.offsetHeight > min_image_size){
            var block = jqe.parents('.wp-block-image');
            var element_to_add_css_class_to = null;
            if(block.length > 0){
                element_to_add_css_class_to = block;
            } else {
                var figure = jQuery(element).parents('figure');
                if(figure.length > 0){
                    element_to_add_css_class_to = figure;
                } else {
                    element_to_add_css_class_to = jqe;
                }
            }
            // double-check the element doesn't already have the CSS class
            if(! element_to_add_css_class_to.hasClass('pmb-dynamic-resize')){
                element_to_add_css_class_to.addClass('pmb-dynamic-resize');
            }
        }
        pmb_set_image_dimension_attributes(element);
    });

    // wrap the images again in order for flexbox layout to fill the space properly.
    jQuery('.pmb-dynamic-resize img').wrap('<div class="pmb-dynamic-resized-image-wrapper"></div>');
    // tell JetPack to not resize these images, as we may want a bigger size.
    jQuery('.pmb-dynamic-resize img.wp-image-1108[src*="?resize"]').each(function(i,element){var jqe = jQuery(element); jqe.prop('src',jqe.prop('src').substring(0, src.indexOf('?')))})
}

/**
 *
 * @param element
 * @param callback_when_done
 */
function pmb_set_image_dimension_attributes(element, callback_when_done){
    if (element.hasAttribute('height') && element.hasAttribute('width')) {
        if(typeof(callback_when_done) === 'function'){
            callback_when_done();
        }
        return;
    }
    // record the image's resolution as attributes on it
    var newImg = new Image();

    newImg.onload = function () {
        console.log('PMB loaded image ' + element.currentSrc + ' to determine dimensions');
        var height = newImg.height;
        var width = newImg.width;
        element.setAttribute('height', height);
        element.setAttribute('width', width);
        if(typeof(callback_when_done) === 'function'){
            callback_when_done();
        }
    }

    newImg.onerror = function(error, otherarg) {
        console.log('PMB error loading image ' + element.currentSrc + ' to determine its dimensions');
    }

    newImg.src = element.attributes['src'].value; // this must be done AFTER setting onload
}

/**
 * Change the image "src" attribute to use a different size of the image. Useful if you want to make the PDF really small
 * (save on filesize) or if you demand high quality images (eg in print).
 * @param string image_quality '1024x768', 'scaled' (for the resized original introduced in WP 5.3, see https://www.wpbeginner.com/wp-tutorials/how-to-display-full-size-images-in-wordpress-4-methods/)
 * 'uploaded' (meaning the actual original, fullsized file), '' (to not change at all), or some other string of form '?x?'
 * @param string domain eg "www.mysite.com", so we can identify external images which usually shouldn't be resized. An exception is wp.com JetPack images.
 */
function pmb_change_image_quality(image_quality, domain){
    // If it's '' (empty string), then treat it as the previous default behaviour
    if(image_quality === '' || ! image_quality){
        return;
    }
    // ok I admit this will be confusing. The forms system needed the null option to be '' (empty string), but
    // inside this function '' means the full, uploaded image size. So swap 'uploaded' for '' here.

    if(image_quality === 'uploaded'){
        image_quality = '';
    }
    jQuery('img[src*="' + domain + '"]:not(.pmb-dont-change-image-quality), img[src*=".wp.com"]:not(.pmb-dont-change-image-quality)').each(function(index, element){
        var jqe = jQuery(element);
        var src = jqe.prop('src');
        var index_of_last_slash = src.lastIndexOf('/');
        var filename = src.substring(index_of_last_slash + 1);
        var reg = /-(([^-]*)x([^-]*)|scaled)\./;
        filename = filename.replace(reg, '.');
        var index_of_last_period = filename.lastIndexOf('.');
        var extension = filename.substring(index_of_last_period + 1);
        if(image_quality !== ''){
            filename = filename.replace('.' + extension, '-' + image_quality + '.' + extension);
        }

        jqe.prop('src',src.substring(0,index_of_last_slash + 1) + filename);
    });
}

function pmb_load_avada_lazy_images(){
    // Load Avada's lazy images (they took out images' "src" attribute and put it into "data-orig-src". Put it back.)
    jQuery('img[data-orig-src]').each(function(index,element){
        var jqelement = jQuery(this);
        jqelement.attr('src',jqelement.attr('data-orig-src'));
        jqelement.attr('srcset',jqelement.attr('data-orig-src'));
    });
}

function pmb_reveal_dynamic_content(){
    // Expand all Arconix accordion parts (see https://wordpress.org/plugins/arconix-shortcodes/)
    jQuery('.arconix-accordion-content').css('display','block');

    // Reveal all https://wordpress.org/plugins/show-hidecollapse-expand/ content (the reveal buttons got removed in CSS)
    jQuery('div[id^="bg-showmore-hidden-"]').css('display','block');
    // Change canvases to regular images please! Helpful if someone is using chart.js or something else that
    // creates canvases
    setTimeout(function(){
        var canvases = jQuery('canvas').each(function(index){
            var chartImage = this.toDataURL();
            jQuery(this).after('<div class="pmb-image"><img src="' + chartImage + '"></div>');
            jQuery(this).remove();
        })
            // Expand all Kadence Accordion blocks and make them all look equally active
            jQuery('.kt-accordion-panel-hidden').removeClass('kt-accordion-panel-hidden');
            jQuery('.kt-accordion-panel-active').removeClass('kt-accordion-panel-active');

    },
    2000);
    //break images out of JetPack slideshows, which make no effort at print-readiness whatsoever

    jQuery('.wp-block-jetpack-slideshow').each(function(slideshow_index, slideshow_element){
        jQuery(this).find('figure').each(function(figure_index,figure_element){
            var figure = jQuery(figure_element);
            if(figure.parents('.swiper-slide-duplicate').length !== 0){
                return;
            }
            jQuery(slideshow_element).after(figure);
            figure.addClass('wp-block-image');
        });
        jQuery(this).remove();
    });
}

/**
 * Function used to loop over all the hyperlinks on the print page and call one of two callbacks on each of them.
 * The first callback is executed on links to content that are in this project, the second is used for everything
 * outside of this project. Each callback is passed the jQuery selection of the hyperlink and the URI to the element's ID
 * (if any exists), and the selector you can pass to jQuery to get the section element linked-to.
 * @param internal_hyperlink_callback
 * @param external_hyperlink_callback
 * @global string pmb_pro.site_url
 * @private
 */
function _pmb_for_each_hyperlink(internal_hyperlink_callback, external_hyperlink_callback){
    jQuery('.pmb-section a[href]:not(.pmb-leave-link)').each(function(index){
        var a = jQuery(this);
        // ignore invisible hyperlinks
        if(! a.text().trim()){
            return;
        }
        var href = a.attr('href');
        if(typeof(URL) === 'function'){
            href = new URL(href, pmb_pro.site_url).href;
        }
        var id_selector = '#' + href.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1').replace('%','-');
        var id_url = '#' + href;
        try{
            var matching_elements = jQuery(id_selector).length;
            // if that doesn't point to any internal posts, and it's an anchor link, then just use it as an anchor link
            if( matching_elements === 0 && href[0] === '#'){
                id_selector = id_url = href;
                matching_elements = jQuery(id_selector).length;
            }
        }catch(exception){
            // somehow the query didn't work. Remove this link then.
            a.contents().unwrap();
        }
        if( matching_elements > 0){
            internal_hyperlink_callback(a, id_url, id_selector)
        } else {
            // external
            external_hyperlink_callback(a, id_url, id_selector);
        }
    });
}

/**
 * Changes external stylesheets to inline ones so a generated file is more independent (with regards to CSS anyway)
 */
function pmb_inline_css(){
    // don't inline dashicons, as it uses relative links to images
    jQuery('link[rel="stylesheet"][id!="dashicons-css"]').each(function(index,element){
        var jqe = jQuery(element);
        var url = jqe.attr('href');
        if(url){
            jQuery.get(url).success(function(data,status){
                jqe.replaceWith('<style><!-- PMB inlined from ' + url + ' -->\r\n' + data + '</style>');
            });
        }

    })
}