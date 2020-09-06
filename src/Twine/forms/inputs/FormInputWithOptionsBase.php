<?php
namespace Twine\forms\inputs;
use EEH_Array;
use Twine\forms\strategies\normalization\BooleanNormalization;
use Twine\forms\strategies\normalization\IntNormalization;
use Twine\forms\strategies\normalization\ManyValuedNormalization;
use Twine\forms\strategies\normalization\TextNormalization;

/**
 * FormInputWithOptionsBase
 * For form inputs which are meant to only have a limit set of options that can be used
 * (like for checkboxes or select dropdowns, etc; as opposed to more open-ended text boxes etc)
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
abstract class FormInputWithOptionsBase extends FormInputBase
{

    /**
     * array of available options to choose as an answer
     *
     * @var array
     */
    protected $_options = array();

    /**
     * whether to display the html_label_text above the checkbox/radio button options
     *
     * @var boolean
     */
    protected $_display_html_label_text = true;

    /**
     * whether to display an question option description as part of the input label
     *
     * @var boolean
     */
    protected $_use_desc_in_label = true;

    /**
     * strlen() result for the longest input value (what gets displayed in the label)
     * this is used to apply a css class to the input label
     *
     * @var int
     */
    protected $_label_size = 0;

    /**
     * whether to enforce the label size value passed in the constructor
     *
     * @var boolean
     */
    protected $_enforce_label_size = false;

    /**
     * whether to allow multiple selections (ie, the value of this input should be an array)
     * or not (ie, the value should be a simple int, string, etc)
     *
     * @var boolean
     */
    protected $_multiple_selections = false;



    /**
     * @param array     $answer_options
     * @param array     $input_settings {
     * @type int|string $label_size
     * @type boolean    $display_html_label_text
     *                                  }
     *                                  And all the options accepted by FormInputBase
     */
    public function __construct($answer_options = array(), $input_settings = array())
    {
        if (isset($input_settings['label_size'])) {
            $this->_set_label_size($input_settings['label_size']);
            if (isset($input_settings['enforce_label_size']) && $input_settings['enforce_label_size']) {
                $this->_enforce_label_size = true;
            }
        }
        if (isset($input_settings['display_html_label_text'])) {
            $this->set_display_html_label_text($input_settings['display_html_label_text']);
        }
        $this->set_select_options($answer_options);
        parent::__construct($input_settings);
    }



    /**
     * Sets the allowed options for this input. Also has the side-effect of
     * updating the normalization strategy to match the keys provided in the array
     *
     * @param array $answer_options
     * @return void  just has the side-effect of setting the options for this input
     */
    public function set_select_options($answer_options = array())
    {
        $answer_options = is_array($answer_options) ? $answer_options : array($answer_options);
        // get the first item in the select options and check it's type
        $this->_options = $answer_options;
        // d( $this->_options );
        $select_option_keys = array_keys($this->_options);
        // attempt to determine data type for values in order to set normalization type
        // purposefully only
        if (count($this->_options) === 2
            && (
                (in_array(true, $select_option_keys, true) && in_array(false, $select_option_keys, true))
                || (in_array(1, $select_option_keys, true) && in_array(0, $select_option_keys, true))
            )
        ) {
            // values appear to be boolean, like TRUE, FALSE, 1, 0
            $normalization = new BooleanNormalization();
        } else {
            // are ALL the options ints (even if we're using a multi-dimensional array)? If so use int validation
            $all_ints = true;
            array_walk_recursive(
                $this->_options,
                function ($value, $key) use (&$all_ints) {
                    // is this a top-level key? ignore it
                    if (! is_array($value)
                        && ! is_int($key)
                       && $key !== ''
                       && $key !== null) {
                        $all_ints = false;
                    }
                }
            );
            if ($all_ints) {
                $normalization = new IntNormalization();
            } else {
                $normalization = new TextNormalization();
            }
        }
        // does input type have multiple options ?
        if ($this->_multiple_selections) {
            $this->_set_normalization_strategy(new ManyValuedNormalization($normalization));
        } else {
            $this->_set_normalization_strategy($normalization);
        }
    }



    /**
     * @return array
     */
    public function options()
    {
        return $this->_options;
    }



    /**
     * Returns an array which is guaranteed to not be multidimensional
     *
     * @return array
     */
    public function flat_options()
    {
        return $this->_flatten_select_options($this->options());
    }



    /**
     * Makes sure $arr is a flat array, not a multidimensional one
     *
     * @param array $arr
     * @return array
     */
    protected function _flatten_select_options($arr)
    {
        $flat_array = array();
        if (EEH_Array::is_multi_dimensional_array($arr)) {
            foreach ($arr as $sub_array) {
                foreach ((array) $sub_array as $key => $value) {
                    $flat_array[ $key ] = $value;
                    $this->_set_label_size($value);
                }
            }
        } else {
            foreach ($arr as $key => $value) {
                $flat_array[ $key ] = $value;
                $this->_set_label_size($value);
            }
        }
        return $flat_array;
    }

    /**
     *    set_label_sizes
     *
     * @return void
     */
    public function set_label_sizes()
    {
        // did the input settings specifically say to NOT set the label size dynamically ?
        if (! $this->_enforce_label_size) {
            foreach ($this->_options as $option) {
                // calculate the strlen of the label
                $this->_set_label_size($option);
            }
        }
    }



    /**
     *    _set_label_size_class
     *
     * @param int|string $value
     * @return void
     */
    private function _set_label_size($value = '')
    {
        // don't change label size if it has already been set and is being enforced
        if ($this->_enforce_label_size && $this->_label_size >  0) {
            return;
        }
        // determine length of option value
        $val_size = is_int($value) ? $value : strlen($value);
        // use new value if bigger than existing
        $this->_label_size = $val_size > $this->_label_size ? $val_size : $this->_label_size;
    }

    /**
     * Returns the pretty value for the normalized value
     *
     * @return string
     */
    public function pretty_value()
    {
        $options = $this->flat_options();
        $unnormalized_value_choices = $this->get_normalization_strategy()->unnormalize($this->_normalized_value);
        if (! $this->_multiple_selections) {
            $unnormalized_value_choices = array($unnormalized_value_choices);
        }
        $pretty_strings = array();
        foreach ((array) $unnormalized_value_choices as $unnormalized_value_choice) {
            if (isset($options[ $unnormalized_value_choice ])) {
                $pretty_strings[] = $options[ $unnormalized_value_choice ];
            } else {
                $pretty_strings[] = $this->normalized_value();
            }
        }
        return implode(', ', $pretty_strings);
    }



    /**
     * @return boolean
     */
    public function display_html_label_text()
    {
        return $this->_display_html_label_text;
    }



    /**
     * @param boolean $display_html_label_text
     */
    public function set_display_html_label_text($display_html_label_text)
    {
        $this->_display_html_label_text = filter_var($display_html_label_text, FILTER_VALIDATE_BOOLEAN);
    }
}
