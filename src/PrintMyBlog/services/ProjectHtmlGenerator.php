<?php


namespace PrintMyBlog\services;

use PrintMyBlog\orm\Project;
use Twine\services\filesystem\FileWriter;

class ProjectHtmlGenerator {
	/**
	 * @var Project
	 */
	private $project;

	/**
	 * @var FileWriter
	 */
	private $file_writer;

	/**
	 * ProjectHtmlGenerator constructor.
	 *
	 * @param Project $project
	 */
	public function __construct(Project $project){

		$this->project = $project;
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
		$posts = $query->get_posts();

		$this->addPostsToHtmlFile($posts);

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
	 * @param WP_Post[] $posts
	 */
	protected function addPostsToHtmlFile($posts)
	{
		foreach($posts as $post){
			$this->addPostToHtmlFile($post);
		}
	}

	/**
	 * @return FileWriter
	 */
	protected function getFileWriter()
	{
		if(! $this->file_writer instanceof FileWriter){
			$this->file_writer = new FileWriter($this->project->generatedHtmlFileUrl());
		}
		return $this->file_writer;
	}

	protected function addPostToHtmlFile(WP_Post $post)
	{
		$this->getFileWriter()->append(
			apply_filters('the_content',
				get_the_content(null, false, $post)
			)
		);
	}
}