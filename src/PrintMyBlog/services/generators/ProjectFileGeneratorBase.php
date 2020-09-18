<?php


namespace PrintMyBlog\services\generators;

use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectGeneration;
use Twine\services\filesystem\File;
use WP_Post;
use WP_Query;

abstract class ProjectFileGeneratorBase {
	/**
	 * @var Project
	 */
	protected $project;
	/**
	 * @var ProjectGeneration
	 */
	protected $project_generation;

	/**
	 * ProjectHtmlGenerator constructor.
	 *
	 * @param Project $project
	 */
	public function __construct(ProjectGeneration $project_generation){
		$this->project_generation = $project_generation;
		$this->project = $project_generation->getProject();
	}

	/**
	 * @return bool whether complete or not
	 */
	public function generateHtmlFile() {
		// If not, generate it...
		$part_post_ids = $this->project->getPartPostIds();
		// Fetch some of its posts at the same time...
		$query = new WP_Query(
			[
				'post__in' => $part_post_ids,
//				'showposts' => 10
			]
		);
		// Includes the design's functions.php file, if it exists
		if(file_exists($this->getSelectedDesignDir() . 'functions.php')){
			include($this->getSelectedDesignDir() . 'functions.php');
		}
		$this->startGenerating();

		$this->sortPosts($query, $part_post_ids);
		$this->addPostsToHtmlFile($query);
		$this->finishGenerating();
		return true;

//		// If that's all the posts done, add the header and footer, using the scripts we enqueued.
//		$total = $part_fetcher->countParts($this->getWpPost()->ID);
//		if( $total >= $part_post_ids){
//			$this->setGenerated(true);
//		}
//		return [
//			'done' => $part_post_ids,
//			'total' => $total
//		];
	}

	/**
	 * @global Project $pmb_project
	 * @global Design $pmb_design
	 */
	protected abstract function startGenerating();

	/**
	 * Generates for the current post in global $wp_post. We call WP_Query::the_post() just before calling this.
	 * @global WP_Post $wp_post
	 * @global Project $pmb_project
	 * @global Design $pmb_design
	 * @return bool success
	 */
	protected abstract function generatePost();

	/**
	 * @global WP_Post $wp_post
	 * @global Project $pmb_project
	 * @global Design $pmb_design
	 * @return bool
	 */
	protected abstract function finishGenerating();

	public function remove_theme_style()
	{
		$active_theme_slug = get_stylesheet();
		wp_dequeue_style($active_theme_slug . '-style');
		wp_dequeue_style($active_theme_slug . '-print-style');
	}

	/**
	 * Orders $query->posts according to the order specified by $post_ids_in_order
	 * @param WP_Query $query
	 * @param $post_ids_in_order
	 */
	protected function sortPosts(WP_Query $query, $post_ids_in_order){
		$ordered_posts = [];
		$unordered_posts = $query->posts;
		foreach($post_ids_in_order as $post_id){
			foreach($unordered_posts as $post){
				if($post_id == $post->ID){
					$ordered_posts[] = $post;
				}
			}
		}
		$query->posts = $ordered_posts;
	}

	/**
	 * @param WP_Post[] $posts
	 */
	protected function addPostsToHtmlFile(WP_Query $query) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$this->generatePost();
		}
		wp_reset_postdata();
	}

	protected function getSelectedDesignSlug()
	{
		return 'classic';
	}
	protected function getSelectedDesignDir()
	{
		return PMB_DEFAULT_DESIGNS_DIR . $this->getSelectedDesignSlug() . '/';
	}
	protected function getSelectedDesignUrl()
	{
		return PMB_DEFAULT_DESIGNS_URL .$this->getSelectedDesignSlug() . '/';
	}

	/**
	 * Deletes the generated HTML file, if it exists.
	 * @return bool
	 */
	public function deleteHtmlFile()
	{
		return $this->getFileWriter()->delete();
	}
}