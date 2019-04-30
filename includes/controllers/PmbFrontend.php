<?php

namespace PrintMyBlog\controllers;

use mnelson4\RestApiDetector\RestApiDetector;
use mnelson4\RestApiDetector\RestApiDetectorError;
use PrintMyBlog\domain\PrintOptions;
use Twine\controllers\BaseController;
use stdClass;

class PmbFrontend extends BaseController
{
    /**
     * @var URL of domain we'd like this site to proxy for, so we can print that blog instead.
     */
    protected $proxy_for;
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

        if (isset($_GET[PMB_PRINTPAGE_SLUG])) {
            try {
                $site_info = new RestApiDetector($this->getFromRequest('site', ''));
            }catch(RestApiDetectorError $exception){
                global $pmb_wp_error;
                $pmb_wp_error = $exception->wp_error();
                return PMB_TEMPLATES_DIR . 'print_page_error.template.php';
            }
            global $pmb_site_name, $pmb_site_description, $pmb_site_url,  $pmb_printout_meta;
            $pmb_site_name = $site_info->getName();
            $pmb_site_description = $site_info->getDescription();
            $pmb_site_url = $site_info->getSite();
            $pmb_printout_meta = $this->getFromRequest('printout-meta', false);
            if($site_info->isLocal()) {
                $this->proxy_for = '';
            } else {
                $this->proxy_for = $site_info->getRestApiUrl();
            }
            // enqueue our scripts and styles at the right time
            // specifically, after everybody else, so we can override them.
            add_action(
                'wp_enqueue_scripts',
                array($this,'enqueue_scripts'),
                100
            );

            return PMB_TEMPLATES_DIR . 'print_page.template.php';
        }
        return $template;
    }

    public function enqueue_scripts()
    {
        wp_register_script(
            'luxon',
            PMB_ASSETS_URL . 'scripts/luxon.min.js',
            array(),
            filemtime(PMB_ASSETS_DIR . 'scripts/luxon.min.js')
        );
        wp_enqueue_script(
            'pmb_print_page',
            PMB_ASSETS_URL . 'scripts/print-page.js',
            array('jquery', 'wp-api', 'luxon'),
            filemtime(PMB_ASSETS_DIR . 'scripts/print-page.js')
        );
        wp_enqueue_style(
            'pmb_print_page',
            PMB_ASSETS_URL . 'styles/print-page.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/print-page.css')
        );
        // Enqueue tiled gallery too. It's par of Jetpack so it's common, and if we're printing a WordPress.com blog
        // it's very likely to be used.
        wp_enqueue_style(
            'tiled-gallery',
            PMB_ASSETS_URL . 'styles/tiled-gallery.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/tiled-gallery.css')
        );
        // Enqueue the CSS for compatibility with known troublemaking plugins.
        wp_enqueue_style(
            'pmb-plugin-compatibility',
            PMB_ASSETS_URL . 'styles/plugin-compatibility.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/plugin-compatibility.css')
        );
        $data = [
            'header_selector' => '#pmb-in-progress-h1',
            'status_span_selector' => '.pmb-status',
            'posts_count_span_selector' => '.pmb-posts-count',
            'posts_div_selector' => '.pmb-posts-body',
            'waiting_area_selector' => '.pmb-posts-placeholder',
            'print_ready_selector' => '.pmb-print-ready',
            'locale' => get_locale(),
            'image_size' => $this->getImageRelativeSize(),
            'proxy_for' => $this->proxy_for,
            'columns' => $this->getFromRequest('columns', 1),
            'post_type' => $this->getFromRequest('post-type', 'post'),
            'rendering_wait' => $this->getFromRequest('rendering-wait', 500),
            'include_inline_js' => (bool) $this->getFromRequest('include-inline-js', false),
            'links' => (string) $this->getFromRequest('links', 'include'),
            'filters' => (object) $this->getFromRequest('filters', new stdClass),
            'foogallery' => function_exists('foogallery_fs'),
            'is_user_logged_in' => is_user_logged_in()
        ];
        $print_options = new PrintOptions();
        foreach($print_options->postContentOptions() as $option_name => $option_details){
            $data[$option_name] = (bool)$this->getFromRequest($option_name, false);
        }

        wp_localize_script(
            'pmb_print_page',
            'pmb_print_data',
            array(
                'i18n' => array(
                    'loading_content' => esc_html__('Loading Content', 'print-my-blog'),
                    'loading_comments' => esc_html__('Loading Comments', 'print-my-blog'),
                    'organizing_posts' => esc_html__('Ordering Posts', 'print-my-blog'),
                    'organizing_comments' => esc_html__('Ordering Comments', 'print-my-blog'),
                    'rendering_posts' => esc_html__('Placing Content on Page', 'print-my-blog'),
                    'wrapping_up' => esc_html__('Optimizing for Print', 'print-my-blog'),
                    'ready' => esc_html__('Print-Page Ready', 'print-my-blog'),
                    'error_fetching_posts' => esc_html__('There was an error fetching posts. It was: ', 'print-my-blog'),
                    'comments' => esc_html__('Comments', 'print-my-blog'),
                    'no_comments' => esc_html('No Comments', 'print-my-blog'),
                    'says' => __('<span class="screen-reader-text says">says:</span>', 'print-my-blog'),
                    'id' => esc_html__('ID:', 'print-my-blog')
                ),
                'data' => $data,
            )
        );
        $this->enqueueInlineStyleBasedOnOptions();
        $this->loadThemeCompatibilityScriptsAndStylesheets();
    }

    protected function getImageRelativeSize()
    {
        $requested_size = sanitize_key($this->getFromRequest('image-size','full'));
        $page_width = 8.5;
        switch($requested_size) {
            case 'large':
                return $page_width * 3 / 4;
                break;
            case 'medium':
                return $page_width / 2;
                break;
            case 'small':
                return $page_width / 4;
                break;
            case 'none':
                return 0;
                break;
            default:
                return false;
        }
    }

    /**
     * Loads stylesheets that help certain themes look better on the printed page.
     * @since $VID:$
     */
    protected function loadThemeCompatibilityScriptsAndStylesheets()
    {
        $theme = wp_get_theme();
        $slug = $theme->get('TextDomain');
        $theme_slug_path =  'styles/theme-compatibility/' . $slug . '.css';
        if(file_exists(PMB_ASSETS_DIR . $theme_slug_path)){
            wp_enqueue_style(
                'pmb_print_page_theme_compatibility',
                PMB_ASSETS_URL . $theme_slug_path,
                array(),
                filemtime(PMB_ASSETS_DIR .  $theme_slug_path)
            );
        }
        $script_slug_path = 'scripts/theme-compatibility/' . $slug . '.js';
        if(file_exists(PMB_ASSETS_DIR . $script_slug_path)){
            wp_enqueue_script(
                'pmb_print_page_script_compatibility',
                PMB_ASSETS_URL . $script_slug_path,
                array('pmb_print_page'),
                filemtime(PMB_ASSETS_DIR .  $script_slug_path)
            );
        }
    }

    /**
     * Adds the styles that depend on the user's preferences.
     * @since 1.1.0
     */
    protected function enqueueInlineStyleBasedOnOptions()
    {
        $columns = intval($this->getFromRequest('columns',1));
        $post_page_break = (bool)$this->getFromRequest('post-page-break',false);
        $font_size = sanitize_key($this->getFromRequest('font-size', 'small'));
        $css = "
        .entry-content{
            column-count: $columns;
        }
        ";
        if($post_page_break){
            $css .= '.pmb-post-article:not(:first-child){page-break-before:always;}';
        }
        $font_size_map = array(
            'tiny' => '0.50em',
            'small' => '0.75em',
            // Leave out normal, let the theme decide.
            'large' => '1.25em',
        );
        if(isset($font_size_map[$font_size])){
            $font_size_css = $font_size_map[$font_size];
            $css .= ".pmb-posts{font-size:$font_size_css;}
            h1{font-size:1.3em !important;}
            h2{font-size:1.2em !important;}
            h3{font-size:1.1em !important;}
            ul, ol, p{margin-bottom:0.5em;margin-top:0.5em;}
            blockquote{font-size:1em}";
        }
        // Let's make margin smaller or bigger too, if the text was resized.
        wp_add_inline_style(
            'pmb_print_page',
            $css
        );
    }

    /**
     * Helper for getting a value from the request, or setting a default.
     * @since 1.1.0
     * @param $query_param_name
     * @param $default
     * @return mixed
     */
    protected function getFromRequest($query_param_name, $default) {
        return isset($_GET[$query_param_name]) ? $_GET[$query_param_name] : $default;
    }
}