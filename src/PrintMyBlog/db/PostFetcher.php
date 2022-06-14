<?php

namespace PrintMyBlog\db;

use PrintMyBlog\system\CustomPostTypes;
use Twine\orm\managers\PostWrapperManager;
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

    /**
     * @param CustomPostTypes $custom_post_types
     */
    public function inject(CustomPostTypes $custom_post_types)
    {
        $this->custom_post_types = $custom_post_types;
    }

    /**
     * Based on the request, fetches posts. Returns an array of WP_Posts
     * @since $VID:$
     * @return object[]
     */
    public function fetchPostOptionssForProject()
    {
        global $wpdb;
        // todo: cache
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            'SELECT ID, post_title FROM '
            . $wpdb->posts
            . ' WHERE post_type IN (\''
            . implode('\',\'', $this->getProjectPostTypes())
            . '\') AND post_status in ("publish","draft")'
        );
    }

    /**
     * @param string $output passed into `get_post_types`. See @get_post_types
     * @return array of all the post types that can be in projects.
     */
    public function getProjectPostTypes($output = 'names')
    {
        $in_search_post_types = get_post_types(array( 'exclude_from_search' => false ), $output);
        unset($in_search_post_types['attachment']);
        foreach ($this->otherPostTypesToInclude() as $post_type) {
            if (! post_type_exists($post_type)) {
                continue;
            }
            if ($output === 'objects') {
                $in_search_post_types[$post_type] = get_post_type_object($post_type);
            } else {
                $in_search_post_types[$post_type] = esc_sql($post_type);
            }
        }

        return $in_search_post_types;
    }

    /**
     * @return string[]
     */
    protected function otherPostTypesToInclude()
    {
        return [
            'stm-lessons', // from MasterStudy LMS
            'lesson', // LifterLMS
            'section',
        ];
    }


    /**
     * Deletes all PMB custom post type posts
     * @return int
     */
    public function deleteCustomPostTypes()
    {
        global $wpdb;
        // todo: cache
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->query(
            'DELETE posts, postmetas FROM '
            . $wpdb->posts
            . ' AS posts LEFT JOIN '
            . $wpdb->postmeta
            . ' AS postmetas ON posts.ID=postmetas.post_id WHERE posts.post_type IN ("'
            . implode('","', $this->custom_post_types->getPostTypes())
            . '")'
        );
    }
}
