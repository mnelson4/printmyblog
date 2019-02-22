<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;
use WP_Error;

class PmbFrontend extends BaseController
{
    /**
     * @var URL of domain we'd like this site to proxy for, so we can print that blog instead.
     */
    protected $proxy_for;
    public function setHooks()
    {
        add_filter('template_include', array($this, 'templateRedirect'), 20001 /* after Enfold theme, 20,000 */);
    }

    /**
     * Determines if the request is for our page generator page, and if so, uses our template for it.
     * @since 1.0.0
     */
    public function templateRedirect($template)
    {

        if (isset($_GET[PMB_PRINTPAGE_SLUG])) {

            $site_info = $this->getSiteInfo();
            if(is_wp_error($site_info)){
                global $pmb_wp_error;
                $pmb_wp_error = $site_info;
                return PMB_TEMPLATES_DIR . 'print_page_error.template.php';
            }
            global $pmb_site_name, $pmb_site_description, $pmb_site_url,  $pmb_printout_meta;
            $pmb_site_name = $site_info['name'];
            $pmb_site_description = $site_info['description'];
            $pmb_site_url = $site_info['url'];
            $pmb_printout_meta = $this->getFromRequest('printout-meta', false);
            $this->proxy_for = $site_info['proxy_for'];
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
                    'says' => __('<span class="screen-reader-text says">says:</span>', 'print-my-blog')
                ),
                'data' => array(
                    'locale' => get_locale(),
                    'image_size' => $this->getImageRelativeSize(),
                    'proxy_for' => $this->proxy_for,
                    'include_excerpts' => (bool)$this->getFromRequest('include-excerpts', false),
                    'columns' => $this->getFromRequest('columns',1),
                    'post_type' => $this->getFromRequest('post-type', 'post'),
                    'rendering_wait' => $this->getFromRequest('rendering-wait', 500),
                    'include_inline_js' => (bool)$this->getFromRequest('include-inline-js', false),
                    'links' => (string)$this->getFromRequest('links', 'include'),
                    'comments' => (bool)$this->getFromRequest('comments', false),
                ),
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
     * Gets the site name and URL (works if they provide the "site" query param too,
     * being the URL, including schema, of a self-hosted or WordPress.com site)
     * @since $VID:$
     * @return array|null
     */
    protected function getSiteInfo()
    {
        // check for a site request param
        if(empty($_GET['site'])){
            return array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => get_bloginfo('url'),
                'proxy_for' => null
            );
        }
        // If they forgot to add http(s), add it for them.
        if(strpos($_GET['site'], 'http://') === false && strpos($_GET['site'], 'https://') === false) {
            $_GET['site'] = 'http://' . $_GET['site'];
        }
        // if there is one, check if it exists in wordpress.com, eg "retirementreflections.com"
        $site = sanitize_text_field($_GET['site']);


        // Let's see if it's self-hosted...
        $data = $this->getSelfHostedSiteInfo($site);
        if($data === false){
            $data = $this->getWordPressComSiteInfo($site);
        }
        // Alright, there was no link to the REST API index. But maybe it's a WordPress.com site...
        return $data;
    }

    /**
     * Tries to get the site's name, description, and URL, assuming it's self-hosted.
     * Returns an array on success, false if the site wasn't a self-hosted WordPress site, or
     * WP_Error if the site is self-hosted WordPress but had an error.
     * @since $VID:$
     * @param $site
     * @return array|bool|WP_Error
     */
    protected function getSelfHostedSiteInfo($site){
        $response = wp_remote_get($site, array('timeout'     => 30));
        if (is_wp_error($response)) {
            return $response;
        }
        $response_body = wp_remote_retrieve_body($response);
        $wp_api_url = null;
        $matches = array();
        if( preg_match(
            //looking for somethign like "<link rel='https://api.w.org/' href='http://wpcowichan.org/wp-json/' />"
                '<link rel=\'https\:\/\/api\.w\.org\/\' href=\'(.*)\' \/>',
                $response_body,
                $matches
            )
            && count($matches) === 2) {
            // grab from site index
            $wp_api_url = $matches[1];
            $response = wp_remote_get($wp_api_url, array('timeout'     => 30));
            if (is_wp_error($response)) {
                // The WP JSON index existed, but didn't work. Let's tell the user.
                return $response;
            }
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body,true);
            if (! is_array($response_data)) {
                return new WP_Error('no_json', __('The self-hosted WordPress site has an error in its REST API data.', 'print-my-blog'));
            }
            if (isset($response_data['code'], $response_data['message'])) {
                return new WP_Error($response_data['code'], $response_data['message']);
            }
            if(isset($response_data['name'], $response_data['description'])){
                return array(
                    'name' => $response_data['name'],
                    'description' => $response_data['description'],
                    'proxy_for' => $wp_api_url . 'wp/v2/',
                    'url' => $site
                );
            }
            // so we didn't get an error or a proper response, but it's JSON? That's really weird.
            return new WP_Error('unknown_response', __('The self-hosted WordPress site responded with an unexpected response.', 'print-my-blog'));
        }
        // ok, let caller know we didn't have an error, but nor did we find the site's data.
        return false;
    }

    /**
     * Tries to get the site name, description and URL from a site on WordPress.com.
     * Returns an array on success, or a WP_Error. If the site doesn't appear to be on WordPress.com
     * also has an error.
     * @since $VID:$
     * @param $site
     * @return array|WP_Error
     */
    protected function getWordPressComSiteInfo($site){
        $domain = str_replace(array('http://','https://'),'',$site);
        $response = wp_remote_get(
            'https://public-api.wordpress.com/rest/v1.1/sites/' . $domain
        );

        // let's see what WordPress.com has to say about this site...
        if (is_wp_error($response)) {
            return $response;
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        if (! is_array($response_data)) {
            return new WP_Error('no_json', __('The WordPress.com site has an error in its REST API data.', 'print-my-blog'));
        }
        if (isset($response_data['name'], $response_data['description'])) {
            return array(
                'name' => $response_data['name'],
                'description' => $response_data['description'],
                'proxy_for' => 'https://public-api.wordpress.com/wp/v2/sites/' . $domain,
                'url' => $site,
            );
        }
        if(isset($response_data['error'], $response_data['message'])) {
            if($response_data['error'] === 'unknown_blog') {
                return new WP_Error('not_wordpress', esc_html__('The URL you provided does not appear to be a WordPress website', 'print-my-blog'));
            }
            return new WP_Error($response_data['error'], $response_data['message']);
        }
        // so we didn't get an error or a proper response, but it's JSON? That's really weird.
        return new WP_Error('unknown_response', __('The WordPress.com site responded with an unexpected response.', 'print-my-blog'));
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