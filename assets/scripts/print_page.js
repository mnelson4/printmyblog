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
    this.posts_so_far = 0;
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

        // Get the posts count
        var collection = this.getCollection();
        var data = this.getCollectionQueryData();
        data.per_page = 1;
        collection.fetch({data: data
        }).done((posts) => {
            this.total_posts = collection.state.totalObjects;
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
            per_page: 5,
            status: 'publish',
            _embed:true,
            proxy_for: this.proxy_for,
        };
        if(this.post_type === 'post') {
            data.orderby = 'date';
            data.order = 'asc';
        } else if(this.post_type === 'page') {
            data.orderby = 'menu_order';
            data.order = 'asc';
        }
        return data;
    };

    this.begin_loading = function () {
        let collection = this.getCollection();
        var data = this.getCollectionQueryData();
        data.parent = 0;
        collection.fetch({data: data, async: false
        }).done((posts) => {
            this.renderAndMaybeFetchMore(posts, collection, true);
        });
    };

    this.renderAndMaybeFetchMore = function (posts, postsCollection, finish_when_done) {
        this.renderPostsInPage(posts);
        if (postsCollection.hasMore()) {
            this.load_more(postsCollection, finish_when_done);
        } else if(finish_when_done) {
            this.finish();
        }
    };

    this.load_more = function (postsCollection, finish_when_done) {
        postsCollection.more().done((posts) => {
            this.renderAndMaybeFetchMore(posts, postsCollection, finish_when_done);
        });
    };

    /**
     * @var
     */
    this.renderPostsInPage = function (posts) {
        for (let post of posts) {
            // add it to the page
            this.addPostToPage(post);
            if (this.post_type === 'page') {
                let collection = this.getCollection();
                var data = this.getCollectionQueryData();
                data.parent = [post.id];
                collection.fetch({data: data, async: false
                }).done((posts) => {
                    this.renderAndMaybeFetchMore(posts, collection, false);
                });
            }
        }
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
        jQuery(document).trigger('pmb_wrap_up');
    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {

        var html_to_add = '<div class="pmb-post-header">'
            + '<h1 class="entry-title">'
            + post.title.rendered
            + '</h1>'
            + '<div class="entry-meta">';
        if(this.post_type === 'post') {
            html_to_add += '<span class="posted-on">'
                +   this.getPublishedDate(post)
                +   '</span>';
        }
        html_to_add += '</div>'
            + '</div>'
            + '<div class="entry-content">'
            + this.getFeaturedImageHtml(post);
        if(this.include_excerpts) {
            html_to_add += '<div class="entry-excerpt">'
                + post.excerpt.rendered
                + '</div>';
        }
         html_to_add += post.content.rendered
            + '</div>';
        // add header
        // add body
        this.posts_div.append(html_to_add);
        this.posts_so_far = this.posts_so_far + 1;
        this.posts_count_span.html(this.posts_so_far + '/' + this.total_posts);
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
    jQuery('.pmb-posts').toggle();
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
            },
            {
                wrapping_up: pmb_print_data.i18n.wrapping_up
            }
        );

        pmb.initialize();
        pmb.begin_loading();
    });
});


