<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

/**
 * Class PmbActivation
 *
 * Takes care of any special activation logic relating to this PMB.
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         1.0.0
 *
 */
class PmbActivation extends BaseController
{


    /**
     * Sets hooks needed for this controller to execute its logic.
     * @since 1.0.0
     */
    public function setHooks()
    {
        add_action('init', array($this, 'detectActivation'));
    }

    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since 1.0.0
     */
    public function detectActivation()
    {
        if (get_option('pmb_activation') && current_user_can(PMB_ADMIN_CAP)) {
            update_option('pmb_activation', false);
            // Don't redirect if it's a bulk plugin activation
            if(isset($_GET['activate-multi'])){
               return;
            }
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
}
// End of file PmbActivation.php
// Location: PrintMyBlog\controllers/PmbActivation.php
