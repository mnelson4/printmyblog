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
     * @return string of html to display the field
     */
    public function display()
    {
        $input = $this->getInput();
        $html = '';
        $html_generator = Html::instance();
        foreach ($input->options() as $value => $option) {
            $value = $input->getNormalizationStrategy()->unnormalize($value);

            $html_id = $this->getSubInputId($value);
            $html .= $html_generator->nl(0, 'radio');

            $html .= $this->openingTag('label');
            $html .= $this->attributesString(
                array(
                    'for' => $html_id,
                    'id' => $html_id . '-lbl',
                    'class' => apply_filters(
                        'FH_RadioButtonDisplay__display__option_label_class',
                        'twine-radio-label',
                        $this,
                        $input,
                        $value
                    ),
                    'aria-label' => sprintf(
                        // translators: 1: label text, 2: display text
                        __('%1$s: %2$s', 'print-my-blog'),
                        wp_strip_all_tags($input->htmlLabelText()),
                        wp_strip_all_tags($option->getDisplayText())
                    ),
                )
            );
            $html .= '>';
            $html .= $html_generator->nl(1, 'radio');
            $html .= $this->openingTag('input');
            $attributes = array(
                'id' => $html_id,
                'name' => $input->htmlName(),
                'class' => $input->htmlClass(),
                'style' => $input->htmlStyle(),
                'type' => 'radio',
                'value' => $value,
                0 => $input->otherHtmlAttributesString(),
                'data-question_label' => $input->htmlLabelId(),
            );
            if ($input->rawValue() === $value) {
                $attributes['checked'] = 'checked';
            }
            $html .= $this->attributesString($attributes);

            $html .= '>&nbsp;';
            $text = $option->getDisplayText();
            $help_text = $option->getHelpText();


            $html .= $text;
            $html .= $html_generator->nl(-1, 'radio') . '</label>';
            if ($help_text) {
                $html .= $html_generator->span(
                    $help_text,
                    '',
                    'twine-radio-help description'
                );
            }
        }
        $html .= $html_generator->div('', '', 'clear-float');
        $html .= $html_generator->divx();
        return apply_filters('FH_RadioButtonDisplay__display', $html, $this, $this->input);
    }
}
