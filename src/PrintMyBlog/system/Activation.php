<?php

namespace PrintMyBlog\system;

use PrintMyBlog\db\TableManager;
use PrintMyBlog\domain\DefaultProjectContents;
use PrintMyBlog\services\DesignRegistry;
use Twine\system\RequestType;
use Twine\system\Activation as BaseActivation;

/**
 * Class Activation
 *
 * Handles installing Print My Blog, redirecting, and upgrades.
 *
 * Managed by \PrintMyBlog\system\Context.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class Activation extends BaseActivation
{

    /**
     * @var TableManager
     */
    protected $table_manager;

    /**
     * @var Capabilities
     */
    protected $capabilities;
    /**
     * @var DesignRegistry|null
     */
    protected $design_registry;
    /**
     * @var DefaultProjectContents|null
     */
    protected $project_contents;


    public function inject(
        RequestType $request_type,
        TableManager $table_manager = null,
        Capabilities $capabilities = null,
        DesignRegistry $design_registry = null,
        DefaultProjectContents $project_contents = null
    ) {
        parent::inject($request_type);
        $this->table_manager = $table_manager;
        $this->capabilities = $capabilities;
        $this->design_registry = $design_registry;
        $this->project_contents = $project_contents;
    }
    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since 1.0.0
     */
    public function detectActivation()
    {
        parent::detectActivation();
        if ($this->request_type->isBrandNewInstall() && current_user_can(PMB_ADMIN_CAP)) {
            update_option('pmb_activation', false);
            // Don't redirect if it's a bulk plugin activation
            if (isset($_GET['activate-multi'])) {
                return;
            }
            // @todo Do redirection later if we can.
            $this->redirectToActivationPage();
        }
    }


    /**
     *
     */
    public function install()
    {
        $this->table_manager->installTables();
        $this->capabilities->grantCapabilities();
        $this->design_registry->createRegisteredDesigns();
        $this->project_contents->addDefaultContents();
    }


    /**
     * Redirects
     */
    public function redirectToActivationPage()
    {
        wp_redirect(
            add_query_arg(
                array(
                    'welcome' => 1
                ),
                admin_url(PMB_ADMIN_PAGE_PATH)
            )
        );
        exit;
    }
}
