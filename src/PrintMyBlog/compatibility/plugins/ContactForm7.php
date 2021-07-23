<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

//
class ContactForm7 extends CompatibilityBase
{
    public function setRenderingHooks()
    {
        add_action('wp_enqueue_scripts', array( $this, 'hideGoogleCaptchaBadge'));
    }

    /**
     * Remove the coblcoks animation script as it has an error in DocRaptor
     */
    public function hideGoogleCaptchaBadge()
    {
        wp_add_inline_script(
            'pmb_pro_page',
            'jQuery(document).ready(function(){jQuery(".grecaptcha-badge").parent().hide();});'
        );
    }
}
