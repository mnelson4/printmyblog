<?php


namespace Twine\orm\managers;


use PrintMyBlog\system\Context;
use WP_Post;

class PostWrapperManager {
	protected $class_to_instantiate;
	protected $cap_slug;
	/**
	 * @param $post_id
	 *
	 * @return PostWrapperManager
	 */
	public function getById($post_id){
		$wp_post = get_post($post_id);
		$post_wrapper = $this->createProjectFromWpPost($wp_post);
		return $post_wrapper;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return PostWrapperManager
	 */
	protected function createProjectFromWpPost(WP_Post $post){
		$post_wrapper = Context::instance()->use_new(
			$this->class_to_instantiate,
			[$post]
		);

		/**
		 * @var $post_wrapper PostWrapperManager
		 */
		return $post_wrapper;
	}

	/**
	 * @param int[] $ids
	 */
	public function deleteProjects($ids){
		foreach($ids as $id){
			$post = get_post($id);
			if(! current_user_can('delete_' . $this->cap_slug, $post)){
				continue;
			}
			$project = $this->createProjectFromWpPost($post);
			if($project instanceof Project){
				$project->delete();
			}
		}
	}
}