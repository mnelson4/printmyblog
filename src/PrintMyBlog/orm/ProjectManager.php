<?php


namespace PrintMyBlog\orm;


class ProjectManager {
	public function getById($project_id){
		$wp_post = get_post($project_id);
	}

	protected function createProjectFromWpPost(WP_Post $post){
		$project = new Project($post);
		return $project;
	}
}