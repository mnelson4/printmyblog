<?php
namespace Twine\forms\inputs;
/**
 * Select_Multiple_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class SelectMultipleInput extends FormInputWithOptionsBase
{

    /**
     * @param array | Question_Option[] $answer_options
     * @param array $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->_set_display_strategy(new SelectMultipleDisplay());
        $this->_add_validation_strategy(new ManyValuedValidation(array( new EnumValidation(isset($input_settings['validation_error_message']) ? $input_settings['validation_error_message'] : null) )));
        $this->_multiple_selections = true;
        parent::__construct($answer_options, $input_settings);
    }
}
