<?php

namespace PrintMyBlog\controllers;

use mnelson4\RestApiDetector\RestApiDetector;
use mnelson4\RestApiDetector\RestApiDetectorError;
use PrintMyBlog\domain\PrintOptions;
use stdClass;
use Twine\controllers\BaseController;

/**
 * Class PmbPrintPage
 *
 * Sets up the print page.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PmbPrintPage extends BaseController
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
            } catch (RestApiDetectorError $exception) {
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
            $pmb_site_url = str_replace(
                array(
                    'https://',
                    'http://'
                ),
                '',
                $site_info->getSite()
            );
            $pmb_site_name = $site_info->getName();
            // If there's no name for the site, use the URL. dotEpub has an error if there is no title for the eBooks.
            if (! $pmb_site_name) {
                $pmb_site_name = $pmb_site_url;
            }
            $pmb_site_description = $site_info->getDescription();

            $pmb_show_site_title = $this->getFromRequest('show_site_title', false);
            $pmb_show_site_tagline = $this->getFromRequest('show_site_tagline', false);
            $pmb_show_site_url = $this->getFromRequest('show_site_url', false);
            $pmb_show_filters = $this->getFromRequest('show_filters', false);
            $pmb_show_date_printed = $this->getFromRequest('show_date_printed', false);
            $pmb_show_credit = $this->getFromRequest('show_credit', false);
            $user_id_to_filter_by = $this->getFromRequest('pmb-author', null);
            if ($user_id_to_filter_by) {
                $pmb_author = get_userdata($user_id_to_filter_by);
            } else {
                $pmb_author = null;
            }
            if ($site_info->isLocal()) {
                $this->proxy_for = '';
            } else {
                $this->proxy_for = $site_info->getRestApiUrl();
            }
            // enqueue our scripts and styles at the right time
            // specifically, after everybody else, so we can override them.
            add_action(
                'wp_enqueue_scripts',
                array($this,'enqueueScripts'),
                100
            );
            $pmb_after_date = $this->getDateString('after');
            $pmb_before_date = $this->getDateString('before');

            // Figure out what post type was selected.
            $post_types_using_query_var = get_post_types(array('name' => $_GET['post-type']), 'object');
            if (is_array($post_types_using_query_var)) {
                $post_type_info = reset($post_types_using_query_var);
                $pmb_post_type = $post_type_info->label;
            } else {
                $pmb_post_type = esc_html__('Unknown post type', 'print-my-blog');
            }

            // Figure out what taxonomies were selected (if any) and their terms.
            // Ideally we'll do this via the REST API, but I'm in a pinch so just doing it via PHP and
            // only when not using WP REST Proxy.
            if (empty($_GET['site']) && !empty($_GET['taxonomies'])) {
                $filtering_taxonomies = $_GET['taxonomies'];
                foreach ($filtering_taxonomies as $taxonomy => $terms_ids) {
                    $matching_taxonomy_objects = get_taxonomies(
                        array(
                            'rest_base' => $taxonomy
                        ),
                        'objects'
                    );
                    if (! is_array($matching_taxonomy_objects) || ! $matching_taxonomy_objects) {
                        continue;
                    }
                    $taxonomy_object = reset($matching_taxonomy_objects);
                    $term_objects = get_terms(
                        array(
                            'include' => implode(',', $terms_ids),
                            'hide_empty' => false
                        )
                    );
                    $term_names = array();
                    foreach ($term_objects as $term_object) {
                        $term_names[] = $term_object->name;
                    }
                    $pmb_taxonomy_filters[] = array(
                        'taxonomy' => $taxonomy_object,
                        'terms' => $term_names
                    );
                }
            } else {
                $pmb_taxonomy_filters = array();
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
    protected function getDateString($date_filter_key)
    {
        if (
            isset(
                $_GET['dates'],
                $_GET['dates'][$date_filter_key]
            ) && $_GET['dates'][$date_filter_key]
        ) {
            return date_i18n(get_option('date_format'), strtotime($_GET['dates'][$date_filter_key]));
        } else {
            return null;
        }
    }

    /**
     * @since $VID:$
     * @return string
     */
    protected function getBrowser()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = '';
        }
        // From https://stackoverflow.com/questions/3047894/how-to-detect-google-chrome-as-the-user-agent-using-php
        // Note: Brave gets detected as Chrome.
        if (
            preg_match('/(Chrome|CriOS)\//i', $agent)
            //phpcs:disable Generic.Files.LineLength.TooLong
            && !preg_match('/(Aviator|ChromePlus|coc_|Dragon|Edge|Flock|Iron|Kinza|Maxthon|MxNitro|Nichrome|OPR|Perk|Rockmelt|Seznam|Sleipnir|Spark|UBrowser|Vivaldi|WebExplorer|YaBrowser)/i', $_SERVER['HTTP_USER_AGENT'])
            //phpcs:enable
        ) {
            return 'chrome';
        }
        // From https://stackoverflow.com/questions/9209649/how-to-detect-if-browser-is-firefox-with-php
        if (strlen(strstr($agent, 'Firefox')) > 0) {
            return 'firefox';
        }
        // From https://stackoverflow.com/a/186779/1493883
        if (strstr($agent, " AppleWebKit/") && strstr($agent, " Mobile/")) {
            return 'mobile_safari';
        }
        // From https://stackoverflow.com/q/15415883/1493883
        if (strlen(strstr($agent, "Safari")) > 0) {
            return 'desktop_safari';
        }
        return 'unknown';
    }

    //phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function enqueueScripts()
    {
        //phpcs:enable
        do_action('PrintMyBlog\controllers\PmbPrintPage->enqueueScripts');
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
        if ($post_type === 'post') {
            $order_var_to_use = 'order-date';
        } else {
            $order_var_to_use = 'order-menu';
        }
        $order = $this->getFromRequest($order_var_to_use, 'asc');
        $statuses = $this->getFromRequest('statuses', ['publish','password','private','future']);
        $statuses = array_filter(
            $statuses,
            function ($input) {
                return in_array($input, ['draft','pending','private','password','publish','future','trash']);
            }
        );
        $data = [
            'header_selector' => '#pmb-in-progress-h1',
            'status_span_selector' => '.pmb-status',
            'posts_count_span_selector' => '.pmb-posts-count',
            'posts_div_selector' => '.pmb-posts-body',
            'waiting_area_selector' => '.pmb-posts-placeholder',
            'print_ready_selector' => '.pmb-print-ready',
            'loading_content_selector' => '.pmb-loading-content',
            'locale' => get_locale(),
            'image_size' => $this->getImageRelativeSize(),
            'proxy_for' => $this->proxy_for,
            'columns' => $this->getFromRequest('columns', 1),
            'post_type' => $post_type,
            'rendering_wait' => $this->getFromRequest('rendering-wait', 500),
            'include_inline_js' => (bool) $this->getFromRequest('include-inline-js', false),
            'links' => (string) $this->getFromRequest('links', 'include'),
            'filters' => (object) $this->getFromRequest('taxonomies', new stdClass()),
            'foogallery' => function_exists('foogallery_fs'),
            'is_user_logged_in' => is_user_logged_in(),
            'format' => $this->getFromRequest('format', 'print'),
            'statuses' => $statuses,
            'author' => $this->getFromRequest('pmb-author', null),
            'post' => $this->getFromRequest('pmb-post', null),
            'order' => $order,
            'shortcodes' => $this->getFromRequest('shortcodes', null),
        ];
        $lang = $this->getFromRequest('lang', null);
        if ($lang) {
            $data['lang'] = $lang;
        }
        // add the before and after filters, if they were provided
        $dates = $this->getFromRequest('dates', array());
        // Check if they entered the dates backwards.
        if (!empty($dates['before']) && !empty($dates['after']) && $dates['before'] < $dates['after']) {
            $dates = [
                'after' => $dates['before'],
                'before' => $dates['after']
            ];
        }
        if (!empty($dates['after'])) {
            $data['filters']->after = $dates['after'] . 'T00:00:00';
        }
        if (!empty($dates['before'])) {
            $data['filters']->before = $dates['before'] . 'T23:59:59';
        }
        $print_options = new PrintOptions();
        foreach ($print_options->postContentOptions() as $option_name => $option_details) {
            $data[ $option_name] = (bool)$this->getFromRequest(
                $option_name,
                false
            );
        }

        $init_error_message = esc_html__(
            'There seems to be an error initializing. Please verify you are using an up-to-date web browser.',
            'print-my-blog'
        );
        if (is_user_logged_in()) {
            $init_error_message .= "\n"
                . esc_html__(
                //phpcs:disable Generic.Files.LineLength.TooLong
                    'Also check the WP REST API isn’t disabled by a security plugin. Feel free to contact Print My Blog support.',
                    //phpcs:enable
                    'print-my-blog'
                );
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
                    'error_fetching_posts' => esc_html__(
                        'There was an error fetching posts. It was: ',
                        'print-my-blog'
                    ),
                    'no_response' => esc_html__('No response from WP REST API.', 'print-my-blog'),
                    'error' => esc_html__('Sorry, There was a Problem 😢', 'print-my-blog'),
                    'troubleshooting' => sprintf(
                        //phpcs:disable Generic.Files.LineLength.TooLong
                        esc_html__('%1$sRead our FAQs%2$s, then feel free to ask for help in %3$sthe support forum.%2$s', 'print-my-blog'),
                        '<a href="https://wordpress.org/plugins/print-my-blog/#%0Athe%20print%20page%20says%20%E2%80%9Cthere%20seems%20to%20be%20an%20error%20initializing%E2%80%A6%E2%80%9D%2C%20or%20is%20stuck%20on%20%E2%80%9Cloading%20content%E2%80%9D%2C%20or%20i%20can%E2%80%99t%20filter%20by%20categories%20or%20terms%20from%20the%20print%20setup%20page%0A" target="_blank">',
                        //phpcs:enable
                        '</a>',
                        '<a href="https://wordpress.org/support/plugin/print-my-blog/" target="_blank">'
                    ),
                    'comments' => esc_html__('Comments', 'print-my-blog'),
                    'no_comments' => esc_html__('No Comments', 'print-my-blog'),
                    'says' => __('<span class="screen-reader-text says">says:</span>', 'print-my-blog'),
                    'id' => esc_html__('ID:', 'print-my-blog'),
                    'by' => esc_html__('By', 'print-my-blog'),
                    'protected' => esc_html__('Protected:', 'print-my-blog'),
                    'private' => esc_html__('Private:', 'print-my-blog'),
                    'init_error' => $init_error_message,
                    'copied' => esc_html__('Copied! Ready to paste.', 'print-my-blog'),
                    //phpcs:disable Generic.Files.LineLength.TooLong
                    'copy_error' => esc_html__('There was an error copying. You can still select all the text manually and copy it.', 'print-my-blog')
                    //phpcs:enable
                ),
                'data' => $data,
            )
        );
        $this->enqueueInlineStyleBasedOnOptions();
        $this->loadThemeCompatibilityScriptsAndStylesheets();
    }

    protected function getImageRelativeSize()
    {
        $requested_size = sanitize_key($this->getFromRequest('image-size', 'full'));
        // The actual usable vertical space is quite a bit less than 11 inches; especially when you take
        // into account there is the post's header and metainfo.
        $page_height = 8;
        switch ($requested_size) {
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
                return $page_height;
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
        $this->loadThemeCompatibilityIfItExists($slug);
        $this->loadThemeCompatibilityIfItExists($theme->template);
    }

    protected function loadThemeCompatibilityIfItExists($slug)
    {
        $theme_slug_path =  'styles/theme-compatibility/' . $slug . '.css';
        if (file_exists(PMB_ASSETS_DIR . $theme_slug_path)) {
            wp_enqueue_style(
                'pmb_print_page_theme_compatibility',
                PMB_ASSETS_URL . $theme_slug_path,
                array(),
                filemtime(PMB_ASSETS_DIR .  $theme_slug_path)
            );
        }
        $script_slug_path = 'scripts/theme-compatibility/' . $slug . '.js';
        if (file_exists(PMB_ASSETS_DIR . $script_slug_path)) {
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
        $columns = intval($this->getFromRequest('columns', 1));
        $post_page_break = (bool)$this->getFromRequest('post-page-break', false);
        $font_size = sanitize_key($this->getFromRequest('font-size', 'small'));
        $css = "
        .entry-content{
            column-count: $columns;
        }
        ";
        // If it's a multi-column design, remove the margins around "pmb_image"s. They offset the image so that even
        // if it takes up the full column width, it's now offset and so spills over onto the other column.
        // Removing the margins fixes that. And because "pmb_image"s take up the width, they don't prevent
        // the image contained inside them from being centered anyhow. So this seems to be win-win.
        if ($columns > 1) {
            $css .= "
        	.pmb-image{
        	    margin-left:0;
        	    margin-right:0;
        	}
        	.pmb-image img{
        	    width:100%;
        	}
        	.single-featured-image-header img{
        	    width:100%;
        	}";
        }
        if ($post_page_break) {
            $css .= '.pmb-post-article:not(:first-child){page-break-before:always;}';
            $css .= '@media screen{.pmb-post-article:not(:first-child){margin-top:20vw;}}';
        }
        $font_size_map = array(
            'tiny' => '0.50em',
            'small' => '0.75em',
            // Leave out normal, let the theme decide.
            'large' => '1.25em',
        );
        if (isset($font_size_map[$font_size])) {
            $font_size_css = $font_size_map[$font_size];
            $css .= ".pmb-posts{font-size:$font_size_css;}
            h1{font-size:1.3em !important;}
            h2{font-size:1.2em !important;}
            h3{font-size:1.1em !important;}
            ul, ol, p{margin-bottom:0.5em;margin-top:0.5em;}
            blockquote{font-size:1em}";
        }

        // Dynamically handle adding the CSS to place URLs in parentheses after some hyperlinks
        // (but be careful to not put them in headers, image galleries, and other places they look terrible.)
        if ($this->getFromRequest('links', '') === 'parens') {
            $pre_selects = array(
                '.pmb-posts p',
                '.pmb-posts ul',
                '.pmb-posts ol'
            );
            $full_css_selctors = array();
            foreach ($pre_selects as $pre_select) {
                $full_css_selctors[] = $pre_select . ' a[href]:after';
            }
            $css .= implode(', ', $full_css_selctors) . '{content: " (" attr(href) ")"';
        }
        // Let's make margin smaller or bigger too, if the text was resized.
        wp_add_inline_style(
            'pmb_print_page',
            $css
        );
    }
}
// End of file PmbPrintPage.php
// Location: PrintMyBlog\controllers/PmbPrintPage.php
