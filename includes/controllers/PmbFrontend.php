<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

class PmbFrontend extends BaseController
{
    public function setHooks()
    {
        add_filter('template_include', array($this, 'templateRedirect'), 12 /* after Elementor */);
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since $VID:$
     */
    public function templateRedirect($template)
    {

        if (isset($_GET[PMB_PRINTPAGE_SLUG])) {
            wp_register_script(
                'luxon',
                PMB_ASSETS_URL . 'scripts/luxon.min.js',
                array(),
                filemtime(PMB_ASSETS_DIR . 'scripts/luxon.min.js')
            );
            wp_enqueue_script(
                'pmg_print_page',
                PMB_ASSETS_URL . 'scripts/print_page.js',
                array('jquery', 'wp-api', 'luxon'),
                filemtime(PMB_ASSETS_DIR . 'scripts/print_page.js')
            );
            wp_enqueue_style(
                'pmg_print_page',
                PMB_ASSETS_URL . 'styles/print_page.css',
                array(),
                filemtime(PMB_ASSETS_DIR . 'styles/print_page.css')
            );
            wp_localize_script(
                'pmg_print_page',
                'pmg_print_data',
                array(
                    'strings' => array(

                    ),
                    'data' => array(
                        'locale' => get_locale(),
                    ),
                )
            );
            return PMB_TEMPLATES_DIR . 'print_page.template.php';
        }
        return $template;
    }
}