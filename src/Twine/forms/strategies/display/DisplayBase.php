<?php

namespace Twine\forms\strategies\display;

use Twine\forms\strategies\FormInputStrategyBase;

/**
 * Class DisplayBase
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson, Brent Christensen
 * @since                 4.6
 */
abstract class DisplayBase extends FormInputStrategyBase
{


    /**
     * @var string $tag
     */
    protected $tag = '';





    /**
     * returns HTML and javascript related to the displaying of this input
     *
     * @return string
     */
    abstract public function display();



    /**
     * _remove_chars - takes an incoming string, and removes the string $chars from the end of it, but only if $chars
     * is already there
     *
     * @param string $string - the string being processed
     * @param string $chars  - exact string of characters to remove
     * @return string
     */
    protected function removeChars($string = '', $chars = '-')
    {
        $char_length = strlen($chars) * -1;
        // if last three characters of string is  " - ", then remove it
        return substr($string, $char_length) === $chars ? substr($string, 0, $char_length) : $string;
    }



    /**
     * _append_chars - takes an incoming string, and adds the string $chars to the end of it, but only if $chars is not
     * already there
     *
     * @param string $string - the string being processed
     * @param string $chars  - exact string of characters to be added to end of string
     * @return string
     */
    protected function appendChars($string = '', $chars = '-')
    {
        return $this->removeChars($string, $chars) . $chars;
    }



    /**
     * Gets the HTML IDs of all the inputs
     *
     * @param bool $add_pound_sign
     * @return array
     */
    public function getHtmlInputIds($add_pound_sign = false)
    {
        return array($this->getInput()->htmlId($add_pound_sign));
    }



    /**
     * Adds js variables for localization to the $other_js_data. These should be put
     * in each form's "other_data" javascript object.
     *
     * @param array $other_js_data
     * @return array
     */
    public function getOtherJsData($other_js_data = array())
    {
        return $other_js_data;
    }



    /**
     * Opportunity for this display strategy to call wp_enqueue_script and wp_enqueue_style.
     * This should be called during wp_enqueue_scripts
     */
    public function enqueueJs()
    {
    }



    /**
     * returns string like: '<tag'
     *
     * @param string $tag
     * @return string
     */
    protected function openingTag($tag)
    {
        $this->tag = $tag;
        return "<{$this->tag}";
    }



    /**
     * returns string like: '</tag>
     *
     * @return string
     */
    protected function closingTag()
    {
        return "</{$this->tag}>";
    }



    /**
     * returns string like: '/>'
     *
     * @return string
     */
    protected function closeTag()
    {
        return '/>';
    }



    /**
     * returns an array of standard HTML attributes that get added to nearly all inputs,
     * where string keys represent named attributes like id, class, etc
     * and numeric keys represent single attributes like 'required'.
     * Note: this does not include "value" because many inputs (like dropdowns, textareas, and checkboxes) don't use
     * it.
     *
     * @return array
     */
    protected function standardAttributesArray()
    {
        return array(
            'name'  => $this->input->htmlName(),
            'id'    => $this->input->htmlId(),
            'class' => $this->input->htmlClass(true),
            0       => array('required', $this->input->required()),
            1       => $this->input->otherHtmlAttributesString(),
            'style' => $this->input->htmlStyle(),
        );
    }



    /**
     * sets the attributes using the incoming array
     * and returns a string of all attributes rendered as valid HTML
     *
     * @param array $attributes
     * @return string
     */
    protected function attributesString($attributes = array())
    {
        $attributes = apply_filters(
            'FH_DisplayBase__attributes_string__attributes',
            $attributes,
            $this,
            $this->input
        );
        $attributes_string = '';
        foreach ($attributes as $attribute => $value) {
            if (is_numeric($attribute)) {
                $add = true;
                if (is_array($value)) {
                    $attribute = isset($value[0]) ? $value[0] : '';
                    $add = isset($value[1]) ? $value[1] : false;
                } else {
                    $attribute = $value;
                }
                $attributes_string .= $this->singleAttribute($attribute, $add);
            } else {
                $attributes_string .= $this->attribute($attribute, $value);
            }
        }
        return $attributes_string;
    }



    /**
     * returns string like: ' attribute="value"'
     * returns an empty string if $value is null
     *
     * @param string $attribute
     * @param string $value
     * @return string
     */
    protected function attribute($attribute, $value = '')
    {
        if ($value === null) {
            return '';
        }
        $value = esc_attr($value);
        return " {$attribute}=\"{$value}\"";
    }



    /**
     * returns string like: ' data-attribute="value"'
     * returns an empty string if $value is null
     *
     * @param string $attribute
     * @param string $value
     * @return string
     */
    protected function dataAttribute($attribute, $value = '')
    {
        if ($value === null) {
            return '';
        }
        $value = esc_attr($value);
        return " data-{$attribute}=\"{$value}\"";
    }



    /**
     * returns string like: ' attribute' if $add is true
     *
     * @param string  $attribute
     * @param boolean $add
     * @return string
     */
    protected function singleAttribute($attribute, $add = true)
    {
        return $add ? " {$attribute}" : '';
    }
}
