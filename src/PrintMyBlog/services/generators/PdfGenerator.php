<?php

namespace PrintMyBlog\services\generators;

use Exception;
use FS_Plugin_License;
use FS_Site;
use PrintMyBlog\entities\DesignTemplate;
use PrintMyBlog\entities\SectionTemplate;
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
class PdfGenerator extends HtmlBaseGenerator
{
    /**
     * Enqueues themes and styles we'll use on this AJAX request.
     */
    public function enqueueStylesAndScripts()
    {
        wp_enqueue_style('pmb_print_common');
        wp_enqueue_style('pmb_pro_page');
        wp_enqueue_style('pmb-plugin-compatibility');
        wp_enqueue_script(
            'pmb-pdf-beautifier-functions',
            PMB_SCRIPTS_URL . 'pdf-beautifier-functions.js',
            ['pmb-beautifier-functions'],
            filemtime(PMB_SCRIPTS_DIR . 'pdf-beautifier-functions.js')
        );
        wp_enqueue_script('pmb_pro_page');
        wp_enqueue_style(
            'pmb_pro_pdf',
            PMB_STYLES_URL . 'pmb-pro-pdf.css',
            null,
            filemtime(PMB_STYLES_DIR . 'pmb-pro-pdf.css')
        );
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
        if ($license instanceof FS_Plugin_License) {
            $license_info = $this->getPmbCentral()->getCreditsInfo();
        } else {
            $license_info = null;
        }
        $site = pmb_fs()->get_site();
        $use_pmb_central = 0;
        if (pmb_fs()->is_plan__premium_only('business')) {
            $use_pmb_central = 1;
        }
        wp_localize_script(
            'pmb_pro_page',
            'pmb_pro',
            [
                'site_url' => site_url(),
                'use_pmb_central_for_previews' => $use_pmb_central,
                'license_data' => [
                    'endpoint' => $this->getPmbCentral()->getCentralUrl(),
                    'license_id' => $license instanceof FS_Plugin_License ? $license->id : '',
                    'install_id' => $site instanceof FS_Site ? $site->id : '',
                    'authorization_header' => $site instanceof FS_Site ? $this->getPmbCentral()->getSiteAuthorizationHeader() : '',
                ],
                'ajaxurl' => admin_url('admin-ajax.php'),
                'project_id' => $this->project->getWpPost()->ID,
                'format' => $this->project_generation->getFormat()->slug(),
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
                            'media' => 'print',
                            'http_timeout' => 60,
                            'http_insecure' => true,
                            'javascript' => true, // before sending the HTML to DocRaptor, we turn all the "script" tags into "disabled-script"; and all the "prince-script" into "script" tags.
                        ]
                    ]
                ),
                'translations' => [
                    'error_generating' => __('There was an error preparing your content. Please visit the Print My Blog Help page.', 'print-my-blog'),
                    'socket_error' => __('Your project could not be accessed in order to generate the file. Maybe your website is not public? Please visit the Print My Blog Help page.', 'print-my-blog'),
                    'pro_description' => sprintf(
                        esc_html__(
                            'Downloading the Paid PDF will use one of your %1$s remaining credits, and is non-refundable.',
                            'print-my-blog'
                        ),
                        is_array($license_info) ? $license_info['remaining_credits'] : '0'
                    )
                ]
            ]
        );

        add_action(
            'wp_print_scripts',
            [$this, 'printScripts']
        );
    }

    /**
     * Prints the scripts and other stuff that's really custom (like the Prince script)
     */
    public function printScripts()
    {
        // now add the Prince script, which Prince will run
        // pass in its variables, like maximum image size
        $prince_js_vars = [];
        $max_image_size = $this->design->getSetting('image_size');
        if (! $max_image_size) {
            $max_image_size = 1200;
        }
        $prince_js_vars['max_image_size'] = $max_image_size;
        echo '<prince-script>'
            . 'var pmb = ' . wp_json_encode($prince_js_vars) . ';'
            . pmb_get_contents(PMB_SCRIPTS_DIR . '/prince-print-page.js')
            . '</prince-script>';
    }

    /**
     * Adds a "base" tag to the head which tells DocRaptor how to resolve relative links. See
     * https://help.docraptor.com/en/articles/2154806-file-system-access-is-not-allowed-error-message
     */
    public function addBaseTag()
    {
        echo '<base href="' . esc_url(site_url()) . '">';
    }

    public function startGenerating()
    {
        parent::startGenerating(); // TODO: Change the autogenerated stub
        // Add the "base" tag so relative links work. But if Oxygen pagebuilder is active, we need to use a different hook.
        // (because Oxygen puts everything from wp_head in the footer)
        if (defined('CT_VERSION')) {
            add_action('oxygen_enqueue_frontend_scripts', [$this,'addBaseTag']);
        } else {
            add_action('wp_head', [$this,'addBaseTag']);
        }
    }


    /**
     * @return PmbCentral
     */
    protected function getPmbCentral()
    {
        return Context::instance()->reuse('PrintMyBlog\services\PmbCentral');
    }



    /**
     * Writes out the PMB Pro print "window" which appears at the top of pro print pages.
     * Echoes, instead of using `$this->file_writer`, because this is a callback on an action called inside the template HTML.
     * @throws Exception
     */
    public function addPrintWindowToPage()
    {
        $license_info = null;
        if (pmb_fs()->is__premium_only()) {
            try {
                $license = pmb_fs()->_get_license();
                if ($license instanceof FS_Plugin_License) {
                    $license_info = $this->getPmbCentral()->getCreditsInfo();
                }
            } catch (Exception $e) {
                // error retrieving credits info. We should have warned the user about this earlier.
                // This probably means their subscription isn't good anymore. Treat it like they have none.
            }
        }
        echo pmb_get_contents(PMB_TEMPLATES_DIR . 'partials/pro_print_page_window.php', ['license_info' => $license_info, 'project' => $this->project, 'generate_url' => $this->getUrlBackToGenerateStep()]);
    }
}
