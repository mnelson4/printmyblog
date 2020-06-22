<?php

namespace PrintMyBlog\system;

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
    public function install(){
        // install tables etc
    }


    /**
     * Redirects
     */
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
}