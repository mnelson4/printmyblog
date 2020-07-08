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
        $query = new WP_Query(
            [
                'posts_per_page' => 20
            ]
        );
        $posts = $query->get_posts();
        return $posts;
    }
}