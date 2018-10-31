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
 * @since         $VID:$
 *
 */
class PmbActivation extends BaseController
{


    /**
     * Sets hooks needed for this controller to execute its logic.
     * @since $VID:$
     */
    public function setHooks()
    {
        add_action('init', array($this,'detectActivation'));
    }

    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since $VID:$
     */
    public function detectActivation()
    {
        if (get_option('pmb_activation') && current_user_can(PMB_ADMIN_CAP)) {
            update_option('pmb_activation', false);
            wp_redirect(
                admin_url(
                    '/tools.php?page=print-my-blog'
                )
            );
            exit;
        }
    }
}
// End of file PmbActivation.php
// Location: PrintMyBlog\controllers/PmbActivation.php
