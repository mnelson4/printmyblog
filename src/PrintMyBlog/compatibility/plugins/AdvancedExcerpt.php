<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

/**
 * Class AdvancedExcerpt
 * @package PrintMyBlog\compatibility\plugins
 */
class AdvancedExcerpt extends CompatibilityBase
{
    /**
     * Remove a script with an error in DocRaptor.
     */
    public function setRenderingHooks()
    {
        // setup our filter to run right after their $advanced_excerpt->hook_content_filters()
        add_action( 'loop_start', array( $this, 'dontFilterContent' ), 11 );
    }

    /**
     * Don't let AdvancedExcerpt filter the content. We want the full content as normal.
     */
    public function dontFilterContent(){
        global $advanced_excerpt;
        remove_filter( 'the_content', array( $advanced_excerpt, 'filter_content' ) );
    }
}
