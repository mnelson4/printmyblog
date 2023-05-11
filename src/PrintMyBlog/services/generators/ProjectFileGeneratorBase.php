<?php

namespace PrintMyBlog\services\generators;

use Automattic\WooCommerce\Vendor\League\Container\Argument\ClassName;
use PrintMyBlog\compatibility\DetectAndActivate;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\services\ExternalResourceCache;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\services\SectionTemplateRegistry;
use ReflectionObject;
use stdClass;
use Twine\services\filesystem\File;
use WP_Post;
use WP_Query;
use WP_Screen;

/**
 * Class ProjectFileGeneratorBase
 * @package PrintMyBlog\services\generators
 */
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
     * @var ExternalResourceCache
     */
    protected $external_resource_cache;

    /**
     * ProjectHtmlGenerator constructor.
     *
     * @param ProjectGeneration $project_generation
     * @param Design $design
     */
    public function __construct(ProjectGeneration $project_generation, Design $design)
    {
        $this->project_generation = $project_generation;
        $this->project = $project_generation->getProject();
        $this->design = $design;
    }

    /**
     * Called by Context.
     * @param PostFetcher $post_fetcher
     * @param DetectAndActivate $plugin_compatibility
     * @param ExternalResourceCache $external_resource_cache
     */
    public function inject(
        PostFetcher $post_fetcher,
        DetectAndActivate $plugin_compatibility,
        ExternalResourceCache $external_resource_cache
    ) {
        $this->post_fetcher = $post_fetcher;
        $this->plugin_compatibility = $plugin_compatibility;
        $this->external_resource_cache = $external_resource_cache;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return bool whether complete or not
     */
    public function generateHtmlFile()
    {
        // Includes the design's functions.php file, if it exists
        if (file_exists($this->getDesignDir() . 'functions.php')) {
            include $this->getDesignDir() . 'functions.php';
        }

        $this->plugin_compatibility->activateRenderingCompatibilityModes();
        $this->startGenerating();
        // Don't let anything from a previous generation affect this one.
        $this->project_generation->setLastSectionId(null);
        $this->generateMatter();
        $this->finishGenerating();
        return true;

// If that's all the posts done, add the header and footer, using the scripts we enqueued.
// $total = $part_fetcher->countParts($this->getWpPost()->ID);
// if( $total >= $part_post_ids){
// $this->setGenerated(true);
// }
// return [
// 'done' => $part_post_ids,
// 'total' => $total
// ];
    }

    /**
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    protected function startGenerating()
    {
        // show protected posts' bodies as normal.
        add_filter('post_password_required', '__return_false');
        // don't add "protected" or "private" onto post titles when generating
        add_filter(
            'protected_title_format',
            function () {
                return '%s';
            }
        );
        add_filter(
            'private_title_format',
            function () {
                return '%s';
            }
        );
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->startGenerating', $this);
        register_shutdown_function(array( $this, 'shutdown' ));
    }

    /**
     * Generates for the current post in global $wp_post. We call WP_Query::the_post() just before calling this.
     * @global WP_Post $wp_post
     * @global Project $pmb_project
     * @global Design $pmb_design
     * @return void
     */
    abstract protected function generateSection();

    /**
     * @global WP_Post $wp_post
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    abstract protected function finishGenerating();

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
            $found = false;
            $post = null;
            foreach ($unordered_posts as $post) {
                $post_id_from_section = $section->getPostId();
                if ($post_id_from_section === $post->ID) {
                    $found = true;
                    break;
                }
            }
            // if the post is somehow missing from the query results, fix that. Especially helpful if a section was added via the filter.
            if (! $found) {
                $post = get_post($section->getPostId());
            } else {
                // use a clone so posts can have different section info (i.e., be included in different spots in the project)
                $post = clone $post;
            }
            if ($post) {
                $post->pmb_section = $section;
                $ordered_posts[] = $post;
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
    abstract protected function generateMatter();

    /**
     * @param ProjectSection[] $project_sections
     */
    protected function generateSections(array $project_sections)
    {
        global $post, $wp_the_query;
        // Override WP_Query global to generate sections like WP's "the loop".
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $wp_the_query = $this->setupWpQuery($project_sections);
        while ($wp_the_query->have_posts()) {
            $wp_the_query->the_post();
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
        // Show all the content at once, don't chop it up into pages.
        //phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
        $more = true;
        // Remove all pagebreak blocks and stitch it all back together.
        $content_ignoring_pages = implode('<br class="pmb-page-break">', $pages);
        $pages = [$content_ignoring_pages];
        $numpages = 1;
        $multipage = false;
        //phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
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
                'post_type' => $this->post_fetcher->getProjectPostTypes(),
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

    /**
     * @param ProjectSection|null $last_section
     * @param ProjectSection|null $current_section
     * @return void
     */
    abstract protected function maybeGenerateDivisionStart(
        ProjectSection $last_section = null,
        ProjectSection $current_section = null
    );

    /**
     * @param ProjectSection|null $previous_section
     * @param ProjectSection|null $current_section
     * @return void
     */
    abstract protected function maybeGenerateDivisionEnd(
        ProjectSection $previous_section = null,
        ProjectSection $current_section = null
    );

    /**
     * Gets a string of HTML from inluding the specified file.
     *
     * @param string $template_file
     *
     * @param array $context to be used in the template.
     *
     * @return false|string
     */
    protected function getHtmlFrom($template_file, $context = [])
    {
        global $post, $pmb_project, $pmb_design, $pmb_project_generation;
        // extract so all that context is available in the template file.
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract($context);
        $pmb_project = $this->project;
        $pmb_design = $this->design;
        $pmb_project_generation = $this->project_generation;
        do_action('\PrintMyBlog\services\generators\ProjectFileGeneratorBase->getHtmlFrom before_ob_start');
        ob_start();
        include $template_file;
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

    /**
     * Records any PHP fatal errors while generating the file.
     */
    public function shutdown()
    {
        // copy-aste from Fatal Error Notify plugin
        $error = error_get_last();

        if (is_null($error)) {
            return;
        }

        // A couple types of errors we don't need reported.

        if (E_WARNING === $error['type'] && strpos($error['message'], 'unlink')) {
            // a lot of plugins generate these because it's faster to unlink()
            // without checking if the file exists first, even if it creates a
            // warning.
            return;
        }

        $generation = $this->project_generation;
        if ($generation instanceof ProjectGeneration) {
            // This debug information is inteded for developers.
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
            $generation->setLastError(var_export($error, true));
        }
    }
}
