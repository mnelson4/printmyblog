<?php

namespace Twine\forms\inputs;

use Twine\forms\strategies\validation\TextValidation;

/**
 *
 * Phone_Input
 *
 * Validates that the phone number is either 10 digits, or like
 * 123-123-1231
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 */
class PhoneInput extends TextInput
{
    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->addValidationStrategy(
            new TextValidation(
                __('Please enter a valid phone number. Eg 123-456-7890 or 1234567890', 'print-my-blog'),
                '~^(([\d]{10})|(^[\d]{3}-[\d]{3}-[\d]{4}))$~'
            )
        );
        parent::__construct($options);
    }
}
