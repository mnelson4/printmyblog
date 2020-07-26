<?php


namespace PrintMyBlog\orm;

/**
 * Class Project
 * @package PrintMyBlog\orm
 * Class that wraps a WP_Post, but also stores related info like parts, and has related methods.
 */
class Project {
	/**
	 * @var WP_Post
	 */
	protected $wp_post;
	public function __construct(WP_Post $post){
		$this->wp_post = $post;
	}

	/**
	 * @return WP_Post
	 */
	public function getWpPost() {
		return $this->wp_post;
	}
}