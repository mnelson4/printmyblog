<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class LazyLoadingFeaturePlugin
 *
 * Disalbe lazy loading images for PMB.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class LazyLoadingFeaturePlugin extends CompatibilityBase
{

    /**
     * Disable lazy-loading on REST requests. Firefox's print-preview doesn't show the images unless you scroll down.
     */
    public function setHooks()
    {
        add_filter(
            'wp_lazy_loading_enabled',
            function () {
                return ! defined('REST_REQUEST');
            }
        );
    }
}
