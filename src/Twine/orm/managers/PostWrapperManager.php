<?php


namespace Twine\orm\managers;


use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\system\Context;
use Twine\orm\entities\PostWrapper;
use WP_Post;
use WP_Query;

class PostWrapperManager {
	protected $class_to_instantiate;
	protected $cap_slug;
	/**
	 * @param $post_id
	 *
	 * @return PostWrapper
	 */
	public function getById($post_id){
		$wp_post = get_post($post_id);
		$post_wrapper = $this->createWrapperAroundPost($wp_post);
		return $post_wrapper;
	}

	/**
	 * @param $slug
	 * @return PostWrapper|null
	 */
	public function getBySlug($slug){
		$post_object = get_page_by_path($slug, OBJECT, $this->cap_slug);
		if($post_object instanceof WP_Post){
			return $this->createWrapperAroundPost($post_object);
		}
		return null;
	}

	/**
	 * @param WP_Query $query
	 *
	 * @return array|PostWrapperManager[]
	 */
	public function getAll(WP_Query $query = null){
		if(! $query instanceof WP_Query){
			$query = new WP_Query();
		}
		$query->set('post_type',$this->cap_slug);
		$posts = $query->get_posts();
		$wrappers = [];
		foreach($posts as $post){
			$wrappers[] = $this->createWrapperAroundPost($post);
		}
		return $wrappers;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return PostWrapper
	 */
	protected function createWrapperAroundPost(WP_Post $post){
		$post_wrapper = Context::instance()->use_new(
			$this->class_to_instantiate,
			[$post]
		);

		/**
		 * @var $post_wrapper PostWrapper
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
			$project = $this->createWrapperAroundPost($post);
			if($project instanceof Project){
				$project->delete();
			}
		}
	}
}