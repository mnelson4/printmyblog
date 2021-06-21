<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;

class GoogleLanguageTranslator extends CompatibilityBase
{
    public function setRenderingHooks()
    {
        global $google_language_translator;
        add_action('wp_enqueue_scripts', array($google_language_translator, 'flags'));
    }
}
