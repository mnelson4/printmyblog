<?php
/**
 * @package PrintMyBlog
 * @version 1.0
 */

/*
Plugin Name: Print My Blog
Plugin URI: https://wordpress.org/plugins/print-my-blog/
Description: Print your blog to paper or pdf in one click! Just go to tools -> Print My Blog.
Author: Michael Nelson
Version: 1.15.0
Requires at least: 4.6
Requires PHP: 5.4
Author URI: https://cmljnelson.blog
Text Domain: print-my-blog
*/

if (! defined('PMB_MIN_PHP_VER_REQUIRED')) {
    define('PMB_MIN_PHP_VER_REQUIRED', '5.4.0');
}
// make sure another version of PMB isn't installed
if (defined('PMB_VERSION')) {
    /**
     * pmb_minimum_php_version_error
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

    add_action('admin_notices', 'pmb_already_active', 1);
    // then make sure the minimum version of PHP is being used
} else if (! version_compare(PHP_VERSION, PMB_MIN_PHP_VER_REQUIRED, '>=')) {
        /**
         * pmb_minimum_php_version_error
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
                        esc_html__(
                            'We’re sorry, but Print My Blog requires PHP version %1$s or greater in order to operate. You are currently running version %2$s.%3$sIn order to update your version of PHP, you will need to contact your current hosting provider.%3$sFor information on stable PHP versions, please go to %4$s.',
                            'print-my-blog'
                        ),
                        PMB_MIN_PHP_VER_REQUIRED,
                        PHP_VERSION,
                        '<br/>',
                        '<a href="http://php.net/downloads.php">http://php.net/downloads.php</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        add_action('admin_notices', 'pmb_minimum_php_version_error', 1);
    } else {
    // it's all good! go for it!
    define('PMB_VERSION', '1.15.0.rc.000');
    define('PMB_DIR', wp_normalize_path(__DIR__) . '/');
    define('PMB_MAIN_FILE', __FILE__);
    define('PMB_TEMPLATES_DIR', PMB_DIR . 'templates/');
    define('PMB_INCLUDES_DIR', PMB_DIR . 'includes/');
    define('PMB_TWINE_DIR', PMB_DIR . 'twine_framework/');
    define('PMB_TWINE_INCLUDES_DIR', PMB_TWINE_DIR . 'includes/');
    define('PMB_ADMIN_CAP', 'read_private_posts');
    define('PMB_BASENAME', plugin_basename(PMB_MAIN_FILE));
    define('PMB_ADMIN_PAGE_SLUG', 'print-my-blog-now');
    define('PMB_ADMIN_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_PAGE_SLUG);
    define('PMB_ADMIN_SETTINGS_PAGE_SLUG', 'print-my-blog-settings');
    define('PMB_ADMIN_SETTINGS_PAGE_PATH', '/admin.php?page=' . PMB_ADMIN_SETTINGS_PAGE_SLUG);


    /**
     * adds a wp-option to indicate that PMB has been activated via the WP admin plugins page.
     * This can be used to do initial plugin installation or redirect the user to the setup page.
     */
    function pmb_plugin_activation()
    {
        update_option('pmb_activation', true);
    }

    register_activation_hook(PMB_MAIN_FILE, 'pmb_plugin_activation');
    require_once(PMB_INCLUDES_DIR . 'constants.php');
    require_once(PMB_INCLUDES_DIR . 'vendor/mnelson4/RestApiDetector/RestApiDetector.php');
    require_once(PMB_INCLUDES_DIR . 'vendor/mnelson4/RestApiDetector/RestApiDetectorError.php');
    require_once(PMB_INCLUDES_DIR . 'domain/PrintOptions.php');
    require_once(PMB_INCLUDES_DIR . 'domain/FrontendPrintSettings.php');
    require_once(PMB_TWINE_INCLUDES_DIR . 'controllers/BaseController.php');
    require_once(PMB_INCLUDES_DIR . 'controllers/PmbInit.php');
    $init_controller = new PrintMyBlog\controllers\PmbInit();
    $init_controller->setHooks();
}