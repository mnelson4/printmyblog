// begin loading posts

// and append them to the page
function PmbPrintPage(pmb_instance_vars, translations) {
    this.header_selector = pmb_instance_vars.header_selector;
    this.header = null;
    this.status_span_selector = pmb_instance_vars.status_span_selector;
    this.status_span = null;
    this.posts_div_selector = pmb_instance_vars.posts_div_selector;
    this.posts_div = null;
    this.waiting_area_selector = pmb_instance_vars.waiting_area_selector;
    this.waiting_area = null;
    this.print_ready_selector = pmb_instance_vars.print_ready_selector;
    this.print_ready = null;
    this.locale = pmb_instance_vars.locale;
    this.show_images = pmb_instance_vars.show_images;
    this.translations = translations;
    /**
     * @function
     */
    this.initialize = function () {
        this.header = jQuery(this.header_selector);
        this.status_span = jQuery(this.status_span_selector);
        this.posts_div = jQuery(this.posts_div_selector);
        this.waiting_area = jQuery(this.waiting_area_selector);
        this.print_ready = jQuery(this.print_ready_selector);
    };

    this.begin_loading = function () {
        var postsCollection = new wp.api.collections.Posts();
        postsCollection.fetch({data: {per_page: 5, status: 'publish', _embed:true}}).done((posts) => {
            this.renderAndMaybeFetchMore(posts, postsCollection);
        });
    };

    this.renderAndMaybeFetchMore = function (posts, postsCollection) {
        this.renderPostsInPage(posts);
        if (postsCollection.hasMore()) {
            this.load_more(postsCollection);
            this.status_span.append('.');
        } else {
            this.finish();
        }
    };

    this.load_more = function (postsCollection) {
        postsCollection.more().done((posts) => {
            this.renderAndMaybeFetchMore(posts, postsCollection);
        });
    };

    /**
     * @var
     */
    this.renderPostsInPage = function (posts) {
        for (let post of posts) {
            // add it to the page
            this.addPostToPage(post);
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
        if(this.show_images){
            jQuery('img:not(.emoji)').wrap('<div class="pmb-image"></div>');
        } else {
            jQuery('img:not(.emoji)').remove();
        }
        //jQuery('.pmb-posts-body').css('font-size','0.5em');

        jQuery('h1').wrap('<div class="pmb-header"></div>');
        jQuery('h2').wrap('<div class="pmb-header"></div>');
        jQuery('h3').wrap('<div class="pmb-header"></div>');
        jQuery('h4').wrap('<div class="pmb-header"></div>');
        jQuery('h5').wrap('<div class="pmb-header"></div>');
    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {

        let html_to_add = '<div class="pmb-post-header"><h1 class="entry-title">'
            + post.title.rendered
            + '</h1>'
            + '<div class="entry-meta"><span class="posted-on">'
            + this.getPublishedDate(post)
            + '</span><span class="byline">'
            // + 'By '
            // + this.getAuthorName(post)
            + '</span></div>'
            + this.getFeaturedImageHtml(post)
            + '</div><div class="entry-content">'
            + post.content.rendered
            + '</div>';
        ;
        // add header
        // add body
        this.posts_div.append(html_to_add);
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
    jQuery('.print-preview-instructions').toggle();
}

jQuery(document).ready(function () {
    wp.api.loadPromise.done( function() {
        var pmb = new PmbPrintPage(
            {
                header_selector: '.pmb-waiting-h1',
                status_span_selector: '.pmb-status',
                posts_div_selector: '.pmb-posts-body',
                waiting_area_selector: '.pmb-waiting-area',
                print_ready_selector: '.pmb-print-ready',
                locale: pmb_print_data.data.locale,
                show_images: pmb_print_data.data.show_images,
            },
            {
                wrapping_up: pmb_print_data.i18n.wrapping_up
            }
        );

        pmb.initialize();
        pmb.begin_loading();
    });
});


