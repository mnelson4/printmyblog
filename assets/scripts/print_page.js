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
        postsCollection.fetch({data: {per_page: 5, status: 'publish'}}).done((posts) => {
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
                this.status_span.html('<button onclick="window.print()">Print Now</button>.');
                jQuery('img').wrap('<div class="pmg-image"></div>');
                jQuery('h1').wrap('<div class="pmg-header"></div>');
                jQuery('h2').wrap('<div class="pmg-header"></div>');
                jQuery('h3').wrap('<div class="pmg-header"></div>');
                jQuery('h4').wrap('<div class="pmg-header"></div>');
                jQuery('h5').wrap('<div class="pmg-header"></div>');
            },
            5000
        );

    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {
        let html_to_add = '<h1 class="entry-title">'
            + post.title.rendered
            + '</h1>'
            + '<div class="entry-content">'
            + post.content.rendered
            + '</div>'
            + '<br class="pmg-page-break"/>'
        ;
        // add header
        // add body
        this.posts_div.append(html_to_add);
    };
}

jQuery(document).ready(function () {

    const pmg = new PmgPrintPage(
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


