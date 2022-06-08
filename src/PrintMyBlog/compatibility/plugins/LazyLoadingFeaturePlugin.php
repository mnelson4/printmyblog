<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class LazyLoadingFeaturePlugin
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class LazyLoadingFeaturePlugin extends CompatibilityBase
{

    public function setHooks()
    {
        // Disable lazy-loading on REST requests. Firefox's print-preview doesn't show the images unless you scroll
        // down.
        add_filter(
            'wp_lazy_loading_enabled',
            function () {
                return ! defined('REST_REQUEST');
            }
        );
    }
}
