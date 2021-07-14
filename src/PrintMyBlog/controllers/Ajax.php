<?php

namespace PrintMyBlog\controllers;

use mnelson4\rest_api_detector\RestApiDetector;
use mnelson4\rest_api_detector\RestApiDetectorError;
use PrintMyBlog\db\PostFetcher;
use PrintMyBlog\entities\ProjectGeneration;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\system\CustomPostTypes;
use Twine\controllers\BaseController;
use Twine\helpers\Array2;
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
     * @param ProjectManager $project_manager
     */
    public function inject(
        ProjectManager $project_manager,
        FileFormatRegistry $format_registry,
        PostFetcher $post_fetcher,
        PmbCentral $pmb_central
    ) {
        $this->project_manager = $project_manager;
        $this->format_registry = $format_registry;
        $this->post_fetcher = $post_fetcher;
        $this->pmb_central = $pmb_central;
    }
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        $this->addUnauthenticatedCallback(
            'pmb_fetch_rest_api_url',
            'handleFetchRestApiUrl'
        );
        $this->addUnauthenticatedCallback(
            'pmb_project_status',
            'handleProjectStatus'
        );
        add_action('wp_ajax_pmb_save_project_main', [$this, 'handleSaveProjectMain' ]);
        add_action('wp_ajax_pmb_post_search', [$this,'handlePostSearch']);
        add_action('wp_ajax_pmb_add_print_material', [$this,'addPrintMaterial']);
        add_action('wp_ajax_pmb_reduce_credits', [$this,'reduceCredits']);
        add_action('wp_ajax_pmb_report_error', [$this,'reportError']);
    }

    protected function addUnauthenticatedCallback($ajax_action, $method_name)
    {
        $callback = [$this, $method_name];
        add_action('wp_ajax_' . $ajax_action, $callback);
        add_action('wp_ajax_nopriv_' . $ajax_action, $callback);
    }


    public function handleFetchRestApiUrl()
    {
        try {
            $rest_api_detector = new RestApiDetector(isset($_POST['site']) ? esc_url_raw($_POST['site']) : '');
        } catch (RestApiDetectorError $error) {
                wp_send_json_error(
                    [
                        'error' => $error->stringCode(),
                        'message' => $error->getMessage()
                    ]
                );
        }
        wp_send_json_success(
            [
                'name' => $rest_api_detector->getName(),
                'site' => $rest_api_detector->getSite(),
                'proxy_for' => $rest_api_detector->getRestApiUrl(),
                'is_local' => $rest_api_detector->isLocal()
            ]
        );
    }

    /**
     * Proceeds with loading printing a project and returns a response indicating the status.
     */
    public function handleProjectStatus()
    {
        // Find project by ID.
        /*
         * @var $project Project
         */
        $project = $this->project_manager->getById(Array2::setOr($_REQUEST, 'ID', ''));
        $format = $this->format_registry->getFormat(Array2::setOr($_REQUEST, 'format', ''));
        /**
         * @var $project_generation ProjectGeneration
         */
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
            'media' => $format->slug() === 'digital_pdf' ? 'screen' : 'print'
        ];

        wp_send_json($response);
        exit;
    }

    public function handleSaveProjectMain()
    {
        // Get the ID
        $project_id = Array2::setOr($_REQUEST, 'ID', '');
        // Check permission
        if (
                check_admin_referer('pmb-project-edit')
            && current_user_can('edit_pmb_project', $project_id)
        ) {
            // Save it
            $project = $this->project_manager->getById($project_id);
            $success = $project->setTitle(Array2::setOr($_REQUEST, 'pmb_title', ''));
            $project->setFormatsSelected(Array2::setOr($_REQUEST, 'pmb_format', ''));
        }
        // Say it worked
        if (is_wp_error($success)) {
            wp_send_json_error($success);
        } else {
            wp_send_json_success();
        }
    }

    public function handlePostSearch()
    {
        $requested_posts = 50;
        $query_params = [
            'posts_per_page' => $requested_posts,
            'ignore_sticky_posts' => true
        ];
        $project = $this->project_manager->getById(Array2::setOr($_GET, 'project', 0));
        if (!empty($_GET['page'])) {
            $query_params['paged'] = $_GET['page'];
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        if (!empty($_GET['pmb-search'])) {
            $query_params['s'] = $_GET['pmb-search'];
        }
        if (!empty($_GET['pmb-post-type'])) {
            $query_params['post_type'] = $_GET['pmb-post-type'];
        } else {
            $query_params['post_type'] = $this->post_fetcher->getProjectPostTypes('names');
        }
        if (!empty($_GET['pmb-status'])) {
            $query_params['post_status'] = $_GET['pmb-status'];
        }
        if (!empty($_GET['taxonomies'])) {
            $tax_query = [];
            foreach ($_GET['taxonomies'] as $taxonomy => $ids) {
                $tax_query[] = [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $ids
                ];
            }
            if (! empty($tax_query)) {
                $query_params['tax_query'] = $tax_query;
            }
        }
        if (!empty($_GET['pmb-author'])) {
            $query_params['author'] = $_GET['pmb-author'];
        }
        $date_query = [];
        if (!empty($_GET['pmb-date'])) {
            if (!empty($_GET['pmb-date']['from'])) {
                $date_query['after'] = $_GET['pmb-date']['from'];
            }
            if (!empty($_GET['pmb-date']['to'])) {
                $date_query['before'] = $_GET['pmb-date']['to'];
            }
            if ($date_query) {
                $date_query['inclusive'] = true;
                $query_params['date_query'] = $date_query;
            }
        }
        if (!empty($_GET['exclude'])) {
            $query_params['exclude'] = array_map(
                'intval',
                explode(',', $_GET['exclude'])
            );
        }
        if (!empty($_GET['pmb-order-by'])) {
            $query_params['orderby'] = $_GET['pmb-order-by'];
        }
        if (!empty($_GET['pmb-order'])) {
            $query_params['order'] = $_GET['pmb-order'];
        }
        $posts = get_posts($query_params);
        foreach ($posts as $post) {
            pmb_content_item($post, $project, 0);
        }
        if ($requested_posts == count($posts)) {
            ?>
            <div class="pmb-show-more">
                <div
                        class="load-more-button button no-drag"
                        data-page="<?php echo esc_attr($page + 1);?>"><span
                            class="dashicons dashicons-arrow-down-alt"></span>
                    <?php esc_html_e('Show more...', 'print-my-blog');?>
                </div>
            </div>
            <?php
        } elseif (count($posts) === 0 && $page == 1) {
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

    public function addPrintMaterial()
    {
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
        wp_send_json_success([
            'html' => $html,
            'post_ID' => $post_id
        ]);
        exit;
    }

    public function reduceCredits()
    {
        $updated_credit_info = $this->pmb_central->reduceCredits(pmb_fs()->_get_license()->id);
        wp_send_json_success(
            $updated_credit_info
        );
        exit;
    }

    public function reportError()
    {
        $project_id = (int)Array2::setOr($_REQUEST, 'project_id', '');
        $format = sanitize_key(Array2::setOr($_REQUEST, 'format', ''));
        $error_message = esc_html(Array2::setOr($_REQUEST, 'error', ''));
        $project = $this->project_manager->getById($project_id);
        if (! $project) {
            wp_send_json_error(
                [
                            'error' => 'Could not find project with ID ' . $project_id
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
                            )
                    ]
            );
            exit;
        }
        $generation->setLastError($error_message);
        wp_send_json_success();
        exit;
    }
}
