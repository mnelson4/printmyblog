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
	 *
	 * @param $project_id
	 *
	 * @param int $max_levels
	 *
	 * @return stdClass[] with properties ID, post_title, parent_id, type
	 */
    public function fetchPartsFor($project_id, $max_levels = 1){
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT posts.ID, posts.post_title, parts.parent_id, parts.type, parts.template FROM ' . $wpdb->prefix . 'pmb_project_parts parts
                INNER JOIN
                  ' . $wpdb->posts . ' posts 
                  ON  parts.post_id=posts.ID
                  WHERE project_id=%d ORDER BY part_order ASC',
                $project_id
            )
        );
        $index = 0;
        $parent_id = 0;
        return $this->structureParts($rows, $index, $parent_id, $max_levels);
    }

	/**
	 * Takes the 2d array of $part_rows (From the DB), and using the parent_id property,
	 * creates a tree of rows.
	 *
	 * @param $part_rows
	 * @param int $index
	 * @param int $current_parent_id
	 *
	 * @param int $max_levels
	 * @param int $current_level
	 *
	 * @return array
	 */
    protected function structureParts(&$part_rows, &$index = 0, $current_parent_id = 0, $max_levels = 1, $current_level = 1){
		$structured_rows = [];
		for(;$index<count($part_rows);$index++){

			if( intval($part_rows[$index]->parent_id) === intval($current_parent_id)){
				$structured_rows[] = $part_rows[$index];
			} elseif(intval($part_rows[$index - 1]->ID) === intval($part_rows[$index]->parent_id)) {
				$subs = $this->structureParts(
					$part_rows,
					$index,
					$part_rows[ $index -1 ]->ID,
					$max_levels,
					$current_level + 1
				);
				if( $current_level < $max_levels){
					$structured_rows[ count( $structured_rows ) - 1]->subs = $subs;
				} else {
					$structured_rows = array_merge($structured_rows, $subs);
				}

			} else {
				// this item isn't a child of $current_parent_id, nor the previous item. Just finish
				$index--;
				return $structured_rows;
			}
		}
		return $structured_rows;
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

	/**
	 * @param $project_id
	 * @param array $parts_data. Top-level array has sub-arrays, each with 3 items: the post ID, its "type", and an
	 * array of its sub-items (whose structure is just like the top-level array).
	 *
	 * @return bool|int
	 */
    public function setPartsFor($project_id, $parts_data)
    {
	    global $wpdb;
    	$order = 1;
    	$rows = [];
	    $this->getDbRowsFrom($project_id,$parts_data, 0, $order, $rows);
        $result = $wpdb->query(
            'INSERT INTO ' . $wpdb->prefix . 'pmb_project_parts
            (project_id, post_id, parent_id, part_order, template) VALUES '
            . implode(',', $rows)
        );
        return $result;
    }

	/**
	 * Modifies the $rows array passed in
	 * @param $project_id
	 * @param $parts_data
	 * @param $parent_id
	 * @param int $order passed by reference
	 * @param array $rows passed by reference
	 */
    protected function getDbRowsFrom($project_id, $parts_data, $parent_id, &$order, &$rows){
	    global $wpdb;
	    foreach($parts_data as $part_data){
		    $post_id = $part_data[0];
		    $template = $part_data[1];
		    $sub_parts = $part_data[2];
		    $rows[] = $wpdb->prepare(
			    '(%d, %d, %d, %d, %s)',
			    $project_id,
			    $post_id,
			    $parent_id,
			    $order++,
			    $template
		    );
		    $this->getDbRowsFrom($project_id, $sub_parts, $post_id, $order, $rows);
        }
    }
}