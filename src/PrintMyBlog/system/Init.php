<?php

namespace PrintMyBlog\system;

use EventEspresso\core\domain\values\Version;
use PrintMyBlog\compatibility\DetectAndActivate;
use PrintMyBlog\controllers\Admin;
use PrintMyBlog\controllers\Ajax;
use PrintMyBlog\controllers\Common;
use PrintMyBlog\controllers\Frontend;
use PrintMyBlog\controllers\GutenbergBlock;
use PrintMyBlog\controllers\LegacyPrintPage;
use PrintMyBlog\controllers\Shortcodes;
use PrintMyBlog\domain\DefaultDesigns;
use PrintMyBlog\domain\DefaultDesignTemplates;
use PrintMyBlog\domain\DefaultFileFormats;
use PrintMyBlog\domain\DefaultSectionTemplates;
use Twine\admin\news\DashboardNews;
use PrintMyBlog\system\Context;
use Twine\system\RequestType;
use Twine\system\VersionHistory;
use WPTRT\AdminNotices\Notices;
use Twine\system\Init as BaseInit;
use pmb_fs;

/**
 * Class Init
 *
 * Sets up controller classes and the like.
 *
 * Managed by \PrintMyBlog\system\Context.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          3.0.0
 *
 */
class Init extends BaseInit
{

    /**
     * @var Activation
     */
    protected $activation;

    /**
     * @var VersionHistory
     */
    protected $version_history;

    /**
     * @var RequestType
     */
    protected $request_type;

    /**
     * @var CustomPostTypes
     */
    protected $cpt;


    /**
     * Sets up PMB's general environment.
     */
    public function earlyInit()
    {
        if (function_exists('rest_proxy_loaded')) {
            define('PMB_REST_PROXY_EXISTS', true);
        } else {
            define('PMB_REST_PROXY_EXISTS', false);
        }
        parent::earlyInit();
    }

    /**
     * Just get the frontend print buttons and frontend stuff working
     */
    public function minimalInit()
    {
        define('PMB_REST_PROXY_EXISTS', false);

        $this->includes();

        load_plugin_textdomain('print-my-blog', false, PMB_DIRNAME . '/lang');
        $this->setUrls();

        if (defined('DOING_AJAX') && DOING_AJAX) {
            $ajax = $this->context->reuse('PrintMyBlog\controllers\Ajax');
            $ajax->setHooks();
        } elseif (is_admin()) {
            $admin = $this->context->reuse('PrintMyBlog\controllers\Admin');
            $admin->setHooks();
            $this->initDashboardNews();
        } else {
            (new Frontend())->setHooks();
            (new LegacyPrintPage())->setHooks();
        }
        // These are needed at least during frontend and ajax requests
        (new Shortcodes())->setHooks();


        $block_controller = new GutenbergBlock();
        $block_controller->setHooks();

        $common_controller = new Common();
        $common_controller->setHooks();
    }

    /**
     * Includes files containing functions
     */
    protected function includes()
    {
        require_once(PMB_DIR . 'inc/internal_functions.php');
        require_once(PMB_DIR . 'inc/integration_functions.php');
        require_once(PMB_DIR . 'inc/template_functions.php');
        require_once(PMB_DIR . 'inc/design_functions.php');
    }

    /**
     * Just setting up code. Not doing anything yet.
     */
    protected function registerStuff()
    {
        load_plugin_textdomain('print-my-blog', false, PMB_DIRNAME . '/lang');
        /**
         * @var $cpt CustomPostTypes
         */
        $cpt = $this->context->reuse('PrintMyBlog\system\CustomPostTypes');
        $cpt->register();
        $this->setUrls();

        /**
         * @var $default_formats DefaultFileFormats
         */
        $default_formats = $this->context->reuse('PrintMyBlog\domain\DefaultFileFormats');
        $default_formats->registerFileFormats();

        /**
         * @var $default_design_templates DefaultDesignTemplates
         */
        $default_design_templates = $this->context->reuse('PrintMyBlog\domain\DefaultDesignTemplates');
        $default_design_templates->registerDesignTemplates();

        /**
         * @var $default_designs DefaultDesigns
         */
        $default_designs = $this->context->reuse('PrintMyBlog\domain\DefaultDesigns');
        $default_designs->registerDefaultDesigns();

        /**
         * @var $default_section_templates DefaultSectionTemplates
         */
        $default_section_templates = $this->context->reuse('PrintMyBlog\domain\DefaultSectionTemplates');
        $default_section_templates->registerDefaultSectionTemplates();
    }

    /**
     * Setting up stuff we assume is in the DB
     */
    protected function setupDbEnvironment()
    {
        $activation = $this->context->reuse('PrintMyBlog\system\Activation');
        $activation->detectActivation();
    }

    /**
     * Taking action based on the current request
     */
    protected function takeActionOnIncomingRequest()
    {
        // Persistent notices need to be setup on both admin and ajax requests.
        if (is_admin()) {
            $persistent_messages = $this->context->reuse('PrintMyBlog\services\PersistentNotices');
            $persistent_messages->register();
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $ajax = $this->context->reuse('PrintMyBlog\controllers\Ajax');
            $ajax->setHooks();
        } elseif (is_admin()) {
            $admin = $this->context->reuse('PrintMyBlog\controllers\Admin');
            $admin->setHooks();
            $this->initDashboardNews();
        } else {
            (new Frontend())->setHooks();
            (new LegacyPrintPage())->setHooks();
        }
        // These are needed at least during frontend and ajax requests
        (new Shortcodes())->setHooks();


        $block_controller = new GutenbergBlock();
        $block_controller->setHooks();

        $common_controller = new Common();
        $common_controller->setHooks();
    }

    /**
     * Initializes the dashboard news code to run on AJAX and the WP dashboard page.
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


    /**
     * @since $VID:$
     */
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

        define('PMB_DESIGNS_URL', $plugin_url . 'designs/');
        define('WPTRT_JS_DIR', PMB_DIR . 'src/WPTRT/AdminNotices/');
        define('WPTRT_JS_URL', $plugin_url . 'src/WPTRT/AdminNotices/');
    }

    protected function initContext()
    {
        return Context::instance();
    }
}
