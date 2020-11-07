<?php
namespace Twine\forms\inputs;
use Twine\forms\helpers\ImproperUsageException;
use Twine\forms\helpers\InputOption;
use Twine\forms\strategies\normalization\BooleanNormalization;
use Twine\forms\strategies\normalization\IntNormalization;
use Twine\forms\strategies\normalization\ManyValuedNormalization;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\helpers\Array2;

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
     * @var InputOption[]
     */
    protected $_options = array();

    /**
     * whether to display the html_label_text above the checkbox/radio button options
     *
     * @var boolean
     */
    protected $_display_html_label_text = true;

    /**
     * whether to allow multiple selections (ie, the value of this input should be an array)
     * or not (ie, the value should be a simple int, string, etc)
     *
     * @var boolean
     */
    protected $_multiple_selections = false;



    /**
     * @param InputOption[]     $answer_options
     * @param array     $input_settings {
     * @type int|string $label_size
     * @type boolean    $display_html_label_text
     *                                  }
     *                                  And all the options accepted by FormInputBase
     */
    public function __construct($answer_options = array(), $input_settings = array())
    {
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
     * @param InputOption[] $options
     *
     * @return void  just has the side-effect of setting the options for this input
     */
    public function set_select_options( $options = array())
    {
	    $options = (array) $options;
	    foreach($options as $option){
	    	if(! $option instanceof InputOption){
	    		throw new ImproperUsageException(
	    			sprintf(
	    				__('A form input of type "%s" was passed in an arrya of non-options. It should be given an object of type "%s"', 'print-my-blog'),
				        get_class($this),
					    InputOption::class
				    )
			    );
		    }
	    }
        // get the first item in the select options and check it's type
        $this->_options = $options;
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
     * @return InputOption[]
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
        return $this->options();
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
                $pretty_strings[] = (string)$options[ $unnormalized_value_choice ];
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
