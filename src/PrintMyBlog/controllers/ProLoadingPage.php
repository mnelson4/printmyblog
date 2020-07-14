<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

/**
 * Class ProPrintPage
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class ProLoadingPage extends BaseController
{

    /**
     * Sets hooks needed for this controller to execute its logic.
     *
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_filter(
            'template_include',
            array($this, 'templateRedirect'),
            /* after Elementor at priority 12,
            Enfold theme at the ridiculous priority 20,000...
            Someday, perhaps we should have a regular page dedicated to Print My Blog.
            If you're reading this code and agree, feel free to work on a pull request! */
            20001
        );
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since 1.0.0
     */
    public function templateRedirect($template)
    {
        if (isset($_GET[ PMB_PRINTPAGE_SLUG ]) && $_GET[ PMB_PRINTPAGE_SLUG ] == 3) {
            // enqueue our scripts and styles at the right time
            // specifically, after everybody else, so we can override them.
            add_action(
                'wp_enqueue_scripts',
                array($this,'enqueueScripts'),
                100
            );

            return PMB_TEMPLATES_DIR . 'pro_loading_page.template.php';
        }
        return $template;
    }

    public function enqueueScripts()
    {
        wp_enqueue_script(
            'pmb_pro_loading_page',
            PMB_ASSETS_URL . 'scripts/pro-loading-page.js',
            array('jquery',),
            filemtime(PMB_ASSETS_DIR . 'scripts/pro-loading-page.js')
        );
        wp_enqueue_style(
            'pmb_pro_loading_page',
            PMB_ASSETS_URL . 'styles/pro-loading-page.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/pro-loading-page.css')
        );

        $data = [
        ];
        $init_error_message = esc_html__(
            'There seems to be an error initializing. Please verify you are using an up-to-date web browser.',
            'print-my-blog'
        );
        wp_localize_script(
            'pmb_pro_loading_page',
            'pmb_pro_loading_data',
            array(
                'i18n' => array(
                    'ready' => esc_html__('Print-Page Ready', 'print-my-blog'),
                    'error' => esc_html__('Sorry, There was a Problem 😢', 'print-my-blog'),
                    'init_error' => $init_error_message,
                ),
                'data' => $data,
            )
        );
    }
}