// begin loading posts

// and append them to the page
function PmgPrintPage(pmg_instance_vars, translations) {
    this.header_selector = pmg_instance_vars.header_selector;
    this.header = null;
    this.status_span_selector = pmg_instance_vars.status_span_selector;
    this.status_span = null;
    this.posts_div_selector = pmg_instance_vars.posts_div_selector;
    this.posts_div = null;
    this.spinner_selector = pmg_instance_vars.spinner_selector;
    this.spinner = null;
    /**
     * @function
     */
    this.initialize = function () {
        this.header = jQuery(this.header_selector);
        this.status_span = jQuery(this.status_span_selector);
        this.posts_div = jQuery(this.posts_div_selector);
        this.spinner = jQuery(this.spinner_selector);
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
        this.status_span.html('Wrapping Up!');
        setTimeout(
            () => {
                this.spinner.hide();
                this.header.html('All Done!');
                this.status_span.html('<button onclick="window.print()">Print Now</button><p>Alternatively, in your browser, you can click "File" then "Print Preview" to preview the page before printing.</p>');
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
        jQuery('img').wrap('<div class="pmg-image"></div>');
        jQuery('h1').wrap('<div class="pmg-header"></div>');
        jQuery('h2').wrap('<div class="pmg-header"></div>');
        jQuery('h3').wrap('<div class="pmg-header"></div>');
        jQuery('h4').wrap('<div class="pmg-header"></div>');
        jQuery('h5').wrap('<div class="pmg-header"></div>');
    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {
        let html_to_add = '<h1 class="entry-title">'
            + post.title.rendered
            + '</h1>'
            + this.getFeaturedImageHtml(post)
            + '<div class="entry-content">'
            + post.content.rendered
            + '</div>'
            + '<br class="pmg-page-break"/>'
        ;
        // add header
        // add body
        this.posts_div.append(html_to_add);
    };

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

jQuery(document).ready(function () {
    wp.api.loadPromise.done( function() {
        var pmg = new PmgPrintPage(
            {
                header_selector: '.pmg-waiting-h1',
                status_span_selector: '.pmg-status',
                posts_div_selector: '.pmg-posts',
                spinner_selector: '.pmg-spinner',
            }
        );

        pmg.initialize();
        pmg.begin_loading();
    });
});


