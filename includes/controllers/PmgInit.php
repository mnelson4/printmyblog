<?php

namespace PrintMyBlog\controllers;
use Twine\controllers\BaseController;
/**
 * Class PmgInit
 *
 * Initializes the systems required to handle requests and do our logic.
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PmgInit extends BaseController
{
    /**
     * Sets hooks that trigger this class' logic (which decides what other files to load)
     * @since $VID:$
     */
    public function setHooks()
    {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize PMG if everything is safe.
     * @since $VID:$
     */
    public function init()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // we have nothing to do on ajax requests.
            return;
        }
        $this->setUrls();
        if (is_admin()) {
            require_once('PmgAdmin.php');
            $controller = new PmgAdmin();
        } else {
            require_once('PmgFrontend.php');
            $controller = new PmgFrontend();
        }
        $controller->setHooks();
    }

    public function setUrls()
    {
        $plugin_url = plugin_dir_url(PMG_MAIN_FILE);
        define('PMG_ASSETS_URL', $plugin_url . 'assets/');
        define('PMG_ASSETS_DIR', PMG_DIR . 'assets/');
    }
}
