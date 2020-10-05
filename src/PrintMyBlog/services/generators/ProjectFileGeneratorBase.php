<?php


namespace PrintMyBlog\services\generators;

use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\ProjectSection;
use stdClass;
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
	 * @var Design
	 */
	protected $design;
	/**
	 * @var PostFetcher
	 */
	protected $post_fetcher;

	/**
	 * ProjectHtmlGenerator constructor.
	 *
	 * @param Project $project
	 */
	public function __construct(ProjectGeneration $project_generation, Design $design){
		$this->project_generation = $project_generation;
		$this->project = $project_generation->getProject();
		$this->design = $design;
	}

	public function inject(PostFetcher $post_fetcher){
		$this->post_fetcher = $post_fetcher;
	}

	/**
	 * @return bool whether complete or not
	 */
	public function generateHtmlFile() {
		// Includes the design's functions.php file, if it exists
		if(file_exists( $this->getDesignDir() . 'functions.php')){
			include( $this->getDesignDir() . 'functions.php');
		}

		$this->startGenerating();
		// Don't let anything from a previous generation affect this one.
		$this->project_generation->setLastSectionId(null);
		$this->maybeGenerateFrontMatter();
		$this->generateMainMatter();
		$this->maybeGenerateBackMatter();
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
	 * @return bool
	 */
	protected function maybeGenerateFrontMatter(){
		$front_matter = $this->project->getSections(1000,0,false,DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER);
		if($this->design->getDesignTemplate()->supports(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER)
			&& $front_matter){
			$this->generateFrontMatter($front_matter);
		}
	}

	/**
	 * @param array $project_sections
	 *
	 * @return bool
	 */
	protected abstract function generateFrontMatter(array $project_sections);

	/**
	 * @return bool
	 */
	protected function maybeGenerateBackMatter(){
		$sections = $this->project->getSections(1000,0,false,DesignTemplate::IMPLIED_DIVISION_BACK_MATTER);
		if($this->design->getDesignTemplate()->supports(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER)
		   && $sections){
			$this->generateBackMatter($sections);
		}
	}

	protected abstract function generateBackMatter(array $project_sections);

	/**
	 * Generates for the current post in global $wp_post. We call WP_Query::the_post() just before calling this.
	 * @global WP_Post $wp_post
	 * @global Project $pmb_project
	 * @global Design $pmb_design
	 * @return bool success
	 */
	protected abstract function generateSection();

	/**
	 * @global WP_Post $wp_post
	 * @global Project $pmb_project
	 * @global Design $pmb_design
	 * @return bool
	 */
	protected abstract function finishGenerating();

	public function remove_theme_style()
	{
		$all_styles = wp_styles();
		$active_theme_slug = get_stylesheet();
		foreach($all_styles->queue as $handle){
			if(strpos($handle, $active_theme_slug) !== false){
				wp_dequeue_style($handle);
			}
		}

//		wp_dequeue_style($active_theme_slug . '-style');
//		wp_dequeue_style($active_theme_slug . '-print-style');
	}

	/**
	 * Orders $query->posts according to the order specified by $post_ids_in_order
	 *
	 * @param WP_Query $query
	 * @param ProjectSection[] $sections
	 *
	 * @return void but updates $query->posts by putting the posts in the order
	 * indicated by $project_parts, and also gives each post a new property "pmb_part" which is a ProjectSection
	 */
	protected function sortPostsAndAttachSections(WP_Query $query, $sections){
		$ordered_posts = [];
		$unordered_posts = $query->posts;
		foreach ( $sections as $section){
			foreach($unordered_posts as $post){
				if($section->getPostId() == $post->ID){
					$post->pmb_section = $section;
					$ordered_posts[] = $post;
				}
			}
		}
		$query->posts = $ordered_posts;
	}

	/**
	 * Generates the main matter of the project. May be called repeatedly.
	 * @return void
	 */
	protected abstract function generateMainMatter();

	/**
	 * @param ProjectSection[] $project_sections
	 */
	protected function generateSections(array $project_sections) {
		$query = $this->setupWpQuery($project_sections);
		global $post;
		while ( $query->have_posts() ) {
			$query->the_post();
			$this->maybeGenerateDivisionTransition($post);
			$this->generateSection();
		}
		wp_reset_postdata();
	}

	/**
	 * @param ProjectSection[] $project_sections
	 *
	 * @return WP_Query
	 */
	protected function setupWpQuery(array $project_sections){
		$post_ids = array_map(
			function($item){
				return $item->getPostId();
			},
			$project_sections
		);
		// Fetch some of its posts at the same time...
		$query = new WP_Query(
			[
				'post__in' => $post_ids,
				'showposts' => count($post_ids),
				'post_type' => $this->post_fetcher->getProjectPostTypes()
			]
		);
		$this->sortPostsAndAttachSections($query, $project_sections);
		return $query;
	}

	/**
	 *
	 * @param WP_Post $post with an attached ProjectSection on the property pmb_section
	 *
	 * @return void
	 */
	protected function maybeGenerateDivisionTransition(WP_Post $post){
		$last_section = $this->project_generation->getLastSection();
		if(! $last_section){
			// no transition necessary
			return;
		}

		$this->generateDivisionEnd($last_section, $post->pmb_section);
	}


	protected abstract function generateDivisionEnd(ProjectSection $previous_section, ProjectSection $current_section);

	/**
	 * Gets a string of HTML from inluding the specified file.
	 *
	 * @param $template_file
	 *
	 * @param array $template_variables to be used in the template.
	 *
	 * @return false|string
	 */
	protected function getHtmlFrom($template_file){
		global $post, $pmb_project, $pmb_design, $pmb_project_generation;
		$pmb_project = $this->project;
		$pmb_design = $this->design;
		$pmb_project_generation = $this->project_generation;
		ob_start();
		include($template_file);
		return ob_get_clean();
	}

	/**
	 * Gets the level "height" (how many levels there are under) of a section.
	 * @param ProjectSection $section
	 *
	 * @return string
	 */
	protected function getLevel(ProjectSection $section){
		$level = $this->project_generation->getLastSectionId();
		if( ! $level){
			$level = $section->getLevel();
			$this->project_generation->setLastSectionId($level);
		}
		return $level;
	}

	/**
	 * @return \PrintMyBlog\orm\entities\Design|null
	 */
	protected function getDesign()
	{
		return $this->design;
	}

	/**
	 * Gets the base path to the directory of the design template's files.
	 * @return string
	 */
	protected function getDesignDir()
	{
		return $this->getDesign()->getDesignTemplate()->getDir();
	}

	/**
	 * Gets the base URL of the design template's files
	 * @return string
	 */
	protected function getDesignAssetsUrl()
	{
		return $this->getDesign()->getDesignTemplate()->getAssetsUrl();
	}

	/**
	 * Deletes the generated HTML file, if it exists.
	 * @return bool
	 */
	public function deleteFile()
	{
		return $this->getFileWriter()->delete();
	}
}