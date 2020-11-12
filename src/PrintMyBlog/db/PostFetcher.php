<?php

namespace PrintMyBlog\db;

use PrintMyBlog\system\CustomPostTypes;
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
	 * @var CustomPostTypes
	 */
	private $custom_post_types;

	public function inject(CustomPostTypes $custom_post_types){
		$this->custom_post_types = $custom_post_types;
	}

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
            . ' WHERE post_type IN (\'' . implode('\',\'', $this->getProjectPostTypes()) . '\') AND post_status in ("publish","draft")'
        );
    }

	/**
	 * @param string $output passed into `get_post_types`. See @get_post_types
	 * @return array of all the post types that can be in projects.
	 */
    public function getProjectPostTypes($output = 'names'){
	    $in_search_post_types = get_post_types( array( 'exclude_from_search' => false ), $output );
	    unset($in_search_post_types['attachment']);
	    if($output === 'objects'){
		    $in_search_post_types['pmb_content'] = get_post_type_object('pmb_content');
	    } else {
		    $in_search_post_types['pmb_content'] = 'pmb_content';
		    $in_search_post_types = array_map( 'esc_sql', $in_search_post_types );
	    }
	    return  $in_search_post_types;
    }


	/**
	 * Deletes all PMB custom post type posts
	 * @return int
	 */
    public function deleteCustomPostTypes(){
    	global $wpdb;
    	return $wpdb->query(
    		'DELETE posts, postmetas FROM ' . $wpdb->posts . ' AS posts INNER JOIN ' . $wpdb->postmeta . ' AS postmetas ON posts.ID=postmetas.post_id WHERE posts.post_type IN ("' . implode('","',$this->custom_post_types->getPostTypes()) . '")'
	    );
    }
}