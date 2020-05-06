<?php

namespace Twine\services\display;

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
    protected $inputs_prefixes = [];

    /**
     * @var array
     */
    protected $new_values = [];

    public function setNewValues($new_values)
    {
        $this->new_values = $new_values;
    }
    public function setInputPrefixes($prefixes)
    {
        $this->inputs_prefixes = $prefixes;
    }

    protected function id($id)
    {
        if ($this->inputs_prefixes) {
            $id = implode('_', $this->inputs_prefixes) . '_' . $id;
        }
        return esc_attr($id);
    }

    protected function name($name)
    {
        if ($this->inputs_prefixes) {
            $parts = $this->inputs_prefixes;
            $parts[] = $name;
            $first_part = array_shift($parts);

            $name = $first_part;
            if ($parts) {
                $name .= '[' . implode('][', $parts) . ']';
            }
        }
        return esc_attr($name);
    }
    /**
     * @since $VID:$
     * @param array $options. Top-level keys are the input names, values are arrays with keys 'label', 'help',
     * and 'default'.
     * @return string of HTML
     */
    public function getHtmlForShortOptions($options)
    {
        $html = '';
        foreach ($options as $option_name => $option_details) {
            $html .= '<label for="' . $this->id($option_name) . '">';
            $html .= '<input type="checkbox" name="'
                . $this->name($option_name)
                . '" id="'
                . $this->id($option_name)
                . '"';
            if ($this->getValue($option_name, $option_details)) {
                $html .= ' checked="checked"';
            }
            $html .= 'value="1">' . $option_details['label'] . '</label><br>';
            if (isset($option_details['help'])) {
                $html .= '<p class="description">' . $option_details['help'] . '</p>';
            }
        }
        return $html;
    }

    /**
     * Gets the value if set, otherwise uses the default.
     * @since $VID:$
     * @param $option_name
     * @param $option_details
     * @return mixed
     */
    protected function getValue($option_name, $option_details)
    {
        if (isset($this->new_values[$option_name])) {
            return $this->new_values[$option_name];
        }
        return $option_details['default'];
    }

    public function getHtmlForTabledOptions($options)
    {
        $html = '';
        foreach ($options as $option_name => $option_details) {
            $html .= '<tr>';
            $html .= '<th scope="row">';
            $html .= '<label for="' . $this->id($option_name) . '">' . $option_details['label'] . '</label>';
            $html .= '</th>';
            $html .=  '<td>';
            if (is_bool($option_details['default'])) {
                $html .= '<input type="checkbox" name="'
                    . $this->name($option_name)
                    . '" id="'
                    . $this->id($option_name)
                    . '"';
                if ($this->getValue($option_name, $option_details)) {
                    $html .= ' checked="checked"';
                }
                $html .= '>';
            } elseif (isset($option_details['options'])) {
                $html .= '<select name="' . $this->name($option_name) . '" id="' . $this->id($option_name) . '">';
                foreach ($option_details['options'] as $option_value => $option_label) {
                    $html .= '<option value="' . esc_attr($option_value) . '"';
                    if ($this->getValue($option_name, $option_details) == $option_value) {
                        $html .= 'selected="selected"';
                    }
                    $html .= '>';
                    $html .= $option_label;
                    $html .= '</option>';
                }
                $html .= '</select>';
            } else {
                // normal input
                //phpcs:disable Generic.Files.LineLength.TooLong
                $html .= '<input type="text" name="'
                    . $this->name($option_name)
                    . '" id="'
                    . $this->id($option_name)
                    . '" value="'
                    . esc_attr($this->getValue($option_name, $option_details))
                    . '">';
                //phpcs:enable
                if (isset($option_details['after_input'])) {
                    $html .= $option_details['after_input'];
                }
            }
            if (isset($option_details['help'])) {
                $html .= '<p class="description">' . $option_details['help'] . '</p>';
            }
            $html .= '</td></tr>';
        }
        return $html;
    }
}
// End of file FormInputs.php
// Location: Twine\services\display/FormInputs.php
