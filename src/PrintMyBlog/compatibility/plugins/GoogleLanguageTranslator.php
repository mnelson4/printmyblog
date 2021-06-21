<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

class GoogleLanguageTranslator extends CompatibilityBase
{
    public function setRenderingHooks()
    {
        global $google_language_translator;
        add_action('wp_enqueue_scripts', array($google_language_translator, 'flags'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
    }

    /**
     * Add a few styles to prevent Google Translator stuff from appearing in the printout
     */
    public function enqueueStyles()
    {
        wp_add_inline_style(
            'google-language-translator',
            '@media print{
                .goog-te-banner-frame, #glt-translate-trigger{
                    display:none;
                }
            }
            /* all mike base*/'
        );
    }
}
