<?php

namespace PrintMyBlog\services\generators;

use Exception;
use FS_Plugin_License;
use FS_Site;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\entities\ProjectSection;
use PrintMyBlog\services\PmbCentral;
use PrintMyBlog\system\Context;
use Twine\services\filesystem\File;

/**
 * Class PdfIntermediaryHtmlGenerator
 * Generates an intermediary HTML file on the server which DocRaptor, or the browser, can use to generate a PDF file.
 * @package PrintMyBlog\services\generators
 */
class PdfGenerator extends ProjectFileGeneratorBase
{
    /**
     * @var File
     */
    protected $file_writer;

    /**
     * @var PmbCentral
     */
    protected $pmb_central;

    /**
     * Enqueues themes and styles we'll use on this AJAX request.
     */
    public function enqueueStylesAndScripts(){
        wp_enqueue_style('pmb_print_common');
        wp_enqueue_style('pmb_pro_page');
        wp_enqueue_style('pmb-plugin-compatibility');
        wp_enqueue_script('pmb-beautifier-functions');
        wp_enqueue_script('pmb_pro_page');
        wp_enqueue_script('pmb_general');
        $style_file = $this->getDesignDir() . 'assets/style.css';
        $script_file = $this->getDesignDir() . 'assets/script.js';
        if (file_exists($style_file)) {
            wp_enqueue_style(
                'pmb-design',
                $this->getDesignAssetsUrl() . 'style.css',
                ['pmb_print_common', 'pmb-plugin-compatibility'],
                filemtime($style_file),
                null
            );
        }
        if (file_exists($script_file)) {
            wp_enqueue_script(
                'pmb-design',
                $this->getDesignAssetsUrl() . 'script.js',
                ['jquery', 'pmb-beautifier-functions'],
                filemtime($script_file)
            );
        }
        $license = pmb_fs()->_get_license();
        $site = pmb_fs()->get_site();
        wp_localize_script(
            'pmb_pro_page',
            'pmb_pro',
            [
                'site_url' => site_url(),
                'use_pmb_central_for_previews' => pmb_fs()->is_plan__premium_only('business') ? 1 : 0,
                'license_data' => [
                    'endpoint' => $this->getPmbCentral()->getCentralUrl(),
                    'license_id' => $license instanceof FS_Plugin_License ? $license->id : '',
                    'install_id' => $site instanceof FS_Site ? $site->id : '',
                    'authorization_header' => $this->getPmbCentral()->getSiteAuthorizationHeader(),
                ],

                'doc_attrs' => apply_filters(
                    '\PrintMyBlog\controllers\Admin::enqueueScripts doc_attrs',
                    [
                        'test' => defined('PMB_TEST_LIVE') && PMB_TEST_LIVE ? true : false,
                        'type' => 'pdf',
                        'javascript' => false, // Javascript by web browser
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
                        ]
                    ]
                ),
                'translations' => [
                    // phpcs:disable Generic.Files.LineLength.TooLong
                    'error_generating' => __('There was an error preparing your content. Please visit the Print My Blog Help page.', 'print-my-blog'),
                    'socket_error' => __('Your project could not be accessed in order to generate the file. Maybe your website is not public? Please visit the Print My Blog Help page.', 'print-my-blog')
                    // phpcs:enable Generic.Files.LineLength.TooLong
                ]
            ]
        );
    }

    /**
     * @global Project $pmb_project
     * @global Design $pmb_design
     */
    protected function startGenerating()
    {
        // Try to get enqueued after the theme, if we're doing that, so we get precedence.
        add_action('wp_enqueue_scripts', [$this,'enqueueStylesAndScripts'], 1000);
        do_action('pmb_pdf_generation_start', $this->project_generation, $this->design);
        add_filter('should_load_block_editor_scripts_and_styles', '__return_true');
        add_action('pmb_pro_print_window', [$this,'addPrintWindowToPage']);
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT);
    }



    protected function generateSection()
    {
        global $post;
        // determine which template to use, depending on the current section's height and how template specified
        if ($post->pmb_section instanceof ProjectSection) {
            $this->project_generation->setLastSection($post->pmb_section);
            $template = $post->pmb_section->getTemplate();
            $template = $this->design->getDesignTemplate()->resolveSectionTemplateToUse($template);
            if ($template) {
                $this->writeDesignTemplateInDivision($template);
            } else {
                $this->writeDesignTemplateInDivision(
                    pmb_map_section_to_division($post->pmb_section)
                );
            }
        }
    }

    protected function finishGenerating()
    {
        $this->writeDesignTemplateInDivision(DesignTemplate::IMPLIED_DIVISION_PROJECT, false);
    }


    /**
     * @param string $template_file
     * @param array $context
     */
    protected function writeTemplateToFile($template_file, $context = [])
    {
        $this->getFileWriter()->write(
            '<!-- pmb template: ' . $template_file . '-->' . $this->getHtmlFrom($template_file, $context)
        );
    }

    /**
     * @return File
     */
    protected function getFileWriter()
    {
        if (! $this->file_writer instanceof File) {
            $this->file_writer = new File($this->project_generation->getGeneratedIntermediaryFilePath());
        }
        return $this->file_writer;
    }

    /**
     * @return PmbCentral
     */
    protected function getPmbCentral(){
        return Context::instance()->reuse('PrintMyBlog\services\PmbCentral');
    }

    /**
     * @param $division
     * @param bool $beginning whether to show the beginning, or end, of this division.
     * @param array $context
     */
    protected function writeDesignTemplateInDivision($division, $beginning = true, $context = [])
    {
        $this->writeTemplateToFile(
            $this->design->getDesignTemplate()->getTemplatePathToDivision(
                $division,
                $beginning
            ),
            $context
        );
    }

    protected function writeClosingForDesignTemplate($division)
    {
        if ($this->design->getDesignTemplate()->templateFileExists($division, false)) {
            $this->writeDesignTemplateInDivision($division, false);
        } else {
            // If the design template didn't delcare that file, it's ok. Assume it just ends in a div.
            $this->getFileWriter()->write('</div>');
        }
    }

    protected function maybeGenerateDivisionStart(
        ProjectSection $last_section = null,
        ProjectSection $current_section = null
    ) {
        if (! $current_section instanceof ProjectSection) {
            return;
        }

        $last_section_placement = null;
        if ($last_section instanceof ProjectSection) {
            $last_section_placement = $last_section->getPlacement();
        }
        if (
            $last_section_placement !== $current_section->getPlacement()
            && $this->design->getDesignTemplate()->supports($current_section->getPlacement())
        ) {
            $this->writeDesignTemplateInDivision($current_section->getPlacement());
        }
    }

    /**
     * @param int $previous_depth
     * @param int $current_depth
     */
    protected function maybeGenerateDivisionEnd(
        ProjectSection $previous_section = null,
        ProjectSection $current_section = null
    ) {
        if (! $previous_section) {
            // no transition necessary
            return;
        }
        $iterate_depth = $previous_section->getDepth();
        $current_depth = 0;
        $current_placement = null;
        if ($current_section instanceof ProjectSection) {
            $current_depth = $current_section->getDepth();
            $current_placement = $current_section->getPlacement();
        }
        while ($iterate_depth >= $current_depth) {
            $this->writeClosingForDesignTemplate(
                pmb_map_section_to_division(
                    $previous_section
                )
            );
            $iterate_depth--;
        }
        // take care of closing front_matter, main_matter, and back_matter
        if ($previous_section->getPlacement() !== $current_placement) {
            $this->writeClosingForDesignTemplate(
                pmb_map_section_to_division($previous_section)
            );
        }
    }

    protected function generateMainMatter()
    {
        $this->generateSections($this->project->getFlatSections(1000, 0, false));
    }


    /**
     * Deletes the generated HTML file, if it exists.
     * @return bool
     */
    public function deleteFile()
    {
        return $this->getFileWriter()->delete();
    }

    public function addPrintWindowToPage(){
        if ( pmb_fs()->is__premium_only() ) {
            $license = pmb_fs()->_get_license();
            if ($license instanceof FS_Plugin_License) {
                $license_info = $this->getPmbCentral()->getCreditsInfo();
            }
        }
        $this->getFileWriter()->write(pmb_get_contents(PMB_TEMPLATES_DIR . 'partials/pro_print_page_window.php', ['license_info' => $license_info]));
    }
}
