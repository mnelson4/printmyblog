<?php

namespace PrintMyBlog\system;

use PrintMyBlog\db\migrations\MigrationManager;
use PrintMyBlog\db\TableManager;
use PrintMyBlog\domain\DefaultProjectContents;
use PrintMyBlog\services\DesignRegistry;
use Twine\system\RequestType;
use Twine\system\Activation as BaseActivation;
use Twine\system\VersionHistory;

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
    /**
     * @var MigrationManager
     */
    private $migration_manager;
    /**
     * @var VersionHistory|null
     */
    private $version_history;


    public function inject(
        RequestType $request_type,
        TableManager $table_manager = null,
        Capabilities $capabilities = null,
        DesignRegistry $design_registry = null,
        DefaultProjectContents $project_contents = null,
        MigrationManager $migration_manager = null,
        VersionHistory $version_history = null
    ) {
        parent::inject($request_type);
        $this->table_manager = $table_manager;
        $this->capabilities = $capabilities;
        $this->design_registry = $design_registry;
        $this->project_contents = $project_contents;
        $this->migration_manager = $migration_manager;
        $this->version_history = $version_history;
    }
    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since 1.0.0
     */
    public function detectActivation()
    {
        //activation indicator false so it's not newly activated or reactivated
        // there's no previous version, so it must have been using PMB 2
        $activation_indicator = get_option('pmb_activation', null);
        $previous_version = get_option('pmb_previous_version',null);

        //on a brand new install (or deactivate and activate another version), activation indicator will be true
        //on an upgrade, activation indicator will be false
        // so if previous version isnt set, and its not an activation it must be an upgrade
        parent::detectActivation();
//        if ($this->request_type->isBrandNewInstall() && current_user_can(PMB_ADMIN_CAP)) {
//            // Don't redirect if it's a bulk plugin activation
//            if (isset($_GET['activate-multi'])) {
//                return;
//            }
//            // @todo Do redirection later if we can.
//            $this->redirectToActivationPage();
//        }
        if($activation_indicator === '' && $previous_version === null){
                wp_redirect(
                    add_query_arg(
                        array(
                            'welcome' => 1,
                            'upgrade' => 1
                        ),
                        admin_url(PMB_ADMIN_PAGE_PATH)
                    )
                );
                exit;
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

    public function upgrade()
    {
        $this->migration_manager->migrate();
    }


//    /**
//     * Redirects
//     */
//    public function redirectToActivationPage()
//    {
//        wp_redirect(
//            add_query_arg(
//                array(
//                    'welcome' => 1
//                ),
//                admin_url(PMB_ADMIN_PAGE_PATH)
//            )
//        );
//        exit;
//    }
}
