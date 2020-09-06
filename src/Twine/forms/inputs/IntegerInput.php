<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\NumberInputDisplay;
use Twine\forms\strategies\normalization\IntNormalization;
use Twine\forms\strategies\validation\IntValidation;

/**
 * Class Integer_Input
 * Generates an HTML5 number input using integer normalization and validation strategies
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.34
 */
class IntegerInput extends FormInputBase
{


    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        $this->_set_display_strategy(
            new NumberInputDisplay(
                isset($input_settings['min_value'])
                    ? $input_settings['min_value']
                    : null,
                isset($input_settings['max_value'])
                    ? $input_settings['max_value']
                    : null
            )
        );
        $this->_set_normalization_strategy(
            new IntNormalization(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        $this->_add_validation_strategy(
            new IntValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($input_settings);
    }
}
