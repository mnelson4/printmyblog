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
        // get the activation indicator before its value is updated by RequestType
        $activation_indicator = get_option('pmb_activation', null);

        //on a brand new install (or deactivate and activate another version), activation indicator will be true
        //on an upgrade, activation indicator will be false
        // so if previous version isnt set, and its not an activation it must be an upgrade
        parent::detectActivation();
        // Temporarily for new installs, do an experiment where they won't even be asked to opt in.
        // This prevents asking them later when I will probably start asking again.
        if ($this->request_type->getRequestType() === RequestType::REQUEST_TYPE_NEW_INSTALL) {
            pmb_fs()->skip_connection([get_current_blog_id()]);
        }
        if ($activation_indicator === '' && $this->version_history->previousVersion() === null) {
                wp_redirect(
                    add_query_arg(
                        array(
                            'upgrade_to_3' => 1
                        ),
                        admin_url(PMB_ADMIN_PAGE_PATH)
                    )
                );
                exit;
        }
        // while pmb_fs() declares anonymous mode, this is needed to send users to welcome page
        if (
            in_array(
                $this->request_type->getRequestType(),
                array(
                    RequestType::REQUEST_TYPE_NEW_INSTALL,
                    RequestType::REQUEST_TYPE_REACTIVATION
                )
            )
        ) {
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

    public function upgrade()
    {
        $this->migration_manager->migrate();
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
            ),
            303 // "See Other". Use this instead of 302 because browsers sometimes cache 302s meaning
            // when folks activate a different plugin, the browser might redirect them to the PMB activation page again
        );
        exit;
    }
}
