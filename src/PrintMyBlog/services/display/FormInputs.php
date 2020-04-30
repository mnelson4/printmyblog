<?php

namespace PrintMyBlog\services\display;

/**
 * Class FormInputs
 *
 * For getting HTML for form inputs
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class FormInputs
{
    /**
     * @since $VID:$
     * @param array $options. Top-level keys are the input names, values are arrays with keys 'label', 'help', and 'default'.
     * @return string of HTML
     */
    public function getHtmlForOptions($options)
    {
        $html = '';
        foreach ($options as $option_name => $option_details) {

            $html .= '<label for="show_' . esc_attr($option_name) . '">';
            $html .= '<input type="checkbox" name="show_' . esc_attr($option_name) . '" id="show_' . esc_attr($option_name) . '"';
            if ($option_details['default']) {
                $html .= ' checked="checked"';
            }
            $html .= 'value="1">' . $option_details['label'] . '</label><br>';
            if (isset($option_details['help'])){
                $html .= '<p class="description">' . $option_details['help'] .'</p>';
            }
        }
        return $html;
    }
}
// End of file FormInputs.php
// Location: PrintMyBlog\services\display/FormInputs.php
