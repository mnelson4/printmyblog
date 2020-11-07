<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\RadioButtonDisplay;
use Twine\forms\strategies\validation\EnumValidation;

/**
 * Class Radio_Button_Input
 * configures a set of radio button inputs
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson, Brent Christensen
 * @since                 4.9.51
 */
class RadioButtonInput extends FormInputWithOptionsBase
{

    /**
     * @param array $answer_options
     * @param array $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->_set_display_strategy(new RadioButtonDisplay());
        $this->_add_validation_strategy(
            new EnumValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        $this->_multiple_selections = false;
        parent::__construct($answer_options, $input_settings);
    }
}
