<?php

namespace PrintMyBlog\controllers;

use Twine\admin\news\DashboardNews;
use Twine\controllers\BaseController;

/**
 * Class PmbInit
 *
 * Initializes the systems required to handle requests and do our logic.
 *
 * @package     Event Espresso
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
    }

    public function earlyInit()
    {
        require_once('PmbActivation.php');
        $controller = new PmbActivation();
        $controller->setHooks();
        if(function_exists('rest_proxy_loaded')) {
            define('PMB_REST_PROXY_EXISTS',true);
        } else{
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
            require_once('PmbAjax.php');
            $controller = new PmbAjax();
        } else if (is_admin()) {
            require_once('PmbAdmin.php');
            $controller = new PmbAdmin();
            require_once(PMB_TWINE_INCLUDES_DIR . 'admin/news/DashboardNews.php');
            $news = new DashboardNews(
                'https://cmljnelson.blog/category/wordpress/print-my-blog/rss',
                'https://cmljnelson.blog/category/wordpress/print-my-blog',
                [
                    'product_title' => 'print my blog',
                    'item_prefix' => esc_html__('Print My Blog', 'print-my-blog'),
                    'item_description' => esc_html__('Print My Blog news', 'print-my-blog'),
                    'dismiss_tooltip' => __('Dismiss all Print My Blog news', 'print-my-blog'),
                    'dismiss_confirm' => __('Are you sure you want to dismiss all Print My Blog news forever?', 'print-my-blog'),
                ]
            );
        } else {
            require_once('PmbFrontend.php');
            $controller = new PmbFrontend();
        }
        $controller->setHooks();

        require_once('PmbGutenbergBlock.php');
        $block_controller = new PmbGutenbergBlock();
        $block_controller->setHooks();

        require_once('PmbCommon.php');
        $common_controller = new PmbCommon();
        $common_controller->setHooks();
    }

    public function setUrls()
    {
        $plugin_url = plugin_dir_url(PMB_MAIN_FILE);
        define('PMB_ASSETS_URL', $plugin_url . 'assets/');
        define('PMB_ASSETS_DIR', PMB_DIR . 'assets/');
    }
}
