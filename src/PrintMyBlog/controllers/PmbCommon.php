<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

/**
 * Class PmbCommon
 *
 * Common controller logic that should run on all requests.
 *
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PmbCommon extends BaseController
{
    public function setHooks()
    {
        add_action(
            'wp_enqueue_scripts',
            [$this, 'enqueueScripts'],
            9
        );
        add_action(
            'admin_enqueue_scripts',
            [$this, 'enqueueScripts'],
            9
        );
    }


    public function enqueueScripts()
    {
        wp_register_style(
            'jquery-ui',
            'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css',
            array(),
            '1.8'
        );
        wp_enqueue_style(
            'pmb_common',
            PMB_ASSETS_URL . 'styles/pmb-common.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/pmb-common.css')
        );
        wp_register_script(
            'jquery-debounce',
            PMB_ASSETS_URL . 'scripts/libs/jquery.debounce-1.1.min.js',
            ['jquery'],
            '1.1'
        );
        wp_register_script(
            'pmb-select2',
            PMB_ASSETS_URL . 'scripts/libs/select2/select2.min.js',
            [],
            '4.0.6'
        );
        wp_register_style(
            'pmb-select2',
            PMB_ASSETS_URL . 'styles/libs/select2.css',
            [],
            '4.0.6'
        );
        wp_register_script(
            'pmb-setup-page',
            PMB_ASSETS_URL . 'scripts/setup-page.js',
            ['jquery-debounce', 'pmb-select2', 'wp-api', 'jquery-ui-datepicker'],
            filemtime(PMB_ASSETS_DIR .  'scripts/setup-page.js')
        );
        wp_register_style(
            'pmb-setup-page',
            PMB_ASSETS_URL . 'styles/setup-page.css',
            ['pmb_common', 'pmb-select2', 'jquery-ui'],
            filemtime(PMB_ASSETS_DIR .  'styles/setup-page.css')
        );
        wp_localize_script(
            'pmb-setup-page',
            'pmb_setup_page',
            [
                'translations' => [
                    'unknown_site_name' => esc_html__('Unknown site name', 'print-my-blog'),
                    'no_categories' => esc_html__('No categories available.', 'print-my-blog')
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
                    'default_rest_url' => function_exists('rest_url') ? rest_url('/wp/v2') : '',
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'author_selector' => '#pmb-author-select',
                    'nonce' => wp_create_nonce('wp_rest'),
                    'order_date_selector' => '#pmb-order-by-date',
                    'order_menu_selector' => '#pmb-order-by-menu'
                ]
            ]
        );
    }
}
