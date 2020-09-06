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
            $validation_error_message = esc_html__('Please enter a valid email address.', 'event_espresso');
        }
        parent::__construct($validation_error_message);
    }



    /**
     * just checks the field isn't blank
     *
     * @param $normalized_value
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidInterfaceException
     * @throws InvalidDataTypeException
     * @throws ValidationError
     */
    public function validate($normalized_value)
    {
        if ($normalized_value && ! $this->_validate_email($normalized_value)) {
            throw new ValidationError($this->get_validation_error_message(), 'required');
        }
        return true;
    }



    /**
     * @return array
     */
    public function get_jquery_validation_rule_array()
    {
        return array('email' => true, 'messages' => array('email' => $this->get_validation_error_message()));
    }



    /**
     * Validate an email address.
     * Provide email address (raw input)
     *
     * @param $email
     * @return bool of whether the email is valid or not
     */
    private function _validate_email($email)
    {
        return is_email($email);
    }
}
