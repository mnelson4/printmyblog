<?php


namespace PrintMyBlog\orm\entities;


use PrintMyBlog\system\Context;
use stdClass;

class ProjectSection {
	protected $ID;
	protected $post_id;
	protected $post_title;
	protected $parent_id;
	protected $section_order;
	protected $template;
	protected $placement;
	/**
	 * The layer this is in. This can be
	 * @var int
	 */
	protected $layer;

	/**
	 * @var ProjectSection[]
	 */
	protected $subsections;

	/**
	 * ProjectSection constructor.
	 *
	 * @param stdClass $db_row
	 */
	public function __construct(stdClass $db_row){
		$this->ID = $db_row->ID;
		$this->post_id = $db_row->post_id;
		if(isset($db_row->post_title)){
			$this->post_title = $db_row->post_title;
		}
		$this->parent_id = $db_row->parent_id;
		if(isset($db_row->section_order)){
			$this->section_order = $db_row->section_order;
		}
		$this->template  = $db_row->template;
		$this->placement = $db_row->placement;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return (int)$this->ID;
	}

	/**
	 * @return int
	 */
	public function getPostId() {
		return (int)$this->post_id;
	}

	/**
	 * @return string
	 */
	public function getPostTitle() {
		return $this->post_title;
	}

	/**
	 * @return int
	 */
	public function getParentId() {
		return (int)$this->parent_id;
	}

	/**
	 * @return int
	 */
	public function getSectionOrder() {
		return (int)$this->section_order;
	}

	/**
	 * @return string
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * @return string
	 */
	public function getPlacement() {
		return $this->placement;
	}

	/**
	 * Populates the child sections for convenience in retrieval, but does not do anything that will affect the database.
	 *
	 * @param ProjectSection[] $sections
	 */
	public function cacheSubSections( $sections){
	 	$this->subsections = $sections;
	}

	/**
	 * @return ProjectSection[]
	 */
	public function getCachedSubsections(){
		return (array)$this->subsections;
	}

	/**
	 * Figures out what level this section is in the project (1 being the top-most, 2 being the one under that etc.)
	 * @return int
	 */
	public function getLevel(){
		// Cheat
		$manager = Context::instance()->reuse('PrintMyBlog\orm\managers\ProjectSectionManager');
		$parent_id = $this->getParentId();
		$level = 1;
		while($parent_id){
			$parent_id = $manager->getParentOf($parent_id);
			$level++;
		};
		return $level;
	}
}