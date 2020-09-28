<?php


namespace PrintMyBlog\orm\managers;


use PrintMyBlog\db\TableManager;
use PrintMyBlog\orm\entities\ProjectSection;
use stdClass;

class ProjectSectionManager {
	/**
	 * Gets ProjectSections for this project.
	 *
	 * @param $project_id
	 *
	 * @param int $max_levels
	 * @param int $limit
	 * @param int $offset
	 * @param bool $include_title
	 * @param string $placement
	 *
	 * @return ProjectSection[]
	 */
	public function fetchSectionsFor($project_id, $max_levels = 1, $limit = 20, $offset = 0, $include_title = false, $placement = 'main'){
		$sections = $this->fetchFlatSectionsFor($project_id, $limit, $offset, $include_title, $placement);
		$index = 0;
		$parent_id = 0;
		return $this->nestSections(
			$sections,
			$index,
			$parent_id,
			$max_levels
		);
	}

	/**
	 * Gets a 1-dimensional array of project parts, ignoring parent hierarchy (although the parent_id is included on each
	 * object)
	 * @param int $project_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return ProjectSection[] unlike fetchPartsFor(), this is a flat array, objects don't have a 'subs' property
	 */
	public function fetchFlatSectionsFor($project_id, $limit = 20, $offset = 0, $include_post_title = false, $placement = null){
		global $wpdb;

		$select_sql = 'sections.ID, sections.post_id, sections.parent_id, sections.placement, sections.template';
		$join_sql = '';
		$where_sql = $wpdb->prepare(' WHERE project_id=%d', $project_id);;
		if($include_post_title){
			$select_sql .= ', posts.post_title';
			$join_sql .= 'INNER JOIN
                  ' . $wpdb->posts . ' posts 
                  ON  sections.post_id=posts.ID';
		}
		if($placement){
			$where_sql .= $wpdb->prepare(' AND placement=%s', $placement);
		}
		return $this->createObjsFromRows(
			$wpdb->get_results(
				$wpdb->prepare(
					'SELECT ' . $select_sql . ' FROM ' . $wpdb->prefix . TableManager::SECTIONS_TABLE . ' sections '
	                    . $join_sql . $where_sql .
	                  ' ORDER BY section_order ASC
	                  limit %d, %d',
					$offset,
					$limit
				)
			)
		);
	}

	/**
	 * @param $rows
	 *
	 * @return ProjectSection[]
	 */
	public function createObjsFromRows($rows){
		$objs = [];
		foreach($rows as $row){
			$objs[] = $this->createObjFromRow($row);
		}
		return $objs;
	}

	/**
	 * @param stdClass $row
	 *
	 * @return ProjectSection
	 */
	public function createObjFromRow($row){
		return new ProjectSection($row);
	}

	/**
	 * Takes the 2d array of $part_rows (From the DB), and using the parent_id property,
	 * creates a tree of rows.
	 *
	 * @param ProjectSection[] $flat_sections
	 * @param int $index
	 * @param int $current_parent_id
	 *
	 * @param int $max_levels
	 * @param int $current_level
	 *
	 * @return ProjectSection[]
	 */
	protected function nestSections(&$flat_sections, &$index = 0, $current_parent_id = 0, $max_levels = 1, $current_level = 1){
		$nested_sections = [];
		for(;$index<count($flat_sections);$index++){

			if( $flat_sections[$index]->getParentId() === intval($current_parent_id)){
				$nested_sections[] = $flat_sections[$index];
			} elseif( intval($flat_sections[ $index - 1]->getId()) === intval($flat_sections[$index]->getParentId())) {
				$subs = $this->nestSections(
					$flat_sections,
					$index,
					$flat_sections[ $index - 1 ]->getId(),
					$max_levels,
					$current_level + 1
				);
				if( $current_level < $max_levels){
					$nested_sections[ count( $nested_sections ) - 1]->cacheSubSections($subs);
				} else {
					$nested_sections = array_merge($nested_sections, $subs);
				}

			} else {
				// this item isn't a child of $current_parent_id, nor the previous item. Just finish
				$index--;
				return $nested_sections;
			}
		}
		return $nested_sections;
	}

	/**
	 * @param $project_id
	 *
	 * @return int|false number of rows deleted, or false on error
	 */
	public function clearSectionsFor($project_id){
		global $wpdb;
		return $wpdb->delete(
			$wpdb->prefix . TableManager::SECTIONS_TABLE,
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
	 * @param array $sections_data . Top-level array has sub-arrays, each with 3 items: the post ID, its "type", and an
	 * array of its sub-items (whose structure is just like the top-level array).
	 * @param int $layers_detected
	 *
	 * @return bool|int
	 */
	public function setSectionsFor($project_id, $sections_data)
	{
		$order = 1;
		return $this->insertDbRows($project_id,$sections_data,0,$order);
	}

	/**
	 * @param $project_id
	 * @param $sections_data
	 * @param $parent_id
	 * @param $order
	 */
	protected function insertDbRows($project_id, $sections_data, $parent_id, &$order){
		global $wpdb;
		foreach ( $sections_data as $section_data){
			$post_id = $section_data[0];
			$template = $section_data[1];
			$subsections = $section_data[2];
			$success = $wpdb->insert(
				$wpdb->prefix . TableManager::SECTIONS_TABLE,
				[
					'project_id' => $project_id,
					'post_id' => $post_id,
					'parent_id' => $parent_id,
					'section_order' => $order++,
					'template' => $template,
					'placement' => 'main'
				],
				[
					'%d',//project_id
					'%d',//post_id
					'%d',//parent_id
					'%d',//section_order
					'%s',//template
					'%s',//placement
				]
			);
			if(! $success) {
				return false;
			}
			$this->insertDbRows(
				$project_id,
				$subsections,
				$wpdb->insert_id,
				$order
			);
		}
		return true;
	}

	/**
	 * Gets the ID of this part's parent.
	 * @param int $part_id
	 *
	 * @return int
	 */
	public function getParentOf($part_id){
		global $wpdb;
		return (int)$wpdb->get_var(
			$wpdb->prepare(
				'SELECT parent_id FROM ' . $wpdb->prefix . TableManager::SECTIONS_TABLE . ' WHERE ID=%d',
				$part_id
			)
		);
	}
}