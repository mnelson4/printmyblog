<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\NumberInputDisplay;
use Twine\forms\strategies\normalization\FloatNormalization;
use Twine\forms\strategies\validation\FloatValidation;

/**
 * Float_Input
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class FloatInput extends FormInputBase
{

    /**
     * @param array $input_settings
     * @throws InvalidArgumentException
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
                    : null,
                isset($input_settings['step_value'])
                    ? $input_settings['step_value']
                    : null
            )
        );
        $this->_set_normalization_strategy(new FloatNormalization());
        $this->_add_validation_strategy(
            new FloatValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($input_settings);
    }
}