<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class AdvancedExcerpt
 * @package PrintMyBlog\compatibility\plugins
 */
class Hustle extends CompatibilityBase
{
    /**
     * Remove a script that sometimes has an error on the print page.
     */
    public function setRenderingHooks()
    {
        // setup our filter to run right after theiy enqueue their script
        add_action( 'wp_enqueue_scripts', array( $this, 'removeJS' ), 11 );
    }

    /**
     * Don't let AdvancedExcerpt filter the content. We want the full content as normal.
     */
    public function removeJS(){
        wp_deregister_script('hustle_front');
        wp_deregister_script('hui_scripts');
    }
}
