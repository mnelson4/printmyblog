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
        $input = $this->get_input();
        $html = '';
        if (! is_array($input->raw_value()) && $input->raw_value() !== null) {
            throw new Exception(
                sprintf(
                    esc_html_x(
                        'Input values for checkboxes should be an array of values, but the value for input "%1$s" is "%2$s". Please verify that the input name is exactly "%3$s"',
                        'Input values for checkboxes should be an array of values, but the value for input "form-input-id" is "form-input-value". Please verify that the input name is exactly "form_input_name[]"',
                        'event_espresso'
                    ),
                    $input->html_id(),
                    var_export($input->raw_value(), true),
                    $input->html_name() . '[]'
                )
            );
        }
        $html_generator = Html::instance();
        $input_raw_value = (array) $input->raw_value();
        foreach ($input->options() as $value => $option) {
            $value = $input->get_normalization_strategy()->unnormalize_one($value);
            $html_id = $this->get_sub_input_id($value);
            $html .= $html_generator->nl(0, 'checkbox');
            $html .= '<label for="'
                     . $html_id
                     . '" id="'
                     . $html_id
                     . '-lbl" class="ee-checkbox-label-after'
                     . '">';
            $html .= $html_generator->nl(1, 'checkbox');
            $html .= '<input type="checkbox"';
            $html .= ' name="' . $input->html_name() . '[]"';
            $html .= ' id="' . $html_id . '"';
            $html .= ' class="' . $input->html_class() . '"';
            $html .= ' style="' . $input->html_style() . '"';
            $html .= ' value="' . esc_attr($value) . '"';
            $html .= ! empty($input_raw_value) && in_array($value, $input_raw_value, true)
                ? ' checked="checked"'
                : '';
            $html .= ' ' . $this->_input->otherHtmlAttributesString();
            $html .= ' data-question_label="' . $input->html_label_id() . '"';
            $html .= '>&nbsp;';
            $html .= $option->getDisplayText();
            $html .= $html_generator->nl(-1, 'checkbox') . '</label>';
            $help_text = $option->getHelpText();
            if($help_text){
            	$html .= $html_generator->span($help_text,'', 'description');
            }
        }
        return $html;
    }
}
