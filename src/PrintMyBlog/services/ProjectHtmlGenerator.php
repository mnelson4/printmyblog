<?php


namespace PrintMyBlog\services;

use PrintMyBlog\orm\Project;
use Twine\services\filesystem\FileWriter;
use WP_Post;
use WP_Query;

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

		$this->generateHtmlFileHeader();
		$this->addPostsToHtmlFile($posts);
		$this->generateHtmlFileFooter();
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

	protected function generateHtmlFileHeader()
	{
		global $pmb_project;
		$pmb_project = $this->project;
		$pmb_show_site_title = true;
		$pmb_show_site_tagline = false;
		$pmb_site_name = $pmb_project->getWpPost()->post_title;
		$pmb_site_description = '';
		$pmb_show_date_printed = true;
		$pmb_show_credit = true;
		ob_start();
		$file = $this->getSelectedDesignDir() . 'header.php';
		include( $file );
		$this->getFileWriter()->write(ob_get_clean());
	}

	protected function generateHtmlFileFooter()
	{
		ob_start();
		include( $this->getSelectedDesignDir() . 'footer.php');
		$this->getFileWriter()->write(ob_get_clean());
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
			$this->file_writer = new FileWriter($this->project->generatedHtmlFilePath());
		}
		return $this->file_writer;
	}

	protected function getSelectedDesignDir()
	{
		return PMB_DEFAULT_DESIGNS_DIR . 'classic/';
	}

	protected function addPostToHtmlFile(WP_Post $post_to_add)
	{
		global $post;
		$global_post = $post;
		$post = $post_to_add;
		ob_start();
		include( $this->getSelectedDesignDir() . 'section.php');
		$this->getFileWriter()->write(ob_get_clean());
		$post = $global_post;
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