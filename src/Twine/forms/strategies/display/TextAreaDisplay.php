<?php

namespace Twine\forms\strategies\display;

use Twine\forms\inputs\TextAreaInput;
use Twine\forms\strategies\validation\FullHtmlValidation;

/**
 * Class TextAreaDisplay
 * @package Twine\forms\strategies\display
 */
class TextAreaDisplay extends DisplayBase
{
    /**
     *
     * @return string of html to display the field
     */
    public function display()
    {
        $input = $this->input;
        $raw_value = maybe_serialize($input->rawValue());
        if ($input instanceof TextAreaInput) {
            $rows = $input->getRows();
            $cols = $input->getCols();
        } else {
            $rows = 4;
            $cols = 20;
        }
        $html = '<textarea';
        $html .= ' id="' . $input->htmlId() . '"';
        $html .= ' name="' . $input->htmlName() . '"';
        $html .= ' class="' . $input->htmlClass() . '"';
        $html .= ' style="' . $input->htmlStyle() . '"';
        $html .= $input->otherHtmlAttributesString();
        $html .= ' rows= "' . $rows . '" cols="' . $cols . '">';
        $html .= esc_textarea($raw_value);
        $html .= '</textarea>';
        foreach ($this->input->getValidationStrategies() as $validation_strategy) {
            if (
                $validation_strategy instanceof FullHtmlValidation
            ) {
                $html .= '<p class="twine-question-desc">' . sprintf(
                    // translators: 1: list of alloed htm;l tags
                    __('(allowed tags: %1$s)', 'print-my-blog'),
                    $validation_strategy->getListOfAllowedTags()
                ) . '</p>';
            }
        }
        return $html;
    }
}
