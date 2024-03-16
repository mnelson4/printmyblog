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
    jQuery('body>*:not(link)').each(function(){
        var element = jQuery(this);
        if(this.innerHTML === ''){
            element.remove();
        }
    });
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
    // There can be invalid URLs in CSS inline styles too.
    jQuery('style:contains(url("//), style:contains(url(\'//)').each(function(index,element){
        var new_html = element.innerHTML.replace('url("//','url("' + location.protocol + '//').replace("url('//","url('" + location.protocol + "//");
        element.innerHTML = new_html;
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

/**
 * Converts YouTube videos, Vimeo Videos, and self-hosted videos to screenshots and URLs.
 * @param string format either "pretty" or "simple". Pretty works best in PDFs, "simple" works better in ePub and Word
 * @param boolean add_qr_codes whether to add QR codes or not. Defaults to adding them.
 */
function pmb_convert_youtube_videos_to_images(format, add_qr_codes) {
    var video_converter = new PmbVideo(format, add_qr_codes);
    video_converter.convert();
};

/**
 * Converts videos into screenshots-with-links. Probably easier to just call the wrapper
 * pmb_convert_youtube_videos_to_images().
 * @param string format "pretty" or "simple". Pretty works best where CSS assets/styles/pmb-print-page-common-pdf.css is loaded.
 *  If we can't be sure that's loaded, "simple" is better.
 *  @param boolean|string add_qr_codes whether to add QR codes or not (accepts the string "1" as if it were `true`)
 */
function PmbVideo(format, add_qr_codes){
    this._format = format || 'pretty';
    this._add_qr_codes = add_qr_codes == true || add_qr_codes == '1';
    /**
     * Number of open HTTP requests to get video data.
     * @type {number}
     * @private
     */
    this._open_requests = 0;
    this._doneSearchForVideos = false;
    this._triggeredDoneProcessingVideos = false;
    /**
     * If there are no open HTTP requests for video data
     * and we're done searching for videos, triggers "pmb_done_processing_videos" on the document.
     * @returns null
     * @private
     */
    this._checkDoneProcessingVideos = function(){
        if(this._open_requests <= 0 && this._doneSearchForVideos && ! this._triggeredDoneProcessingVideos){
            console.log('trigger done processing videos');
            this._triggeredDoneProcessingVideos = true;
            this._doneProcessingVideos();
        }
    }

    /**
     * Increments the count of open HTTP requests.
     * @private
     */
    this._addOpenRequest = function(){
        this._open_requests++;
    }

    /**
     * Decrements the count of open HTTP requests
     * @private
     */
    this._closeRequest = function(){
        this._open_requests--;
    }

    /**
     * Main function that converts videos into screenshots etc.
     * Fires "pmb_done_processing_videos" on document when finished.
     */
    this.convert = function(){
        var that = this;
        console.log('PMB converting videos ');
        this._convertVimeoVideos();
        this._convertYoutubeVideos();
        this._convertOtherVideos();
        this._doneSearchForVideos = true;
        this._checkDoneProcessingVideos();
    }

    /**
     * If done processing videos, maybe add QR codes, then trigger "pmb_done_processing_videos"
     * @private
     */
    this._doneProcessingVideos = function(){
        if(this._add_qr_codes){
            this._addQrCodes();
            // give it a second to add QR codes
            var that = this;
            setTimeout(
                function(){
                    that._notifydoneProcessingVideos();
                },
                1000
            )
        } else {
            this._notifydoneProcessingVideos();
        }

    }

    /**
     * Just triggers "pmb_done_processing_videos" when videos are totally done.
     * @private
     */
    this._notifydoneProcessingVideos = function(){
        jQuery(document).trigger('pmb_done_processing_videos');
    }

    /**
     * After videos are converted, adds a QR code next to them.
     * Not sure how long this takes, but not terribly long as there's no HTTP request required.
     * @private
     */
    this._addQrCodes = function(){
        jQuery('.pmb-video-qrcode').each(function(){
             new QRCode(
                 this,
                 {
                     text:this.attributes['data-url'].value,
                     height:64,
                     width:64
                 });
        })
    }

    this._convertYoutubeVideos = function(){
        jQuery('div.wp-block-embed__wrapper iframe[src*=youtube]').unwrap();
        var selection = jQuery('iframe[src*=youtube], iframe[data-src*=youtube]');
        var that = this;
        selection.replaceWith(function(index){
            var title = this.title;
            var src = this.src || this.attributes['data-src'].value;
            var youtube_id = src.replace('https://www.youtube.com/embed/','');
            youtube_id = youtube_id.substring(0, youtube_id.indexOf('?'));
            var image_url = 'https://img.youtube.com/vi/' + youtube_id + '/0.jpg';
            var link = 'https://youtube.com/watch?v=' + youtube_id;
            return that._getHtml(title, link, image_url, );
        });
    };

    this._convertVimeoVideos = function(){
        jQuery('div.wp-block-embed__wrapper iframe[src*=vimeo]').unwrap();
        var vimeo_videos = jQuery('iframe[src*=vimeo], iframe[data-src*=vimeo]');
        var that = this;
        vimeo_videos.each(function(index){
            var iframe = this;
            var title = iframe.title;
            var src = iframe.src || iframe.attributes['data-src'].value;
            var vimeo_id = src.replace('https://player.vimeo.com/video/','');
            vimeo_id = vimeo_id.substring(0, vimeo_id.indexOf('?'));
            var vimeo_api_url = 'https://vimeo.com/api/v2/video/' + vimeo_id+ '.json';
            that._addOpenRequest();
            jQuery.ajax({
                url: vimeo_api_url,
                dataType: 'json',
                success: function(response){
                    if(typeof(response) === 'object' && typeof(response[0]) === 'object' && typeof(response[0].thumbnail_large)){
                        var image_url = response[0].thumbnail_large || response[0].thumbnail_medium || response[0].thumbnail_small;
                        var link = response[0].url;
                        var new_html = that._getHtml(title, link, image_url);
                        jQuery(iframe).replaceWith(new_html);
                    }
                },
                complete: function(){
                    setTimeout(function(){
                            that._closeRequest();
                            that._checkDoneProcessingVideos();
                        },
                        100
                    );

                }
            }
            );
        });
    };

    this._convertOtherVideos = function(){
        var that = this;
        var videos = jQuery('video');

        videos.replaceWith(function(index){
           var video_element = this;
            // Elementor puts the video src on "data-src" and later lazy-loads it.

            // look for the source URL on the "src" or "data-src" attributes, or on the sub-element "source"'s "src" attribute.
           var src = null;
           if(video_element.src){
               src = video_element.src;
           }else if ('data-src' in video_element.attributes && video_element.attributes['data-src'].value){
               src = video_element.attributes['data-src'].value;
           } else if (jQuery(video_element).children('source')){
               var source_selection = jQuery(video_element).children('source');
               src = source_selection.attr('src');
           }
           var screenshot = video_element.poster || '';
           return that._getHtml('', src, screenshot);
        });
    };

    /**
     * Probably best for PDFs where advanced CSS is OK.
     * @param video_title
     * @param video_url
     * @param video_screenshot_src
     * @returns {string}
     * @private
     */
    this._getScreenshotAndLinkHtml = function(video_title, video_url, video_screenshot_src){
        var html = '<div class="pmb-video-wrapper">' +
            '<div class="pmb-video-inner-wrapper">' +
                '<div class="pmb-video-gradient"></div>' +
                '<div class="pmb-video-overlay">' +
                    '<div class="pmb-pretend-play-button"><svg height="100%" version="1.1" viewBox="0 0 68 48" width="100%"><circle cx="34" cy="24" r="20" stroke="white" stroke-width="3" fill="black" /><path d="M 45,24 27,14 27,34" fill="white"></path></svg></div></div>' +
                    '<div class="pmb-video-qrcode" data-url="' + video_url+ '"></div>' +
                '<div class="pmb-video-text">';
        if(typeof(video_title) === 'string' && video_title.length > 0){
            html += '<b class="pmb-video-title">' + video_title + '</b><br/>';
        }
        html += '<a href="' + video_url +'" target="_blank">' + video_url + '</a>' +
                '</div>';
        if(typeof(video_screenshot_src) === 'string'  && video_screenshot_src.length > 0){
            html += '<img class="pmb-video-screenshot" src="' + video_screenshot_src + '">';
        } else {
            html += '<div class="pmb-video-screenshot-placeholder"></div>';
        }

            html += '</div>' +
        '</div>';
        return html;
    };

    /**
     * Gets HTML that's intended to look good even without any external CSS files. This is done by using inline CSS and keeping it basic.
     * @param video_title
     * @param video_url
     * @param video_screenshot_src
     * @returns {string}
     * @private
     */
    this._getSimpleHtml = function(video_title, video_url, video_screenshot_src){
        var html = '<div style="border:1px solid black;padding:15px;page-break-inside:avoid"><a href="' + video_url + '">';
        if(typeof(video_title) === 'string' && video_title.length > 0){
            html += '<b>' + video_title + '</b><br>';
        } else {
            html += '<b>' + video_url + '</b><br>';
        }
        if(! typeof(video_screenshot_src) === 'string' ||  video_screenshot_src.length == 0){
            video_screenshot_src = pmb.play_button_gif;
        }
        html += '<img src="' + video_screenshot_src + '" style="max-height:500px; max-width:100vw; display:block; margin-left:auto; margin-right:auto;">';
        html += '</a></div>';
        return html;
    };

    /**
     * Gets the HTML to use instead of the video.
     * @param video_title
     * @param video_url
     * @param video_screenshot_src
     * @returns {string}
     * @private
     */
    this._getHtml = function(video_title, video_url, video_screenshot_src){
        if(this._format === 'pretty'){
            return this._getScreenshotAndLinkHtml(video_title, video_url, video_screenshot_src);
        } else {
            return this._getSimpleHtml(video_title, video_url, video_screenshot_src);
        }
    };
}

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
        if(jqe.parents('.wp-block-columns, .wp-block-embed, .wp-block-gallery, .gallery, .pmb-dont-dynamic-resize').length > 0){
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
 * @param funciton callback_when_done
 * @param funciton callback_on_error
 */
function pmb_set_image_dimension_attributes(element, callback_when_done, callback_on_error){
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
        if(typeof(callback_on_error) === 'function'){
            callback_on_error();
        }
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
function pmb_change_image_quality(desired_image_quality, domain){
    // If it's '' (empty string), then treat it as the previous default behaviour, which was to leave the images alone
    if(desired_image_quality === '' || ! desired_image_quality || desired_image_quality === '150'){
        return;
    }
    jQuery('img[src*="' + domain + '"]:not(.pmb-dont-change-image-quality), img[src*=".wp.com"]:not(.pmb-dont-change-image-quality)').each(function(index, element){
        var src_to_use = null;

        // Don't change image quality if any parent divs say not to.
        if(jQuery(element).parents('.pmb-dont-change-image-quality').length){
            return;
        }

        // Before we start parsing the srcset attribute for the right size, make sure it's set.
        // If not, fallback to leaving the image alone
        if(! element.hasAttribute('srcset') || ! element.attributes['srcset'].value){
            return;
        }
        switch(desired_image_quality){
            case 'scaled':
                // Find the biggest size listed on "srcset" and use that
                var size_and_srcs = _pmb_parse_srcset(element.attributes['srcset'].value, false);
                src_to_use = size_and_srcs[0]['src'];
                break;
            default:
                // Find a thumbnail as big than the one requested (if we can't find it, use the next biggest that's available.)
                var size_and_srcs = _pmb_parse_srcset(element.attributes['srcset'].value);
                for(var i=0; i<size_and_srcs.length; i++){
                    if(parseInt(desired_image_quality, 10) > parseInt(size_and_srcs[i]['size'],10)){
                        size_and_srcs.shift();
                        i--;
                    }
                }
                if(size_and_srcs.length > 0){
                    src_to_use = size_and_srcs[0]['src'];
                    break;
                }
                // no thumbnail as big as requested, use the uploaded size
            case 'uploaded':
                // Use the "src" attribute to deduce the original filename.
                src_to_use = element.attributes['src'].value;

                var index_of_last_slash = src_to_use.lastIndexOf('/');
                var filename = src_to_use.substring(index_of_last_slash + 1);
                var reg = /-(([^-]*)x([^-]*)|scaled)\./;
                filename = filename.replace(reg, '.');
                src_to_use = src_to_use.substring(0,index_of_last_slash + 1) + filename
                break;
        }
        element.setAttribute('src', src_to_use);
        element.setAttribute('srcset-original', element.attributes['srcset'].value);
        element.removeAttribute('srcset');
    });
}

/**
 * Takes a srcset attribute's value (like "http://site.com/img1.jpg 4032w, http://site.com/img2.jpg 300w")
 * and turns into an array of objects, each with keys "size" and "src". The array is sorted with the smallest first
 * (unless you set `ascending` to false).
 * [
 *   {'size':'300', 'src': 'http://site.com/img2.jpg'}
 *   {'size':'4032', 'src': 'http://site.com/img1.jpg'},
 * ]
 * @param srcset
 * @param boolean ascending order
 * @returns [{}] in each object, there are keys "size" and "src", both strings.
 * @private
 */
function _pmb_parse_srcset(srcset, ascending = true){
    var srcs_and_sizes = srcset.split(', ');
    var size_and_srcs = [];
    srcs_and_sizes.forEach(function(item){
        var src_and_size = item.split(' ');
        var src=src_and_size[0];
        var size = src_and_size[1];
        size = size.replace('w','');
        size_and_srcs.push({'size':size, 'src':src});
    });
    size_and_srcs.sort(function(a,b,){return parseInt(a['size']) - parseInt(b['size'])})
    if(! ascending){
        size_and_srcs.reverse();
    }
    return size_and_srcs;
}

/**
 * Tries to disable as much lazy-loading as possible. This should generally be called on document ready.
 */
function pmb_prevent_lazy_loading(){
    // Load Avada's lazy images (they took out images' "src" attribute and put it into "data-orig-src". Put it back.)
    jQuery('img[data-orig-src]').each(function(index,element){
        var jqelement = jQuery(this);
        jqelement.attr('src',jqelement.attr('data-orig-src'));
        jqelement.attr('srcset',jqelement.attr('data-orig-src'));
    });
    // Load siteground lazy-loaded images.
    jQuery('img[data-src]').each(function(index,element){
        var jqelement = jQuery(this);
        jqelement.attr('src',jqelement.attr('data-src'));
        jqelement.attr('srcset',jqelement.attr('data-srcset'));
        jqelement.removeAttr('data-src');
        jqelement.removeAttr('data-srcset');
        jqelement.removeClass('lazyload');
    });
    // Load Elementor
}

/**
 * Actually, this does lazy loaded-images from other plugins too.
 * @deprecated use pmb_prevent_lazy_loading
 */
function pmb_load_avada_lazy_images(){
    pmb_prevent_lazy_loading();
}

function pmb_reveal_dynamic_content(){
    // Expand all Arconix accordion parts (see https://wordpress.org/plugins/arconix-shortcodes/)
    jQuery('.arconix-accordion-content').css('display','block');

    // Reveal all https://wordpress.org/plugins/show-hidecollapse-expand/ content (the reveal buttons got removed in CSS)
    jQuery('div[id^="bg-showmore-hidden-"]').css('display','block');
    // Change canvases to regular images please! Helpful if someone is using chart.js or something else that
    // creates canvases
    var canvases = jQuery('canvas').each(function(index){
        var chartImage = this.toDataURL();
        jQuery(this).after('<div class="pmb-image"><img src="' + chartImage + '"></div>');
        jQuery(this).remove();
    })
    // Expand all Kadence Accordion blocks and make them all look equally active
    jQuery('.kt-accordion-panel-hidden').removeClass('kt-accordion-panel-hidden');
    jQuery('.kt-accordion-panel-active').removeClass('kt-accordion-panel-active');
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
 * Given a jQuery selection containing an <a> element, returns its URL (resolves relative links etc)
 * @param jQuery jquery_a_selection
 * @private
 */
function _pmb_get_href_from_a(jquery_a_selection){
    var href = jquery_a_selection.attr('href');
    // if it's an anchor link, leave it alone.
    if(typeof(URL) === 'function' && href[0] !== '#'){
        try{
            href = new URL(href, pmb_pro.site_url).href;
        }catch(error){
            // leave it alone. It's an invalid URL, we can't fix that any more
        }
    }
    return href;
}
/**
 * Function used to loop over all the hyperlinks on the print page and call one of two callbacks on each of them.
 * The first callback is executed on links to content that are in this project, the second is used for everything
 * outside of this project. Each callback is passed the jQuery selection of the hyperlink and the URI to the element's ID
 * (if any exists), and the selector you can pass to jQuery to get the section element linked-to.
 * @param function internal_hyperlink_callback
 * @param function external_hyperlink_callback
 * @global string pmb_pro.site_url
 * @global string pmb_pro.domain
 * @private
 */
function _pmb_for_each_hyperlink(internal_hyperlink_callback, external_hyperlink_callback){
    jQuery('.pmb-section a[href]:not(.pmb-leave-link)').each(function(index){
        var a = jQuery(this);
        // ignore invisible hyperlinks
        if(! a.text().trim()){
            return;
        }
        var url = null;

        // Get the absolute URL.
        var absolute_url = _pmb_get_href_from_a(a);

        // Get the relative URL (to the current URL).
        var article_relative_slug = a.parents('article').attr('id');
        var relative_url = absolute_url
            .replace('https://www.', '')
            .replace('http://www.', '')
            .replace('https://', '')
            .replace('http://', '')
            .replace(pmb_pro.domain, '')
            .replace(article_relative_slug,'') // if it's link to its own post, remove all that (e.g. so we can detect anchor links easier)
            .replace('%', '-');
        // See if it's really an anchor URL.
        if(relative_url[0] === '#'){
            url = relative_url;
        } else {
            url = absolute_url;
        }

        // So, was it a anchor URL?
        if (url[0] === '#') {
            // If so, leave it alone, so long as it points to something.
            var selector = '#' + url.substring(1).replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g, '\\$1')
            try{
                var matching_elements = jQuery(selector).length;
                if(matching_elements){
                    internal_hyperlink_callback(a, url, selector);
                    return;
                }
            } catch(exception){
                // somehow the selector was invalid
            }
            // this anchor link doesn't actually point to anything. Get rid of it.
            a.contents().unwrap();
            return;
        }

        // It's NOT an anchor link. But it might be the URL of a real post, which we'll want to convert into an anchor link.
        var selector = '#' + _pmb_convert_url_into_selector(relative_url);
        var url = '#' + relative_url;

        try{
            var matching_elements = jQuery(selector).length;
            if(matching_elements > 0){
                internal_hyperlink_callback(a, url, selector);
                return;
            }
            // The URL doesn't correspond to any articles in this project.
            // Treat it as external.
            selector = _pmb_convert_url_into_selector(url);
            url = absolute_url;
            external_hyperlink_callback(a, url, selector);
            return;
        }catch(exception){
            // somehow the query didn't work. Remove this link then.
            a.contents().unwrap();
        }
    });
}

/**
 * Helpful for preparing a URL so it can be used in a jQuery selector.
 * @param string url
 * @returns {*}
 * @private
 */
function _pmb_convert_url_into_selector(url){
    return url.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g, '\\$1');
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
            jQuery.ajax(
                url,
                {
                    async: false
                }).success(function(data,status){
                jqe.replaceWith('<style><!-- PMB inlined from ' + url + ' -->\r\n' + data + '</style>');
            });
        }

    });
}