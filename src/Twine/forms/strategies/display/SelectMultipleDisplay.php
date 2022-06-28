<?php

namespace Twine\forms\strategies\display;

use Exception;
use Twine\forms\inputs\FormInputWithOptionsBase;
use Twine\helpers\Array2;
use Twine\helpers\Html;

/**
 * SelectMultipleDisplay
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class SelectMultipleDisplay extends SelectDisplay
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
                __(
                    'Cannot use Select Multiple Display Strategy with an input that doesn\'t have options',
                    'print-my-blog'
                )
            );
        }
        $html_generator = Html::instance();
        $html = $html_generator->nl(0, 'select');
        $html .= '<select multiple';
        $html .= ' id="' . $this->input->htmlId() . '"';
        $html .= ' name="' . $this->input->htmlName() . '[]"';
        $class = $this->input->required() ?
            $this->input->requiredCssClass() . ' ' . $this->input->htmlClass() :
            $this->input->htmlClass();
        $html .= ' class="' . $class . '"';
        // add html5 required
        $html .= $this->input->required() ? ' required' : '';
        $html .= ' style="' . $this->input->htmlStyle() . '"';
        $html .= ' ' . $this->input->otherHtmlAttributesString();
        $html .= '>';

        $html_generator->indent(1, 'select');
        if (Array2::isMultiDimensionalArray($this->input->options())) {
            throw new Exception(
                __('Select multiple display strategy does not allow for nested arrays of options.', 'print-my-blog')
            );
        } else {
            $html .= $this->displayOptions($this->input->options());
        }

        $html .= $html_generator->nl(-1, 'select') . '</select>';
        return $html;
    }



    /**
     * Checks if that $value is one of the selected ones
     * @param string|int $value unnormalized value option (string)
     * @return boolean
     */
    protected function checkIfOptionSelected($value)
    {
        $selected_options = $this->input->rawValue();
        if (empty($selected_options)) {
            return false;
        }
        // Want loose comparison.
        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
        return in_array($value, (array)$selected_options) ? true : false;
    }
}
