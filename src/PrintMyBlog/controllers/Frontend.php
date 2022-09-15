<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintNowSettings;
use PrintMyBlog\entities\ProjectProgress;
use PrintMyBlog\orm\managers\ProjectManager;
use PrintMyBlog\services\FileFormatRegistry;
use PrintMyBlog\system\Context;
use Twine\controllers\BaseController;
use Twine\helpers\Array2;
use WP_Post;

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
     * Context injects these dependencies.
     * @param ProjectManager $project_manager
     * @param FileFormatRegistry $format_registry
     */
    public function inject(
        ProjectManager $project_manager,
        FileFormatRegistry $format_registry
    ) {
        $this->project_manager = $project_manager;
        $this->format_registry = $format_registry;
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
    public function templateRedirect($template)
    {
        // Basically do AJAX logic. We used to simply use AJAX endpoint, but it sets IS_ADMIN to true,
        // which makes lots of plugins malfunction. Plus, many legitimately think they don't need to enqueue
        // scripts on AJAX requests.

        if (isset($_REQUEST[self::PMB_AJAX_INDICATOR], $_REQUEST['action']) && $_REQUEST['action'] === self::PMB_PROJECT_STATUS_ACTION) {
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
        return $template;
    }
}
