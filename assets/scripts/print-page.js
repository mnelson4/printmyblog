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
    this.cancel_button_selector = pmb_instance_vars.cancel_button_selector;
    this.cancel_button = null;
    this.proxy_for = pmb_instance_vars.proxy_for;
    this.locale = pmb_instance_vars.locale;
    this.image_size = pmb_instance_vars.image_size;
    this.translations = translations;
    this.columns = pmb_instance_vars.columns;
    this.post_type = pmb_instance_vars.post_type;
    this.total_posts = 0;
    this.posts = [];
    this.taxonomies = {};
    this.ordered_posts = [];
    this.comments = [];
    this.total_comments = 0;
    this.ordered_comments = [];
    this.rendering_wait = pmb_instance_vars.rendering_wait;
    this.include_inline_js = pmb_instance_vars.include_inline_js;
    this.links = pmb_instance_vars.links;
    this.showUrl = pmb_instance_vars.show_url;
    this.showId = pmb_instance_vars.show_id;
    this.showTitle = pmb_instance_vars.show_title;
    this.showFeaturedImage = pmb_instance_vars.show_featured_image;
    this.showDate = pmb_instance_vars.show_date;
    this.showCategories = pmb_instance_vars.show_categories;
	this.showExcerpt = pmb_instance_vars.show_excerpt;
    this.showContent = pmb_instance_vars.show_content;
	this.showComments = pmb_instance_vars.show_comments;
	this.showDivider = pmb_instance_vars.show_divider;
	this.filters = pmb_instance_vars.filters;
	this.foogallery = pmb_instance_vars.foogallery;
	this.isUserLoggedIn = pmb_instance_vars.is_user_logged_in;
	this.format = pmb_instance_vars.format;
	this.include_private_posts = pmb_instance_vars.include_private_posts;
    /**
     * Initializes variables and begins fetching taxonomies, then gets started fetching posts/pages.
     * @function
     */
    this.initialize = function () {
        this.header = jQuery(this.header_selector);
        this.status_span = jQuery(this.status_span_selector);
        this.posts_count_span = jQuery(this.posts_count_span_selector);
        this.posts_div = jQuery(this.posts_div_selector);
        this.waiting_area = jQuery(this.waiting_area_selector);
        this.print_ready = jQuery(this.print_ready_selector);
        this.cancel_button = jQuery(this.cancel_button_selector);

        var alltaxonomiesCollection = new wp.api.collections.Taxonomies();
        alltaxonomiesCollection.fetch().done((taxonomies) => {
            this.taxonomies = taxonomies;
            // ok we have everything we need to start. So let's get it started!
			this.beginLoading();
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

    this.getPostsCollectionQueryData = function () {
        var data = this.getCollectionQueryData();
        if( this.canGetSensitiveData() && this.include_private_posts){
			data.status = 'publish, private, future';
        } else {
			data.status = 'publish';
        }
        data._embed = 1;
        if(this.post_type === 'post') {
            data.orderby = 'date';
            data.order = 'asc';
        }
		if(this.filters){
			jQuery.extend(data, this.filters);
		}
        return data;
    };

	this.getCommentsCollectionQueryData = function () {
		var data = this.getCollectionQueryData();
		data.order = 'asc';
		return data;
	};

	this.getCollectionQueryData = function () {
		let data = {};
        if( this.proxy_for){
			data.proxy_for = this.proxy_for;
        }
        // If they're logged in, and its a request for this site, try to show password-protected content
        if( this.canGetSensitiveData()) {
			data.context = 'edit';
		}
		return data;
	};

	this.canGetSensitiveData = function() {
	    return this.isUserLoggedIn && ! this.proxy_for;
    }



    this.getCommentCollection = function () {
      return new wp.api.collections.Comments();
    };

    this.beginLoading = function () {
        this.header.html(this.translations.loading_content);
        let collection = this.getCollection();
        collection.fetch(
            {
                data: this.getPostsCollectionQueryData(),

        }).done((posts) => {
            this.storePostsAndMaybeFetchMore(posts, collection);
        });
    };

    this.storePostsAndMaybeFetchMore = function(posts, collection) {
        if(typeof posts === 'object' && 'errors' in posts) {
            var first_error_key = Object.keys(posts.errors)[0];
            var first_error_message = posts.errors[first_error_key];
            this.status_span.html( this.translations.error_fetching_posts + first_error_message + ' (' + first_error_key + ')');
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
            this.maybeStoreComments();
        }
    };

    /**
     * Begins loading comments if that was requested, otherwise skips right to sorting and rendering posts.
     */
    this.maybeStoreComments = function() {
        if (this.showComments) {
            this.beginLoadingComments();
            // once we are done loading comments, we'll sort and render posts etc.
        } else {
            // skip loading comments.
            this.sortPosts();
            this.render();
        }
    };




    this.beginLoadingComments = function () {
        this.header.html(this.translations.loading_comments);
        let collection = this.getCommentCollection();
        collection.fetch({data:this.getCommentsCollectionQueryData()}).done((comments) => {
            this.storeCommentsAndMaybeFetchMore(comments, collection);
        });
    };

    this.storeCommentsAndMaybeFetchMore = function(comments, collection) {
        if(typeof comments === 'object' && 'errors' in comments) {
            var first_error_key = Object.keys(comments.errors)[0];
            var first_error_message = comments.errors[first_error_key];
            this.status_span.html( this.translations.error_fetching_posts + first_error_message + ' (' + first_error_key + ')');
            return;
        }
        this.comments = this.comments.concat(comments);
        this.total_comments = collection.state.totalObjects;
        let comments_so_far = this.comments.length;
        this.posts_count_span.html(comments_so_far + '/' + this.total_comments);
        if (collection.hasMore()) {
            collection.more().done((comments) => {
                this.storeCommentsAndMaybeFetchMore(comments, collection);
            });
        } else {
            this.organizeComments();
        }
    };

    this.organizeComments = function(){
        this.header.html(this.translations.organizing_comments);
        for(let i=0; i<this.comments.length; i++) {
            let comment = this.comments[i];
            if(comment.parent === 0) {
                let post = this.findPostWithId(comment.post);
                if( typeof post === 'object' && post !== null) {
                    if( jQuery.type( post.comments) !== 'array') {
                        post.comments = [];
                    }
                    post.comments.push(comment);
                }
            } else {
                let parent_comment = this.getCommentWithId(comment.parent);
                if( typeof parent_comment === 'object' && parent_comment !== null ){
                    if( jQuery.type(parent_comment.children) !== 'array') {
                        parent_comment.children = [];
                    }
                    parent_comment.children.push(comment);
                }
            }
        }
        this.sortPosts();
        this.render();
    };

    /**
     *
     * @param post_id
     * @return {*}
     */
    this.findPostWithId = function(post_id) {
        for(let i=0; i<this.total_posts; i++){
            let post = this.posts[i] || this.ordered_posts[i];
            if( post.id === post_id) {
                return post;
            }
        }
        return null;
    };

    this.getCommentWithId = function(comment_id) {
        for(let i=0; i<this.total_comments; i++){
            let comment = this.comments[i];
            if( comment.id === comment_id) {
                return comment;
            }
        }
        return null;
    }

    /**
     * Sorts posts or pages in the right order and stores them on this.ordered_posts. This is done synchronously.
     */
    this.sortPosts = function(){
        var posts_to_render = this.posts;
        if(this.post_type === 'page') {
            this.status_span.html( this.translations.organizing_posts);
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

    /**
     * Renders the posts on the page
     */
    this.render = function() {
        this.header.html(this.translations.rendering_posts);
        this.renderPosts();
    };

    this.renderPosts = function() {
        var post = this.ordered_posts.shift();
        if(typeof post === 'object') {
            this.status_span.html( (this.total_posts - this.ordered_posts.length) + '/' + this.total_posts);
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
        this.header.html(this.translations.wrapping_up);
        this.status_span.html('');
        setTimeout(
            () => {
                this.header.html(this.translations.ready);
                this.print_ready.css('visibility','visible');
                this.waiting_area.hide();
                this.cancel_button.hide();
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
        this.convertYoutubeVideosToImages();
        // Don't wrap tiled gallery images- we have CSS to avoid page breaks in them
        // although currently, they don't display well because they need JS that doesn't get enqueued
        var non_emojis = jQuery('.pmb-posts img:not(.emoji, div.tiled-gallery img, img.fg-image, img.size-medium, img.size-thumbnail)');
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

        if(this.format !== 'ebook') {
			jQuery('.pmb-posts h1').addClass('pmb-header');
			jQuery('.pmb-posts h2').addClass('pmb-header');
			jQuery('.pmb-posts h3').addClass('pmb-header');
			jQuery('.pmb-posts h4').addClass('pmb-header');
			jQuery('.pmb-posts h5').addClass('pmb-header');
		}

        // Remove inline styles added on image captions. They force a width in pixels which stinks with multiple columns.
        if(this.links === 'remove'){
			jQuery('.pmb-posts a').contents().unwrap();
        }

        // Remove inline styles that dynamically set height and width on WP Videos.
        // They use some Javascript that doesn't get enqueued, so better to let the browser decide their dimensions.
        jQuery('div.wp-video').css({'width': '','min-width':'', 'height': '', 'min-height': ''});
        // unhide the contents.
        jQuery('.pmb-posts').toggle();
        if(this.foogallery) {
            jQuery('img[data-src-fg]').each(function(arg1, arg2){
               let el = jQuery(this);
               el.attr('src', el.attr('data-src-fg'));
               let src = el.attr('src');
            });
            setTimeout(
                () =>{
					this.posts_div.append('<script type="text/javascript" src="/wp-includes/js/masonry.min.js?ver=3.3.2"></script><script type="text/javascript" src="/wp-content/plugins/foogallery/extensions/default-templates/shared/js/foogallery.min.js"></script><link rel="stylesheet" type="text/css" href="/wp-content/plugins/foogallery/extensions/default-templates/shared/css/foogallery.min.css">');
                },
                this.rendering_wait
            );

		}
        jQuery(document).trigger('pmb_wrap_up');
    };

    /**
     * @var  wp.api.models.Post post
     */
    this.addPostToPage = function (post) {
        // Exclude password-protected posts if requested
        if(! this.include_private_posts && post.content.protected){
            return;
        }
        var html_to_add = '';
        if(this.format !== 'ebook'){
            html_to_add += '<article id="post-\' + post.id + \'" class="post-\' + post.id + \' post type-\' + this.post_type + \' status-\' + post.status + \' hentry pmb-post-article">'
			+ '<header class="pmb-post-header entry-header">';
        }
        if(this.showTitle) {
            html_to_add += '<h1 class="entry-title">'
				+ post.title.rendered
				+ '</h1>'
        }
        if(this.format !== 'ebook'){
            html_to_add += '</header>';
        }
        html_to_add += '<div class="entry-meta">';
		if(this.showId) {
			html_to_add += '<span class="post-id pmb-post-meta">' +this.translations.id + post.id + '</span> ';
		}
        if(this.showUrl) {
            html_to_add += '<span class="url pmb-post-meta"><a href="'
                + post.link
                + '">'
                + post.link
                + '</a></span> ';
        }
        if(this.showDate) {
            html_to_add += '<span class="posted-on pmb-post-meta">'
                +   this.getPublishedDate(post)
                +   '</span> ';
        }
        if(this.post_type === 'page') {
            html_to_add += '<!-- id:' + post.id + ' , parent:' + post.parent + ', order:' + post.menu_order + '-->';
        }
        if(this.showCategories) {
			html_to_add += this.addTaxonomies(post);
		}
		html_to_add += '</div>'
			+ '<div class="entry-content">';
		if(this.showFeaturedImage){
            html_to_add += this.getFeaturedImageHtml(post);
        }

        if(this.showExcerpt) {
            html_to_add += '<div class="entry-excerpt">'
                + post.excerpt.rendered
                + '</div>';
        }
        if(this.showContent) {
            var content_html = '';
			if (this.include_inline_js) {
				content_html = post.content.rendered;
			} else {
				var parsed_nodes = jQuery.parseHTML(post.content.rendered);
				if (parsed_nodes !== null) {
					for (var i = 0; i < parsed_nodes.length; i++) {
						if (typeof parsed_nodes[i].outerHTML === 'string') {
							content_html += parsed_nodes[i].outerHTML;
						} else if (typeof parsed_nodes[i].wholeText === 'string') {
							content_html += parsed_nodes[i].wholeText;
						}
					}
				}
			}
			html_to_add += content_html;
		}
        html_to_add += '</div>';
		if(this.format !== 'ebook'){
			html_to_add += '</article>';
		}
		if(this.showComments){
			html_to_add += this.renderCommentsOf(post);
        }
        if(this.showDivider){
		    html_to_add += '<hr class="pmb-divider">';
        }
        this.posts_div.append(html_to_add);
    };

    this.convertYoutubeVideosToImages = function(content) {
		jQuery('div.wp-block-embed__wrapper iframe[src*=youtube]').unwrap().end();
        var selection = jQuery('iframe[src*=youtube]');
		selection.replaceWith(function(index){
            var title = this.title;
            var src = this.src;
			var youtube_id = src.replace('https://www.youtube.com/embed/','');
            youtube_id = youtube_id.substring(0, youtube_id.indexOf('?'));
            var image_url = 'https://img.youtube.com/vi/' + youtube_id + '/maxresdefault.jpg';
            var link = 'https://youtube.com/watch?v=' + youtube_id;
            return '<div class="pmb-youtube-video-replacement-wrapper"><b class="pmb-youtube-video-title">' + title + '</b><br/><a href="' + link +'" target="_blank">' + link + '</a><img class="pmb-youtube-video-replacement" src="' + image_url + '"></div>';
        });

    };

    this.addTaxonomies = function(post) {
        var html = ' ';
        if('_embedded' in post && 'wp:term' in post._embedded) {
            for( taxonomy in post._embedded['wp:term']) {
                var term_names = [];
                var taxonomy_slug = '';
                jQuery.each(post._embedded['wp:term'][taxonomy], (key, term) => {
                    term_names.push(term.name);
                    taxonomy_slug = term.taxonomy;
                });
                if(term_names.length > 0) {
                    html += ' <span class="pmb-post-meta taxonomy-links ' + taxonomy_slug + '-links">'  + this.taxonomies[taxonomy_slug].name + ': ';
                    html += term_names.join(', ');
                    html += '</span>';
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
        return this.getPrettyDate(post.date);
    }

    this.getPrettyDate = function(iso_date)
    {
        let ld = luxon.DateTime.fromJSDate(new Date(iso_date));
        let format = {month: 'long', day: 'numeric', year: 'numeric'};
        ld.setLocale(this.locale);
        return ld.toLocaleString(format);
    }

    /**
     * @param object post
     * @return string HTML for the featured image
     */
    this.getFeaturedImageHtml = function(post)
    {   if( '_embedded' in post && 'wp:featuredmedia' in post._embedded && typeof post._embedded['wp:featuredmedia'] == "object"
            && typeof post._embedded['wp:featuredmedia'][0] == "object"
            && typeof post._embedded['wp:featuredmedia'][0].media_details == "object") {
            var featured_media_url = null;
            if( typeof post._embedded['wp:featuredmedia'][0].media_details.sizes == "object"
                && typeof post._embedded['wp:featuredmedia'][0].media_details.sizes.full == "object") {
                featured_media_url = post._embedded['wp:featuredmedia'][0].media_details.sizes.full.source_url;
            } else if (typeof post._embedded['wp:featuredmedia'][0].source_url == "string") {
                featured_media_url = post._embedded['wp:featuredmedia'][0].source_url;
            }

            if(featured_media_url !== null) {
                return '<div class="single-featured-image-header"><img src="' + featured_media_url + '" class="wp-post-image"/></div>';
            }
        }
        return '';
    }

    this.renderCommentsOf = function(post)
    {
        let html = '';
        let has_comments = typeof post.comments !== 'undefined' && post.comments !== null && post.comments.length > 0;
        var comments_header_text = this.translations.comments;
        // There are comments

        html += '<div id="comments" class="comments-area">';
        html += '<div class="';
        if( has_comments) {
            html += 'comments-title-wrap';
        } else {
            html += 'comments-title-wrap no-responses';
            comments_header_text = this.translations.no_comments;
        }
        html +='">';
        html +='<h2 class="comments-title">' + comments_header_text + '</h2>';
        html += '</div>';
        html += '<ol class="comment-list">';
        if( has_comments) {
            let htmlAndEven = this.renderComments(post.comments, 1, true, true);
            html += htmlAndEven.html;
        }
        html += '</ol>';
        return html;
    }

    this.renderComments = function(comments, depth, evenThread, even) {
        let html = '';
        for(let i=0; i<comments.length; i++){
            let comment = comments[i];
            let even_text;
            if(even){
                even_text = 'even';
            } else {
                even_text = 'odd';
            }
            let even_thread_text;
            if(evenThread){
                even_thread_text = 'thread-even';
            } else {
                even_thread_text = 'thread-odd';
            }
            html += '<li id="comment-'+comment.id+'" class="'+comment.type+' '+even_text+' '+even_thread_text+' depth-'+depth+'">\n' +
                '\t\t\t<article id="div-comment-'+comment.id+'" class="comment-body">\n' +
                '\t\t\t\t<footer class="comment-meta">\n' +
                '\t\t\t\t\t<div class="comment-author vcard">\n' +
                // comment.author_avatar_urls
                '\t\t\t\t\t\t\t\t\t\t\t\t<b class="fn">'+comment.author_name+'</b> '+this.translations.says+'\t\t\t\t\t</div><!-- .comment-author -->\n' +
                '\n' +
                '\t\t\t\t\t<div class="comment-metadata">\n' +
                '\t\t\t\t\t\t\t<time datetime="'+comment.date+'">\n' +
                '\t\t\t\t\t\t\t\t'+this.getPrettyDate(comment.date)+'\t\t\t\t\t\t\t</time>\n' +
                '\t\t\t\t\t\t</a>\n' +
                '\t\t\t\t\t\t\t\t\t\t\t</div><!-- .comment-metadata -->\n' +
                '\n' +
                '\t\t\t\t\t\t\t\t\t</footer><!-- .comment-meta -->\n' +
                '\n' +
                '\t\t\t\t<div class="comment-content">\n' +
                '\t\t\t\t\t'+comment.content.rendered+
                '\t\t\t\t</div><!-- .comment-content -->\n' +
                '\n';

            if( typeof comment.children !== 'undefined' && comment.children !== null && comment.children.length > 0) {
                html += '<ol class="children">';
                let htmlAndEven = this.renderComments(comment.children, depth++, evenThread, ! even);
                even = ! htmlAndEven.even;
                html += htmlAndEven.html;
                html += '</ol>';
            }
            html += '</li>';

            // Alternate even and odd.
            evenThread = ! evenThread;
            even = ! even;
        }
        return {
            html:html,
            even:even
        };
    };
}

/**
 * Show instrutions on how to get a print preview.
 */
function pmb_print_preview()
{
    jQuery('.pmb-waiting-message-fullpage').toggle();
}

function pmb_help_show(id){
    jQuery('.' + id).show();
    jQuery('.pmb-help-ask').hide();
}

var pmb = null;
var original_backbone_sync;
jQuery(document).ready(function () {
    wp.api.loadPromise.done( function() {
        setTimeout(
            function(){
				pmb = new PmbPrintPage(
					pmb_print_data.data,
					pmb_print_data.i18n
				);

				pmb.initialize();
            },
            1000
        );
    });
    // Override Backbone's jQuery AJAX calls to be tolerant of erroneous text before the start of the JSON.
    original_backbone_sync = Backbone.sync;
    Backbone.sync = function(method,model,options){
        // Change the jQuery AJAX "converters" text-to-json method.
		options.converters = {
			'text json': function(result) {
			    let new_result = result;
			    // Sometimes other plugins echo out junk before the start of the real JSON response.
        // So we need to chop off all that extra stuff.
        do{
            // Find the first spot that could be the beginning of valid JSON...
					var start_of_json = Math.min(
						new_result.indexOf('{'),
						new_result.indexOf('['),
						new_result.indexOf('true'),
						new_result.indexOf('false'),
						new_result.indexOf('"')
					);
					// Remove everything before it...
					new_result = new_result.substring(start_of_json);
					try{
					    // Try to parse it...
						let i = jQuery.parseJSON(new_result);
						// If that didn't have an error, great. We found valid JSON!
						return i;
          }catch(error){
					    // There was an error parsing that substring. So let's chop off some more and keep hunting for valid JSON.
            // Chop off the character that made this look like it could be valid JSON, and then continue iterating...
            new_result = new_result.substring(1);
          }
        }while(start_of_json !== false);
				// Never found any valid JSON. Throw the error.
        throw "No JSON found in AJAX response using custom JSON parser.";
			}
		};
        return original_backbone_sync(method,model,options);
    };
});


