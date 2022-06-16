<?php

namespace PrintMyBlog\controllers;

use mnelson4\rest_api_detector\RestApiDetector;
use mnelson4\rest_api_detector\RestApiDetectorError;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\ExternalResourceCache;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\system\Context;
use PrintMyBlog\system\CustomPostTypes;
use Twine\controllers\BaseController;
use Twine\helpers\Array2;
use Twine\orm\managers\PostWrapperManager;
use Twine\services\filesystem\File;
use WP_Error;
use WP_Post_Type;
use WP_Query;
use PrintMyBlog\orm\entities\Project;

/**
 * Class PmbAjax
 *
 * Handles AJAX requests
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class Ajax extends BaseController
{
    /**
     * @var ProjectManager
     */
    protected $project_manager;
    /**
     * @var FileFormatRegistry
     */
    protected $format_registry;

    /**
     * @var PostFetcher
     */
    protected $post_fetcher;
    /**
     * @var PmbCentral
     */
    protected $pmb_central;
    /**
     * @var PostWrapperManager
     */
    protected $post_manager;

    /**
     * @var ExternalResourceCache
     */
    protected $external_resouce_cache;

    /**
     * @param ProjectManager $project_manager
     * @param FileFormatRegistry $format_registry
     * @param PostFetcher $post_fetcher
     * @param PmbCentral $pmb_central
     * @param PostWrapperManager $post_manager
     * @param ExternalResourceCache $external_resource_map
     */
    public function inject(
        ProjectManager $project_manager,
        FileFormatRegistry $format_registry,
        PostFetcher $post_fetcher,
        PmbCentral $pmb_central,
        PostWrapperManager $post_manager,
        ExternalResourceCache $external_resource_map
    ) {
        $this->project_manager = $project_manager;
        $this->format_registry = $format_registry;
        $this->post_fetcher = $post_fetcher;
        $this->pmb_central = $pmb_central;
        $this->post_manager = $post_manager;
        $this->external_resouce_cache = $external_resource_map;
    }

    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        $this->addUnauthenticatedCallback(
            'pmb_fetch_external_resource',
            'handleFetchExternalResource'
        );
        $this->addUnauthenticatedCallback(
            'pmb_fetch_rest_api_url',
            'handleFetchRestApiUrl'
        );
        $this->addUnauthenticatedCallback(
            'pmb_project_status',
            'handleProjectStatus'
        );
        add_action('wp_ajax_pmb_post_search', [$this, 'handlePostSearch']);
        add_action('wp_ajax_pmb_add_print_material', [$this, 'addPrintMaterial']);
        add_action('wp_ajax_pmb_reduce_credits', [$this, 'reduceCredits']);
        add_action('wp_ajax_pmb_report_error', [$this, 'reportError']);
        add_action('wp_ajax_pmb_duplicate_print_material', [$this, 'duplicatePrintMaterial']);
    }

    /**
     * @param string $ajax_action
     * @param string $method_name
     */
    protected function addUnauthenticatedCallback($ajax_action, $method_name)
    {
        $callback = [$this, $method_name];
        add_action('wp_ajax_' . $ajax_action, $callback);
        add_action('wp_ajax_nopriv_' . $ajax_action, $callback);
    }

    /**
     * Handles a request to get a REST API url.
     */
    public function handleFetchRestApiUrl()
    {
        try {
            // Use nonce set in \PrintMyBlog\controllers\Common::registerCommonStuff() and passed in setup-page.js's PmbSetupPage::updateRestApiUrl
            if (! isset($_POST['_nonce']) || ! wp_verify_nonce(sanitize_key($_POST['_nonce']), 'wp_rest')) {
                wp_send_json_error(
                    [
                        'error' => 'nonce_failure',
                        'message' => 'Nonce failure',
                    ]
                );
            }
            $rest_api_detector = new RestApiDetector(isset($_POST['site']) ? esc_url_raw(wp_unslash($_POST['site'])) : '');
        } catch (RestApiDetectorError $error) {
            wp_send_json_error(
                [
                    'error' => $error->stringCode(),
                    'message' => $error->getMessage(),
                ]
            );
        }
        wp_send_json_success(
            [
                'name' => $rest_api_detector->getName(),
                'site' => $rest_api_detector->getSite(),
                'proxy_for' => $rest_api_detector->getRestApiUrl(),
                'is_local' => $rest_api_detector->isLocal(),
            ]
        );
    }

    /**
     * Proceeds with loading printing a project and returns a response indicating the status.
     */
    public function handleProjectStatus()
    {
        if (! isset($_POST['_nonce']) || ! wp_verify_nonce(sanitize_key($_POST['_nonce']), 'pmb-project-edit')) {
            wp_send_json_error(
                [
                    'error' => 'nonce_failure',
                    'message' => 'Nonce failure',
                ]
            );
            return;
        }
        // report errors please
        if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            // We want to see errors, so make sure they're set to display.
            // phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.PHP.IniSet.display_errors_Blacklisted
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            // phpcs:enable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.PHP.IniSet.display_errors_Blacklisted
        }
        // Find project by ID.
        // @var $project Project just so PHPstorm knows what it's dealing with.
        $project = $this->project_manager->getById((int)Array2::setOr($_REQUEST, 'ID', ''));
        $format = $this->format_registry->getFormat(sanitize_key(Array2::setOr($_REQUEST, 'format', '')));
        // @var $project_generation ProjectGeneration just so PHPstorm knows what I'm doing.
        $project_generation = $project->getGenerationFor($format);
        $project_generation->deleteGeneratedFiles();
        $project_generation->clearDirty();
        $project->getProgress()->markStepComplete(ProjectProgress::GENERATE_STEP);
        $done = $project_generation->generateIntermediaryFile();
        if ($done) {
            $url = $project_generation->getGeneratedIntermediaryFileUrl();
        } else {
            $url = null;
        }

        // If we're all done, return the file.
        $response = [
            'url' => $url,
            'media' => $format->slug() === 'digital_pdf' ? 'screen' : 'print',
        ];

        wp_send_json($response);
        exit;
    }

    /**
     * Gets results when searching for a post.
     */
    public function handlePostSearch()
    {
        if (! isset($_GET['_wpnonce']) || ! check_admin_referer('pmb-project-edit')) {
            ?>
            <div class="pmb-no-results no-drag">
                <?php
                esc_html_e(
                    'Nonce failure. Please refresh the page.',
                    'print-my-blog'
                );
                ?>
            </div>
            <?php
        }
        $requested_posts = 50;
        $query_params = [
            'posts_per_page' => $requested_posts,
            'ignore_sticky_posts' => true,
        ];
        $project = $this->project_manager->getById(Array2::setOr($_GET, 'project', 0));
        if (! empty($_GET['page'])) {
            $query_params['paged'] = (int)$_GET['page'];
            $page = $query_params['paged'];
        } else {
            $page = 1;
        }
        if (! empty($_GET['pmb-search'])) {
            $query_params['s'] = sanitize_text_field(wp_unslash($_GET['pmb-search']));
        }
        if (! empty($_GET['pmb-post-type'])) {
            $query_params['post_type'] = sanitize_key($_GET['pmb-post-type']);
        } else {
            $query_params['post_type'] = $this->post_fetcher->getProjectPostTypes('names');
        }
        if (! empty($_GET['pmb-status'])) {
            $query_params['post_status'] = array_map(
                function ($post_status) {
                    return sanitize_key($post_status);
                },
                // Calm down PHPCS. We're sanitizing right now.
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $_GET['pmb-status']
            );
        }
        if (! empty($_GET['taxonomies'])) {
            $tax_query = [];
            // Calm down PHPCS. We're sanitizing right now.
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            foreach ($_GET['taxonomies'] as $taxonomy => $ids) {
                $tax_query[] = [
                    'taxonomy' => sanitize_key($taxonomy),
                    'field' => 'term_id',
                    'terms' => array_map(
                        function ($taxonomy_id) {
                            return (int)$taxonomy_id;
                        },
                        $ids
                    ),
                ];
            }
            if (! empty($tax_query)) {
                // Sorry, yes using tax query might make this query slow. But folks legitimately might want this.
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                $query_params['tax_query'] = $tax_query;
            }
        }
        if (! empty($_GET['pmb-author'])) {
            $query_params['author'] = (int)$_GET['pmb-author'];
        }
        $date_query = [];
        if (! empty($_GET['pmb-date'])) {
            if (! empty($_GET['pmb-date']['from'])) {
                $date_query['after'] = sanitize_key($_GET['pmb-date']['from']);
            }
            if (! empty($_GET['pmb-date']['to'])) {
                $date_query['before'] = sanitize_key($_GET['pmb-date']['to']);
            }
            if ($date_query) {
                $date_query['inclusive'] = true;
                $query_params['date_query'] = $date_query;
            }
        }
        if (! empty($_GET['exclude'])) {
            $query_params['post__not_in'] = array_map(
                'intval',
                // Calm down PHPCS. We're sanitizing right now.
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                explode(',', $_GET['exclude'])
            );
        }
        if (! empty($_GET['pmb-order-by'])) {
            $query_params['orderby'] = sanitize_key($_GET['pmb-order-by']);
        }
        if (! empty($_GET['pmb-order'])) {
            $query_params['order'] = $_GET['pmb-order'] === 'ASC' ? 'ASC' : 'DESC';
        }
        $posts = get_posts(apply_filters('\PrintMyBlog\controllers\Ajax->handlePostSearch $query_params', $query_params));
        $posts = apply_filters('\PrintMyBlog\controllers\Ajax->handlePostSearch $posts', $posts, $query_params);
        foreach ($posts as $post) {
            pmb_content_item($post, $project, 0);
        }
        if ($requested_posts === count($posts)) {
            ?>
            <div class="pmb-show-more">
                <div
                        class="load-more-button button no-drag"
                        tabindex="0" id="pmb-load-more"
                        data-page="<?php echo esc_attr($page + 1); ?>"><span
                            class="dashicons dashicons-arrow-down-alt"></span>
                    <?php esc_html_e('Show more...', 'print-my-blog'); ?>
                </div>
            </div>
            <?php
        } elseif (count($posts) === 0 && (int)$page === 1) {
            ?>
            <div class="pmb-no-results no-drag">
                <?php
                esc_html_e(
                    'No results. Try changing your search and filter criteria.',
                    'print-my-blog'
                );
                ?>
            </div>
            <?php
        }
        exit;
    }

    /**
     * Adds a print material from the editing content page.
     */
    public function addPrintMaterial()
    {
        if (! check_admin_referer('pmb-project-edit')) {
            return wp_send_json_error(
                [
                    'code' => 'nonce_failure',
                    'message' => __('Nonce failure', 'print-my-blog'),
                ]
            );
        }
        if (! current_user_can('publish_pmb_contents')) {
            return wp_send_json_error(
                [
                    'code' => 'unauthorized',
                    'message' => __('You do not have sufficient permissions to do this.', 'print-my-blog'),
                ]
            );
        }
        $title = Array2::setOr($_REQUEST, 'title', '');
        $project_id = Array2::setOr($_REQUEST, 'project', '');
        $project = $this->project_manager->getById($project_id);
        $post_id = wp_insert_post(
            [
                'post_title' => $title,
                'post_status' => 'private',
                'post_type' => CustomPostTypes::CONTENT,
            ]
        );
        $post = get_post($post_id);
        ob_start();
        pmb_content_item($post, $project);
        $html = ob_get_clean();
        wp_send_json_success(
            [
                'html' => $html,
                'post_ID' => $post_id,
            ]
        );
        exit;
    }

    /**
     * Handles a request to duplicate a post as a print material and then returns the HTML of the row in the content-editing step.
     */
    public function duplicatePrintMaterial()
    {
        if (! check_admin_referer('pmb-project-edit')) {
            return wp_send_json_error(
                [
                    'code' => 'nonce_failure',
                    'message' => __('Nonce failure', 'print-my-blog'),
                ]
            );
        }
        if (! current_user_can('publish_pmb_contents')) {
            return wp_send_json_error(
                [
                    'code' => 'unauthorized',
                    'message' => __('You do not have sufficient permissions to do this.', 'print-my-blog'),
                ]
            );
        }
        $post_id = (int)Array2::setOr($_REQUEST, 'id', '');
        $project_id = (int)Array2::setOr($_REQUEST, 'project', 0);
        $project = $this->project_manager->getById($project_id);
        $wrapped_post = $this->post_manager->getById($post_id);
        // check if a duplicate was already made
        $print_materials = $this->post_manager->getByPostMeta('_pmb_original_post', (string)$post_id, 1);
        if ($print_materials) {
            $print_material = reset($print_materials);
            $print_material_post = $print_material->getWpPost();
        } else {
            $print_material_post = $wrapped_post->duplicateAsPrintMaterial();
        }
        ob_start();
        pmb_content_item($print_material_post, $project);
        $html = ob_get_clean();
        wp_send_json_success(
            [
                'html' => $html,
                'post_ID' => $print_material->ID,
            ]
        );
        exit;
    }

    /**
     * Reduces the cached amount of credits this site THINKS it has (what really matters is how many the server
     * says we have.)
     */
    public function reduceCredits()
    {
        if (! check_admin_referer('pmb_pro_page')) {
            return wp_send_json_error(
                [
                    'code' => 'nonce_failure',
                    'message' => __('Nonce failure', 'print-my-blog'),
                ]
            );
        }
        $updated_credit_info = $this->pmb_central->reduceCredits(pmb_fs()->_get_license()->id);
        wp_send_json_success(
            $updated_credit_info
        );
        exit;
    }

    /**
     * Reports an error that was seen client-side to the server (e.g., an API error with DocRaptor)
     */
    public function reportError()
    {
        if (! check_admin_referer('pmb_pro_page')) {
            return wp_send_json_error(
                [
                    'code' => 'nonce_failure',
                    'message' => __('Nonce failure', 'print-my-blog'),
                ]
            );
        }
        $project_id = (int)Array2::setOr($_REQUEST, 'project_id', '');
        $format = sanitize_key(Array2::setOr($_REQUEST, 'format', ''));
        $error_message = esc_html(Array2::setOr($_REQUEST, 'error', ''));
        $project = $this->project_manager->getById($project_id);
        if (! $project) {
            wp_send_json_error(
                [
                    'error' => 'Could not find project with ID ' . $project_id,
                ]
            );
            exit;
        }
        $generation = $project->getGenerationFor($format);
        if (! $generation instanceof ProjectGeneration) {
            wp_send_json_error(
                [
                    'error' => sprintf(
                        'Could not find project generation for project %d on format %s',
                        $project_id,
                        $format
                    ),
                ]
            );
            exit;
        }
        $generation->setLastError($error_message);
        wp_send_json_success();
        exit;
    }

    /**
     * Fetches an external image and caches it on the server in the uploads directory.
     */
    public function handleFetchExternalResource()
    {
        // check print page nonce. Access to the print page is only shared with authorized users and necessary external
        // services. So it acts as a temporary access token.
        $valid_nonce = check_admin_referer('pmb_pro_page', '_pmb_nonce');
        $authorized = current_user_can('edit_pmb_projects');
        if (
            $valid_nonce
            && $authorized
        ) {
            // ok fetch external resource
            $url = isset($_REQUEST['resource_url']) ? esc_url_raw(wp_unslash($_REQUEST['resource_url'])) : '';
            $copy_url = $this->external_resouce_cache->getLocalUrlFromExternalUrl($url);
            if ($copy_url === null) {
                $copy_url = $this->external_resouce_cache->writeAndMapFile($url);
            }
            wp_send_json_success(
                [
                    'copy_url' => $copy_url,
                ]
            );
        }
        wp_send_json_error(
            [
                'nonce_valid' => $valid_nonce,
                'current_user_authenticated' => $authorized,
            ]
        );
    }
}
