<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class CoBlocks
 * @package PrintMyBlog\compatibility\plugins
 */
class CoBlocks extends CompatibilityBase
{
    /**
     * Remove a script with an error in DocRaptor.
     */
    public function setRenderingHooks()
    {
        add_action('wp_enqueue_scripts', array( $this, 'removeBadScripts' ));
    }

    /**
     * Remove the coblcoks animation script as it has an error in DocRaptor
     */
    public function removeBadScripts()
    {
        wp_dequeue_script('coblocks-animation');
    }
}
