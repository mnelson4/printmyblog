<?php

namespace PrintMyBlog\db;

use WP_Query;

/**
 * Class PostFetcher
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class PostFetcher
{

    /**
     * Based on the request, fetches posts. Returns an array of WP_Posts
     * @since $VID:$
     * @return WP_Post[]
     */
    public function fetchPostOptionssForProject(){
        global $wpdb;
        return $wpdb->get_results(
            'SELECT ID, post_title FROM '
            . $wpdb->posts
            . ' WHERE post_type NOT IN (\'auto-draft\')'
        );
    }
}