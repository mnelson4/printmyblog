<?php
/**
 * @package PrintMyBlog
 * @version 1.0
 */
/*
Plugin Name: Print My Blog
Plugin URI: https://github.com/mnelson4/printmyblog
Description: Simplifies printing your entire blog. Just go to tools -> Print My Blog,
Author: Michael Nelson
Version: 1.0
Requires at least: 4.4
Requires PHP: 5.4
Author URI: https://cmljnelson.wordpress.com
*/

use PrintMyBlog\controllers\PmbInit;

if (!defined('PMG_VERSION')) {
    define('PMG_VERSION', '1.0.0.rc.001');
    define('PMG_DIR', wp_normalize_path(__DIR__) . '/');
    define('PMG_MAIN_FILE', __FILE__);
    define('PMG_TEMPLATES_DIR', PMG_DIR . 'templates/');
    define('PMG_INCLUDES_DIR', PMG_DIR . 'includes/');
    define('PMG_TWINE_DIR', PMG_DIR . 'twine_framework/');
    define('PMG_TWINE_INCLUDES_DIR', PMG_TWINE_DIR . 'includes/');
    define('PMG_ADMIN_CAP', 'export');

    /**
     * adds a wp-option to indicate that PMB has been activated via the WP admin plugins page.
     * This can be used to do initial plugin installation or redirect the user to the setup page.
     */
    function pmb_plugin_activation()
    {
        update_option('pmb_activation', true);
    }

    register_activation_hook(PMG_MAIN_FILE, 'pmb_plugin_activation');
    require_once(PMG_INCLUDES_DIR . 'constants.php');
    require_once(PMG_TWINE_INCLUDES_DIR . 'controllers/BaseController.php');
    require_once(PMG_INCLUDES_DIR . 'controllers/PmbInit.php');
    $init_controller = new PmbInit();
    $init_controller->setHooks();
}