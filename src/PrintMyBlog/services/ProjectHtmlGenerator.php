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

		$this->generateHtmlFileHeader();
		$this->addPostsToHtmlFile($query);
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
		wp_enqueue_style(
			'pmb-design' . $this->getSelectedDesignSlug(),
			$this->getSelectedDesignUrl() . 'style.css',
			['pmb_print_common'],
			filemtime($this->getSelectedDesignDir() . 'style.css')
		);
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
	protected function addPostsToHtmlFile(WP_Query $query) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$this->addPostToHtmlFile();
		}
		wp_reset_postdata();
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

	protected function addPostToHtmlFile()
	{
		ob_start();
		include( $this->getSelectedDesignDir() . 'section.php');
		$this->getFileWriter()->write(ob_get_clean());
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