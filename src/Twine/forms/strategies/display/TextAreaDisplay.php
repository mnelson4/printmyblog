<?php

namespace Twine\forms\strategies\display;

use Twine\forms\inputs\TextAreaInput;
use Twine\forms\strategies\validation\FullHtmlValidation;

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
        $html .= ' class="' . $input->htmlClass() . '"' ;
        $html .= ' style="' . $input->htmlStyle() . '"';
        $html .= $input->otherHtmlAttributesString();
        $html .= ' rows= "' . $rows . '" cols="' . $cols . '">';
        $html .= esc_textarea($raw_value);
        $html .= '</textarea>';
        foreach ($this->input->getValidationStrategies() as $validation_strategy) {
            if (
                $validation_strategy instanceof FullHtmlValidation
            ) {
                $html .= sprintf(
                    __('%1$s(allowed tags: %2$s)%3$s', 'print-my-blog'),
                    '<p class="ee-question-desc">',
                    $validation_strategy->getListOfAllowedTags(),
                    '</p>'
                );
            }
        }
        return $html;
    }
}
