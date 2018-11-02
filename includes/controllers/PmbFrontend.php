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
     * @since 1.0.0
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
                'pmb_print_page',
                PMB_ASSETS_URL . 'scripts/print_page.js',
                array('jquery', 'wp-api', 'luxon'),
                filemtime(PMB_ASSETS_DIR . 'scripts/print_page.js')
            );
            wp_enqueue_style(
                'pmb_print_page',
                PMB_ASSETS_URL . 'styles/print_page.css',
                array(),
                filemtime(PMB_ASSETS_DIR . 'styles/print_page.css')
            );
            wp_localize_script(
                'pmb_print_page',
                'pmb_print_data',
                array(
                    'i18n' => array(
                        'wrapping_up' => esc_html__('Wrapping Up!', 'print_my_blog'),
                    ),
                    'data' => array(
                        'locale' => get_locale(),
                        'show_images' => $this->getFromRequest('show_images', 'full') !== 'none'
                    ),
                )
            );
            $this->enqueueInlineStyleBasedOnOptions();

            return PMB_TEMPLATES_DIR . 'print_page.template.php';
        }
        return $template;
    }

    /**
     * Adds the styles that depend on the user's preferences.
     * @since $VID:$
     */
    protected function enqueueInlineStyleBasedOnOptions()
    {
        $columns = intval($this->getFromRequest('columns',2));
        $image_size = sanitize_key($this->getFromRequest('image_size','medium'));
        $post_page_break = (bool)$this->getFromRequest('post-page-break',false);
        $font_size = sanitize_key($this->getFromRequest('font-size', 'small'));
        $css = "
        .entry-content{
            column-count: $columns;
        }
        ";
        if($post_page_break){
            $css .= '.pmb-post-header{page-break-before:always;}';
        }
        $image_size_map = array(
            'small' => '6cm',
            'medium' => '10cm',
            'large' => '18cm',
        );
        $image_size_css = isset($image_size_map[$image_size]) ? $image_size_map[$image_size] : 'full';
        $css .= ".pmb-image img{max-height:$image_size_css;}";
        $font_size_map = array(
            'tiny' => '0.5em',
            'small' => '0.8em',
            'normal' => '1em',
            'large' => '1.3em',
        );
        $font_size_css = isset($font_size_map[$font_size]) ? $font_size_map[$font_size] : '1em';
        $css .= ".pmb-posts-body{font-size:$font_size_css;}";
        wp_add_inline_style(
            'pmb_print_page',
            $css
        );
    }

    /**
     * Helper for getting a value from the request, or setting a default.
     * @since $VID:$
     * @param $query_param_name
     * @param $default
     * @return mixed
     */
    protected function getFromRequest($query_param_name, $default) {
        return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
    }
}