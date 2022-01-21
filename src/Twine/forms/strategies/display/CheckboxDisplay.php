<?php

namespace Twine\forms\strategies\display;

use Exception;
use Twine\helpers\Html;

/**
 * Class CheckboxDisplay
 * displays a set of checkbox inputs
 *
 * @package       Event Espresso
 * @subpackage    core
 * @author        Mike Nelson
 * @since         4.6
 */
class CheckboxDisplay extends CompoundInputDisplay
{

    /**
     * @throws Exception
     * @return string of html to display the field
     */
    public function display()
    {
        $input = $this->getInput();
        $html = '';
        if (! is_array($input->rawValue()) && $input->rawValue() !== null) {
            throw new Exception(
                sprintf(
                    esc_html_x(
                        'Input values for checkboxes should be an array of values, but the value for input "%1$s" is "%2$s". Please verify that the input name is exactly "%3$s"',
                        'Input values for checkboxes should be an array of values, but the value for input "form-input-id" is "form-input-value". Please verify that the input name is exactly "form_input_name[]"',
                        'print-my-blog'
                    ),
                    $input->htmlId(),
                    var_export($input->rawValue(), true),
                    $input->htmlName() . '[]'
                )
            );
        }
        $html_generator = Html::instance();
        $input_raw_value = (array) $input->rawValue();
        foreach ($input->options() as $value => $option) {
            $value = $input->getNormalizationStrategy()->unnormalizeOne($value);
            $html_id = $this->getSubInputId($value);
            $html .= $html_generator->nl(0, 'checkbox');
            $html .= '<label for="'
                     . $html_id
                     . '" id="'
                     . $html_id
                     . '-lbl" class="twine-checkbox-label-after twine-option'
                     . ($option->enabled() ? '  twine-option-enabled' : ' twine-option-disabled')
                     . '">';
            $html .= $html_generator->nl(1, 'checkbox');
            $html .= '<input type="checkbox"';
            $html .= ' name="' . $input->htmlName() . '[]"';
            $html .= ' id="' . $html_id . '"';
            $html .= ' class="' . $input->htmlClass() . '"';
            $html .= ' style="' . $input->htmlStyle() . '"';
            $html .= ' value="' . esc_attr($value) . '"';
            if(! $option->enabled()){
                $html .= ' disabled=1';
            }
            $html .= ! empty($input_raw_value) && in_array($value, $input_raw_value, true)
                ? ' checked="checked"'
                : '';
            $html .= ' ' . $this->input->otherHtmlAttributesString();
            $html .= ' data-question_label="' . $input->htmlLabelId() . '"';
            $html .= '>&nbsp;';
            $html .= $option->getDisplayText();
            $html .= $html_generator->nl(-1, 'checkbox');
            $help_text = $option->getHelpText();
            if ($help_text) {
                $html .= $html_generator->p($help_text, '', 'description');
            }
            $html .= '</label>';
        }
        return $html;
    }
}
