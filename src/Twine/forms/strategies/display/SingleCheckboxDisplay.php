<?php

namespace Twine\forms\strategies\display;

/**
 * Class SingleCheckboxDisplay
 * @package Twine\forms\strategies\display
 */
class SingleCheckboxDisplay extends DisplayBase
{

    /**
     * @return string
     */
    public function display()
    {
        $html = $this->openingTag('input');
        $other_attributes = [
            'type' => 'checkbox',
            'value' => 1,
        ];
        if ($this->input->normalizedValue()) {
            $other_attributes[] = 'checked';
        }
        $html .= $this->attributesString(
            array_merge(
                $this->standardAttributesArray(),
                $other_attributes
            )
        );
        $html .= $this->closeTag();
        return $html;
    }
}
