/**
 * Removes content Prince XML and DocRaptor don't know how to handle properly, no "noscript" tags.
 */
function pmb_remove_unsupported_content(){
    // remove "noscripts" because we actually executed Javascript in the browser, then turn JS off for DocRaptor
    // (but Javascript was executed, so no need to do noscript tags)
    jQuery('noscript').remove();
    // remove all the broken images and links etc
    jQuery('[src^="file:"]').remove();
    jQuery('[href^="file:"]').contents().unwrap();
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
            if(element.parents('.wp-block-columns').length !== 0){
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
    },
    2000);
}

/**
 * Adds the class "pmb-page-ref" onto all hyperlinks to posts/things that are actually in the current project,
 * and a span named "pmb-footnote", with the value of the hyperlink to all the links to external content.
 * @param external_link_policy string can be 'footnote', 'leave', 'remove'
 * @param internal_link_policy string can be 'parens' "(see page 12)", 'footnote' "[1]...See page 12", 'leave'
 * (leaves hyperlink to the page), 'remove' (removes the hyperlink altogether)
 * @param internal_footnote_text string
 * @param external_footnote_text string
 */
function pmb_replace_internal_links_with_page_refs_and_footnotes(external_link_policy, internal_link_policy, external_footnote_text, internal_footnote_text)
{
    if(typeof(internal_footnote_text) === 'undefined' || internal_footnote_text === null){
        internal_footnote_text = 'See page %s';
    }
    var position_of_s = internal_footnote_text.search('%s');
    var pre_internal_footnote = internal_footnote_text.substr(0,position_of_s );
    var post_internal_footnote = internal_footnote_text.substr(position_of_s + 2);

    if(typeof(external_footnote_text) === 'undefined' || internal_footnote_text === null){
        external_footnote_text = 'See %s';
    }
    var position_of_s = external_footnote_text.search('%s');
    var pre_external_footnote = external_footnote_text.substr(0,position_of_s );
    var post_external_footnote = external_footnote_text.substr(position_of_s + 2);


    jQuery('.pmb-section a[href]:not(.pmb-leave-link)').each(function(index){
        var a = jQuery(this);
        // ignore invisible hyperlinks
        if(! a.text().trim()){
            return;
        }
        var id_selector = '#' + a.attr('href').replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1').replace('%','-');
        var id_url = '#' + a.attr('href');
        try{
            var matching_elements = jQuery(id_selector).length;
            // if that doesn't point to any internal posts, and it's an anchor link, then just use it as an anchor link
            if( matching_elements === 0 && a.attr('href')[0] === '#'){
                id_selector = id_url = a.attr('href');
                matching_elements = jQuery(id_selector).length;
            }
        }catch(exception){
            // somehow the query didn't work. Remove this link then.
            a.contents().unwrap();
        }
        if( matching_elements > 0){
            // internal
            switch(internal_link_policy){
                case 'parens':
                    a.addClass('pmb-page-ref');
                    a.attr('href',id_url);
                    break;
                case 'footnote':
                    // only add the footnote if the link isn't just the URL spelled out.
                    if(a.attr('href') !== a.html().trim()) {
                        a.attr('href',id_url);
                        a.after('<span class="pmb-footnote">' + pre_internal_footnote  + '<a class="pmb-page-num" href="' + a.attr('href') + '"></a>' + post_internal_footnote + '</span>');
                    }
                    break;
                case 'leave':
                    a.attr('href',id_url);
                    break;
                case 'remove':
                    a.contents().unwrap();
                    break;
                    // otherwise, leave alone
            }
        } else {
            // external
            switch(external_link_policy){
                case 'footnote':
                    // only add the footnote if the link isn't just the URL spelled out.
                    var link_text = a.html().trim();
                    var href = a.attr('href');
                    var matches = [href, href.replace('https://',''), href.replace('http://',''), href.replace('//',''), href.replace('mailto:','')];
                    if(matches.indexOf(link_text) === -1){
                        a.after('<span class="pmb-footnote">' + pre_external_footnote  + a.attr('href') + post_external_footnote + '</span>');
                    }
                break;
                case 'remove':
                a.contents().unwrap();
                break;
                // otherwise, leave alone
            }
        }
    });
}

/**
 * Creates a table of contents from the content generated by the shortcode pmb_toc
 * @constructor
 */
function PmbToc(){
    /**
     * Search for PMB titles in the selector at the specified depth, and return them
     * @param jquery_obj
     * @param depth_to_look_for
     */
    this.find_articles_of_depth = function(selection, depth){
        return jQuery(selection ).find( ' .pmb-depth-' + depth);
    }

    this.create_toc_for_depth = function(selection, depth){
        var articles = this.find_articles_of_depth(selection, depth);
        var _this = this;
        articles.each(function(index,element){
            // find its title
            var selection = jQuery(element);
            var title_element = jQuery(element).find('.pmb-title');
            var id = selection.attr('id');
            // if it's a PMB-core section, like title page or TOC, don't show it.
            if(id.indexOf('/pmb-') !== -1){
                return;
            }
            var depth = parseInt(selection.attr('data-depth'));
            var height = parseInt(selection.attr('data-height'));
            if(selection.hasClass('pmb-front_matter_article')){
                var matter_class = 'pmb-toc-front';
            } else if(selection.hasClass('pmb-back_matter_article')){
                var matter_class = 'pmb-toc-back';
            } else {
                var matter_class = 'pmb-toc-main';
            }
            var title_text = title_element.html();
            if(title_text){
                jQuery('#pmb-toc-list').append('<li class="pmb-toc-item pmb-toc-depth-' + depth + ' pmb-toc-height-' + height + ' '  + matter_class + '"><a href="#' + id + '">' + title_text + '</a></li>');
            }
            // find its children
            _this.create_toc_for_depth(selection.siblings('div'),depth + 1);
        });
    }
    this.create_toc_for_depth('.pmb-print-page', 0);
}