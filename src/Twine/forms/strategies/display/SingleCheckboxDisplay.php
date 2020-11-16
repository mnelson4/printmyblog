<?php

namespace Twine\forms\strategies\display;

use Twine\helpers\Html;

class SingleCheckboxDisplay extends DisplayBase
{


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
