<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\domain\PrintOptions;
use Twine\controllers\BaseController;

/**
 * Class PmbAdmin
 *
 * Hooks needed to add our stuff to the admin.
 * Mostly it's just an admin page.
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbAdmin extends BaseController
{
    /**
     * Sets hooks that we'll use in the admin.
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('admin_menu', array($this, 'addToMenu'));
        add_filter('plugin_action_links_' . PMB_BASENAME, array($this, 'pluginPageLinks'));
        add_action( 'admin_enqueue_scripts', [$this,'enqueueScripts'] );
    }

    /**
     * Adds our menu page.
     * @since 1.0.0
     */
    public function addToMenu()
    {
        add_submenu_page(
            'tools.php',
            esc_html__('Print My Blog', 'print-my-blog'),
            esc_html__('Print My Blog', 'print-my-blog'),
            PMB_ADMIN_CAP,
            PMB_ADMIN_PAGE_SLUG,
            array(
                $this,
                'renderAdminPage'
            )
        );
    }

    /**
     * Shows the setup page.
     * @since 1.0.0
     */
    public function renderAdminPage()
    {
        $print_options = new PrintOptions();
        include(PMB_TEMPLATES_DIR . 'setup_page.template.php');
    }

    /**
     * Adds links to PMB stuff on the plugins page.
     * @since 1.0.0
     * @param array $links
     */
    public function pluginPageLinks($links)
    {
        $links[] = '<a href="'
            . admin_url(PMB_ADMIN_PAGE_PATH)
            . '">'
            . esc_html__('Print Setup Page', 'print-my-blog')
            . '</a>';
        return $links;
    }

    function enqueueScripts($hook) {
        if ( 'tools_page_print-my-blog' !== $hook ) {
            return;
        }
        wp_register_script(
            'jquery_debounce',
            PMB_ASSETS_URL . 'scripts/libs/jquery.debounce-1.0.5.js',
            ['jquery'],
            '1.0.5'
        );
        wp_register_script(
            'select2',
            PMB_ASSETS_URL . 'scripts/libs/select2/select2.min.js',
            [],
            '4.0.6'
        );
        wp_register_style(
            'select2',
            PMB_ASSETS_URL . 'styles/libs/select2.css',
            [],
            '4.0.6'
        );
        wp_enqueue_script(
            'pmb_setup_page',
            PMB_ASSETS_URL . 'scripts/setup-page.js',
            ['jquery_debounce', 'select2', 'wp-api', 'jquery'],
            filemtime(PMB_ASSETS_DIR .  'scripts/setup-page.js')
        );
        wp_enqueue_style(
            'pmb_setup_page',
            PMB_ASSETS_URL . 'styles/setup-page.css',
            ['pmb_common', 'select2'],
            filemtime(PMB_ASSETS_DIR .  'styles/setup-page.css')
        );
        wp_localize_script(
            'pmb_setup_page',
            'pmb_setup_page',
            [
                'translations' => [
                    'unknown_site_name' => esc_html__('Unknown site name', 'print-my-blog')
                ],
                'data' => [
                    'site_input_selector' => '#pmb-site',
                    'spinner_selector' => '#pmb-site-checking',
                    'dynamic_categories_spinner_selector' => '#pmb-categories-spinner',
                    'site_ok_selector' => '#pmb-site-ok',
                    'site_bad_selector' => '#pmb-site-bad',
                    'site_status_selector' => '#pmb-site-status',
                    'post_type_selector' => '.pmb-post-type',
                    'dynamic_categories_selector' => '#pmb-dynamic-categories',
                    'default_rest_url' => rest_url('/wp/v2')
                ]
            ]
        );
    }
}