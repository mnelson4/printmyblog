<?php


namespace PrintMyBlog\orm;


use PrintMyBlog\system\Context;
use WP_Post;

class ProjectManager {

	/**
	 * @param $project_id
	 *
	 * @return Project
	 */
	public function getById($project_id){
		$wp_post = get_post($project_id);
		$project = $this->createProjectFromWpPost($wp_post);
		return $project;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return Project
	 */
	protected function createProjectFromWpPost(WP_Post $post){
		$project = Context::instance()->use_new(
			'PrintMyBlog\orm\Project',
			[$post]
		);

		/**
		 * @var $project Project
		 */
		return $project;
	}

	/**
	 * @param int[] $ids
	 */
	public function deleteProjects($ids){
		foreach($ids as $id){
			$post = get_post($id);
			if(! current_user_can('delete_project', $post)){
				continue;
			}
			$project = $this->createProjectFromWpPost($post);
			if($project instanceof Project){
				$project->delete();
			}
		}
	}
}