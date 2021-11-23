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
     * Hide the Google Recaptcha floater
     */
    public function hideGoogleCaptchaBadge()
    {
        wp_add_inline_script(
            'pmb_pro_page',
            '
            // Hide Google Recaptchas parent div because it adds an extra page to PDFs
            jQuery(document).ready(function(){
                setTimeout(function(){
                    jQuery(".grecaptcha-badge").parent().hide()
                }, 1000);
            });'
        );
    }
}
