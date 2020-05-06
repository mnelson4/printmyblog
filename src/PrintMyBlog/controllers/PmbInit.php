<?php

namespace PrintMyBlog\controllers;

use PrintMyBlog\compatibility\DetectAndActivate;
use PrintMyBlog\domain\ProNotification;
use Twine\admin\news\DashboardNews;
use Twine\controllers\BaseController;

/**
 * Class PmbInit
 *
 * Initializes the systems required to handle requests and do our logic.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbInit extends BaseController
{
    /**
     * Sets hooks that trigger this class' logic (which decides what other files to load)
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('init', array($this, 'earlyInit'), 5);
        add_action('init', array($this, 'init'));
        $compatibility_mods_loader = new DetectAndActivate();
        $compatibility_mods_loader->detectAndActivateCompatibilityMods();
    }

    public function earlyInit()
    {
        $controller = new PmbActivation();
        $controller->setHooks();
        if (function_exists('rest_proxy_loaded')) {
            define('PMB_REST_PROXY_EXISTS', true);
        } else {
            define('PMB_REST_PROXY_EXISTS', false);
        }
    }
    /**
     * Initialize PMB if everything is safe.
     * @since 1.0.0
     */
    public function init()
    {
        $this->setUrls();
        if (defined('DOING_AJAX') && DOING_AJAX) {
            (new PmbAjax())->setHooks();
        } elseif (is_admin()) {
            (new PmbAdmin())->setHooks();
            $this->initDashboardNews();
            (new ProNotification())->setHooks();
        } else {
            (new PmbFrontend())->setHooks();
            (new PmbPrintPage())->setHooks();
        }


        $block_controller = new PmbGutenbergBlock();
        $block_controller->setHooks();

        $common_controller = new PmbCommon();
        $common_controller->setHooks();
    }

    /**
     * Initializes the dashboard news code to run on AJAX and the WP dashboard page.
     * @since 1.9.1
     */
    protected function initDashboardNews()
    {
        if (is_admin()) {
            new DashboardNews(
                'https://printmy.blog/rss',
                'https://printmy.blog',
                [
                    'product_title' => 'print my blog',
                    'item_prefix' => esc_html__('Print My Blog', 'print-my-blog'),
                    'item_description' => esc_html__('Print My Blog news', 'print-my-blog'),
                    'dismiss_tooltip' => __('Dismiss all Print My Blog news', 'print-my-blog'),
                    'dismiss_confirm' => __(
                        'Are you sure you want to dismiss all Print My Blog news forever?',
                        'print-my-blog'
                    ),
                ]
            );
        }
    }
    public function setUrls()
    {
        $plugin_url = plugin_dir_url(PMB_MAIN_FILE);
        define('PMB_ASSETS_URL', $plugin_url . 'assets/');
        define('PMB_IMAGES_URL', PMB_ASSETS_URL . 'images/');
        define('PMB_SCRIPTS_URL', PMB_ASSETS_URL . 'scripts/');
        define('PMB_STYLES_URL', PMB_ASSETS_URL . 'styles/');

        define('PMB_ASSETS_DIR', PMB_DIR . 'assets/');
        define('PMB_IMAGES_DIR', PMB_ASSETS_DIR . 'images/');
        define('PMB_SCRIPTS_DIR', PMB_ASSETS_DIR . 'scripts/');
        define('PMB_STYLES_DIR', PMB_ASSETS_DIR . 'styles/');
    }
}
