<?php

namespace Twine\forms\strategies\display;

use Exception;
use Twine\forms\helpers\InputOption;
use Twine\forms\inputs\FormInputWithOptionsBase;
use Twine\helpers\Array2;
use Twine\helpers\Html;

/**
 *
 * Class SelectDisplay
 *
 * Displays either simple arrays as selected, or if a 2d array is provided, separates them into optgroups
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 *
 *
 */
class SelectDisplay extends DisplayBase
{

    /**
     *
     * @throws Exception
     * @return string of html to display the field
     */
    public function display()
    {
        if (! $this->input instanceof FormInputWithOptionsBase) {
            throw new Exception(
                __('Cannot use Select Display Strategy with an input that doesn\'t have options', 'print-my-blog')
            );
        }
        $html_generator = Html::instance();
        $html = $html_generator->nl(0, 'select');
        $html .= '<select';
        $html .= $this->attributesString(
            $this->standardAttributesArray()
        );
        $html .= '>';

        if (Array2::isMultiDimensionalArray($this->input->options())) {
            $html_generator->indent(1, 'optgroup');
            foreach ($this->input->options() as $opt_group_label => $opt_group) {
                if (! empty($opt_group_label)) {
                    $html .= $html_generator->nl(0, 'optgroup')
                        . '<optgroup label="'
                        . esc_attr($opt_group_label)
                        . '">';
                }
                $html_generator->indent(1, 'option');
                $html .= $this->displayOptions($opt_group);
                $html_generator->indent(-1, 'option');
                if (! empty($opt_group_label)) {
                    $html .= $html_generator->nl(0, 'optgroup') . '</optgroup>';
                }
            }
            $html_generator->indent(-1, 'optgroup');
        } else {
            $html .= $this->displayOptions($this->input->options());
        }

        $html .= $html_generator->nl(0, 'select') . '</select>';
        return $html;
    }



    /**
     * Displays a flat list of options as option tags
     * @param InputOption[] $options
     * @return string
     */
    protected function displayOptions($options)
    {
        $html = '';
        $html_generator = Html::instance();
        $html_generator->indent(1, 'option');
        foreach ($options as $value => $option) {
            // even if this input uses TextNormalization if one of the array keys is a numeric string, like "123",
            // PHP will have converted it to a PHP integer (eg 123). So we need to make sure it's a string
            $unnormalized_value = $this->input->getNormalizationStrategy()->unnormalizeOne($value);
            $selected = $this->checkIfOptionSelected($unnormalized_value) ? ' selected="selected"' : '';
            $html .= $html_generator->nl(0, 'option')
                . '<option value="'
                . esc_attr($unnormalized_value)
                . '"'
                . $selected
                . '>'
                . $option->getDisplayText()
                . '</option>';
        }
        $html_generator->indent(-1, 'option');
        return $html;
    }



    /**
     * Checks if that value is the one selected
     *
     * @param string|int $option_value unnormalized value option (string). How it will appear in the HTML.
     * @return string
     */
    protected function checkIfOptionSelected($option_value)
    {
        return $option_value === $this->input->rawValue();
    }
}
