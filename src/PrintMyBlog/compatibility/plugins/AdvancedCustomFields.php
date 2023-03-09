<?php


namespace PrintMyBlog\compatibility\plugins;


use Twine\compatibility\CompatibilityBase;

class AdvancedCustomFields extends CompatibilityBase
{
    /**
     * Allow using shortcodes outside of post body
     */
    public function setRenderingHooks()
    {
        add_filter('acf/shortcode/allow_in_block_themes_outside_content', '__return_true');
    }
}