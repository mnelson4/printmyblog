<?php


namespace PrintMyBlog\orm;


use PrintMyBlog\system\Context;

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

	protected function createProjectFromWpPost(WP_Post $post){
		$project = Context::instance()->use_new(
			'PrintMyBlog\orm\Project',
			[$post]
		);
		return $project;
	}
}