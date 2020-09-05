<?php


namespace PrintMyBlog\services;

use PrintMyBlog\orm\entities\Project;
use Twine\services\filesystem\File;
use WP_Post;
use WP_Query;

class ProjectHtmlGenerator {
	/**
	 * @var Project
	 */
	private $project;

	/**
	 * @var File
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
		// Includes the design's functions.php file, if it exists
		if(file_exists($this->getSelectedDesignDir() . 'functions.php')){
			include($this->getSelectedDesignDir() . 'functions.php');
		}
		$this->generateHtmlFileHeader();

		$this->sortPosts($query, $part_post_ids);
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
		wp_enqueue_style('pmb_print_common');
		wp_enqueue_style('pmb-plugin-compatibility');
		wp_enqueue_script('pmb-beautifier-functions');
		$style_file = $this->getSelectedDesignDir() . 'style.css';
		$script_file = $this->getSelectedDesignDir() . 'script.js';
		if(file_exists($style_file)){
			wp_enqueue_style(
				'pmb-design',
				$this->getSelectedDesignUrl() . 'style.css',
				['pmb_print_common', 'pmb-plugin-compatibility'],
				filemtime($style_file)
			);
		}
		if(file_exists($script_file)){
			wp_enqueue_script(
				'pmb-design',
				$this->getSelectedDesignUrl() . 'script.js',
				['jquery', 'pmb-beautifier-functions'],
				filemtime($script_file)
			);		}
		add_filter('wp_enqueue_scripts', [$this,'remove_theme_style'],20);
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

	public function remove_theme_style()
	{
		$active_theme_slug = get_stylesheet();
		wp_dequeue_style($active_theme_slug . '-style');
		wp_dequeue_style($active_theme_slug . '-print-style');
	}
	protected function generateHtmlFileFooter()
	{
		ob_start();
		include( $this->getSelectedDesignDir() . 'footer.php');
		$this->getFileWriter()->write(ob_get_clean());
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
			$this->addPostToHtmlFile();
		}
		wp_reset_postdata();
	}

	/**
	 * @return File
	 */
	protected function getFileWriter()
	{
		if(! $this->file_writer instanceof File){
			$this->file_writer = new File($this->project->generatedHtmlFilePath());
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