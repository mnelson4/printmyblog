// begin loading posts

// and append them to the page
function PmbPrintPage(pmb_instance_vars, translations) {
    this.header_selector = pmb_instance_vars.header_selector;
    this.header = null;
    this.status_span_selector = pmb_instance_vars.status_span_selector;
    this.status_span = null;
    this.posts_count_span_selector = pmb_instance_vars.posts_count_span_selector;
    this.posts_count_span = null;
    this.posts_div_selector = pmb_instance_vars.posts_div_selector;
    this.posts_div = null;
    this.waiting_area_selector = pmb_instance_vars.waiting_area_selector;
    this.waiting_area = null;
    this.print_ready_selector = pmb_instance_vars.print_ready_selector;
    this.print_ready = null;
    this.proxy_for = pmb_instance_vars.proxy_for;
    this.locale = pmb_instance_vars.locale;
    this.image_size = pmb_instance_vars.image_size;
    this.translations = translations;
    this.include_excerpts = pmb_instance_vars.include_excerpts;
    this.columns = pmb_instance_vars.columns;
    this.post_type = pmb_instance_vars.post_type;
    this.total_posts = 0;
    this.posts = [];
    this.taxonomies = {};
    this.original_posts = [];
    this.ordered_posts = [];
    this.rendering_wait = pmb_instance_vars.rendering_wait;
    this.include_inline_js = pmb_instance_vars.include_inline_js;
    /**
     * @function
     */
    this.initialize = function () {
        this.header = jQuery(this.header_selector);
        this.status_span = jQuery(this.status_span_selector);
        this.posts_count_span = jQuery(this.posts_count_span_selector);
        this.posts_div = jQuery(this.posts_div_selector);
        this.waiting_area = jQuery(this.waiting_area_selector);
        this.print_ready = jQuery(this.print_ready_selector);

        var alltaxonomiesCollection = new wp.api.collections.Taxonomies();
        alltaxonomiesCollection.fetch().done((taxonomies) => {
            this.taxonomies = taxonomies;
        });
    };

    this.getCollection = function() {
        if(this.post_type === 'post') {
            return new wp.api.collections.Posts();
        } else if(this.post_type === 'page') {
            return new wp.api.collections.Pages();
        } else {
            throw 'Invalid post type.';
        }
    };

    this.getCollectionQueryData = function () {
        var data = {
            status: 'publish',
            _embed:true,
            proxy_for: this.proxy_for,
        };
        if(this.post_type === 'post') {
            data.orderby = 'date';
            data.order = 'asc';
        }
        return data;
    };

    this.beginLoading = function () {
        let collection = this.getCollection();
        var data = this.getCollectionQueryData();
        collection.fetch({data: data,
        }).done((posts) => {
            this.storePostsAndMaybeFetchMore(posts, collection);
        });
    };

    this.storePostsAndMaybeFetchMore = function(posts, collection) {
        if(typeof posts === 'object' && 'errors' in posts) {
            var first_error_key = Object.keys(posts.errors)[0];
            var first_error_message = posts.errors[first_error_key];
            this.status_span.html( 'There was an error fetching posts. It was: ' + first_error_message + ' (' + first_error_key + ')');
            return;
        }
        this.posts = this.posts.concat(posts);
        // for(var post_index in posts) {
        //     this.posts_div.append(posts[post_index].content.rendered);
        // }
        this.total_posts = collection.state.totalObjects;
        var posts_so_far = this.posts.length;
        this.posts_count_span.html(posts_so_far + '/' + this.total_posts);
        if (collection.hasMore()) {
            collection.more().done((posts) => {
                this.storePostsAndMaybeFetchMore(posts, collection);
            });
        } else {
            this.wrapUp();
        }
    };

    this.wrapUp = function() {
        var posts_to_render = this.posts;
        if(this.post_type === 'page') {
            this.original_posts = this.posts.slice();
            // Sort according to order (don't worry about hierarchy yet).
            this.posts = this.posts.sort(
                (a, b) => {
                    var menu_comparison = a.menu_order - b.menu_order;
                    if( menu_comparison > 0) {
                        return 1;
                    } else if( menu_comparison < 0 ) {
                        return -1;
                    } else {
                        // ok do an alphabetical comparison
                        return a.title.rendered > b.title.rendered;
                    }
                }
            );
            posts_to_render = this.getChildrenOf(0);

            this.organizePostsInPage(posts_to_render);
        } else {
            this.ordered_posts = this.posts;
        }
        //render
        this.renderPosts();
    };


    /**
     * @var
     */
    this.organizePostsInPage = function (posts) {
        var post = posts.shift();
        while(typeof post === 'object' ) {
            // add it to the page
            this.ordered_posts.push(post);
            if (this.post_type === 'page') {
                this.organizeChildrenOf(post.id);
            }
            post = posts.shift();
        }
    };

    this.renderPosts = function() {
        var post = this.ordered_posts.shift();
        if(typeof post === 'object') {
            this.status_span.html('Rendering posts. ' + this.ordered_posts.length + ' left...');
            this.addPostToPage(post);
            setTimeout(
                () => {
                    this.renderPosts();
                },
                this.rendering_wait
            );
        } else {
            this.finish();
        }

    };

    this.organizeChildrenOf = function(parent_id) {
        var children = this.getChildrenOf(parent_id);
        this.organizePostsInPage(children);
    };

    this.getChildrenOf = function( parent_id ) {
        var i = 0;
        var children_posts = [];
        while(i < this.posts.length) {
            var post = this.posts[i];
            if(post.parent === parent_id) {
                children_posts.push(post);
                this.posts.splice(i,1);
                // no need to move index on, because we've removed the item that was previously at this index
                // so there is a new item at this index now.
            } else {
                // move on, this index isn't a child
                i++;
            }
        }
        return children_posts;
    };




    this.finish = function () {
        this.status_span.html(this.translations.wrapping_up);
        setTimeout(
            () => {
                this.waiting_area.hide();
                this.print_ready.show();
                this.prettyUpPrintedPage();
            },
            5000
        );
    };

    /**
     * Takes the page look better on the printed page. Mostly this helps prevent page breaks in awkward places,
     * like in the middle of images and right after headers.
     */
    this.prettyUpPrintedPage = function()
    {
        // Don't wrap tiled gallery images- we have CSS to avoid page breaks in them
        // although currently, they don't display well because they need JS that doesn't get enqueued
        var non_emojis = jQuery('img:not(.emoji, div.tiled-gallery img)');
        if(this.image_size === 0){
            non_emojis.remove();
        } else{
            non_emojis.wrap('<div class="pmb-image"></div>');
            if(this.image_size !== false) {
                var pmb = this;
                non_emojis.each(function () {
                    var obj = jQuery(this);
                    var width = pmb.image_size / pmb.columns;
                    obj.css('width', width + 'in');
                });
            }
        }

        jQuery('h1').addClass('pmb-header');
        jQuery('h2').addClass('pmb-header');
        jQuery('h3').addClass('pmb-header');
        jQuery('h4').addClass('pmb-header');
        jQuery('h5').addClass('pmb-header');

        // Remove inline styles that dynamically set height and width on WP Videos.
        // They use some Javascript that doesn't get enqueued, so better to let the browser decide their dimensions.
        jQuery('div.wp-video').css({'width': '','min-width':'', 'height': '', 'min-height': ''});
        // unhide the contents. Google Chrome doesn't print headers properly if they're not displayed. (Mind you, we still
        // have the full page overlay hiding them.)
        jQuery('.pmb-posts').toggle();
        jQuery(document).trigger('pmb_wrap_up');
    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {
        var html_to_add = '<article id="post-' + post.id + '" class="post-' + post.id + ' post type-' + this.post_type + ' status-' + post.status + ' hentry pmb-post-article">' +
            '<header class="pmb-post-header entry-header">'
            + '<h1 class="entry-title">'
            + post.title.rendered
            + '</h1>'
            + '</header>'
            + '<div class="entry-meta">';
        if(this.post_type === 'post') {
            html_to_add += '<span class="posted-on">'
                +   this.getPublishedDate(post)
                +   '</span>';
        }
        if(this.post_type === 'page') {
            html_to_add += '<!-- id:' + post.id + ' , parent:' + post.parent + ', order:' + post.menu_order + '-->';
        }
        html_to_add += this.addTaxonomies(post);
        html_to_add += '</div>'
            + '<div class="entry-content">'
            + this.getFeaturedImageHtml(post);
        if(this.include_excerpts) {
            html_to_add += '<div class="entry-excerpt">'
                + post.excerpt.rendered
                + '</div>';
        }
        this.posts_div.append(html_to_add);
        if(this.include_inline_js) {
            this.posts_div.append(post.content.rendered);
        } else {
            this.posts_div.append(jQuery.parseHTML(post.content.rendered));
        }
        this.posts_div.append( '</div>'
             + '</article>');
        // add header
        // add body
        // this.posts_div.append(html_to_add);
    };

    this.addTaxonomies = function(post) {
        var html = '';
        if(post._embedded['wp:term']) {
            for( taxonomy in post._embedded['wp:term']) {
                var term_names = [];
                var taxonomy_slug = '';
                jQuery.each(post._embedded['wp:term'][taxonomy], (key, term) => {
                    term_names.push(term.name);
                    taxonomy_slug = term.taxonomy;
                });
                if(term_names.length > 0) {
                    html += ' ' + this.taxonomies[taxonomy_slug].name + ': ';
                    html += term_names.join(', ');
                }
            }
        }
        return html;
    };

    // this.getAuthorName = function (post)
    // {
    //     if( typeof post._embedded['author'] == 'array'
    //         &&  typeof post._embedded['author'][0] == 'object'
    //     ) {
    //         return post._embedded['author'][0].name;
    //     } else {
    //         return 'Unknown';
    //     }
    // }

    this.getPublishedDate = function(post)
    {
        let ld = luxon.DateTime.fromJSDate(new Date(post.date));
        let format = {month: 'long', day: 'numeric', year: 'numeric'};
        ld.setLocale(this.locale);
        return ld.toLocaleString(format);
    }

    /**
     * @param object post
     * @return string HTML for the featured image
     */
    this.getFeaturedImageHtml = function(post)
    {   if( typeof post._embedded['wp:featuredmedia'] == "object"
            && typeof post._embedded['wp:featuredmedia'][0] == "object"
            && typeof post._embedded['wp:featuredmedia'][0].media_details == "object"
            && typeof post._embedded['wp:featuredmedia'][0].media_details.sizes == "object"
            && typeof post._embedded['wp:featuredmedia'][0].media_details.sizes.full == "object") {
            let featured_media_url = post._embedded['wp:featuredmedia'][0].media_details.sizes.full.source_url
            return '<div class="single-featured-image-header"><img src="' + featured_media_url + '" class="wp-post-image"/></div>';
        }
        return '';
    }
}

/**
 * Show instrutions on how to get a print preview.
 */
function pmb_print_preview()
{
    jQuery('.pmb-waiting-message-fullpage').toggle();
}

var pmb = null;
jQuery(document).ready(function () {
    wp.api.loadPromise.done( function() {
        pmb = new PmbPrintPage(
            {
                header_selector: '.pmb-waiting-h1',
                status_span_selector: '.pmb-status',
                posts_count_span_selector: '.pmb-posts-count',
                posts_div_selector: '.pmb-posts-body',
                waiting_area_selector: '.pmb-waiting-area',
                print_ready_selector: '.pmb-print-ready',
                locale: pmb_print_data.data.locale,
                image_size: pmb_print_data.data.image_size,
                proxy_for: pmb_print_data.data.proxy_for,
                include_excerpts: pmb_print_data.data.include_excerpts,
                columns: pmb_print_data.data.columns,
                post_type: pmb_print_data.data.post_type,
                rendering_wait: pmb_print_data.data.rendering_wait,
                include_inline_js: pmb_print_data.data.include_inline_js
            },
            {
                wrapping_up: pmb_print_data.i18n.wrapping_up
            }
        );

        pmb.initialize();
        pmb.beginLoading();
    });
});


