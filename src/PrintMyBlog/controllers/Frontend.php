<?php

namespace PrintMyBlog\controllers;

use Exception;
use FS_Plugin_License;
use FS_Site;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintNowSettings;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\system\Context;
use Twine\controllers\BaseController;
use Twine\helpers\Array2;
use WP_Post;
use PrintMyBlog\domain\DefaultFileFormats;

/**
 * Class PmbFrontend
 *
 * Sets up generic frontend logic.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class Frontend extends BaseController
{
    /**
     * Request variable indicating a request like AJAX
     */
    const PMB_AJAX_INDICATOR = 'pmb_ajax';

    /**
     * Request variable indicating a request to generate a project
     */
    const PMB_LOADING_PAGE_INDICATOR = 'pmb_loading';

    /**
     * Action indicating we should generate a project or check its status.
     */
    const PMB_PROJECT_STATUS_ACTION = 'pmb_project_status';

    /**
     * @var ProjectManager
     */
    protected $project_manager;

    /**
     * @var FileFormatRegistry
     */
    protected $format_registry;

    /**
     * @var PmbCentral
     */
    protected $pmb_central;

    /**
     * @var \Twine\orm\entities\PostWrapper|null
     */
    protected $project;

    /**
     * Context injects these dependencies.
     * @param ProjectManager $project_manager
     * @param FileFormatRegistry $format_registry
     * @param PmbCentral $pmb_central
     */
    public function inject(
        ProjectManager $project_manager,
        FileFormatRegistry $format_registry,
        PmbCentral $pmb_central
    ) {
        $this->project_manager = $project_manager;
        $this->format_registry = $format_registry;
        $this->pmb_central = $pmb_central;
    }

    /**
     * Sets up hooks which could be used on the front end.
     */
    public function setHooks()
    {
        add_filter(
            'the_content',
            array($this, 'addPrintButton'),
            /**
             * Allows changing the priority of the print buttons to place them above or below other content
             * automatically added.
             */
            apply_filters(
                'PrintMyBlog\controllers\PmbFrontend->setHooks $priority',
                10
            )
        );

        add_filter(
            'template_redirect',
            array($this, 'templateRedirect'),
            10
        );

        add_filter(
            'template_include',
            array($this, 'templateInclude'),
            /**
            After Elementor at priority 12,
            Enfold theme at the ridiculous priority 20,000...
            Someday, perhaps we should have a regular page dedicated to Print My Blog.
            If you're reading this code and agree, feel free to work on a pull request!
             */
            20001
        );
    }

    /**
     * @param string $content
     * @return string
     */
    public function addPrintButton($content)
    {
        global $post;
        if (! $post instanceof WP_Post || ! in_array($post->post_type, ['post', 'page'], true)) {
            return $content;
        }
        $pmb_print_settings = Context::instance()->reuse('PrintMyBlog\domain\FrontendPrintSettings');

        $postmeta_override = get_post_meta($post->ID, 'pmb_buttons', true);
        $active_post_types = $pmb_print_settings->activePostTypes();
        if (
            /**
             * Lets you override if Print My Blog adds print buttons or not.
             *
             * @param bool $show whether to show print buttons on this post/page or not
             * @param WP_Post $post
             * @param FrontendPrintSettings $pmb_print_settings
             * @param string $active_post_types 'show' to always show buttons on this post, 'hide' to hide on this post,
             *                                  'default', null, or false to use the default settings.
             * default settings.
             */
            apply_filters(
                'PrintMyBlog\controllers\PmbFrontend->addPrintButtons $show_buttons',
                (
                isset($active_post_types[$post->post_type])
                && $active_post_types[$post->post_type]
                && (is_single() || $post->post_type === 'page')
                && ! post_password_required($post)
                && $postmeta_override !== 'hide'
                )
                || $postmeta_override === 'show',
                $post,
                $pmb_print_settings,
                $postmeta_override
            )
        ) {
            $html = Context::instance()->reuse('PrintMyBlog\domain\PrintButtons')->getHtmlForPrintButtons($post);
            $add_to_top = apply_filters(
                '\PrintMyBlog\controllers\PmbFrontend->addPrintButton $add_to_top',
                $pmb_print_settings->showButtonsAbove(),
                $post
            );
            if ($add_to_top) {
                return $html . $content;
            } else {
                return $content . $html;
            }
        }
        return $content;
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @param string $template
     * @deprecated 2.2.3. Instead use `PrintMyBlog/controllers/PmbPrintPage::templateRedirect`
     */
    public function templateInclude($template)
    {

        // check for loading page
        if (isset($_REQUEST[self::PMB_LOADING_PAGE_INDICATOR])){
            return $this->loadingPage();
        }

        return $template;
    }

    public function templateRedirect(){
        // check for PMB ajax
        if (isset($_REQUEST[self::PMB_AJAX_INDICATOR], $_REQUEST['action']) && $_REQUEST['action'] === self::PMB_PROJECT_STATUS_ACTION) {
            return $this->pmbAjax();
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function loadingPage(){
        global $pmb_format;
        $pmb_format = Array2::setOr($_REQUEST, 'pmb_f', DefaultFileFormats::DIGITAL_PDF);
        $post_id = Array2::setOr($_REQUEST, 'pmb_post', 0);
        if( ! $post_id){
            throw new Exception(__('Invalid Post ID. The link you used to get here may be old. If it\'s new, please contact Print My Blog support.', 'print-my-blog'));
        }
        $this->project = $this->project_manager->getById($post_id);
        add_action(
            'wp_enqueue_scripts',
            array($this, 'loadingPageEnqueueScripts'),
            100
        );
        return PMB_TEMPLATES_DIR . 'loading.php';
    }

    public function loadingPageEnqueueScripts(){
        wp_enqueue_script(
            'pmb_loading',
            PMB_SCRIPTS_URL . 'pmb-loading.js',
            array('jquery', 'pmb_general'),
            filemtime(PMB_SCRIPTS_DIR . 'pmb-loading.js')
        );
        wp_enqueue_style(
            'pmb_print_page',
            PMB_STYLES_URL . 'pmb-loading.css',
            array('pmb_print_common'),
            filemtime(PMB_STYLES_DIR . 'pmb-loading.css')
        );

        $license = pmb_fs()->_get_license();
        $site = pmb_fs()->get_site();
        wp_localize_script(
            'pmb_loading',
            'pmb_loading',
            [
                'generate_ajax_data' => apply_filters(
                    '\PrintMyBlog\controllers\Admin->enqueueScripts generate generate_ajax_data',
                    [
                        'action' => Frontend::PMB_PROJECT_STATUS_ACTION,
                        'ID' => Array2::setOr($_REQUEST,'ID',null),
                        '_nonce' => wp_create_nonce('pmb-loading'),
                    ]
                ),
                'pmb_ajax' => pmb_ajax_url(),
                'site_url' => site_url(),
                'use_pmb_central_for_previews' => pmb_use_pmb_central(),
                'license_data' => [
                    'endpoint' => $this->pmb_central->getCentralUrl(),
                    'license_id' => $license instanceof FS_Plugin_License ? $license->id : '',
                    'install_id' => $site instanceof FS_Site ? $site->id : '',
                    'authorization_header' => $site instanceof FS_Site ? $this->pmb_central->getSiteAuthorizationHeader() : '',
                ],

                'doc_attrs' => apply_filters(
                    '\PrintMyBlog\controllers\Admin::enqueueScripts doc_attrs',
                    [
                        'test' => defined('PMB_TEST_LIVE') && PMB_TEST_LIVE ? true : false,
                        'type' => 'pdf',
                        'javascript' => true, // Javascript by DocRaptor
                        'name' => $this->project->getPublishedTitle(),
                        'ignore_console_messages' => true,
                        'ignore_resource_errors' => true,
                        'pipeline' => 9,
                        'prince_options' => [
                            'base_url' => site_url(),
                            'media' => 'print',                                       // use screen
                            'http_timeout' => 60,
                            'http_insecure' => true,
                            // styles
                            // instead of print styles
                            // javascript: true, // use Prince's JS, which is more error tolerant
                        ],
                    ]
                ),
                'translations' => [
                    'error_generating' => __('There was an error preparing your content. Please visit the Print My Blog Help page.', 'print-my-blog'),
                    'socket_error' => __('Your project could not be accessed in order to generate the file. Maybe your website is not public? Please visit the Print My Blog Help page.', 'print-my-blog'),
                ],
            ]
        );
    }



    /**
     * Basically do AJAX logic. We used to simply use AJAX endpoint, but it sets IS_ADMIN to true,
     * which makes lots of plugins malfunction. Plus, many legitimately think they don't need to enqueue
     * scripts on AJAX requests.
     */
    protected function pmbAjax(){
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
}
