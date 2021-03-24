<?php

namespace Twine\forms\strategies\display;

use Twine\forms\strategies\normalization\NormalizationBase;

/**
 * Class SubmitInputDisplay
 * Description
 *
 * @package       Event Espresso
 * @author        Mike Nelson
 */
class SubmitInputDisplay extends DisplayBase
{

    /**
     * @return string of html to display the input
     */
    public function display()
    {
        $default_value = $this->input->getDefault();
        if ($this->input->getNormalizationStrategy() instanceof NormalizationBase) {
            $default_value = $this->input->getNormalizationStrategy()->unnormalize($default_value);
        }
        $html = $this->openingTag('input');
        $html .= $this->attributesString(
            array_merge(
                $this->standardAttributesArray(),
                array(
                    'type'  => 'submit',
                    'value' => $default_value,
                    // overwrite the standard id with the backwards compatible one
                    'id' => $this->input->htmlId() . '-submit',
                    'class' => $this->input->htmlClass()
                )
            )
        );
        $html .= $this->closeTag();
        return $html;
    }
}
