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
    public function __construct($template_file, $args = array(), $options_array = array())
    {
        $html = require_once($template_file);

//      echo " filepath:$template_file html $html";
        parent::__construct($html, $options_array);
    }
}
