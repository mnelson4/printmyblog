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
use PrintMyBlog\controllers\LoadingPage;
use PrintMyBlog\controllers\Shortcodes;
use PrintMyBlog\domain\DefaultDesigns;
use PrintMyBlog\domain\DefaultDesignTemplates;
use PrintMyBlog\domain\DefaultFileFormats;
use PrintMyBlog\domain\ProNotification;
use Twine\admin\news\DashboardNews;
use Twine\system\RequestType;
use Twine\system\VersionHistory;
use WPTRT\AdminNotices\Notices;

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
class Init
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
     * @var Context
     */
    protected $context;

    /**
     * Sets up hooks that will initialize the code that will run PMB.
     */
    public function setHooks()
    {
        $this->context = Context::instance();
        add_action('init', array($this, 'earlyInit'), 5);
        add_action('init', array($this, 'init'));
        $compatibility_mods_loader = new DetectAndActivate();
        $compatibility_mods_loader->detectAndActivateCompatibilityMods();
    }


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
        $persistent_notices = $this->context->reuse('WPTRT\AdminNotices\Notices');
        $persistent_notices->boot();
    }
    /**
     * Sets up PMB's code that will will set other hooks
     */
    public function init()
    {
        $this->includes();
        $this->defineTerms();
        $this->setupDbEnvironment();
        $this->takeActionOnIncomingRequest();
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
    protected function defineTerms()
    {
        /**
         * @var $request_type RequestType
         */
        $request_type = $this->context->reuse('Twine\system\RequestType');
        $request_type->getRequestType();

        /**
         * @var $version_history VersionHistory
         */
        $version_history = $this->context->reuse('Twine\system\VersionHistory');
        $version_history->maybeRecordVersionChange();

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
            $persistent_messages = $this->context->reuse('PrintMyBlog\system\PersistentNotices');
            $persistent_messages->register();
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $ajax = $this->context->reuse('PrintMyBlog\controllers\Ajax');
            $ajax->setHooks();
        } elseif (is_admin()) {
            $admin = $this->context->reuse('PrintMyBlog\controllers\Admin');
            $admin->setHooks();
            $this->initDashboardNews();
            (new ProNotification())->setHooks();
        } else {
            (new Frontend())->setHooks();
            (new LegacyPrintPage())->setHooks();
            (new LoadingPage())->setHooks();
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
    }
}
