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
    protected $options = array();

    /**
     * whether to display the html_label_text above the checkbox/radio button options
     *
     * @var boolean
     */
    protected $display_html_label_text = true;

    /**
     * whether to allow multiple selections (ie, the value of this input should be an array)
     * or not (ie, the value should be a simple int, string, etc)
     *
     * @var boolean
     */
    protected $multiple_selections = false;



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
            $this->setDisplayHtmlLabelText($input_settings['display_html_label_text']);
        }
        $this->setSelectOptions($answer_options);
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
    public function setSelectOptions($options = array())
    {
        $options = (array) $options;
        foreach ($options as $option) {
            if (! $option instanceof InputOption) {
                throw new ImproperUsageException(
                    sprintf(
                        // phpcs:disable Generic.Files.LineLength.TooLong
                        __('A form input of type "%s" was passed in an arrya of non-options. It should be given an object of type "%s"', 'print-my-blog'),
                        // phpcs:enable Generic.Files.LineLength.TooLong
                        get_class($this),
                        InputOption::class
                    )
                );
            }
        }
        // get the first item in the select options and check it's type
        $this->options = $options;
        // d( $this->_options );
        $select_option_keys = array_keys($this->options);
        // attempt to determine data type for values in order to set normalization type
        // purposefully only
        if (
            count($this->options) === 2
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
                $this->options,
                function ($value, $key) use (&$all_ints) {
                    // is this a top-level key? ignore it
                    if (
                        ! is_array($value)
                        && ! is_int($key)
                        && $key !== ''
                        && $key !== null
                    ) {
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
        if ($this->multiple_selections) {
            $this->setNormalizationStrategy(new ManyValuedNormalization($normalization));
        } else {
            $this->setNormalizationStrategy($normalization);
        }
    }



    /**
     * @return InputOption[]
     */
    public function options()
    {
        return $this->options;
    }


    /**
     * Returns an array which is guaranteed to not be multidimensional
     *
     * @return array
     */
    public function flatOptions()
    {
        return $this->options();
    }

    /**
     * Returns the pretty value for the normalized value
     *
     * @return string
     */
    public function prettyValue()
    {
        $options = $this->flatOptions();
        $unnormalized_value_choices = $this->getNormalizationStrategy()->unnormalize($this->normalized_value);
        if (! $this->multiple_selections) {
            $unnormalized_value_choices = array($unnormalized_value_choices);
        }
        $pretty_strings = array();
        foreach ((array) $unnormalized_value_choices as $unnormalized_value_choice) {
            if (isset($options[ $unnormalized_value_choice ])) {
                $pretty_strings[] = (string)$options[ $unnormalized_value_choice ];
            } else {
                $pretty_strings[] = $this->normalizedValue();
            }
        }
        return implode(', ', $pretty_strings);
    }



    /**
     * @return boolean
     */
    public function displayHtmlLabelText()
    {
        return $this->display_html_label_text;
    }



    /**
     * @param boolean $display_html_label_text
     */
    public function setDisplayHtmlLabelText($display_html_label_text)
    {
        $this->display_html_label_text = filter_var($display_html_label_text, FILTER_VALIDATE_BOOLEAN);
    }
}
