<?php

namespace PrintMyBlog\db;

use WP_Query;

/**
 * Class PartFetcher
 *
 * Fetches the parts of projects
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class PartFetcher
{
	/**
	 * Gets the database rows (stdClasses) from pmb_project_parts for this project in order
	 * @param $project_id
	 *
	 * @return stdClass[]
	 */
    public function fetchPartsFor($project_id){
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT posts.ID, posts.post_title FROM ' . $wpdb->prefix . 'pmb_project_parts parts
                INNER JOIN
                  ' . $wpdb->posts . ' posts 
                  ON  parts.post_id=posts.ID
                  WHERE project_id=%d ORDER BY part_order ASC',
                $project_id
            )
        );
    }

	/**
	 * Gets the IDs of the posts that comprise this project. Unordered for efficiency.
	 * @param $project_id
	 *
	 * @return int[]
	 */
    public function fetchPartPostIdsUnordered($project_id)
    {
	    global $wpdb;
	    return $wpdb->get_col(
		    $wpdb->prepare(
			    'SELECT post_id FROM ' . $wpdb->prefix . 'pmb_project_parts parts
                  WHERE project_id=%d',
			    $project_id
		    )
	    );
    }

	/**
	 * @param $project_id
	 *
	 * @return int|false number of rows deleted, or false on error
	 */
    public function clearPartsFor($project_id){
        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix . 'pmb_project_parts',
            [
                'project_id' => $project_id
            ],
            [
                '%d'
            ]
        );
    }

    public function setPartsFor($project_id, $parts_data)
    {
        global $wpdb;
        $rows = [];
        $order = 1;
        foreach($parts_data as $post_id){
            $rows[] = $wpdb->prepare(
                '(%d, %d, %d)',
                $project_id,
                $post_id,
                $order++
            );
        }
        $result = $wpdb->query(
            'INSERT INTO ' . $wpdb->prefix . 'pmb_project_parts
            (project_id, post_id, part_order) VALUES '
            . implode(',', $rows)
        );
        return $result;
    }
}