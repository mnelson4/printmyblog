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
        $in_search_post_types = get_post_types( array( 'exclude_from_search' => false ) );
        unset($in_search_post_types['attachment']);
        $post_types = array_map( 'esc_sql', $in_search_post_types );

        return $wpdb->get_results(
            'SELECT ID, post_title FROM '
            . $wpdb->posts
            . ' WHERE post_type IN (\'' . implode('\',\'',$post_types) . '\')'
        );
    }
}