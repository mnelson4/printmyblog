<?php
namespace Twine\forms\inputs;
use Twine\forms\strategies\display\TextInputDisplay;
use Twine\forms\strategies\normalization\TextNormalization;
use Twine\forms\strategies\validation\EmailValidation;
use Twine\forms\strategies\validation\EqualToValidation;

/**
 * Email_Confirm_Input
 *
 * @package         Event Espresso
 * @subpackage
 * @since           4.10.5.p
 * @author          Rafael Goulart
 */
class EmailConfirmInput extends FormInputBase
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
        $this->_add_validation_strategy(
            new EqualToValidation(
                isset($input_settings['validation_error_message'])
                    ? $input_settings['validation_error_message']
                    : null,
                '#' . str_replace('email_confirm', 'email', $input_settings['html_id'])
            )
        );
        parent::__construct($input_settings);
        $this->set_html_class($this->html_class() . ' email');
    }
}
