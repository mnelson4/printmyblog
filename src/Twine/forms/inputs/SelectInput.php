<?php
namespace Twine\forms\inputs;
/**
 * Class Select_Input
 *
 * Generates an HTML <select> form input
 *
 * @package             Event Espresso
 * @subpackage  core
 * @author              Mike Nelson
 * @since               4.6
 *
 */
class SelectInput extends FormInputWithOptionsBase
{

    /**
     * @param array $answer_options
     * @param array $input_settings
     */
    public function __construct($answer_options, $input_settings = array())
    {
        $this->_set_display_strategy(new SelectDisplay($answer_options));
        $this->_add_validation_strategy(
            new EnumValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($answer_options, $input_settings);
    }
}
