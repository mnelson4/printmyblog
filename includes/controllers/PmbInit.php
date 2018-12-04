<?php

namespace PrintMyBlog\controllers;

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
        if(function_exists('rest_api_proxy_loaded')) {
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
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // we have nothing to do on ajax requests.
            return;
        }
        $this->setUrls();
        if (is_admin()) {
            require_once('PmbAdmin.php');
            $controller = new PmbAdmin();
        } else {
            require_once('PmbFrontend.php');
            $controller = new PmbFrontend();
        }
        $controller->setHooks();
    }

    public function setUrls()
    {
        $plugin_url = plugin_dir_url(PMB_MAIN_FILE);
        define('PMB_ASSETS_URL', $plugin_url . 'assets/');
        define('PMB_ASSETS_DIR', PMB_DIR . 'assets/');
    }
}
