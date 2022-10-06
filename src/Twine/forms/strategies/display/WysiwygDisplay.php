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
        ob_start();
        wp_editor(
            $this->input->rawValue(),
            $this->input->htmlID(),
            [
                'editor_class' => $this->input->htmlClass(),
                'textarea_name' => $this->input->htmlName(),
            ]
        );
        return ob_get_clean();
    }
}
