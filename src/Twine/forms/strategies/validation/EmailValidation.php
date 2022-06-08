<?php

namespace Twine\forms\strategies\validation;

use EventEspresso\core\domain\services\factories\EmailAddressFactory;
use EventEspresso\core\domain\services\validation\email\EmailValidationException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use InvalidArgumentException;
use Twine\forms\helpers\ValidationError;

/**
 * Class EmailValidation
 *
 * @package               Event Espresso
 * @subpackage            core
 * @author                Mike Nelson
 * @since                 4.6
 */
class EmailValidation extends TextValidation
{


    /**
     * @param string               $validation_error_message
     */
    public function __construct($validation_error_message = '')
    {
        if (! $validation_error_message) {
            $validation_error_message = esc_html__('Please enter a valid email address.', 'print-my-blog');
        }
        parent::__construct($validation_error_message);
    }



    /**
     * Just checks the field isn't blank
     *
     * @param string $normalized_value
     * @return bool
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if ($normalized_value && ! $this->validateEmail($normalized_value)) {
            throw new ValidationError($this->getValidationErrorMessage(), 'required');
        }
        return true;
    }



    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'email' => true,
            'messages' => array(
                'email' => $this->getValidationErrorMessage(),
            ),
        );
    }



    /**
     * Validate an email address.
     * Provide email address (raw input)
     *
     * @param string $email
     * @return bool of whether the email is valid or not
     */
    private function validateEmail($email)
    {
        return is_email($email);
    }
}
