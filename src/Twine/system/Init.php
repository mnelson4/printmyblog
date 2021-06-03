<?php

namespace Twine\system;

use PrintMyBlog\compatibility\DetectAndActivate;

abstract class Init
{

    /**
     * @var Context
     */
    protected $context;
    /**
     * @return Context
     */
    abstract protected function initContext();

    public function setHooks()
    {
        add_action('plugins_loaded', array($this,'pluginsLoaded'));
    }

    public function pluginsLoaded(){
        // prevent loading any PMB until they've ever registered or opted-out of Freemius
        $this->context = $this->initContext();
        if(pmb_fs()->is_anonymous() || pmb_fs()->is_registered()){
            add_action('init', array($this, 'earlyInit'), 5);
            add_action('init', array($this, 'init'));
        } else {
            add_action('init', array($this,'minimalInit'));
        }
    }

    /**
     * Sets up PMB's general environment.
     */
    public function earlyInit()
    {
        $compatibility_mods_loader = $this->context->reuse('PrintMyBlog\compatibility\DetectAndActivate');
        $compatibility_mods_loader->detectAndActivateGlobalCompatibilityMods();
        // There's no actions between when we know it's a REST request ('parse_request' is when "REST_REQUEST" gets
        // defined)
        // and the posts are fetched for the REST API response, except this one (and maybe another).
        add_filter('rest_pre_dispatch', [$compatibility_mods_loader,'activateRenderingCompatibilityModes'], 11);
    }

    /**
     * Sets up PMB's code that will will set other hooks
     */
    public function init()
    {
        $this->includes();
        $this->initRequest();
        $this->registerStuff();
        $this->setupDbEnvironment();
        $this->takeActionOnIncomingRequest();
    }

    /**
     * Initializes a minimal set of features, for before Freemius has been opted-into. Eg frontend stuff taht should still work
     * even before they've opted in or out of Freemius.
     * This is really only helpful when migrating from pmb 2 to 3 (not so much new activations) because pmb 2 users
     * expect their print buttons to keep working after upgrading to 3 and before opting it or out of Freemius
     */
    public function minimalInit(){

    }

    /**
     * Good place to include non-autoloaded files, like "template tags"
     */
    abstract protected function includes();

    /**
     * Makes use of your context's 'Twine\system\RequestType' and 'Twine\system\VersionHistory'
     * to figure out the type of request and record the version history
     */
    protected function initRequest()
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
    }

    /**
     * Good place to register custom post types and the like
     */
    abstract protected function registerStuff();

    /**
     * Good place to detect if there's an activation and setup the DB
     */
    abstract protected function setupDbEnvironment();

    /**
     * This is where you can actually do something based on the
     * request. Eg, process the request, do business logic, start
     * thinking about a response
     */
    abstract protected function takeActionOnIncomingRequest();

    /**
     * Right place to use plugin_dir_url() to get the
     * URLs to any URLs of your site
     */
    abstract protected function setUrls();
}
