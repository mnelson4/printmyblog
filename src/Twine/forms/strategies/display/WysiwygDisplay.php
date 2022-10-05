<?php

namespace Twine\forms\strategies\display;

use Twine\forms\inputs\TextAreaInput;
use Twine\forms\strategies\validation\FullHtmlValidation;

/**
 * Class TextAreaDisplay
 * @package Twine\forms\strategies\display
 */
class WysiwygDisplay extends TextAreaDisplay
{
    /**
     *
     * @return string of html to display the field
     */
    public function display()
    {
        $this->input->setHtmlClass($this->input->htmlClass() . ' theEditor');
        return parent::display();
    }
}
