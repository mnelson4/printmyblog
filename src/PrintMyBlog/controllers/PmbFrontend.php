<?php

namespace PrintMyBlog\controllers;

use mnelson4\RestApiDetector\RestApiDetector;
use mnelson4\RestApiDetector\RestApiDetectorError;
use PrintMyBlog\domain\FrontendPrintSettings;
use PrintMyBlog\domain\PrintNowSettings;
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
        add_filter(
            'the_content',
            array($this, 'addPrintButton')
        );
    }
    public function addPrintButton($content){
        global $post;
        if($post->post_type === 'post' && is_single() && ! post_password_required($post)){
            $print_settings = new FrontendPrintSettings();
            $print_settings->load();
            if($print_settings->showButtons()){
                $base_url = site_url() . "?post-type=post&include-private-posts=1&show_site_title=1&show_site_tagline=1&show_site_url=1&show_date_printed=1&show_title=1&show_date=1&show_categories=1&show_featured_image=1&show_content=1&post-page-break=on&columns=1&font-size=normal&image-size=medium&links=include&rendering-wait=10&print-my-blog=1&format=%s&pmb-post=%d";
                $html = '<div class="pmb-print-this-page wp-block-button">';
                foreach($print_settings->formats() as $slug => $settings){
                    if(! $print_settings->isActive($slug)){
                        continue;
                    }
                    $url = sprintf(
                        $base_url,
                        $slug,
                        $post->ID
                    );
                    $html .= ' <a href="' . $url . '" class="button button-secondary wp-block-button__link">' . $print_settings->getFrontendLabel($slug) . '</a>';
                }
                $html .= '</div>';
                return $html . $content;
            }
        }
        return $content;
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
            } catch(RestApiDetectorError $exception) {
                global $pmb_wp_error;
                $pmb_wp_error = $exception->wp_error();
                return PMB_TEMPLATES_DIR . 'print_page_error.template.php';
            }
            global $pmb_site_name,
                   $pmb_site_description,
                   $pmb_site_url,
                   $pmb_show_site_title,
                   $pmb_show_site_tagline,
                   $pmb_show_site_url,
                   $pmb_show_filters,
                   $pmb_show_date_printed,
                   $pmb_show_credit,
                   $pmb_after_date,
                   $pmb_before_date,
                   $pmb_post_type,
                   $pmb_taxonomy_filters,
                   $pmb_format,
                   $pmb_browser,
                   $pmb_author;
            $pmb_site_name = $site_info->getName();
            $pmb_site_description = $site_info->getDescription();
            $pmb_site_url = str_replace(
                array(
                    'https://',
                    'http://'
                ),
                '',
                $site_info->getSite()
            );
            $pmb_show_site_title = $this->getFromRequest('show_site_title', false);
            $pmb_show_site_tagline = $this->getFromRequest('show_site_tagline', false);
            $pmb_show_site_url = $this->getFromRequest('show_site_url', false);
            $pmb_show_filters = $this->getFromRequest('show_filters', false);
            $pmb_show_date_printed = $this->getFromRequest('show_date_printed', false);
            $pmb_show_credit = $this->getFromRequest('show_credit', false);
            $user_id_to_filter_by = $this->getFromRequest('pmb-author', null);
            if($user_id_to_filter_by){
                $pmb_author = get_userdata( $user_id_to_filter_by);
            } else {
                $pmb_author = null;
            }
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
            $pmb_after_date = $this->getDateString('after');
            $pmb_before_date = $this->getDateString('before');

            // Figure out what post type was selected.
            $post_types_using_query_var = get_post_types(array('name' => $_GET['post-type']), 'object');
            if(is_array($post_types_using_query_var)){
                $post_type_info = reset($post_types_using_query_var);
                $pmb_post_type = $post_type_info->label;
            } else {
                $pmb_post_type = esc_html__('Unknown post type', 'print-my-blog');
            }

            // Figure out what taxonomies were selected (if any) and their terms.
            // Ideally we'll do this via the REST API, but I'm in a pinch so just doing it via PHP and
            // only when not using WP REST Proxy.
            global $wp_taxonomies;
            if(empty($_GET['site']) && !empty($_GET['taxonomies'])){
                $filtering_taxonomies = $_GET['taxonomies'];
                foreach($filtering_taxonomies as $taxonomy => $terms_ids){
                    $matching_taxonomy_objects = get_taxonomies(
                        array(
                            'rest_base' => $taxonomy
                        ),
                        'objects'
                    );
                    if(! is_array($matching_taxonomy_objects) || ! $matching_taxonomy_objects){
                        continue;
                    }
                    $taxonomy_object = reset($matching_taxonomy_objects);
                    $term_objects = get_terms(
                        array(
                            'include' => implode(',',$terms_ids),
                            'hide_empty' => false
                        )
                    );
                    $term_names = array();
                    foreach($term_objects as $term_object){
                        $term_names[] = $term_object->name;
                    }
                    $pmb_taxonomy_filters[] = array(
                        'taxonomy' => $taxonomy_object,
                        'terms' => $term_names
                    );
                }
            } else {
                $pmb_taxonomy_filters = array();
                $wp_taxonomies = array();
            }
            $pmb_format = $this->getFromRequest('format', 'print');
            $pmb_browser = $this->getBrowser();
            return PMB_TEMPLATES_DIR . 'print_page.template.php';
        }
        return $template;
    }

    /**
     * @since $VID:$
     * @param $date_filter_key
     * @return null|string
     */
    protected function getDateString($date_filter_key){
        if(isset(
            $_GET['dates'],
            $_GET['dates'][$date_filter_key]
        ) && $_GET['dates'][$date_filter_key]
        ) {
            return date_i18n( get_option( 'date_format'), strtotime($_GET['dates'][$date_filter_key]));
        } else {
            return null;
        }
    }

    /**
     * @since $VID:$
     * @return string
     */
    protected function getBrowser(){
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = '';
        }
        // From https://stackoverflow.com/questions/3047894/how-to-detect-google-chrome-as-the-user-agent-using-php
        // Note: Brave gets detected as Chrome.
        if(preg_match('/(Chrome|CriOS)\//i',$agent)
            && !preg_match('/(Aviator|ChromePlus|coc_|Dragon|Edge|Flock|Iron|Kinza|Maxthon|MxNitro|Nichrome|OPR|Perk|Rockmelt|Seznam|Sleipnir|Spark|UBrowser|Vivaldi|WebExplorer|YaBrowser)/i',$_SERVER['HTTP_USER_AGENT'])){
            return 'chrome';
        }
        // From https://stackoverflow.com/questions/9209649/how-to-detect-if-browser-is-firefox-with-php
        if (strlen(strstr($agent, 'Firefox')) > 0) {
            return 'firefox';
        }
        // From https://stackoverflow.com/a/186779/1493883
        if (strstr($agent, " AppleWebKit/") && strstr($agent, " Mobile/"))
        {
            return 'mobile_safari';
        }
        // From https://stackoverflow.com/q/15415883/1493883
        if(strlen(strstr($agent,"Safari")) > 0 ){
            return 'desktop_safari';
        }
        return 'unknown';
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

        $post_type = $this->getFromRequest('post-type', 'post');
        if($post_type === 'post'){
            $order_var_to_use = 'order-date';

        } else {
            $order_var_to_use = 'order-menu';
        }
        $order = $this->getFromRequest($order_var_to_use, 'asc');
        $data = [
            'header_selector' => '#pmb-in-progress-h1',
            'status_span_selector' => '.pmb-status',
            'posts_count_span_selector' => '.pmb-posts-count',
            'posts_div_selector' => '.pmb-posts-body',
            'waiting_area_selector' => '.pmb-posts-placeholder',
            'print_ready_selector' => '.pmb-print-ready',
            'cancel_button_selector' => '.pmb-cancel-button',
            'locale' => get_locale(),
            'image_size' => $this->getImageRelativeSize(),
            'proxy_for' => $this->proxy_for,
            'columns' => $this->getFromRequest('columns', 1),
            'post_type' => $post_type,
            'rendering_wait' => $this->getFromRequest('rendering-wait', 500),
            'include_inline_js' => (bool) $this->getFromRequest('include-inline-js', false),
            'links' => (string) $this->getFromRequest('links', 'include'),
            'filters' => (object) $this->getFromRequest('taxonomies', new stdClass),
            'foogallery' => function_exists('foogallery_fs'),
            'is_user_logged_in' => is_user_logged_in(),
            'format' => $this->getFromRequest('format', 'print'),
            'include_private_posts' => (bool) $this->getFromRequest('include-private-posts', false),
            'author' => $this->getFromRequest('pmb-author', null),
            'post' => $this->getFromRequest('pmb-post', null),
            'order' => $order
        ];
        // add the before and after filters, if they were provided
        $dates = $this->getFromRequest('dates', array());
        if(isset($dates['after'])){
            $data['filters']->after = $dates['after'] . 'T00:00:00';
        }
        if(isset($dates['before'])){
            $data['filters']->before = $dates['before'] . 'T23:59:59';
        }
        $print_options = new PrintOptions();
        foreach($print_options->postContentOptions() as $option_name => $option_details){
            $data['show_' . $option_name] = (bool)$this->getFromRequest('show_' . $option_name, false);
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
                    'no_comments' => esc_html__('No Comments', 'print-my-blog'),
                    'says' => __('<span class="screen-reader-text says">says:</span>', 'print-my-blog'),
                    'id' => esc_html__('ID:', 'print-my-blog'),
                    'by' => esc_html__('By', 'print-my-blog'),
                    'protected' => esc_html__('Protected:', 'print-my-blog'),
                    'private' => esc_html__('Private:', 'print-my-blog'),
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
        // The actual usable vertical space is quite a bit less than 11 inches; especially when you take
        // into account there is the post's header and metainfo.
        $page_height = 8;
        switch($requested_size) {
            case 'large':
                return $page_height * 3 / 4;
                break;
            case 'medium':
                return $page_height / 2;
                break;
            case 'small':
                return $page_height / 4;
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