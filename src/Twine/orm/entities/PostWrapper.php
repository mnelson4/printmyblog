<?php


namespace Twine\orm\entities;


use WP_Post;

class PostWrapper {
	/**
	 * @var WP_Post
	 */
	protected $wp_post;

	/**
	 * Project constructor.
	 *
	 * @param WP_Post|int $post object or ID
	 */
	public function __construct($post){
		if(is_int($post) || is_string($post)){
			$post = get_post($post);
		}
		$this->wp_post = $post;
	}

	/**
	 * @return WP_Post
	 */
	public function getWpPost() {
		return $this->wp_post;
	}

	/**
	 *
	 * return bool success
	 */
	public function delete()
	{
		return wp_delete_post($this->getWpPost()->ID);
	}
}