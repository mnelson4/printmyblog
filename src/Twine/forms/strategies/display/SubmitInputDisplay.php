<?php
namespace Twine\forms\strategies\display;


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
        $default_value = $this->_input->get_default();
        if ($this->_input->get_normalization_strategy() instanceof NormalizationBase) {
            $default_value = $this->_input->get_normalization_strategy()->unnormalize($default_value);
        }
        $html = $this->_opening_tag('input');
        $html .= $this->_attributes_string(
            array_merge(
                $this->_standard_attributes_array(),
                array(
                    'type'  => 'submit',
                    'value' => $default_value,
                    // overwrite the standard id with the backwards compatible one
                    'id' => $this->_input->html_id() . '-submit',
                    'class' => $this->_input->html_class() . ' ' . $this->_input->button_css_attributes()
                )
            )
        );
        $html .= $this->_close_tag();
        return $html;
    }
}
