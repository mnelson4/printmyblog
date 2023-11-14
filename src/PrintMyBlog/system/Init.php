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
use mnelson4\AdminNotices\Notices;
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
        $compatibility_mods_loader = $this->context->reuse('PrintMyBlog\compatibility\DetectAndActivate');
        $compatibility_mods_loader->detectAndActivateGlobalCompatibilityMods();
        // There's no actions between when we know it's a REST request ('parse_request' is when "REST_REQUEST" gets
        // defined)
        // and the posts are fetched for the REST API response, except this one (and maybe another).
        add_filter('rest_pre_dispatch', [$compatibility_mods_loader, 'activateRenderingCompatibilityModes'], 11);
        parent::earlyInit();
    }

    /**
     * Includes files containing functions
     */
    protected function includes()
    {
        require_once PMB_DIR . 'inc/internal_functions.php';
        require_once PMB_DIR . 'inc/integration_functions.php';
        require_once PMB_DIR . 'inc/template_functions.php';
        require_once PMB_DIR . 'inc/design_functions.php';
    }

    /**
     * Just setting up code. Not doing anything yet.
     */
    protected function registerStuff()
    {
        load_plugin_textdomain('print-my-blog', false, PMB_DIRNAME . '/lang');

        $uploads = $this->context->reuse('PrintMyBlog\system\FileUploads');
        $uploads->setup();

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
        } else {
            $frontend = $this->context->reuse('PrintMyBlog\controllers\Frontend');
            $frontend->setHooks();
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
        define('MNELSON4_JS_DIR', PMB_DIR . 'src/mnelson4/AdminNotices/');
        define('MNELSON4_JS_URL', $plugin_url . 'src/mnelson4/AdminNotices/');
    }

    /**
     * @return \Twine\system\Context
     */
    protected function initContext()
    {
        return Context::instance();
    }
}
