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
    protected $inputs_prefix = '';

    public function setInputPrefix($prefix){
        $this->inputs_prefix = $prefix;
    }

    protected function id($id){
        if($this->inputs_prefix){
            $id = $this->inputs_prefix . '_' . $id;
        }
        return esc_attr($id);
    }

    protected function name($name){
        if($this->inputs_prefix){
            $name = '[' . $this->inputs_prefix . ']' . $name;
        }
        return esc_attr($name);
    }
    /**
     * @since $VID:$
     * @param array $options. Top-level keys are the input names, values are arrays with keys 'label', 'help', and 'default'.
     * @return string of HTML
     */
    public function getHtmlForShortOptions($options)
    {
        $html = '';
        foreach ($options as $option_name => $option_details) {

            $html .= '<label for="' . $this->id('show_' . $option_name) . '">';
            $html .= '<input type="checkbox" name="' . $this->name('show_' . $option_name) . '" id="' . $this->id('show_' . $option_name) . '"';
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

    public function getHtmlForTabledOptions($options)
    {
        $html = '';
        foreach($options as $option_name => $option_details) {
            $html .= '<tr>';
            $html .= '<th scope="row">';
            $html .= '<label for="' . $this->id($option_name) .'">' . $option_details['label'] . '</label>';
            $html .= '</th>';
            $html .=  '<td>';
            if(is_bool($option_details['default'])){
                $html .= '<input type="checkbox" name="' . $this->name($option_name) . '" id="' . $this->id($option_name) . '"';
                if ($option_details['default']) {
                    $html .= ' checked="checked"';
                }
                $html .= '>';
            } elseif(isset($option_details['options'])){
                $html .= '<select name="' . $this->name($option_name). '" id="' . $this->id($option_name) . '">';
                foreach($option_details['options'] as $option_value => $option_label){
                    $html .= '<option value="' . esc_attr($option_value) . '"';
                    if($option_details['default'] == $option_value){
                        $html .= 'selected="selected"';
                    }
                    $html .= '>';
                    $html .= $option_label;
                    $html .= '</option>';
                }
                $html .= '</select>';
            } else {
                // normal input
                $html .= '<input type="text" name="' . $this->name($option_name) . '" id="' . $this->id($option_name) . '" value="' . esc_attr($option_details['default']) . '">';
                if(isset($option_details['after_input'])){
                    $html .= $option_details['after_input'];
                }
            }
            if(isset($option_details['help'])){
                $html .= '<p class="description">' . $option_details['help'] . '</p>';
            }
            $html .= '</td></tr>';
        }
        return $html;
    }
}
// End of file FormInputs.php
// Location: PrintMyBlog\services\display/FormInputs.php
