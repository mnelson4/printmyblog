<?php
namespace Twine\forms\strategies\display;
use Twine\helpers\Html;

/**
 * Class RadioButtonDisplay
 * displays a set of radio buttons
 *
 * @package     Event Espresso
 * @subpackage  core
 * @author      Mike Nelson
 * @since       4.6
 */
class RadioButtonDisplay extends CompoundInputDisplay
{

    /**
     *
     * @throws Exception
     * @return string of html to display the field
     */
    public function display()
    {
        $input = $this->get_input();
        $input->set_label_sizes();
        $label_size_class = $input->get_label_size_class();
        $html = '';
        $html_generator = Html::instance();
        foreach ($input->options() as $value => $display_text) {
            $value = $input->get_normalization_strategy()->unnormalize($value);

            $html_id = $this->get_sub_input_id($value);
            $html .= $html_generator->nl(0, 'radio');

            $html .= $this->_opening_tag('label');
            $html .= $this->_attributes_string(
                array(
                    'for' => $html_id,
                    'id' => $html_id . '-lbl',
                    'class' => apply_filters(
                        'FH_RadioButtonDisplay__display__option_label_class',
                        'ee-radio-label-after' . $label_size_class,
                        $this,
                        $input,
                        $value
                    )
                )
            );
            $html .= '>';
            $html .= $html_generator->nl(1, 'radio');
            $html .= $this->_opening_tag('input');
            $attributes = array(
                'id' => $html_id,
                'name' => $input->html_name(),
                'class' => $input->html_class(),
                'style' => $input->html_style(),
                'type' => 'radio',
                'value' => $value,
                0 => $input->other_html_attributes(),
                'data-question_label' => $input->html_label_id()
            );
            if ($input->raw_value() === $value) {
                $attributes['checked'] = 'checked';
            }
            $html .= $this->_attributes_string($attributes);

            $html .= '>&nbsp;';
            $html .= $display_text;
            $html .= $html_generator->nl(-1, 'radio') . '</label>';
        }
        $html .= $html_generator->div('', '', 'clear-float');
        $html .= $html_generator->divx();
        return apply_filters('FH_RadioButtonDisplay__display', $html, $this, $this->_input);
    }
}
