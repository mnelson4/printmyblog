<?php

namespace PrintMyBlog\system;

/**
 * Class Activation
 *
 * Handles installing Print My Blog, redirecting, and upgrades
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class Activation
{
    const VERSION_HISTORY = 'pmb_version_history';
    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since 1.0.0
     */
    public function detectActivation()
    {
        if (get_option('pmb_activation') && current_user_can(PMB_ADMIN_CAP)) {
            $this->recordVersion();
            update_option('pmb_activation', false);
            // Don't redirect if it's a bulk plugin activation
            if (isset($_GET['activate-multi'])) {
                return;
            }
            // @todo Do redirection later if we can.
            $this->redirectToActivationPage();
        }
    }

    public function redirectToActivationPage(){
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

    public function recordVersion(){
        $previous_versions = get_option( self::VERSION_HISTORY,[]);
        if(is_string($previous_versions)){
            $previous_versions = json_decode($previous_versions,true);
        }
        if(empty($previous_versions)){
            $previous_versions = [];
        }
        if(! isset($previous_versions[PMB_VERSION])){
            $previous_versions[PMB_VERSION] = [];
        }
        $previous_versions[PMB_VERSION][] = date('Y-m-d H:i:s');
        update_option(self::VERSION_HISTORY,wp_json_encode($previous_versions));
    }

    public function install()
    {
        // Install tables
    }
}