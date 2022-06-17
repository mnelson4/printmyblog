<?php

namespace Twine\forms\strategies\validation;

use EventEspresso\core\domain\services\factories\EmailAddressFactory;
use EventEspresso\core\domain\services\validation\email\EmailValidationException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;

/**
 * Class EqualToValidation
 *
 * @package               Event Espresso
 * @subpackage            core
 * @since                 4.10.5.p
 * @author                Rafael Goulart
 */
class EqualToValidation extends TextValidation
{
    /**
     * @var string|null
     */
    protected $compare_to = null;


    /**
     * Construction.
     * @param string $validation_error_message
     * @param string $compare_to
     */
    public function __construct($validation_error_message = '', $compare_to = '')
    {
        if (! $validation_error_message) {
            $validation_error_message = apply_filters(
                'FH_EqualToValidation____construct__validation_error_message',
                esc_html__('Fields do not match.', 'print-my-blog')
            );
        }
        parent::__construct($validation_error_message);
        $this->compare_to = $compare_to;
    }



    /**
     * Just checks the field isn't blank
     *
     * @param string $normalized_value
     * @return bool
     */
    public function validate($normalized_value)
    {
        // No need to be validated
        return true;
    }



    /**
     * @return array
     */
    public function getJqueryValidationRuleArray()
    {
        return array(
            'equalTo' => $this->compare_to,
            'messages' => array(
                'equalTo' => $this->getValidationErrorMessage(),
            ),
        );
    }
}
