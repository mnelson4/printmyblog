<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\EmailValidation;

/**
 * Email_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 */
class EmailInput extends FormInputBase
{

    /**
     * @param array $input_settings
     */
    public function __construct($input_settings = array())
    {
        $this->_set_display_strategy(new TextInputDisplay('email'));
        $this->_set_normalization_strategy(new TextNormalization());
        $this->_add_validation_strategy(
            new EmailValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null
            )
        );
        parent::__construct($input_settings);
        $this->set_html_class($this->html_class() . ' email');
    }
}
