<?php

namespace PrintMyBlog\controllers;

use Twine\controllers\BaseController;

/**
 * Class PmbCommon
 *
 * Common controller logic that should run on all requests.
 *
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class PmbCommon extends BaseController
{
    public function setHooks()
    {
        add_action(
            'wp_enqueue_scripts',
            [$this, 'enqueueScripts']
        );
        add_action(
            'admin_enqueue_scripts',
            [$this, 'enqueueScripts']
        );
    }


    public function enqueueScripts()
    {
        wp_enqueue_style(
            'pmb_common',
            PMB_ASSETS_URL . 'styles/pmb-common.css',
            array(),
            filemtime(PMB_ASSETS_DIR . 'styles/pmb-common.css')
        );
    }
}