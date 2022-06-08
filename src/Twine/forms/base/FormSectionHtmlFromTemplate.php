<?php

namespace Twine\forms\base;

/**
 * FormSectionHtml_From_Template
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class FormSectionHtmlFromTemplate extends FormSectionHtml
{
    /**
     * FormSectionHtmlFromTemplate constructor.
     * @param string $template_file
     * @param array $args
     * @param array $options_array
     */
    public function __construct($template_file, $args = array(), $options_array = array())
    {
        $html = require_once $template_file;

        parent::__construct($html, $options_array);
    }
}
