<?php


namespace Twine\system;


use PrintMyBlog\compatibility\DetectAndActivate;

abstract class Init{

	/**
	 * @var Context
	 */
	protected $context;
	/**
	 * @return Context
	 */
	protected abstract function initContext();

	public function setHooks(){
		$this->context = $this->initContext();
		add_action('init', array($this, 'earlyInit'), 5);
		add_action('init', array($this, 'init'));
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
		$persistent_notices = $this->context->reuse('WPTRT\AdminNotices\Notices');
		$persistent_notices->boot();
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
	 * Good place to include non-autoloaded files, like "template tags"
	 */
	protected abstract function includes();

	/**
	 * Makes use of your context's 'Twine\system\RequestType' and 'Twine\system\VersionHistory'
	 * to figure out the type of request and record the version history
	 */
	protected function initRequest(){
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
	protected abstract function registerStuff();

	/**
	 * Good place to detect if there's an activation and setup the DB
	 */
	protected abstract function setupDbEnvironment();

	/**
	 * This is where you can actually do something based on the
	 * request. Eg, process the request, do business logic, start
	 * thinking about a response
	 */
	protected abstract function takeActionOnIncomingRequest();

	/**
	 * Right place to use plugin_dir_url() to get the
	 * URLs to any URLs of your site
	 */
	protected abstract function setUrls();
}