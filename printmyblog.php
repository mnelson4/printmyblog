<?php //phpcs:disable Files.SideEffects.FoundWithSymbols -- it's normal for plugin main files to be loose with that requirement.

/**
 * @package PrintMyBlog
 *
 * @wordpress-plugin
 * Plugin Name: Print My Blog
 * Plugin URI: https://printmy.blog
 * Description: Make printing your blog easy and impressive. For you & your visitors. One post or thousands.
 * Author: Michael Nelson
 * Author URI: https://printmy.blog
 * Version: 3.20.0
 * Requires at least: 4.7
 * Requires PHP: 5.4
 * Text Domain: print-my-blog
 */

use PrintMyBlog\controllers\Frontend;

if (! defined('PMB_MIN_PHP_VER_REQUIRED')) {
    define('PMB_MIN_PHP_VER_REQUIRED', '5.4.0');
}
if (! defined('PMB_MIN_WP_VER_REQUIRED')) {
    define('PMB_MIN_WP_VER_REQUIRED', '4.7');
}
global $wp_version;
// make sure another version of PMB isn't installed
if (defined('PMB_VERSION')) {
    /**
     * Function that says another version of PMB is already active.
     *
     * @return void
     */
    function pmb_already_active()
    {
        ?>
        <div class="error">
            <p>
                <?php
                esc_html_e(
                    'We’re sorry, but you have another version of Print My Blog active. Only one can be active at a time. Please deactivate one.',
                    'print-my-blog'
                );
                ?>
            </p>
        </div>
        <?php
    }

    add_action('admin_notices', 'pmb_already_active', 1, 0);

    // then make sure the minimum version of PHP is being used
} elseif (version_compare(PHP_VERSION, PMB_MIN_PHP_VER_REQUIRED, '<')) {
    /**
     * Function that says PHP version isn't high enough to run PMB.
     *
     * @return void
     */
    function pmb_minimum_php_version_error()
    {
        ?>
        <div class="error">
            <p>
                <?php
                printf(
                // translators: 1: version number, 2: version number, 3: HTML line break, 4: website address
                    esc_html__(
                        'We’re sorry, but Print My Blog requires PHP version %1$s or greater in order to operate. You are currently running version %2$s.%3$sIn order to update your version of PHP, you will need to contact your current hosting provider.%3$sFor information on stable PHP versions, please go to %4$s.',
                        'print-my-blog'
                    ),
                    esc_html(PMB_MIN_PHP_VER_REQUIRED),
                    PHP_VERSION,
                    '<br/>',
                    '<a href="http://php.net/downloads.php">http://php.net/downloads.php</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    add_action('admin_notices', 'pmb_minimum_php_version_error', 1, 0);
} elseif (
version_compare(
// first account for wp_version being pre-release
// (like RC, beta etc) which are usually in the format like 4.7-RC3-39519
    strpos($wp_version, '-') > 0 ?
        substr($wp_version, 0, strpos($wp_version, '-')) :
        $wp_version,
    PMB_MIN_WP_VER_REQUIRED,
    '<'
)
) {
    /**
     * Function that expresses WP version isn't high enough to run PMB.
     *
     * @return void
     */
    function pmb_minimum_wp_version_error()
    {
        global $wp_version;
        ?>
        <div class="error">
            <p>
                <?php
                printf(
                // translators: 1: version number, 2: version number, 3: HTML line break, 4: URL
                    esc_html__(
                        'We’re sorry, but Print My Blog requires WordPress %1$s. You are using %2$s.%3$sFor information on how to update, please see %4$s.',
                        'print-my-blog'
                    ),
                    esc_html(PMB_MIN_WP_VER_REQUIRED),
                    esc_html($wp_version),
                    '<br/>',
                    '<a href="https://wordpress.org/support/article/updating-wordpress/">https://wordpress.org/support/article/updating-wordpress/</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    add_action('admin_notices', 'pmb_minimum_wp_version_error', 1, 0);
} else {
    // it's all good! start bootstraping PMB.
    define('PMB_VERSION', '3.20.0');
    define('PMB_DIR', wp_normalize_path(__DIR__) . '/');
    define('PMB_MAIN_FILE', __FILE__);
    define('PMB_TEMPLATES_DIR', PMB_DIR . 'templates/');
    define('PMB_NO_THEME_DIR', PMB_TEMPLATES_DIR . 'no_theme/');
    define('PMB_VENDOR_DIR', PMB_DIR . 'vendor/');
    define('PMB_ADMIN_CAP', 'read_private_posts');
    define('PMB_BASENAME', plugin_basename(PMB_MAIN_FILE));
    define('PMB_DIRNAME', dirname(PMB_BASENAME));
    define('PMB_ADMIN_PAGE_SLUG', 'print-my-blog-now');
    define('PMB_ADMIN_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_PAGE_SLUG);
    define('PMB_ADMIN_PROJECTS_PAGE_SLUG', 'print-my-blog-projects');
    define('PMB_ADMIN_PROJECTS_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_PROJECTS_PAGE_SLUG);
    define('PMB_ADMIN_SETTINGS_PAGE_SLUG', 'print-my-blog-settings');
    define('PMB_ADMIN_SETTINGS_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_SETTINGS_PAGE_SLUG);
    define('PMB_ADMIN_HELP_PAGE_SLUG', 'print-my-blog-help');
    define('PMB_ADMIN_HELP_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_HELP_PAGE_SLUG);
    define('PMB_DESIGNS_DIR', PMB_DIR . 'designs/');
    define('TWINE_MAIN_FILE', PMB_MAIN_FILE);
    define('PMB_SUPPORT_EMAIL', 'please@printmy.blog');

    /* WPML support */
    if (! defined('WPML_LOAD_API_SUPPORT')) {
        define('WPML_LOAD_API_SUPPORT', true);
    }

    /**
     * Adds a wp-option to indicate that PMB has been activated via the WP admin plugins page.
     * This can be used to do initial plugin installation or redirect the user to the setup page.
     */
    function pmb_plugin_activation()
    {
        update_option('pmb_activation', true, false);
    }

    register_activation_hook(PMB_MAIN_FILE, 'pmb_plugin_activation');
    define('PMB_PRINTPAGE_SLUG', 'print-my-blog');
    require_once 'bootstrap.php';

    if (function_exists('pmb_fs')) {
        pmb_fs()->set_basename(true, __FILE__);
    } else {
        if (! function_exists('pmb_fs')) {
            /**
             * Initialize Freemius' code.
             * @return Freemius
             * @throws Freemius_Exception
             */
            function pmb_fs()
            {
                global $pmb_fs;

                if (! isset($pmb_fs)) {
                    // Include Freemius SDK.
                    require_once dirname(__FILE__) . '/freemius/start.php';
                    // don't ask to opt in if it's a tastewp site
                    $site_url = get_site_url();
                    $is_demo_site = (bool)preg_match('~https:\/\/([^\.]*\.[^-]*-tastewp\.com|[^.]*.instawp.xyz)~',$site_url);
                    $pmb_fs = fs_dynamic_init(
                        array(
                            'id' => '5396',
                            'slug' => 'print-my-blog',
                            'premium_slug' => 'print-my-blog-pro',
                            'type' => 'plugin',
                            'public_key' => 'pk_0443e9596f0e906d282bf05b115dd',
                            'is_premium' => true,
                            'premium_suffix' => 'Pro',
                            // If your plugin is a serviceware, set this option to false.
                            'has_premium_version' => true,
                            'has_addons' => false,
                            'has_paid_plans' => true,
                            'menu' => array(
                                'slug' => 'print-my-blog-projects',
                                'first-path' => 'admin.php?page=print-my-blog-now&welcome=1',
                                'contact' => false,
                                'support' => false,
                            ),
                            'anonymous_mode' => $is_demo_site
                        )
                    );
                }

                return $pmb_fs;
            }


            // Init Freemius.
            pmb_fs();
            // Signal that SDK was initiated.
            do_action('pmb_fs_loaded');
        }
    }

    // Disable the active theme if generating a PDF.
    // This needs to be done super early
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- we're just looking, not processing or saving etc.
    if ((
            defined('DOING_AJAX') ||
            isset($_REQUEST[Frontend::PMB_AJAX_INDICATOR])
        ) &&
        isset($_REQUEST['action'], $_REQUEST['format']) &&
        $_REQUEST['action'] === Frontend::PMB_PROJECT_STATUS_ACTION) {
        // Find if this project's design for this format uses the theme or not.
        // This circumvents a ton of our own code which isn't setup at all yet.
        $project_id = isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : null;
        if (! $project_id) {
            return;
        }
        $post_object = get_post($project_id);
        if (! $post_object) {
            return;
        }
        $format = isset($_REQUEST['format']) ? sanitize_key($_REQUEST['format']) : null;
        if (! $format) {
            return;
        }
        $design_id = get_post_meta($project_id, '_pmb_design_for_' . $format, true);
        if (! $design_id) {
            return;
        }
        $use_theme = get_post_meta($design_id, '_pmb_use_theme', true);
        if ($use_theme) {
            // ha, they say to use the theme. So don't change anything
            return;
        }
        // unregister the theme once we have a moment to override it
        add_action(
            'template_redirect',
            function(){
                // We don't want the theme interfering. Kill it.
                add_filter('wp_using_themes', '__return_false');
            },
            9
        );

        // some plugins and theme still assume a theme, so give them the directory of our bundled fake theme
        add_filter(
            'template_directory',
            function () {
                return PMB_NO_THEME_DIR;
            },
            100
        );
        add_filter(
            'stylesheet_directory',
            function () {
                return PMB_NO_THEME_DIR;
            },
            100
        );
        add_action(
            'init',
            function () {
                remove_action('wp_head', 'wp_custom_css_cb', 11);
                remove_action('wp_head', 'wp_custom_css_cb', 101);
            }
        );
    }
}
