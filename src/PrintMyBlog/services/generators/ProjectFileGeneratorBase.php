<?php

namespace PrintMyBlog\services\generators;

use PrintMyBlog\compatibility\DetectAndActivate;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\services\SectionTemplateRegistry;
use stdClass;
use WP_Post;
use WP_Query;

abstract class ProjectFileGeneratorBase
{
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
     * @var DetectAndActivate
     */
    private $plugin_compatibility;
    /**
     * @var SectionTemplateRegistry
     */
    protected $section_template_registry;

    /**
     * ProjectHtmlGenerator constructor.
     *
     * @param Project $project
     */
    public function __construct(ProjectGeneration $project_generation, Design $design)
    {
        $this->project_generation = $project_generation;
        $this->project = $project_generation->getProject();
        $this->design = $design;
    }

    public function inject(
        PostFetcher $post_fetcher,
        DetectAndActivate $plugin_compatibility
    ) {
        $this->post_fetcher = $post_fetcher;
        $this->plugin_compatibility = $plugin_compatibility;
    }

    /**
     * @return bool whether complete or not
     */
    public function generateHtmlFile()
    {
        // Includes the design's functions.php file, if it exists
        if (file_exists($this->getDesignDir() . 'functions.php')) {
            include($this->getDesignDir() . 'functions.php');
        }

        $this->plugin_compatibility->activateRenderingCompatibilityModes();
        $this->startGenerating();
        // Don't let anything from a previous generation affect this one.
        $this->project_generation->setLastSectionId(null);
        $this->generateMainMatter();
        $this->finishGenerating();
        return true;

//      // If that's all the posts done, add the header and footer, using the scripts we enqueued.
//      $total = $part_fetcher->countParts($this->getWpPost()->ID);
//      if( $total >= $part_post_ids){
//          $this->setGenerated(true);
//      }
//      return [
//          'done' => $part_post_ids,
//          'total' => $total
//      ];
    }

    /**
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    protected function startGenerating()
    {
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->startGenerating', $this);
        // show protected posts' bodies as normal.
        add_filter('post_password_required', '__return_false');
    }

    /**
     * Generates for the current post in global $wp_post. We call WP_Query::the_post() just before calling this.
     * @global WP_Post $wp_post
     * @global Project $pmb_project
     * @global Design $pmb_design
     * @return bool success
     */
    abstract protected function generateSection();

    /**
     * @global WP_Post $wp_post
     * @global Project $pmb_project
     * @global Design $pmb_design
     * @return bool
     */
    abstract protected function finishGenerating();

    /**
     * Dequeues the active theme's styles by guessing that all their styles are registered with their name in it.
     */
//  public function remove_theme_style()
//  {
//      $all_styles = wp_styles();
//      $active_theme_slug = get_stylesheet();
//      foreach($all_styles->queue as $handle){
//          if(strpos($handle, $active_theme_slug) !== false){
//              wp_dequeue_style($handle);
//          }
//      }
//  }

    /**
     * Orders $query->posts according to the order specified by $post_ids_in_order
     *
     * @param WP_Query $query
     * @param ProjectSection[] $sections
     *
     * @return void but updates $query->posts by putting the posts in the order
     * indicated by $project_parts, and also gives each post a new property "pmb_part" which is a ProjectSection
     */
    protected function sortPostsAndAttachSections(WP_Query $query, $sections)
    {
        $ordered_posts = [];
        $unordered_posts = $query->posts;
        foreach (apply_filters('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->sortPostsAndAttachSections $sections', $sections, $this, $unordered_posts) as $section) {
            foreach ($unordered_posts as $post_from_wp_query) {
                $post_id_from_section = $section->getPostId();
                if ( $post_id_from_section == $post_from_wp_query->ID) {
                    $post_from_wp_query->pmb_section = $section;
                    $ordered_posts[] = $post_from_wp_query;
                }
            }
        }
        $query->posts = $ordered_posts;
        $query->post_count = count($ordered_posts);
        $query->found_posts = count($ordered_posts);
    }

    /**
     * Generates the main matter of the project. May be called repeatedly.
     * @return void
     */
    abstract protected function generateMainMatter();

    /**
     * @param ProjectSection[] $project_sections
     */
    protected function generateSections(array $project_sections)
    {
        global $post, $wp_query;
        $wp_query = $this->setupWpQuery($project_sections);
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $this->setupPostData();
            $this->maybeGenerateDivisionTransition($post);
            $this->generateSection();
        }
        wp_reset_postdata();
    }

    /**
     * Setup WordPress post-related globals correctly for PMB
     */
    protected function setupPostData()
    {
        global $more, $multipage, $pages, $numpages;
        // we want to see what's after "more" tags
        $more = true;
        // Remove all pagebreak blocks and stitch it all back together.
        $content_ignoring_pages = implode('<br class="pmb-page-break">', $pages);
        $pages = [$content_ignoring_pages];
        $numpages = 1;
        $multipage = false;
    }

    /**
     * @param ProjectSection[] $project_sections
     *
     * @return WP_Query
     */
    protected function setupWpQuery(array $project_sections)
    {
        $post_ids = array_map(
            function ($item) {
                return $item->getPostId();
            },
            $project_sections
        );
        // Fetch some of its posts at the same time...
        $query = new WP_Query(
            [
                'post_status' => 'any',
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
    protected function maybeGenerateDivisionTransition(WP_Post $post)
    {
        $last_section = $this->project_generation->getLastSection();
        $this->maybeGenerateDivisionEnd($last_section, $post->pmb_section);
        $this->maybeGenerateDivisionStart($last_section, $post->pmb_section);
    }

    abstract protected function maybeGenerateDivisionStart(
        ProjectSection $last_section = null,
        ProjectSection $current_section = null
    );


    abstract protected function maybeGenerateDivisionEnd(
        ProjectSection $previous_section = null,
        ProjectSection $current_section = null
    );

    /**
     * Gets a string of HTML from inluding the specified file.
     *
     * @param $template_file
     *
     * @param array $context to be used in the template.
     *
     * @return false|string
     */
    protected function getHtmlFrom($template_file, $context = [])
    {
        global $post, $pmb_project, $pmb_design, $pmb_project_generation;
        extract($context);
        $pmb_project = $this->project;
        $pmb_design = $this->design;
        $pmb_project_generation = $this->project_generation;
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom before_ob_start');
        ob_start();
        include($template_file);
        // if Oxygen page builder is active, clear ALL buffers. It starts a buffer and then clears it in the footer
        // somehow resulting in the HTML head getting echoed into the JSON response instead of being added to the top
        // of the print page). Avoid all that by clearing its buffer immediately.
        if (
            apply_filters(
                'PrintMyBlog\services\generators\ProjectFileGeneratorBase::getHtmlFrom clean_multiple_buffers',
                defined('CT_VERSION')
            )
        ) {
            $str = $this->obGetAllClean();
        } else {
            $str = ob_get_clean();
        }
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom after_get_clean');

        return $str;
    }

    /**
     * Gets and cleans ALL buffers (like wp_ob_end_flush_all but gets instead of flushing)
     * @return string
     */
    protected function obGetAllClean()
    {
        $output = '';
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; $i++) {
            $output .= ob_get_clean();
        }
        return $output;
    }

    /**
     * Gets the level "height" (how many levels there are under) of a section.
     * @param ProjectSection $section
     *
     * @return string
     */
    protected function getLevel(ProjectSection $section)
    {
        $level = $this->project_generation->getLastSectionId();
        if (! $level) {
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
     * @return bool
     */
    abstract public function deleteFile();
}
